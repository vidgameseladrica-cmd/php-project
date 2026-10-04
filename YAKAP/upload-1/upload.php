<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/simple_xlsx_reader.php';

$dbHost = 'localhost';
$dbName = 'yakap';
$dbUser = 'root';
$dbPass = '';

$message = '';
$messageType = '';
$preview = [];
$errors = [];

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function normalize_header(string $header): string {
    $header = strtolower(trim($header));
    $header = preg_replace('/[^a-z0-9]+/', '_', $header);
    return trim($header, '_');
}

function money_value($value): ?float {
    if ($value === null || trim((string)$value) === '') {
        return null;
    }

    $value = str_replace([',', '₱', 'PHP'], '', trim((string)$value));

    return is_numeric($value) ? (float)$value : null;
}

function integer_value($value): ?int {
    if ($value === null || trim((string)$value) === '') {
        return null;
    }

    $value = trim((string)$value);

    // Accept values such as 100 or 100.0, but reject decimals such as 10.5.
    if (!preg_match('/^\d+(?:\.0+)?$/', $value)) {
        return null;
    }

    return (int)$value;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['excel_file'])) {
        $errors[] = 'Please select an Excel file.';
    } else {

        $file = $_FILES['excel_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload failed. Error code: ' . $file['error'];

        } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'xlsx') {
            $errors[] = 'This importer accepts .xlsx files only.';

        } elseif ($file['size'] > 10 * 1024 * 1024) {
            $errors[] = 'Maximum file size is 10 MB.';

        } else {

            try {

                $rows = xlsx_read($file['tmp_name']);

                if (count($rows) < 2) {
                    throw new RuntimeException('The Excel file has no medicine records.');
                }

                /*
                 * Expected Excel columns:
                 * Medicine Name
                 * Generic Name
                 * Dosage Form
                 * Current Stock
                 * Reorder Level
                 * Unit Price
                 * Status
                 */
                $headers = $rows[0];
                $map = [];

                foreach ($headers as $col => $header) {
                    $map[normalize_header((string)$header)] = $col;
                }

                $requiredColumns = [
                    'medicine_name',
                    'generic_name',
                    'dosage_form',
                    'current_stock',
                    'reorder_level',
                    'unit_price',
                    'status'
                ];

                foreach ($requiredColumns as $required) {
                    if (!isset($map[$required])) {
                        $errors[] = 'Missing required column: ' . $required;
                    }
                }

                if (!$errors) {

                    for ($i = 1; $i < count($rows); $i++) {

                        $row = $rows[$i];
                        $excelRow = $i + 1;

                        $medicineName = trim((string)($row[$map['medicine_name']] ?? ''));
                        $genericName  = trim((string)($row[$map['generic_name']] ?? ''));
                        $dosageForm   = trim((string)($row[$map['dosage_form']] ?? ''));
                        $currentStock = integer_value($row[$map['current_stock']] ?? null);
                        $reorderLevel = integer_value($row[$map['reorder_level']] ?? null);
                        $unitPrice    = money_value($row[$map['unit_price']] ?? null);
                        $status       = trim((string)($row[$map['status']] ?? 'Active'));

                        // Ignore completely blank rows.
                        if (
                            $medicineName === '' &&
                            $genericName === '' &&
                            $dosageForm === '' &&
                            $currentStock === null &&
                            $reorderLevel === null &&
                            $unitPrice === null &&
                            $status === ''
                        ) {
                            continue;
                        }

                        if ($medicineName === '') {
                            $errors[] = "Row {$excelRow}: Medicine Name is required.";
                            continue;
                        }

                        if ($currentStock === null || $currentStock < 0) {
                            $errors[] = "Row {$excelRow}: Current Stock must be a whole number greater than or equal to 0.";
                            continue;
                        }

                        if ($reorderLevel === null || $reorderLevel < 0) {
                            $errors[] = "Row {$excelRow}: Reorder Level must be a whole number greater than or equal to 0.";
                            continue;
                        }

                        if ($unitPrice === null || $unitPrice < 0) {
                            $errors[] = "Row {$excelRow}: Unit Price must be a valid number greater than or equal to 0.";
                            continue;
                        }

                        if ($status === '') {
                            $status = 'Active';
                        }

                        if (!in_array($status, ['Active', 'Inactive'], true)) {
                            $errors[] = "Row {$excelRow}: Status must be Active or Inactive.";
                            continue;
                        }

                        $preview[] = [
                            'row' => $excelRow,
                            'medicine_name' => $medicineName,
                            'generic_name' => $genericName,
                            'dosage_form' => $dosageForm,
                            'current_stock' => $currentStock,
                            'reorder_level' => $reorderLevel,
                            'unit_price' => $unitPrice,
                            'status' => $status
                        ];
                    }
                }

                /*
                 * Insert into:
                 *   medicines          -> medicine details, price, status
                 *   medicine_inventory -> current stock and reorder level
                 *
                 * This matches the database structure in yakap.sql.
                 */
                if (!$errors && $preview) {

                    $pdo = new PDO(
                        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
                        $dbUser,
                        $dbPass,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_EMULATE_PREPARES => false
                        ]
                    );

                    $pdo->beginTransaction();

                    $check = $pdo->prepare(
                        "SELECT id
                         FROM medicines
                         WHERE LOWER(TRIM(medicine_name)) = LOWER(TRIM(?))
                         LIMIT 1"
                    );

                    $insertMedicine = $pdo->prepare(
                        "INSERT INTO medicines
                         (medicine_name, generic_name, dosage_form, unit_price, status)
                         VALUES (?, ?, ?, ?, ?)"
                    );

                    $insertInventory = $pdo->prepare(
                        "INSERT INTO medicine_inventory
                         (medicine_id, quantity, reorder_level)
                         VALUES (?, ?, ?)"
                    );

                    $imported = 0;
                    $duplicate = 0;

                    foreach ($preview as $item) {

                        $check->execute([$item['medicine_name']]);

                        if ($check->fetchColumn()) {
                            $duplicate++;
                            continue;
                        }

                        $insertMedicine->execute([
                            $item['medicine_name'],
                            $item['generic_name'] !== '' ? $item['generic_name'] : null,
                            $item['dosage_form'] !== '' ? $item['dosage_form'] : null,
                            $item['unit_price'],
                            $item['status']
                        ]);

                        $medicineId = (int)$pdo->lastInsertId();

                        $insertInventory->execute([
                            $medicineId,
                            $item['current_stock'],
                            $item['reorder_level']
                        ]);

                        $imported++;
                    }

                    $pdo->commit();

                    $messageType = $imported > 0 ? 'success' : 'warning';
                    $message = "{$imported} imported, {$duplicate} duplicate(s) skipped.";

                    $preview = [];

                } elseif (!$errors) {
                    $errors[] = 'No valid medicine rows were found.';
                }

            } catch (Throwable $ex) {

                if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $errors[] = $ex->getMessage();
            }
        }
    }

    if ($errors) {
        $messageType = 'danger';
        $message = implode('<br>', array_map('e', $errors));
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>YAKAP - Medicine Import</title>

<style>
*{box-sizing:border-box}
body{
    margin:0;
    background:#f4f7fb;
    font-family:Arial,Helvetica,sans-serif;
    color:#243447
}
.wrap{
    max-width:1200px;
    margin:40px auto;
    padding:0 15px
}
.card{
    background:#fff;
    border-radius:16px;
    box-shadow:0 8px 30px rgba(0,0,0,.08);
    overflow:hidden
}
.head{
    background:#0d6efd;
    color:#fff;
    padding:24px 28px
}
.head h2{margin:0 0 6px}
.head p{margin:0;opacity:.9}
.body{padding:28px}
.alert{
    padding:14px 16px;
    border-radius:9px;
    margin-bottom:20px
}
.success{background:#d1e7dd;color:#0f5132}
.danger{background:#f8d7da;color:#842029}
.warning{background:#fff3cd;color:#664d03}
.btn{
    display:inline-block;
    padding:11px 17px;
    border-radius:8px;
    text-decoration:none;
    border:0;
    cursor:pointer;
    font-weight:600
}
.primary{background:#0d6efd;color:#fff}
.outline{background:#fff;color:#0d6efd;border:1px solid #0d6efd}
.upload{
    border:2px dashed #b8c7dc;
    padding:35px;
    text-align:center;
    border-radius:12px;
    background:#fafcff;
    margin:20px 0
}
input[type=file]{
    display:block;
    width:100%;
    padding:10px;
    margin:15px 0
}
.table-wrap{
    overflow-x:auto
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:18px
}
th,td{
    border:1px solid #dee2e6;
    padding:9px;
    text-align:left;
    white-space:nowrap
}
th{background:#eef3f8}
.note{
    font-size:13px;
    color:#6c757d
}
code{
    background:#f1f3f5;
    padding:2px 5px;
    border-radius:4px
}
</style>
</head>

<body>
<div class="wrap">
<div class="card">

<div class="head">
    <h2>YAKAP Medicine Excel Import</h2>
    <p>
        Excel Import →
        <code style="background:rgba(255,255,255,.15);color:#fff">
            yakap.medicines
        </code>
        +
        <code style="background:rgba(255,255,255,.15);color:#fff">
            yakap.medicine_inventory
        </code>
    </p>
</div>

<div class="body">

<?php if ($message): ?>
<div class="alert <?= e($messageType) ?>">
    <?= $message ?>
</div>
<?php endif; ?>

<h3>1. Excel Template</h3>

<p class="note">
    Use the updated medicine template and keep the column names unchanged.
</p>

<a class="btn outline" href="medicine_template_updated.xlsx">
    Download Medicine Template
</a>

<hr style="border:0;border-top:1px solid #eee;margin:28px 0">

<h3>2. Upload XLSX</h3>

<form method="post" enctype="multipart/form-data">

<div class="upload">
    <strong>Select your Excel file</strong>

    <input
        type="file"
        name="excel_file"
        accept=".xlsx"
        required
    >

    <div class="note">
        Supported: .xlsx only • Maximum 10 MB
    </div>
</div>

<button class="btn primary" type="submit">
    Upload & Import Medicines
</button>

</form>

<?php if ($preview): ?>

<h3>Preview</h3>

<div class="table-wrap">
<table>
<thead>
<tr>
    <th>Row</th>
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

<?php foreach ($preview as $item): ?>

<tr>
    <td><?= (int)$item['row'] ?></td>
    <td><?= e($item['medicine_name']) ?></td>
    <td><?= e($item['generic_name']) ?></td>
    <td><?= e($item['dosage_form']) ?></td>
    <td><?= (int)$item['current_stock'] ?></td>
    <td><?= (int)$item['reorder_level'] ?></td>
    <td>₱<?= number_format((float)$item['unit_price'], 2) ?></td>
    <td><?= e($item['status']) ?></td>
</tr>

<?php endforeach; ?>

</tbody>
</table>
</div>

<?php endif; ?>

<hr style="border:0;border-top:1px solid #eee;margin:28px 0">

<div class="note">
    <strong>Excel Columns:</strong><br>
    Medicine Name → medicine_name<br>
    Generic Name → generic_name<br>
    Dosage Form → dosage_form<br>
    Current Stock → current_stock<br>
    Reorder Level → reorder_level<br>
    Unit Price → unit_price<br>
    Status → status
    <br><br>

    <strong>Database mapping:</strong><br>
    Medicine details are saved to <code>medicines</code>.
    Current Stock and Reorder Level are saved to
    <code>medicine_inventory</code>.
    <br><br>

    Existing medicine names are checked case-insensitively and duplicates are skipped.
</div>

</div>
</div>
</div>
</body>
</html>
