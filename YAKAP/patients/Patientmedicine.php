<?php

$pageTitle = 'Patient Medicine History';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';


/*
|--------------------------------------------------------------------------
| GET PATIENT ID
|--------------------------------------------------------------------------
*/

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('Invalid patient ID.');
}


/*
|--------------------------------------------------------------------------
| GET PATIENT INFORMATION
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
| GET MEDICINE HISTORY
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

    FROM medicine_purchases AS mp

    INNER JOIN medicine_purchase_items AS mpi
        ON mpi.purchase_id = mp.id

    INNER JOIN medicines AS m
        ON m.id = mpi.medicine_id

    WHERE mp.patient_id = ?

    GROUP BY
        m.id,
        m.medicine_name,
        m.generic_name,
        m.dosage_form

    ORDER BY
        m.medicine_name ASC
");

$stmt->execute([$id]);

$medicines = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| GET SUMMARY
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT

        COUNT(DISTINCT mp.id) AS transaction_count,

        COUNT(DISTINCT mpi.medicine_id) AS medicine_count,

        COALESCE(
            SUM(mpi.quantity),
            0
        ) AS total_quantity,

        COALESCE(
            SUM(mpi.total_price),
            0
        ) AS total_amount

    FROM medicine_purchases AS mp

    INNER JOIN medicine_purchase_items AS mpi
        ON mpi.purchase_id = mp.id

    WHERE mp.patient_id = ?
");

$stmt->execute([$id]);

$summary = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| PATIENT NAME
|--------------------------------------------------------------------------
*/

$patientName =
    $patient['last_name'] . ', ' .
    $patient['first_name'] . ' ' .
    ($patient['middle_name'] ?? '');

?>

<!-- =========================================================
     PATIENT MEDICINE HISTORY
========================================================= -->

<div class="card-panel">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h5 class="mb-1">Supervised by FriendlyCare Foundation, Inc.</h5>
         <!-- <small class="text-muted">
            
        </small>-->
    </div>

    <div class="d-flex gap-2">

        <a
            href="print.php?id=<?= $id ?>"
            target="_blank"
            class="btn btn-outline-primary">

            🖨 Print Report

        </a>

        <a
            href="view.php?id=<?= $id ?>"
            class="btn btn-secondary">

            Back to Patient

        </a>

    </div>

