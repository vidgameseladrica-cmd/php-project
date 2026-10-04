<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';


// =====================================================
// CHECK LOGIN
// =====================================================

if (empty($_SESSION['user_id'])) {
    header('Location: /YAKAP/auth/login.php');
    exit;
}


// =====================================================
// ONLY POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create.php');
    exit;
}


// =====================================================
// GET DATA
// =====================================================

$patientId = (int)($_POST['patient_id'] ?? 0);

$referenceNo = trim(
    $_POST['reference_no'] ?? ''
);

$purchaseDate = $_POST['purchase_date'] ?? '';

$medicineIds = $_POST['medicine_id'] ?? [];

$quantities = $_POST['quantity'] ?? [];

$unitPrices = $_POST['unit_price'] ?? [];


// =====================================================
// VALIDATION
// =====================================================

if ($patientId <= 0) {
    exit('Invalid patient.');
}

if ($referenceNo === '') {
    exit('Reference number is required.');
}

if ($purchaseDate === '') {
    exit('Purchase date is required.');
}

if (empty($medicineIds)) {
    exit('No medicine selected.');
}


// =====================================================
// CHECK PATIENT
// =====================================================

$stmt = $pdo->prepare("
    SELECT id
    FROM patients
    WHERE id = ?
    AND status = 'Active'
    LIMIT 1
");

$stmt->execute([
    $patientId
]);

if (!$stmt->fetch()) {
    exit('Patient not found or inactive.');
}


// =====================================================
// CHECK DUPLICATE REFERENCE NO.
// =====================================================

$stmt = $pdo->prepare("
    SELECT id
    FROM medicine_purchases
    WHERE reference_no = ?
    LIMIT 1
");

$stmt->execute([
    $referenceNo
]);

if ($stmt->fetch()) {
    exit(
        'Reference No. already exists: '
        . htmlspecialchars($referenceNo)
    );
}


// =====================================================
// GENERATE TRANSACTION NUMBER
// =====================================================

function generateTransactionNo(PDO $pdo): string
{
    do {

        $transactionNo =
            'TXN-'
            . date('Ymd-His')
            . '-'
            . random_int(1000, 9999);

        $stmt = $pdo->prepare("
            SELECT id
            FROM medicine_purchases
            WHERE transaction_no = ?
            LIMIT 1
        ");

        $stmt->execute([
            $transactionNo
        ]);

    } while ($stmt->fetch());


    return $transactionNo;
}


// =====================================================
// TRANSACTION
// =====================================================

try {

    $pdo->beginTransaction();


    // Generate unique transaction number

    $transactionNo =
        generateTransactionNo($pdo);


    $totalQuantity = 0;

    $totalAmount = 0;

    $items = [];


    // =================================================
    // VALIDATE EACH MEDICINE
    // =================================================

    foreach (
        $medicineIds as $index => $medicineId
    ) {

        $medicineId =
            (int)$medicineId;

        $quantity =
            (int)(
                $quantities[$index] ?? 0
            );


        if (
            $medicineId <= 0 ||
            $quantity <= 0
        ) {

            throw new Exception(
                'Invalid medicine or quantity.'
            );

        }


        // ---------------------------------------------
        // GET MEDICINE AND STOCK
        // ---------------------------------------------

        $stmt = $pdo->prepare("
            SELECT
                m.id,
                m.medicine_name,
                m.unit_price,
                COALESCE(i.quantity, 0) AS stock
            FROM medicines m
            LEFT JOIN medicine_inventory i
                ON i.medicine_id = m.id
            WHERE m.id = ?
            AND m.status = 'Active'
            LIMIT 1
            FOR UPDATE
        ");

        $stmt->execute([
            $medicineId
        ]);


        $medicine =
            $stmt->fetch();


        if (!$medicine) {

            throw new Exception(
                'Medicine not found.'
            );

        }


        $stock =
            (int)$medicine['stock'];


        // ---------------------------------------------
        // CHECK STOCK
        // ---------------------------------------------

        if ($quantity > $stock) {

            throw new Exception(
                'Insufficient stock for '
                . $medicine['medicine_name']
                . '. Available stock: '
                . $stock
            );

        }


        $unitPrice =
            (float)$medicine['unit_price'];


        $lineTotal =
            $unitPrice * $quantity;


        $totalQuantity +=
            $quantity;


        $totalAmount +=
            $lineTotal;


        $items[] = [

            'medicine_id' =>
                $medicineId,

            'quantity' =>
                $quantity,

            'unit_price' =>
                $unitPrice,

            'total_price' =>
                $lineTotal

        ];

    }


    // =================================================
    // SAVE PURCHASE HEADER
    // =================================================

    $stmt = $pdo->prepare("
        INSERT INTO medicine_purchases
        (
            transaction_no,
            patient_id,
            reference_no,
            purchase_date,
            total_amount,
            created_by
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");


    $stmt->execute([

        $transactionNo,

        $patientId,

        $referenceNo,

        $purchaseDate,

        $totalAmount,

        (int)$_SESSION['user_id']

    ]);


    $purchaseId =
        (int)$pdo->lastInsertId();


    // =================================================
    // SAVE PURCHASE ITEMS
    // =================================================

    $itemStmt = $pdo->prepare("
        INSERT INTO medicine_purchase_items
        (
            purchase_id,
            medicine_id,
            quantity,
            unit_price,
            total_price
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");


    // =================================================
    // UPDATE INVENTORY
    // =================================================

    $stockStmt = $pdo->prepare("
        UPDATE medicine_inventory
        SET quantity = quantity - ?
        WHERE medicine_id = ?
    ");


    foreach ($items as $item) {

        // Save item

        $itemStmt->execute([

            $purchaseId,

            $item['medicine_id'],

            $item['quantity'],

            $item['unit_price'],

            $item['total_price']

        ]);


        // Deduct stock

        $stockStmt->execute([

            $item['quantity'],

            $item['medicine_id']

        ]);

    }


    // =================================================
    // COMMIT
    // =================================================

    $pdo->commit();


    // =================================================
    // SUCCESS
    // =================================================

    header(
        'Location: view.php?id='
        . $purchaseId
        . '&success=1'
    );

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    exit(
        'Unable to save purchase: '
        . htmlspecialchars(
            $e->getMessage()
        )
    );

}