<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| PATIENT ID
|--------------------------------------------------------------------------
*/

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('Invalid patient ID.');
}


/*
|--------------------------------------------------------------------------
| GET PATIENT
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        *,
        TIMESTAMPDIFF(
            YEAR,
            birthdate,
            CURDATE()
        ) AS age

    FROM patients

    WHERE id = ?

    LIMIT 1
");

$stmt->execute([$id]);

$patient = $stmt->fetch();


if (!$patient) {
    exit('Patient not found.');
}


/*
|--------------------------------------------------------------------------
| GET PURCHASED MEDICINES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT

        m.id AS medicine_id,

        m.medicine_name,

        m.generic_name,

        m.dosage_form,

        SUM(mpi.quantity) AS total_quantity,

        SUM(mpi.total_price) AS total_amount,

        COUNT(DISTINCT mp.id) AS transaction_count

    FROM medicine_purchases mp

    INNER JOIN medicine_purchase_items mpi
        ON mpi.purchase_id = mp.id

    INNER JOIN medicines m
        ON m.id = mpi.medicine_id

    WHERE mp.patient_id = ?

    GROUP BY

        m.id,
        m.medicine_name,
        m.generic_name,
        m.dosage_form

    ORDER BY m.medicine_name ASC
");

$stmt->execute([$id]);

$medicines = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| SUMMARY
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT

        COUNT(DISTINCT mp.id)
            AS transaction_count,

        COUNT(DISTINCT mpi.medicine_id)
            AS medicine_count,

        COALESCE(
            SUM(mpi.quantity),
            0
        ) AS total_quantity,

        COALESCE(
            SUM(mpi.total_price),
            0
        ) AS total_amount

    FROM medicine_purchases mp

    INNER JOIN medicine_purchase_items mpi
        ON mpi.purchase_id = mp.id

    WHERE mp.patient_id = ?
");

$stmt->execute([$id]);

$summary = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| REPORT INFORMATION
|--------------------------------------------------------------------------
*/

$reportDate = date('F d, Y');

$reportTime = date('h:i A');

$patientName =
    $patient['last_name'] . ', ' .
    $patient['first_name'] . ' ' .
    ($patient['middle_name'] ?? '');

?>

<!doctype html>

<html lang="en">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1">

<title>
    Patient Medicine History Report
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

    max-width: 1100px;

    margin: 0 auto;

}


/*
|--------------------------------------------------------------------------
| ACTION BUTTONS
|--------------------------------------------------------------------------
*/

.report-actions {

    display: flex;

    gap: 8px;

    margin-bottom: 20px;

}


.print-button,
.cancel-button {

    display: inline-block;

    padding: 9px 16px;

    border-radius: 6px;

    font-size: 13px;

    text-decoration: none;

    cursor: pointer;

}


.print-button {

    border: 0;

    background: #1f7a5a;

    color: #ffffff;

}


.print-button:hover {

    background: #185f47;

}


.cancel-button {

    background: #6c757d;

    color: #ffffff;

}


.cancel-button:hover {

    background: #5c636a;

    color: #ffffff;

}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.report-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    border-bottom:
        2px solid #123b35;

    padding-bottom: 15px;

    margin-bottom: 20px;

}


