<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| ONLY POST REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/

$id = (int)($_POST['id'] ?? 0);

$medicineName = trim(
    $_POST['medicine_name'] ?? ''
);

$genericName = trim(
    $_POST['generic_name'] ?? ''
);

$dosageForm = trim(
    $_POST['dosage_form'] ?? ''
);

$quantity = (int)(
    $_POST['quantity'] ?? 0
);

$reorderLevel = (int)(
    $_POST['reorder_level'] ?? 0
);

$unitPrice = (float)(
    $_POST['unit_price'] ?? 0
);

$status = $_POST['status'] ?? 'Active';


/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if ($id <= 0) {

    exit('Invalid medicine ID.');

}


if ($medicineName === '') {

    exit('Medicine name is required.');

}


if ($quantity < 0) {

    exit('Quantity cannot be negative.');

}


if ($reorderLevel < 0) {

    exit('Reorder level cannot be negative.');

}


if ($unitPrice < 0) {

    exit('Unit price cannot be negative.');

}


if (!in_array(
    $status,
    ['Active', 'Inactive'],
    true
)) {

    exit('Invalid medicine status.');

}


/*
|--------------------------------------------------------------------------
| GET CURRENT INVENTORY
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        quantity
    FROM medicine_inventory
    WHERE medicine_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$currentInventory = $stmt->fetch();


$currentQuantity = $currentInventory
    ? (int)$currentInventory['quantity']
    : 0;


/*
|--------------------------------------------------------------------------
| COMPUTE STOCK DIFFERENCE
|--------------------------------------------------------------------------
*/

$stockDifference =
    $quantity - $currentQuantity;


/*
|--------------------------------------------------------------------------
| SAVE CHANGES
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | UPDATE MEDICINE
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE medicines

        SET
            medicine_name = ?,
            generic_name = ?,
            dosage_form = ?,
            unit_price = ?,
            status = ?

        WHERE id = ?
    ");

    $stmt->execute([

        $medicineName,
        $genericName !== ''
            ? $genericName
            : null,

        $dosageForm !== ''
            ? $dosageForm
            : null,

        $unitPrice,
        $status,
        $id

    ]);


    /*
    |--------------------------------------------------------------------------
    | UPDATE OR CREATE INVENTORY
    |--------------------------------------------------------------------------
    */

    if ($currentInventory) {

        $stmt = $pdo->prepare("
            UPDATE medicine_inventory

            SET
                quantity = ?,
                reorder_level = ?

            WHERE medicine_id = ?
        ");

        $stmt->execute([

            $quantity,
            $reorderLevel,
            $id

        ]);

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO medicine_inventory
            (
                medicine_id,
                quantity,
                reorder_level
            )

            VALUES
            (
                ?,
                ?,
                ?
            )
        ");

        $stmt->execute([

            $id,
            $quantity,
            $reorderLevel

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | STOCK ADJUSTMENT RECORD
    |--------------------------------------------------------------------------
    |
    | Only record an adjustment when quantity actually changed.
    |
    */

    if ($stockDifference !== 0) {

        $userId = !empty($_SESSION['user_id'])
            ? (int)$_SESSION['user_id']
            : null;


        $remarks = $stockDifference > 0
            ? 'Stock increased through inventory edit.'
            : 'Stock decreased through inventory edit.';


        $stmt = $pdo->prepare("
            INSERT INTO medicine_stock_movements
            (
                medicine_id,
                movement_type,
                quantity,
                remarks,
                user_id
            )

            VALUES
            (
                ?,
                'ADJUSTMENT',
                ?,
                ?,
                ?
            )
        ");


        $stmt->execute([

            $id,
            abs($stockDifference),
            $remarks,
            $userId

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | RETURN TO INVENTORY
    |--------------------------------------------------------------------------
    */

    header(
        'Location: index.php?updated=1'
    );

    exit;


} catch (Throwable $e) {


    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }


    exit(
        'Unable to update medicine: ' .
        htmlspecialchars($e->getMessage())
    );

}