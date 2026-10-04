<?php

declare(strict_types=1);

$pageTitle = 'Import Medicine Inventory';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';


// ---------------------------------------------------------
// SETTINGS
// ---------------------------------------------------------

$success = '';
$error = '';

$imported = 0;
$updated = 0;
$skipped = 0;
$errors = [];


// ---------------------------------------------------------
// REQUIRED CSV HEADERS
// ---------------------------------------------------------

$requiredHeaders = [
    'Medicine Name',
    'Generic Name',
    'Dosage Form',
    'Current Stock',
    'Reorder Level',
    'Unit Price',
    'Status'
];


// ---------------------------------------------------------
// PROCESS UPLOAD
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_FILES['csv_file']) ||
        $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK
    ) {

        $error = 'Please select a CSV file to upload.';

    } else {

        $file = $_FILES['csv_file'];

        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );


        if ($extension !== 'csv') {

            $error = 'Invalid file format. Please upload a CSV file.';

        } elseif ($file['size'] > 10 * 1024 * 1024) {

            $error = 'File is too large. Maximum allowed size is 10 MB.';

        } else {

            $handle = fopen(
                $file['tmp_name'],
                'r'
            );


            if ($handle === false) {

                $error = 'Unable to open the uploaded CSV file.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | READ HEADER
                |--------------------------------------------------------------------------
                */

                $header = fgetcsv($handle);


                if ($header === false) {

                    $error = 'The CSV file is empty.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | REMOVE UTF-8 BOM
                    |--------------------------------------------------------------------------
                    */

                    if (
                        isset($header[0])
                        && str_starts_with(
                            $header[0],
                            "\xEF\xBB\xBF"
                        )
                    ) {

                        $header[0] = substr(
                            $header[0],
                            3
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CLEAN HEADERS
                    |--------------------------------------------------------------------------
                    */

                    $header = array_map(
                        static function ($value) {

                            return trim(
                                (string)$value
                            );

                        },
                        $header
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | CHECK REQUIRED HEADERS
                    |--------------------------------------------------------------------------
                    */

                    $missingHeaders = [];

                    foreach ($requiredHeaders as $required) {

                        if (!in_array(
                            $required,
                            $header,
                            true
                        )) {

                            $missingHeaders[] = $required;

                        }

                    }


                    if (!empty($missingHeaders)) {

                        $error =
                            'Invalid CSV template. Missing column(s): '
                            .
                            implode(
                                ', ',
                                $missingHeaders
                            );

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | CREATE HEADER MAP
                        |--------------------------------------------------------------------------
                        */

                        $headerMap = [];

                        foreach ($header as $index => $name) {

                            $headerMap[$name] = $index;

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | PREPARE SQL
                        |--------------------------------------------------------------------------
                        */

                        $findMedicine = $pdo->prepare("
                            SELECT id
                            FROM medicines
                            WHERE medicine_name = ?
                            LIMIT 1
                        ");


                        $insertMedicine = $pdo->prepare("
                            INSERT INTO medicines
                            (
                                medicine_name,
                                generic_name,
                                dosage_form,
                                unit_price,
                                status
                            )
                            VALUES (?, ?, ?, ?, ?)
                        ");


                        $updateMedicine = $pdo->prepare("
                            UPDATE medicines
                            SET
                                generic_name = ?,
                                dosage_form = ?,
                                unit_price = ?,
                                status = ?
                            WHERE id = ?
                        ");


                        $findInventory = $pdo->prepare("
                            SELECT id
                            FROM medicine_inventory
                            WHERE medicine_id = ?
                            LIMIT 1
                        ");


                        $insertInventory = $pdo->prepare("
                            INSERT INTO medicine_inventory
                            (
                                medicine_id,
                                quantity,
                                reorder_level
                            )
                            VALUES (?, ?, ?)
                        ");


                        $updateInventory = $pdo->prepare("
                            UPDATE medicine_inventory
                            SET
                                quantity = ?,
                                reorder_level = ?
                            WHERE medicine_id = ?
                        ");


                        /*
                        |--------------------------------------------------------------------------
                        | READ CSV ROWS
                        |--------------------------------------------------------------------------
                        */

                        $rowNumber = 1;


                        while (
                            ($row = fgetcsv($handle))
                            !== false
                        ) {

                            $rowNumber++;


                            /*
                            | Skip completely empty rows
                            */

                            $hasData = false;

                            foreach ($row as $value) {

                                if (trim((string)$value) !== '') {

                                    $hasData = true;

                                    break;

                                }

                            }


                            if (!$hasData) {

                                continue;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | GET VALUES
                            |--------------------------------------------------------------------------
                            */

                            $medicineName = trim(
                                (string)(
                                    $row[
                                        $headerMap['Medicine Name']
                                    ] ?? ''
                                )
                            );


                            $genericName = trim(
                                (string)(
                                    $row[
                                        $headerMap['Generic Name']
                                    ] ?? ''
                                )
                            );


                            $dosageForm = trim(
                                (string)(
                                    $row[
                                        $headerMap['Dosage Form']
                                    ] ?? ''
                                )
                            );


                            $currentStockRaw = trim(
                                (string)(
                                    $row[
                                        $headerMap['Current Stock']
                                    ] ?? ''
                                )
                            );


                            $reorderLevelRaw = trim(
                                (string)(
                                    $row[
                                        $headerMap['Reorder Level']
                                    ] ?? ''
                                )
                            );


                            $unitPriceRaw = trim(
                                (string)(
                                    $row[
                                        $headerMap['Unit Price']
                                    ] ?? ''
                                )
                            );


                            $status = trim(
                                (string)(
                                    $row[
                                        $headerMap['Status']
                                    ] ?? ''
                                )
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | VALIDATE MEDICINE NAME
                            |--------------------------------------------------------------------------
                            */

                            if ($medicineName === '') {

                                $errors[] =
                                    "Row {$rowNumber}: Medicine Name is required.";

                                $skipped++;

                                continue;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | VALIDATE STOCK
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $currentStockRaw === ''
                                ||
                                !is_numeric($currentStockRaw)
                            ) {

                                $errors[] =
                                    "Row {$rowNumber}: Current Stock must be a number.";

                                $skipped++;

                                continue;

                            }


                            $currentStock =
                                (int)$currentStockRaw;


                            if ($currentStock < 0) {

                                $errors[] =
                                    "Row {$rowNumber}: Current Stock cannot be negative.";

                                $skipped++;

                                continue;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | VALIDATE REORDER LEVEL
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $reorderLevelRaw === ''
                                ||
                                !is_numeric($reorderLevelRaw)
                            ) {

                                $errors[] =
                                    "Row {$rowNumber}: Reorder Level must be a number.";

                                $skipped++;

                                continue;

                            }


                            $reorderLevel =
                                (int)$reorderLevelRaw;


                            if ($reorderLevel < 0) {

                                $errors[] =
                                    "Row {$rowNumber}: Reorder Level cannot be negative.";

                                $skipped++;

                                continue;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | VALIDATE UNIT PRICE
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $unitPriceRaw === ''
                                ||
                                !is_numeric($unitPriceRaw)
                            ) {

                                $errors[] =
                                    "Row {$rowNumber}: Unit Price must be a number.";

                                $skipped++;

                                continue;

                            }


                            $unitPrice =
                                (float)$unitPriceRaw;


                            if ($unitPrice < 0) {

                                $errors[] =
                                    "Row {$rowNumber}: Unit Price cannot be negative.";

                                $skipped++;

                                continue;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | VALIDATE STATUS
                            |--------------------------------------------------------------------------
                            */

                            if ($status === '') {

                                $status = 'Active';

                            }


                            if (!in_array(
                                $status,
                                ['Active', 'Inactive'],
                                true
                            )) {

                                $errors[] =
                                    "Row {$rowNumber}: Status must be Active or Inactive.";

                                $skipped++;

                                continue;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | DATABASE TRANSACTION
                            |--------------------------------------------------------------------------
                            */

                            try {

                                $pdo->beginTransaction();


                                /*
                                |--------------------------------------------------------------------------
                                | CHECK MEDICINE
                                |--------------------------------------------------------------------------
                                */

                                $findMedicine->execute([
                                    $medicineName
                                ]);

                                $existing =
                                    $findMedicine->fetch();


                                if ($existing) {

                                    /*
                                    | Existing medicine
                                    */

                                    $medicineId =
                                        (int)$existing['id'];


                                    $updateMedicine->execute([

                                        $genericName,

                                        $dosageForm,

                                        $unitPrice,

                                        $status,

                                        $medicineId

                                    ]);


                                    $updated++;

                                } else {

                                    /*
                                    | New medicine
                                    */

                                    $insertMedicine->execute([

                                        $medicineName,

                                        $genericName,

                                        $dosageForm,

                                        $unitPrice,

                                        $status

                                    ]);


                                    $medicineId =
                                        (int)$pdo->lastInsertId();


                                    $imported++;

                                }


                                /*
                                |--------------------------------------------------------------------------
                                | CHECK INVENTORY
                                |--------------------------------------------------------------------------
                                */

                                $findInventory->execute([
                                    $medicineId
                                ]);

                                $inventory =
                                    $findInventory->fetch();


                                if ($inventory) {

                                    $updateInventory->execute([

                                        $currentStock,

                                        $reorderLevel,

                                        $medicineId

                                    ]);

                                } else {

                                    $insertInventory->execute([

                                        $medicineId,

                                        $currentStock,

                                        $reorderLevel

                                    ]);

                                }


                                $pdo->commit();


                            } catch (Throwable $e) {

                                if (
                                    $pdo->inTransaction()
                                ) {

                                    $pdo->rollBack();

                                }


                                $errors[] =
                                    "Row {$rowNumber}: Unable to save medicine.";

                                $skipped++;

                            }

                        }


                        fclose($handle);


                        if (
                            $imported > 0
                            ||
                            $updated > 0
                        ) {

                            $success =
                                "Import completed. "
                                .
                                "{$imported} new medicine(s) added, "
                                .
                                "{$updated} medicine(s) updated.";

                        } elseif (empty($errors)) {

                            $error =
                                'No medicine records were found in the CSV file.';

                        }

                    }

                }


                if (is_resource($handle)) {

                    fclose($handle);

                }

            }

        }

    }

}

?>


<div class="card-panel">


    <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h5 class="mb-1">
                Import Medicine Inventory
            </h5>

            <small class="text-muted">
                Upload medicine records using CSV template
            </small>

        </div>


        <a
            href="index.php"
            class="btn btn-secondary">

            ← Back to Inventory

        </a>

    </div>


    <!-- SUCCESS -->

    <?php if ($success !== ''): ?>

        <div class="alert alert-success">

            <strong>
                Import Successful
            </strong>

            <br>

            <?= htmlspecialchars($success) ?>

        </div>

    <?php endif; ?>


    <!-- ERROR -->

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- IMPORT ERRORS -->

    <?php if (!empty($errors)): ?>

        <div class="alert alert-warning">

            <strong>
                Some records were skipped:
            </strong>

            <ul class="mb-0 mt-2">

                <?php foreach ($errors as $importError): ?>

                    <li>
                        <?= htmlspecialchars($importError) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <!-- CSV FORMAT -->

    <div class="alert alert-info">

        <strong>
            CSV Template Format
        </strong>

        <p class="mb-2 mt-2">
            Your CSV file must contain the following columns:
        </p>


        <code>

            Medicine Name,
            Generic Name,
            Dosage Form,
            Current Stock,
            Reorder Level,
            Unit Price,
            Status

        </code>

        <hr>


        <strong>
            Example:
        </strong>


        <div class="table-responsive mt-2">

            <table class="table table-sm table-bordered mb-0">

                <thead>

                    <tr>

                        <th>Medicine Name</th>
                        <th>Generic Name</th>
                        <th>Dosage Form</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Unit Price</th>
                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>

                    <tr>

                        <td>Paracetamol 500mg</td>

                        <td>Paracetamol</td>

                        <td>Tablet</td>

                        <td>100</td>

                        <td>20</td>

                        <td>2.50</td>

                        <td>Active</td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>


    <!-- UPLOAD FORM -->

    <form
        method="post"
        enctype="multipart/form-data">


        <div class="mb-3">

            <label class="form-label">

                Select CSV File *

            </label>


            <input
                type="file"
                name="csv_file"
                class="form-control"
                accept=".csv,text/csv"
                required>


            <div class="form-text">

                Maximum file size: 10 MB.

            </div>

        </div>


        <div class="d-flex gap-2">


            <button
                type="submit"
                class="btn btn-primary">

                📥 Import CSV

            </button>


            <a
                href="index.php"
                class="btn btn-secondary">

                Cancel

            </a>

        </div>


    </form>


</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>