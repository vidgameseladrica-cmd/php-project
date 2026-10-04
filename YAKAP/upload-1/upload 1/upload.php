<?php
/**
 * upload.php
 * Import medicines from Excel into the yakap.medicines table.
 *
 * Requirements:
 * 1. PHP 7.4+ / PHP 8.x
 * 2. MySQL / MariaDB
 * 3. PhpSpreadsheet:
 *      composer require phpoffice/phpspreadsheet
 *
 * Expected Excel columns:
 * Medicine Name | Generic Name | Dosage Form | Unit Price | Status
 */

declare(strict_types=1);

session_start();

$dbHost = 'localhost';
$dbName = 'yakap';
$dbUser = 'root';
$dbPass = '';

$message = '';
$messageType = '';
$previewRows = [];
$imported = 0;
$duplicates = 0;
$errors = [];

$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    $message = 'PhpSpreadsheet is not installed. Run: composer require phpoffice/phpspreadsheet';
    $messageType = 'danger';
} else {
    require $autoload;
}

function cleanText($value): string
{
    return trim((string)$value);
}

function normalizeHeader(string $header): string
{
    $header = strtolower(trim($header));
    $header = preg_replace('/[^a-z0-9]+/', '_', $header);
    return trim($header, '_');
}

function moneyValue($value): ?float
{
    if ($value === null || trim((string)$value) === '') {
        return null;
    }

    $value = str_replace([',', '₱', 'PHP'], '', trim((string)$value));

    return is_numeric($value) ? (float)$value : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file']) && empty($message)) {
    $file = $_FILES['excel_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload failed. Please select a valid Excel file.';
    } else {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, ['xlsx', 'xls'], true)) {
            $errors[] = 'Only .xlsx and .xls files are allowed.';
        } elseif ($file['size'] > 10 * 1024 * 1024) {
            $errors[] = 'Maximum file size is 10 MB.';
        } else {
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file['tmp_name']);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file['tmp_name']);
                $sheet = $spreadsheet->getActiveSheet();

                $rows = $sheet->toArray(null, true, true, true);

                if (count($rows) < 2) {
                    $errors[] = 'The Excel file has no medicine records.';
                } else {
                    $headers = array_shift($rows);
                    $map = [];

                    foreach ($headers as $column => $header) {
                        $map[normalizeHeader((string)$header)] = $column;
                    }

                    $required = ['medicine_name', 'unit_price'];
                    foreach ($required as $requiredHeader) {
                        if (!isset($map[$requiredHeader])) {
                            $errors[] = "Missing required column: {$requiredHeader}";
                        }
                    }

                    if (!$errors) {
                        foreach ($rows as $rowNumber => $row) {
                            $excelRow = $rowNumber + 2;

                            $medicineName = cleanText($row[$map['medicine_name']] ?? '');
                            $genericName  = cleanText($row[$map['generic_name']] ?? '');
                            $dosageForm   = cleanText($row[$map['dosage_form']] ?? '');
                            $unitPrice     = moneyValue($row[$map['unit_price']] ?? null);
                            $status        = cleanText($row[$map['status']] ?? 'Active');

                            // Ignore completely empty rows.
                            if ($medicineName === '' && $genericName === '' && $dosageForm === '' &&
                                ($unitPrice === null || $unitPrice == 0) && $status === '') {
                                continue;
                            }

                            if ($medicineName === '') {
                                $errors[] = "Row {$excelRow}: Medicine Name is required.";
                                continue;
                            }

                            if ($unitPrice === null || $unitPrice < 0) {
                                $errors[] = "Row {$excelRow}: Unit Price must be a valid non-negative number.";
                                continue;
                            }

                            if ($status === '') {
                                $status = 'Active';
                            }

                            if (!in_array($status, ['Active', 'Inactive'], true)) {
                                $errors[] = "Row {$excelRow}: Status must be Active or Inactive.";
                                continue;
                            }

                            $previewRows[] = [
                                'row' => $excelRow,
                                'medicine_name' => $medicineName,
                                'generic_name' => $genericName,
                                'dosage_form' => $dosageForm,
                                'unit_price' => $unitPrice,
                                'status' => $status
                            ];
                        }
                    }
                }

                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            } catch (Throwable $e) {
                $errors[] = 'Unable to read the Excel file: ' . $e->getMessage();
            }
        }
    }

    // Save immediately after successful validation.
    // Remove this block if you want a separate confirmation page before INSERT.
    if (!$errors && $previewRows) {
        try {
            $pdo = new PDO(
                "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

            $check = $pdo->prepare(
                "SELECT id FROM medicines WHERE LOWER(TRIM(medicine_name)) = LOWER(TRIM(?)) LIMIT 1"
            );

            $insert = $pdo->prepare(
                "INSERT INTO medicines
                (medicine_name, generic_name, dosage_form, unit_price, status)
                VALUES (?, ?, ?, ?, ?)"
            );

            foreach ($previewRows as $item) {
                $check->execute([$item['medicine_name']]);

                if ($check->fetch()) {
                    $duplicates++;
                    continue;
                }

                $insert->execute([
                    $item['medicine_name'],
                    $item['generic_name'] !== '' ? $item['generic_name'] : null,
                    $item['dosage_form'] !== '' ? $item['dosage_form'] : null,
                    $item['unit_price'],
                    $item['status']
                ]);

                $imported++;
            }

            if ($imported > 0 && $duplicates > 0) {
                $message = "{$imported} medicine(s) imported. {$duplicates} duplicate(s) skipped.";
                $messageType = 'warning';
            } elseif ($imported > 0) {
                $message = "{$imported} medicine(s) successfully imported.";
                $messageType = 'success';
            } elseif ($duplicates > 0) {
                $message = "No new medicine imported. {$duplicates} duplicate(s) skipped.";
                $messageType = 'warning';
            }

            $previewRows = [];
        } catch (Throwable $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

if ($errors) {
    $message = implode('<br>', array_map('htmlspecialchars', $errors));
    $messageType = 'danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Medicine Excel Import - YAKAP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background: #f4f7fb;
        font-family: Arial, sans-serif;
    }
    .container-main {
        max-width: 1100px;
        margin: 40px auto;
    }
    .card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08);
    }
    .header {
        background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        color: #fff;
        border-radius: 16px 16px 0 0;
        padding: 25px;
    }
    .upload-box {
        border: 2px dashed #b8c7dc;
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        background: #fafcff;
    }
    .table th {
        background: #f0f4f9;
        white-space: nowrap;
    }
    .small-note {
        font-size: 13px;
        color: #6c757d;
    }
</style>
</head>
<body>

<div class="container-main">
    <div class="card">
        <div class="header">
            <h3 class="mb-1">Medicine Excel Import</h3>
            <div>Upload medicine master data into the <strong>yakap.medicines</strong> table.</div>
        </div>

        <div class="p-4">

            <?php if ($message): ?>
                <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Step 1 — Download Template</h5>
                    <div class="small-note">Use the supplied template and keep the column headers unchanged.</div>
                </div>
                <a href="medicine_template.xlsx" class="btn btn-outline-primary">
                    Download Excel Template
                </a>
            </div>

            <hr>

            <form method="POST" enctype="multipart/form-data">
                <h5 class="mb-3">Step 2 — Upload Excel File</h5>

                <div class="upload-box mb-3">
                    <div class="mb-3">
                        <strong>Select Medicine Excel File</strong>
                    </div>
                    <input type="file"
                           name="excel_file"
                           class="form-control"
                           accept=".xlsx,.xls"
                           required>
                    <div class="small-note mt-2">
                        Allowed: XLSX / XLS &nbsp; | &nbsp; Maximum: 10 MB
                    </div>
                </div>

                <button type="submit" class="btn btn-primary px-4">
                    Upload & Import Medicines
                </button>
            </form>

            <?php if ($previewRows): ?>
                <hr class="my-4">

                <h5>Import Preview</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Excel Row</th>
                                <th>Medicine Name</th>
                                <th>Generic Name</th>
                                <th>Dosage Form</th>
                                <th>Unit Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($previewRows as $item): ?>
                            <tr>
                                <td><?= (int)$item['row'] ?></td>
                                <td><?= htmlspecialchars($item['medicine_name']) ?></td>
                                <td><?= htmlspecialchars($item['generic_name']) ?></td>
                                <td><?= htmlspecialchars($item['dosage_form']) ?></td>
                                <td>₱<?= number_format((float)$item['unit_price'], 2) ?></td>
                                <td><?= htmlspecialchars($item['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <hr class="my-4">

            <div class="alert alert-info mb-0">
                <strong>Database mapping:</strong><br>
                Medicine Name → <code>medicine_name</code><br>
                Generic Name → <code>generic_name</code><br>
                Dosage Form → <code>dosage_form</code><br>
                Unit Price → <code>unit_price</code><br>
                Status → <code>status</code><br>
                <code>id</code> and <code>created_at</code> are generated automatically by MySQL.
            </div>

        </div>
    </div>
</div>

</body>
</html>