</div>


    <!-- =====================================================
         PATIENT INFORMATION
    ====================================================== -->

    <div class="profile-grid mb-4">


        <!-- PATIENT ID -->

        <div>

            <small>
                Patient ID
            </small>

            <strong>

                <?= htmlspecialchars(
                    $patient['patient_code']
                ) ?>

            </strong>

        </div>


        <!-- PHILHEALTH ID -->

        <div>

            <small>
                PhilHealth ID
            </small>

            <strong>

                <?= htmlspecialchars(
                    $patient['philhealth_id']
                ) ?>

            </strong>

        </div>


        <!-- PATIENT NAME -->

        <div>

            <small>
                Patient Name
            </small>

            <strong>

                <?= htmlspecialchars(
                    $patientName
                ) ?>

            </strong>

        </div>


        <!-- AGE -->

        <div>

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


        <!-- SEX -->

        <div>

            <small>
                Sex
            </small>

            <strong>

                <?= htmlspecialchars(
                    $patient['sex']
                ) ?>

            </strong>

        </div>


        <!-- STATUS -->

        <div>

            <small>
                Status
            </small>

            <strong>

                <?= htmlspecialchars(
                    $patient['status']
                ) ?>

            </strong>

        </div>

    </div>


    <!-- =====================================================
         SUMMARY CARDS
    ====================================================== -->

    <div class="row g-3 mb-4">


        <!-- DIFFERENT MEDICINES -->

        <div class="col-md-3">

            <div class="stat-card">

                <span>
                    Different Medicines
                </span>

                <strong>

                    <?= number_format(
                        (int)$summary['medicine_count']
                    ) ?>

                </strong>

            </div>

        </div>


        <!-- TOTAL QUANTITY -->

        <div class="col-md-3">

            <div class="stat-card">

                <span>
                    Total Quantity
                </span>

                <strong>

                    <?= number_format(
                        (int)$summary['total_quantity']
                    ) ?>

                </strong>

            </div>

        </div>


        <!-- TRANSACTIONS -->

        <div class="col-md-3">

            <div class="stat-card">

                <span>
                    Number of Transactions
                </span>

                <strong>

                    <?= number_format(
                        (int)$summary['transaction_count']
                    ) ?>

                </strong>

            </div>

        </div>


        <!-- TOTAL AMOUNT -->

        <div class="col-md-3">

            <div class="stat-card">

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

    </div>


    <!-- =====================================================
         MEDICINE PURCHASE TABLE
    ====================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <h5 class="mb-1">
                Summary of all medicine purchases
            </h5>

            <small class="text-muted">
                Accountability of FriendlyCare Foundation, Inc.
            </small>

        </div>

    </div>


    <div class="table-responsive">

        <table class="table table-hover align-middle">


            <!-- TABLE HEADER -->

            <thead>

                <tr>

                    <th>
                        #
                    </th>

                    <th>
                        Medicine
                    </th>

                    <th>
                        Generic Name
                    </th>

                    <th>
                        Dosage Form
                    </th>

                    <th class="text-center">
                        Transactions
                    </th>

                    <th class="text-center">
                        Total Quantity
                    </th>

                    <th class="text-end">
                        Total Amount
                    </th>

                </tr>

            </thead>


            <!-- TABLE BODY -->

            <tbody>

            <?php if (empty($medicines)): ?>

                <tr>

                    <td
                        colspan="7"
                        class="text-center text-muted py-4">

                        No medicine purchase record.

                    </td>

                </tr>

            <?php else: ?>


                <?php

                $counter = 1;

                ?>


                <?php foreach ($medicines as $medicine): ?>

                    <tr>


                        <!-- NUMBER -->

                        <td>

                            <?= $counter++ ?>

                        </td>


                        <!-- MEDICINE -->

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $medicine['medicine_name']
                                ) ?>

                            </strong>

                        </td>


                        <!-- GENERIC -->

                        <td>

                            <?= htmlspecialchars(
                                $medicine['generic_name'] ?? '-'
                            ) ?>

                        </td>


                        <!-- DOSAGE -->

                        <td>

                            <?= htmlspecialchars(
                                $medicine['dosage_form'] ?? '-'
                            ) ?>

                        </td>


                        <!-- TRANSACTIONS -->

                        <td class="text-center">

                            <?= number_format(
                                (int)$medicine['transaction_count']
                            ) ?>

                        </td>


                        <!-- QUANTITY -->

                        <td class="text-center">

                            <span class="badge text-bg-primary">

                                <?= number_format(
                                    (int)$medicine['total_quantity']
                                ) ?>

                            </span>

                        </td>


                        <!-- AMOUNT -->

                        <td class="text-end">

                            <strong>

                                ₱<?= number_format(
                                    (float)$medicine['total_amount'],
                                    2
                                ) ?>

                            </strong>

                        </td>


                    </tr>

                <?php endforeach; ?>


            <?php endif; ?>

            </tbody>


            <!-- =================================================
                 GRAND TOTAL
            ================================================== -->

            <tfoot>

                <tr>

                    <th
                        colspan="5"
                        class="text-end">

                        GRAND TOTAL:

                    </th>


                    <th class="text-center">

                        <?= number_format(
                            (int)$summary['total_quantity']
                        ) ?>

                    </th>


                    <th class="text-end">

                        ₱<?= number_format(
                            (float)$summary['total_amount'],
                            2
                        ) ?>

                    </th>

                </tr>

            </tfoot>


        </table>

    </div>


</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>