<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| GET ALL MEDICINES
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        m.id,
        m.medicine_name,
        m.generic_name,
        m.dosage_form,
        m.unit_price,
        m.status,

        COALESCE(i.quantity, 0) AS quantity,

        COALESCE(i.reorder_level, 10) AS reorder_level

    FROM medicines AS m

    LEFT JOIN medicine_inventory AS i
        ON i.medicine_id = m.id

    ORDER BY m.medicine_name ASC
";


$stmt = $pdo->query($sql);

$rows = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| REPORT SUMMARY
|--------------------------------------------------------------------------
*/

$totalMedicines = count($rows);

$totalStock = 0;

$lowStock = 0;

$outOfStock = 0;

$totalInventoryValue = 0;


foreach ($rows as $r) {

    $quantity = (int)$r['quantity'];

    $reorderLevel = (int)$r['reorder_level'];

    $unitPrice = (float)$r['unit_price'];


    $totalStock += $quantity;


    $totalInventoryValue +=
        $quantity * $unitPrice;


    if ($quantity <= 0) {

        $outOfStock++;

    } elseif ($quantity <= $reorderLevel) {

        $lowStock++;

    }

}


$reportDate = date('F d, Y');

$reportTime = date('h:i A');

?>

<!doctype html>

<html lang="en">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1">

<title>
    YAKAP Medicine Inventory Report
</title>


<style>

    * {
        box-sizing: border-box;
    }


    body {

        margin: 0;

        padding: 25px;

        background: #ffffff;

        color: #243447;

        font-family:
            "Segoe UI",
            Arial,
            sans-serif;

        font-size: 13px;

    }


    .report-container {

        max-width: 1200px;

        margin: 0 auto;

    }


    .report-header {

        display: flex;

        justify-content: space-between;

        align-items: center;

        border-bottom: 2px solid #123b35;

        padding-bottom: 15px;

        margin-bottom: 20px;

    }


    .report-title {

        margin: 0;

        color: #123b35;

        font-size: 24px;

        font-weight: 700;

    }


    .report-subtitle {

        margin-top: 5px;

        color: #718096;

        font-size: 13px;

    }


    .report-date {

        text-align: right;

        color: #718096;

        font-size: 12px;

    }


    .summary {

        display: grid;

        grid-template-columns:
            repeat(4, 1fr);

        gap: 12px;

        margin-bottom: 20px;

    }


    .summary-box {

        border: 1px solid #dfe7e4;

        border-radius: 8px;

        padding: 12px;

        background: #f8fbfa;

    }


    .summary-box span {

        display: block;

        color: #718096;

        font-size: 11px;

        text-transform: uppercase;

        margin-bottom: 5px;

    }


    .summary-box strong {

        font-size: 20px;

        color: #123b35;

    }


    table {

        width: 100%;

        border-collapse: collapse;

    }


    th {

        background: #123b35;

        color: #ffffff;

        padding: 9px 8px;

        border: 1px solid #123b35;

        text-align: left;

        font-size: 11px;

    }


    td {

        padding: 8px;

        border: 1px solid #dfe3e6;

        vertical-align: middle;

    }


    tbody tr:nth-child(even) {

        background: #f8fafb;

    }


    .text-center {

        text-align: center;

    }


    .text-right {

        text-align: right;

    }


    .badge {

        display: inline-block;

        padding: 4px 7px;

        border-radius: 4px;

        font-size: 10px;

        font-weight: 600;

    }


    .ok {

        background: #dff3e8;

        color: #18794e;

    }


    .low {

        background: #fff0cc;

        color: #9a6700;

    }


    .out {

        background: #fde2e2;

        color: #b42318;

    }


    .report-footer {

        margin-top: 25px;

        padding-top: 12px;

        border-top: 1px solid #dfe3e6;

        display: flex;

        justify-content: space-between;

        color: #718096;

        font-size: 11px;

    }


    .print-button {

        display: inline-block;

        padding: 9px 16px;

        border: 0;

        border-radius: 6px;

        background: #1f7a5a;

        color: #ffffff;

        cursor: pointer;

        font-size: 13px;

        margin-bottom: 20px;

    }


    .no-record {

        text-align: center;

        padding: 30px;

        color: #718096;

    }


    @media print {

        body {

            padding: 0;

            font-size: 10px;

        }


        .report-container {

            max-width: none;

        }


        .print-button {

            display: none;

        }


        .report-header {

            margin-bottom: 12px;

        }


        .summary {

            gap: 6px;

            margin-bottom: 12px;

        }


        .summary-box {

            padding: 7px;

        }


        .summary-box strong {

            font-size: 14px;

        }


        th {

            padding: 6px;

        }


        td {

            padding: 5px;

        }


        @page {

            size: A4 landscape;

            margin: 10mm;

        }


    }


    @media screen and (max-width: 700px) {

        body {

            padding: 12px;

        }


        .report-header {

            display: block;

        }


        .report-date {

            text-align: left;

            margin-top: 8px;

        }


        .summary {

            grid-template-columns: 1fr 1fr;

        }

    }

