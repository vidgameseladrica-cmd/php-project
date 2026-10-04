<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }

$patientId=(int)($_POST['patient_id']??0);
$medicineId=(int)($_POST['medicine_id']??0);
$qty=(int)($_POST['quantity']??0);

$pdo->beginTransaction();
try {
    $stmt=$pdo->prepare("SELECT m.unit_price, i.quantity FROM medicines m JOIN medicine_inventory i ON i.medicine_id=m.id WHERE m.id=? FOR UPDATE");
    $stmt->execute([$medicineId]);
    $m=$stmt->fetch();
    if (!$m || $qty < 1 || $qty > (int)$m['quantity']) throw new RuntimeException('Insufficient stock.');

    $total=$qty*(float)$m['unit_price'];
    $transaction='TXN-'.date('YmdHis').random_int(100,999);

    $stmt=$pdo->prepare("INSERT INTO medicine_purchases (transaction_no,patient_id,pharmacist_id,total_amount,reference_no) VALUES (?,?,?,?,?)");
    $stmt->execute([$transaction,$patientId,$_SESSION['user_id'],$total,trim($_POST['reference_no']??'')]);
    $purchaseId=(int)$pdo->lastInsertId();

    $stmt=$pdo->prepare("INSERT INTO medicine_purchase_items (purchase_id,medicine_id,quantity,unit_price,total_price) VALUES (?,?,?,?,?)");
    $stmt->execute([$purchaseId,$medicineId,$qty,(float)$m['unit_price'],$total]);

    $stmt=$pdo->prepare("UPDATE medicine_inventory SET quantity=quantity-? WHERE medicine_id=?");
    $stmt->execute([$qty,$medicineId]);

    $pdo->commit();
} catch(Throwable $e) {
    $pdo->rollBack();
    exit('Purchase failed: '.htmlspecialchars($e->getMessage()));
}
header('Location: index.php');
exit;