.report-title {

    margin: 0;

    color: #123b35;

    font-size: 24px;

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


/*
|--------------------------------------------------------------------------
| PATIENT INFORMATION
|--------------------------------------------------------------------------
*/

.patient-info {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 10px;

    margin-bottom: 20px;

}


.patient-box {

    border:
        1px solid #dfe7e4;

    background: #f8fbfa;

    border-radius: 7px;

    padding: 10px;

}


.patient-box.full {

    grid-column: span 2;

}


.patient-box small {

    display: block;

    color: #718096;

    font-size: 10px;

    text-transform: uppercase;

}


.patient-box strong {

    display: block;

    margin-top: 4px;

    color: #243447;

}


/*
|--------------------------------------------------------------------------
| SUMMARY
|--------------------------------------------------------------------------
*/

.summary {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 12px;

    margin-bottom: 20px;

}


.summary-box {

    border:
        1px solid #dfe7e4;

    border-radius: 8px;

    padding: 12px;

    background: #ffffff;

}


.summary-box span {

    display: block;

    color: #718096;

    font-size: 11px;

    text-transform: uppercase;

}


.summary-box strong {

    display: block;

    margin-top: 5px;

    font-size: 20px;

    color: #123b35;

}


/*
|--------------------------------------------------------------------------
| TABLE
|--------------------------------------------------------------------------
*/

table {

    width: 100%;

    border-collapse: collapse;

}


th {

    background: #123b35;

    color: #ffffff;

    padding: 9px 8px;

    border:
        1px solid #123b35;

    text-align: left;

    font-size: 11px;

}


td {

    padding: 8px;

    border:
        1px solid #dfe3e6;

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


.empty-state {

    text-align: center;

    padding: 30px;

    color: #718096;

}


/*
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
*/

.report-footer {

    margin-top: 25px;

    padding-top: 12px;

    border-top:
        1px solid #dfe3e6;

    display: flex;

    justify-content: space-between;

    color: #718096;

    font-size: 11px;

}


/*
|--------------------------------------------------------------------------
| PRINT
|--------------------------------------------------------------------------
*/

@media print {

    body {

        padding: 0;

        font-size: 10px;

    }


    .report-container {

        max-width: none;

    }


    .report-actions {

        display: none;

    }


    .patient-info {

        gap: 6px;

    }


    .patient-box {

        padding: 7px;

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


    .patient-info {

        grid-template-columns: 1fr 1fr;

    }


    .patient-box.full {

        grid-column: span 2;

    }


    .summary {

        grid-template-columns: 1fr 1fr;

    }

}

</style>

</head>


<body>


<div class="report-container">


    <!-- ACTION BUTTONS -->

    <div class="report-actions">

        <button
            type="button"
            class="print-button"
            onclick="window.print()">

            🖨 Print Report

        </button>


        <a
            href="/YAKAP/patients/Patientmedicine.php?id=<?= $id ?>"
            class="cancel-button">

            ✕ Cancel

        </a>

    </div>


    <!-- REPORT HEADER -->

    <div class="report-header">

        <div>

            <h1 class="report-title">

                YAKAP Patient Medicine History

            </h1>


            <div class="report-subtitle">

                PhilHealth Medicine Monitoring System

            </div>

        </div>


        <div class="report-date">

            <strong>
                Patient Medicine Report
            </strong>

            <br>

            <?= htmlspecialchars($reportDate) ?>

            <br>

            <?= htmlspecialchars($reportTime) ?>

        </div>

    </div>


    <!-- PATIENT INFORMATION -->

    <div class="patient-info">


        <div class="patient-box">

            <small>
                Patient ID
            </small>

            <strong>

                <?= htmlspecialchars(
                    $patient['patient_code']
                ) ?>

            </strong>

        </div>


        <div class="patient-box">

            <small>
                PhilHealth ID
            </small>

            <strong>

                <?= htmlspecialchars(
                    $patient['philhealth_id']
                ) ?>

            </strong>

        </div>


        <div class="patient-box full">

            <small>
                Patient Name
            </small>

            <strong>

                <?= htmlspecialchars(
                    $patientName
                ) ?>

            </strong>

        </div>


        <div class="patient-box">

            <small>
                Birthdate
            </small>

            <strong>

                <?= htmlspecialchars(
                    $patient['birthdate']
                ) ?>

            </strong>

        </div>


        <div class="patient-box">

            <small>
                Age
            </small>

            <strong>

                <?= htmlspecialchars(
                    (string)$patient['age']
                ) ?>

                years old

            </strong>

        </div>


        <div class="patient-box">

            <small>
                Sex
            </small>

            <strong>

                <?= htmlspecialchars(
                    $patient['sex']
                ) ?>

            </strong>

        </div>


        <div class="patient-box full">

            <small>
                Address
            </small>

            <strong>

                <?= htmlspecialchars(
                    $patient['address'] ?? '-'
                ) ?>

            </strong>

        </div>


    </div>


    <!-- SUMMARY -->

    <div class="summary">


        <div class="summary-box">

            <span>
                Different Medicines
            </span>

            <strong>

                <?= number_format(
                    (int)$summary['medicine_count']
                ) ?>

            </strong>

        </div>


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
                    (int)$summary['transaction_count']
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


    <!-- MEDICINE HISTORY -->

    <table>


        <thead>

            <tr>

                <th
                    class="text-center"
                    style="width:40px">

                    #

                </th>


                <th>
                    Medicine
                </th>


                <th>
                    Generic Name
                </th>


                <th>
                    Dosage / Form
                </th>


                <th
                    class="text-center">

                    Transactions

                </th>


                <th
                    class="text-center">

                    Total Quantity

                </th>


                <th
                    class="text-right">

                    Total Amount

                </th>

            </tr>

        </thead>


        <tbody>


        <?php if (empty($medicines)): ?>


            <tr>

                <td
                    colspan="7"
                    class="empty-state">

                    No medicine purchase record.

                </td>

            </tr>


        <?php else: ?>


            <?php

            $counter = 1;

            ?>


            <?php foreach ($medicines as $medicine): ?>


                <tr>


                    <td class="text-center">

                        <?= $counter++ ?>

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

                        <?= htmlspecialchars(
                            $medicine['dosage_form']
                                ?? '-'
                        ) ?>

                    </td>


                    <td class="text-center">

                        <?= number_format(
                            (int)$medicine[
                                'transaction_count'
                            ]
                        ) ?>

                    </td>


                    <td class="text-center">

                        <strong>

                            <?= number_format(
                                (int)$medicine[
                                    'total_quantity'
                                ]
                            ) ?>

                        </strong>

                    </td>


                    <td class="text-right">

                        <strong>

                            ₱<?= number_format(
                                (float)$medicine[
                                    'total_amount'
                                ],
                                2
                            ) ?>

                        </strong>

                    </td>


                </tr>


            <?php endforeach; ?>


        <?php endif; ?>


        </tbody>


        <?php if (!empty($medicines)): ?>

        <tfoot>

            <tr>

                <th
                    colspan="5"
                    class="text-right">

                    GRAND TOTAL:

                </th>


                <th class="text-center">

                    <?= number_format(
                        (int)$summary[
                            'total_quantity'
                        ]
                    ) ?>

                </th>


                <th class="text-right">

                    ₱<?= number_format(
                        (float)$summary[
                            'total_amount'
                        ],
                        2
                    ) ?>

                </th>

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

            Patient:
            <?= htmlspecialchars(
                $patientName
            ) ?>

        </div>


        <div>

            Generated:
            <?= htmlspecialchars(
                $reportDate
            ) ?>

        </div>

    </div>


</div>


</body>

</html>