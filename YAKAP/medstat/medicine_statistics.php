<?php

declare(strict_types=1);

$pageTitle = 'Medicine Statistics';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';


// =========================================================
// DATE FILTER
// =========================================================

$filter = $_GET['filter'] ?? 'month';

$startDate = '';
$endDate   = '';

$today = date('Y-m-d');

switch ($filter) {

    case 'today':

        $startDate = $today;
        $endDate   = $today;

        break;


    case 'year':

        $startDate = date('Y-01-01');
        $endDate   = date('Y-12-31');

        break;


    case 'custom':

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate   = $_GET['end_date'] ?? $today;

        break;


    case 'all':

        $startDate = '';
        $endDate   = '';

        break;


    case 'month':

    default:

        $startDate = date('Y-m-01');
        $endDate   = date('Y-m-t');

        $filter = 'month';

        break;
}


// =========================================================
// DATE CONDITION
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

$sql = "
    SELECT

        COUNT(DISTINCT mp.id) AS transactions,

        COUNT(DISTINCT mpi.medicine_id) AS medicines,

        COALESCE(SUM(mpi.quantity), 0) AS total_quantity,

        COALESCE(SUM(mpi.total_price), 0) AS total_amount

    FROM medicine_purchases mp

    INNER JOIN medicine_purchase_items mpi
        ON mpi.purchase_id = mp.id

    $where
";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$summary = $stmt->fetch();


// =========================================================
// TOP 20 MEDICINES
// =========================================================

$sql = "
    SELECT

        m.id,

        m.medicine_name,

        m.generic_name,

        COALESCE(
            SUM(mpi.quantity),
            0
        ) AS total_quantity,

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
";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$topMedicines = $stmt->fetchAll();


// =========================================================
// GRAPH DATA
// =========================================================

$graphLabels = [];
$graphValues = [];

foreach ($topMedicines as $medicine) {

    $graphLabels[] =
        $medicine['medicine_name'];

    $graphValues[] =
        (int)$medicine['total_quantity'];
}


// =========================================================
// FILTER DESCRIPTION
// =========================================================

if ($filter === 'today') {

    $filterLabel = 'Today';

} elseif ($filter === 'month') {

    $filterLabel = 'This Month';

} elseif ($filter === 'year') {

    $filterLabel = 'This Year';

} elseif ($filter === 'custom') {

    $filterLabel =
        date('M d, Y', strtotime($startDate))
        . ' - '
        . date('M d, Y', strtotime($endDate));

} else {

    $filterLabel = 'All Records';

}

?>

<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="card-panel mb-4">

    <div class="d-flex justify-content-between align-items-center">

        <div>

            <h5 class="mb-1">
                Top 20 most purchased medicines
            </h5>

            <small class="text-muted">
                Supervised by FriendlyCare Foundation Inc.
            </small>

        </div>


        <div class="d-flex gap-2">

            <a
                href="medicine_statistics_print.php?filter=<?= urlencode($filter) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>"
                target="_blank"
                class="btn btn-outline-primary">

                🖨 Print Report

            </a>

        </div>

    </div>

</div>


<!-- =========================================================
     DATE FILTER
========================================================= -->

<div class="card-panel mb-4">

    <form method="get">

        <div class="row g-3 align-items-end">


            <div class="col-md-3">

                <label class="form-label">
                    Date Range
                </label>

                <select
                    name="filter"
                    id="filter"
                    class="form-select"
                    onchange="toggleCustomDates()">

                    <option
                        value="today"
                        <?= $filter === 'today' ? 'selected' : '' ?>>

                        Today

                    </option>


                    <option
                        value="month"
                        <?= $filter === 'month' ? 'selected' : '' ?>>

                        This Month

                    </option>


                    <option
                        value="year"
                        <?= $filter === 'year' ? 'selected' : '' ?>>

                        This Year

                    </option>


                    <option
                        value="all"
                        <?= $filter === 'all' ? 'selected' : '' ?>>

                        All Records

                    </option>


                    <option
                        value="custom"
                        <?= $filter === 'custom' ? 'selected' : '' ?>>

                        Custom Date

                    </option>

                </select>

            </div>


            <div
                class="col-md-3 custom-date">

                <label class="form-label">
                    Start Date
                </label>

                <input
                    type="date"
                    name="start_date"
                    value="<?= htmlspecialchars($startDate) ?>"
                    class="form-control">

            </div>


            <div
                class="col-md-3 custom-date">

                <label class="form-label">
                    End Date
                </label>

                <input
                    type="date"
                    name="end_date"
                    value="<?= htmlspecialchars($endDate) ?>"
                    class="form-control">

            </div>


            <div class="col-md-3">

                <button
                    type="submit"
                    class="btn btn-primary w-100">

                    🔍 Generate Statistics

                </button>

            </div>

        </div>

    </form>

</div>


<!-- =========================================================
     SUMMARY CARDS
========================================================= -->

