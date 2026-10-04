<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO medicines (medicine_name,generic_name,dosage_form,unit_price) VALUES (?,?,?,?)");
    $stmt->execute([
        trim($_POST['medicine_name'] ?? ''),
        trim($_POST['generic_name'] ?? ''),
        trim($_POST['dosage_form'] ?? ''),
        (float)($_POST['unit_price'] ?? 0)
    ]);
    $id = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO medicine_inventory (medicine_id,quantity,reorder_level) VALUES (?,?,?)");
    $stmt->execute([$id, (int)($_POST['quantity'] ?? 0), (int)($_POST['reorder_level'] ?? 10)]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    exit('Unable to save medicine.');
}
header('Location: index.php');
exit;
