<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';


// =========================================================
// DATE FILTER
// =========================================================

$filter = $_GET['filter'] ?? 'month';

$startDate = $_GET['start_date'] ?? '';
$endDate   = $_GET['end_date'] ?? '';

$today = date('Y-m-d');


switch ($filter) {

    case 'today':

        $startDate = $today;
        $endDate = $today;

        break;


    case 'month':

        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');

        break;


    case 'year':

        $startDate = date('Y-01-01');
        $endDate = date('Y-12-31');

        break;


    case 'custom':

        if ($startDate === '') {
            $startDate = date('Y-m-01');
        }

        if ($endDate === '') {
            $endDate = $today;
        }

        break;


    case 'all':

        $startDate = '';
        $endDate = '';

        break;

}


// =========================================================
// WHERE
// =========================================================

$where = '';
$params = [];

if ($startDate !== '' && $endDate !== '') {

    $where = "
        WHERE DATE(mp.purchase_date)
        BETWEEN ? AND ?
    ";

    $params = [
        $startDate,
        $endDate
    ];
}


// =========================================================
// SUMMARY
// =========================================================

$stmt = $pdo->prepare("
    SELECT

        COUNT(DISTINCT mp.id) AS transactions,

        COUNT(DISTINCT mpi.medicine_id) AS medicines,

        COALESCE(SUM(mpi.quantity),0)
            AS total_quantity,

        COALESCE(SUM(mpi.total_price),0)
            AS total_amount

    FROM medicine_purchases mp

    INNER JOIN medicine_purchase_items mpi
        ON mpi.purchase_id = mp.id

    $where
");

$stmt->execute($params);

$summary = $stmt->fetch();


// =========================================================
// TOP 20
// =========================================================

$stmt = $pdo->prepare("
    SELECT

        m.medicine_name,

        m.generic_name,

        SUM(mpi.quantity)
            AS total_quantity,

        COUNT(DISTINCT mp.id)
            AS transaction_count,

        COALESCE(
            SUM(mpi.total_price),
            0
        ) AS total_amount

    FROM medicine_purchase_items mpi

    INNER JOIN medicine_purchases mp
        ON mp.id = mpi.purchase_id

    INNER JOIN medicines m
        ON m.id = mpi.medicine_id

    $where

    GROUP BY
        m.id,
        m.medicine_name,
        m.generic_name

    ORDER BY
        total_quantity DESC

    LIMIT 20
");

$stmt->execute($params);

$medicines = $stmt->fetchAll();


// =========================================================
// PERIOD
// =========================================================

if ($startDate !== '' && $endDate !== '') {

    $period =
        date('F d, Y', strtotime($startDate))
        . ' - '
        . date('F d, Y', strtotime($endDate));

} else {

    $period = 'All Records';

}

?>

<!doctype html>

<html lang="en">

<head>

<meta charset="utf-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1">

<title>
YAKAP - Medicine Statistics Report
</title>


<style>

body {

    margin: 0;

    padding: 30px;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    color: #243447;

    background: #fff;

}


.report {

    max-width: 1100px;

    margin: auto;

}


.header {

    text-align: center;

    margin-bottom: 25px;

}


.header h1 {

    margin: 0;

    font-size: 24px;

}


.header h2 {

    margin: 5px 0;

    font-size: 18px;

}


.header p {

    margin: 5px 0;

    color: #666;

}


.summary {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 12px;

    margin-bottom: 25px;

}


.summary-box {

    border: 1px solid #ddd;

    padding: 15px;

    text-align: center;

}


.summary-box span {

    display: block;

    font-size: 12px;

    color: #666;

}


.summary-box strong {

    display: block;

    font-size: 22px;

    margin-top: 5px;

}


table {

    width: 100%;

    border-collapse: collapse;

}


th,
td {

    border: 1px solid #ccc;

    padding: 8px;

    font-size: 12px;

}


th {

    background: #f2f2f2;

    text-align: left;

}


.text-right {

    text-align: right;

}


.footer {

    margin-top: 30px;

    font-size: 11px;

    color: #666;

}


.no-print {

    margin-bottom: 20px;

}


button,
a {

    padding: 9px 15px;

    border: 1px solid #999;

    background: #fff;

    text-decoration: none;

    color: #333;

    cursor: pointer;

}


@media print {

    body {

        padding: 0;

    }


    .no-print {

        display: none;

    }


    .summary {

        grid-template-columns:
            repeat(4, 1fr);

    }

}

</style>

</head>


<body>


<div class="report">


    <!-- =====================================================
         BUTTONS
    ====================================================== -->

    <div class="no-print">

        <button
            onclick="window.print()">

            🖨 Print

        </button>


        <button
            onclick="window.close()">

            Cancel

        </button>

    </div>


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="header">

        <h1>
            YAKAP Monitoring System
        </h1>

        <h2>
            Top 20 Most Purchased Medicines
        </h2>

        <p>
            Medicine Statistics Report
        </p>

        <p>
            Period:
            <strong>
                <?= htmlspecialchars($period) ?>
            </strong>
        </p>

    </div>


    <!-- =====================================================
         SUMMARY
    ====================================================== -->

    <div class="summary">


        <div class="summary-box">

            <span>
                Total Quantity
            </span>

            <strong>
                <?= number_format(
                    (int)$summary['total_quantity']
                ) ?>
            </strong>

        </div>


        <div class="summary-box">

            <span>
                Transactions
            </span>

            <strong>
                <?= number_format(
                    (int)$summary['transactions']
                ) ?>
            </strong>

        </div>


        <div class="summary-box">

            <span>
                Different Medicines
            </span>

            <strong>
                <?= number_format(
                    (int)$summary['medicines']
                ) ?>
            </strong>

        </div>


        <div class="summary-box">

            <span>
                Total Amount
            </span>

            <strong>

                ₱<?= number_format(
                    (float)$summary['total_amount'],
                    2
                ) ?>

            </strong>

        </div>

    </div>


    <!-- =====================================================
         TABLE
    ====================================================== -->

    <table>

        <thead>

            <tr>

                <th>
                    Rank
                </th>

                <th>
                    Medicine
                </th>

                <th>
                    Generic Name
                </th>

                <th>
                    Quantity
                </th>

                <th>
                    Transactions
                </th>

                <th class="text-right">
                    Total Amount
                </th>

            </tr>

        </thead>


        <tbody>

        <?php

        $rank = 1;

        foreach ($medicines as $medicine):

        ?>

            <tr>

                <td>
                    <?= $rank++ ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $medicine['medicine_name']
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $medicine['generic_name']
                        ?? '-'
                    ) ?>
                </td>

                <td>
                    <?= number_format(
                        (int)$medicine['total_quantity']
                    ) ?>
                </td>

                <td>
                    <?= number_format(
                        (int)$medicine['transaction_count']
                    ) ?>
                </td>

                <td class="text-right">

                    ₱<?= number_format(
                        (float)$medicine['total_amount'],
                        2
                    ) ?>

                </td>

            </tr>

        <?php endforeach; ?>


        <?php if (!$medicines): ?>

            <tr>

                <td
                    colspan="6"
                    style="text-align:center">

                    No medicine purchase records
                    found for this period.

                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>


    <div class="footer">

        Generated:
        <?= date('F d, Y h:i A') ?>

        <br>

        YAKAP Medicine Monitoring System
        Version 1.1

    </div>


</div>


<script>

window.addEventListener(
    'load',
    function () {

        // Uncomment if you want
        // the print dialog to open automatically.

        // window.print();

    }
);

</script>


</body>

</html>