<div class="row g-3 mb-4">


    <!-- Total Quantity -->

    <div class="col-md-3">

        <div class="stat-card">

            <span>
                Total Quantity Purchased
            </span>

            <strong>
                <?= number_format(
                    (int)$summary['total_quantity']
                ) ?>
            </strong>

        </div>

    </div>


    <!-- Transactions -->

    <div class="col-md-3">

        <div class="stat-card">

            <span>
                Purchase Transactions
            </span>

            <strong>
                <?= number_format(
                    (int)$summary['transactions']
                ) ?>
            </strong>

        </div>

    </div>


    <!-- Different Medicines -->

    <div class="col-md-3">

        <div class="stat-card">

            <span>
                Different Medicines
            </span>

            <strong>
                <?= number_format(
                    (int)$summary['medicines']
                ) ?>
            </strong>

        </div>

    </div>


    <!-- Total Amount -->

    <div class="col-md-3">

        <div class="stat-card">

            <span>
                Total Purchase Amount
            </span>

            <strong>

                ₱<?= number_format(
                    (float)$summary['total_amount'],
                    2
                ) ?>

            </strong>

        </div>

    </div>

</div>


<!-- =========================================================
     BAR GRAPH
========================================================= -->

<div class="card-panel mb-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <h5 class="mb-1">
                Top 20 Most Purchased Medicines
            </h5>

            <small class="text-muted">
                <?= htmlspecialchars($filterLabel) ?>
            </small>

        </div>

    </div>


    <?php if (!$topMedicines): ?>

        <div class="empty-state">

            No purchase records found
            for the selected date range.

        </div>

    <?php else: ?>

        <div style="height: 650px;">

            <canvas id="medicineChart"></canvas>

        </div>

    <?php endif; ?>

</div>


<!-- =========================================================
     TOP 20 TABLE
========================================================= -->

<div class="card-panel">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <h5 class="mb-1">
                Top 20 Medicine Ranking
            </h5>

            <small class="text-muted">
                Ranked by total quantity purchased
            </small>

        </div>

    </div>


    <div class="table-responsive">

        <table
            class="table table-hover align-middle">


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

                    <th class="text-end">
                        Total Amount
                    </th>

                </tr>

            </thead>


            <tbody>

                <?php if (!$topMedicines): ?>

                    <tr>

                        <td
                            colspan="6"
                            class="text-center text-muted">

                            No medicine purchase records found.

                        </td>

                    </tr>

                <?php else: ?>


                    <?php

                    $rank = 1;

                    foreach ($topMedicines as $medicine):

                    ?>

                        <tr>


                            <td>

                                <?php if ($rank === 1): ?>

                                    🥇

                                <?php elseif ($rank === 2): ?>

                                    🥈

                                <?php elseif ($rank === 3): ?>

                                    🥉

                                <?php else: ?>

                                    <?= $rank ?>

                                <?php endif; ?>

                            </td>


                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $medicine['medicine_name']
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $medicine['generic_name']
                                    ?? '-'
                                ) ?>

                            </td>


                            <td>

                                <span
                                    class="badge text-bg-primary">

                                    <?= number_format(
                                        (int)$medicine['total_quantity']
                                    ) ?>

                                </span>

                            </td>


                            <td>

                                <?= number_format(
                                    (int)$medicine['transaction_count']
                                ) ?>

                            </td>


                            <td class="text-end">

                                <strong>

                                    ₱<?= number_format(
                                        (float)$medicine['total_amount'],
                                        2
                                    ) ?>

                                </strong>

                            </td>


                        </tr>


                    <?php

                        $rank++;

                    endforeach;

                    ?>


                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- =========================================================
     CHART.JS
========================================================= -->

<?php if ($topMedicines): ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const labels =
    <?= json_encode(
        $graphLabels,
        JSON_UNESCAPED_UNICODE
    ) ?>;


const values =
    <?= json_encode(
        $graphValues
    ) ?>;


const ctx =
    document
        .getElementById('medicineChart')
        .getContext('2d');


new Chart(ctx, {

    type: 'bar',

    data: {

        labels: labels,

        datasets: [

            {

                label: 'Total Quantity Purchased',

                data: values,

                borderWidth: 1

            }

        ]

    },

    options: {

        indexAxis: 'y',

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {

                display: true

            },

            tooltip: {

                callbacks: {

                    label: function(context) {

                        return (
                            ' Quantity: '
                            +
                            context.raw.toLocaleString()
                        );

                    }

                }

            }

        },

        scales: {

            x: {

                beginAtZero: true,

                ticks: {

                    precision: 0

                },

                title: {

                    display: true,

                    text: 'Total Quantity'

                }

            },

            y: {

                title: {

                    display: true,

                    text: 'Medicine'

                }

            }

        }

    }

});

</script>

<?php endif; ?>


<!-- =========================================================
     CUSTOM DATE JAVASCRIPT
========================================================= -->

<script>

function toggleCustomDates() {

    const filter =
        document.getElementById('filter').value;

    const fields =
        document.querySelectorAll('.custom-date');


    fields.forEach(function(field) {

        if (filter === 'custom') {

            field.style.display = '';

        } else {

            field.style.display = 'none';

        }

    });

}


document.addEventListener(
    'DOMContentLoaded',
    toggleCustomDates
);

</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>