</style>

</head>


<body>


<div class="report-container">


    <!-- PRINT BUTTON -->

    <button
        type="button"
        class="print-button"
        onclick="window.print()">

        🖨 Print Report

    </button>


    <!-- HEADER -->

    <div class="report-header">

        <div>

            <h1 class="report-title">

                YAKAP Medicine Inventory

            </h1>


            <div class="report-subtitle">

                PhilHealth Medicine Monitoring System

            </div>

        </div>


        <div class="report-date">

            <strong>
                Inventory Report
            </strong>

            <br>

            <?= htmlspecialchars($reportDate) ?>

            <br>

            <?= htmlspecialchars($reportTime) ?>

        </div>

    </div>


    <!-- SUMMARY -->

    <div class="summary">


        <div class="summary-box">

            <span>
                Total Medicines
            </span>

            <strong>
                <?= number_format($totalMedicines) ?>
            </strong>

        </div>


        <div class="summary-box">

            <span>
                Total Stock
            </span>

            <strong>
                <?= number_format($totalStock) ?>
            </strong>

        </div>


        <div class="summary-box">

            <span>
                Low Stock
            </span>

            <strong>
                <?= number_format($lowStock) ?>
            </strong>

        </div>


        <div class="summary-box">

            <span>
                Out of Stock
            </span>

            <strong>
                <?= number_format($outOfStock) ?>
            </strong>

        </div>


    </div>


    <!-- INVENTORY TABLE -->

    <table>


        <thead>

            <tr>

                <th
                    style="width:35px"
                    class="text-center">

                    #

                </th>


                <th>
                    Medicine / Services
                </th>


                <th>
                    Generic Name / Description
                </th>


                <th>
                    Dosage / Form
                </th>


                <th
                    class="text-center">

                    Stock

                </th>


                <th
                    class="text-center">

                    Reorder Level

                </th>


                <th
                    class="text-right">

                    Unit Price

                </th>


                <th
                    class="text-right">

                    Stock Value

                </th>


                <th>

                    Status

                </th>

            </tr>

        </thead>


        <tbody>


        <?php if (empty($rows)): ?>


            <tr>

                <td
                    colspan="9"
                    class="no-record">

                    No medicine records found.

                </td>

            </tr>


        <?php else: ?>


            <?php

            $counter = 1;

            ?>


            <?php foreach ($rows as $r): ?>


                <?php

                $quantity =
                    (int)$r['quantity'];


                $reorderLevel =
                    (int)$r['reorder_level'];


                $unitPrice =
                    (float)$r['unit_price'];


                $stockValue =
                    $quantity * $unitPrice;


                if ($quantity <= 0) {

                    $status =
                        'OUT OF STOCK';

                    $statusClass =
                        'out';

                } elseif (
                    $quantity <= $reorderLevel
                ) {

                    $status =
                        'LOW STOCK';

                    $statusClass =
                        'low';

                } else {

                    $status =
                        'OK';

                    $statusClass =
                        'ok';

                }

                ?>


                <tr>


                    <td class="text-center">

                        <?= $counter++ ?>

                    </td>


                    <td>

                        <strong>

                            <?= htmlspecialchars(
                                $r['medicine_name']
                            ) ?>

                        </strong>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $r['generic_name'] ?? '-'
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $r['dosage_form'] ?? '-'
                        ) ?>

                    </td>


                    <td class="text-center">

                        <strong>

                            <?= number_format(
                                $quantity
                            ) ?>

                        </strong>

                    </td>


                    <td class="text-center">

                        <?= number_format(
                            $reorderLevel
                        ) ?>

                    </td>


                    <td class="text-right">

                        ₱<?= number_format(
                            $unitPrice,
                            2
                        ) ?>

                    </td>


                    <td class="text-right">

                        ₱<?= number_format(
                            $stockValue,
                            2
                        ) ?>

                    </td>


                    <td>

                        <span
                            class="badge <?= $statusClass ?>">

                            <?= $status ?>

                        </span>

                    </td>


                </tr>


            <?php endforeach; ?>


        <?php endif; ?>


        </tbody>


        <!-- TOTAL -->

        <?php if (!empty($rows)): ?>

        <tfoot>

            <tr>

                <th
                    colspan="7"
                    class="text-right">

                    TOTAL INVENTORY VALUE:

                </th>


                <th
                    class="text-right">

                    ₱<?= number_format(
                        $totalInventoryValue,
                        2
                    ) ?>

                </th>


                <th></th>

            </tr>

        </tfoot>

        <?php endif; ?>


    </table>


    <!-- FOOTER -->

    <div class="report-footer">

        <div>

            YAKAP Monitoring System

        </div>


        <div>

            Generated on
            <?= htmlspecialchars($reportDate) ?>

        </div>

    </div>


</div>


</body>

</html>