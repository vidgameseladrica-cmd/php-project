<?php

$pageTitle = 'Dashboard';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';


/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| TOTAL PATIENTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM patients
");

$patients = (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| ACTIVE MEDICINES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM medicines
    WHERE status = 'Active'
");

$medicines = (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| PURCHASE TRANSACTIONS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM medicine_purchases
");

$transactions = (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| TOTAL PURCHASE AMOUNT
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COALESCE(SUM(total_amount), 0)
    FROM medicine_purchases
");

$amount = (float)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| LOW STOCK
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM medicine_inventory
    WHERE quantity <= reorder_level
");

$lowStock = (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| OUT OF STOCK
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM medicine_inventory
    WHERE quantity <= 0
");

$outOfStock = (int)$stmt->fetchColumn();

?>

<div class="container-fluid">


    <!-- =====================================================
         DASHBOARD HEADER
    ====================================================== 

    <div class="topbar">

        <div>

            <h1>
                YAKAP Dashboard
            </h1>

            <small class="text-muted">
                Medicine and Services Monitoring System
            </small>

        </div>

    </div> -->


    <!-- =====================================================
         STATISTICS
    ====================================================== -->

    <div class="row g-3">


        <!-- PATIENTS -->

        <div class="col-md-4">

            <div class="stat-card">

                <span>
                    Total Patients
                </span>

                <strong>
                    <?= number_format($patients) ?>
                </strong>

            </div>

        </div>


        <!-- ACTIVE MEDICINES -->

        <div class="col-md-4">

            <div class="stat-card">

                <span>
                    Active Medicines
                </span>

                <strong>
                    <?= number_format($medicines) ?>
                </strong>

            </div>

        </div>


        <!-- PURCHASE TRANSACTIONS -->

        <div class="col-md-4">

            <div class="stat-card">

                <span>
                    Purchase Transactions
                </span>

                <strong>
                    <?= number_format($transactions) ?>
                </strong>

            </div>

        </div>


        <!-- TOTAL PURCHASE AMOUNT -->

        <div class="col-md-4">

            <div class="stat-card">

                <span>
                    Total Purchase Amount
                </span>

                <strong>

                    ₱<?= number_format(
                        $amount,
                        2
                    ) ?>

                </strong>

            </div>

        </div>


        <!-- LOW STOCK -->

        <div class="col-md-4">

            <div class="stat-card warning">

                <span>
                    Low Stock Items
                </span>

                <strong>
                    <?= number_format($lowStock) ?>
                </strong>

            </div>

        </div>


        <!-- OUT OF STOCK -->

        <div class="col-md-4">

            <div class="stat-card">

                <span>
                    Out of Stock
                </span>

                <strong>

                    <?= number_format($outOfStock) ?>

                </strong>

            </div>

        </div>

    </div>


    <!-- =====================================================
         INVENTORY ALERT
    ====================================================== -->

    <?php if ($outOfStock > 0): ?>

        <div class="alert alert-danger mt-4">

            <strong>
                ⚠ Inventory Alert
            </strong>

            <br>

            There are
            <strong>
                <?= number_format($outOfStock) ?>
            </strong>
            medicine item(s) that are currently
            <strong>OUT OF STOCK</strong>.

        </div>

    <?php elseif ($lowStock > 0): ?>

        <div class="alert alert-warning mt-4">

            <strong>
                ⚠ Low Stock Alert
            </strong>

            <br>

            There are
            <strong>
                <?= number_format($lowStock) ?>
            </strong>
            medicine item(s) that are at or below their
            reorder level.

        </div>

    <?php else: ?>

        <div class="alert alert-success mt-4">

            <strong>
                ✓ Inventory Status
            </strong>

            <br>

            All medicine stocks are currently above their
            reorder levels.

        </div>

    <?php endif; ?>


</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>