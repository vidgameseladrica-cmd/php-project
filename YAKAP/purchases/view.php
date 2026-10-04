<?php

declare(strict_types=1);

$pageTitle = 'Purchase Details';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';


// =====================================================
// GET PURCHASE ID
// =====================================================

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('Invalid purchase ID.');
}


// =====================================================
// GET PURCHASE INFORMATION
// =====================================================

$stmt = $pdo->prepare("
    SELECT
        mp.id,
        mp.transaction_no,
        mp.reference_no,
        mp.purchase_date,
        mp.total_amount,

        p.id AS patient_id,
        p.patient_code,
        p.philhealth_id,
        p.first_name,
        p.middle_name,
        p.last_name,
        p.birthdate,
        p.sex,

        u.full_name AS recorded_by

    FROM medicine_purchases mp

    INNER JOIN patients p
        ON p.id = mp.patient_id

    LEFT JOIN users u
        ON u.id = mp.created_by

    WHERE mp.id = ?

    LIMIT 1
");

$stmt->execute([$id]);

$purchase = $stmt->fetch();


// =====================================================
// CHECK PURCHASE
// =====================================================

if (!$purchase) {
    exit('Purchase record not found.');
}


// =====================================================
// GET PURCHASE ITEMS
// =====================================================

$stmt = $pdo->prepare("
    SELECT
        mpi.id,
        mpi.quantity,
        mpi.unit_price,
        mpi.total_price,

        m.medicine_name,
        m.generic_name,
        m.dosage_form

    FROM medicine_purchase_items mpi

    INNER JOIN medicines m
        ON m.id = mpi.medicine_id

    WHERE mpi.purchase_id = ?

    ORDER BY mpi.id ASC
");

$stmt->execute([$id]);

$items = $stmt->fetchAll();


// =====================================================
// TOTAL QUANTITY
// =====================================================

$totalQuantity = 0;

foreach ($items as $item) {

    $totalQuantity += (int)$item['quantity'];

}

?>

<div class="card-panel">

    <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h5 class="mb-1">
                Purchase Details
            </h5>

            <small class="text-muted">
                Medicine Purchase Transaction
            </small>

        </div>

        <div class="d-flex gap-2">

            <a
                href="print.php?id=<?= $id ?>"
                target="_blank"
                class="btn btn-success">

                🖨 Print Report

            </a>

            <a
                href="index.php"
                class="btn btn-secondary">

                Back

            </a>

        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_GET['success'])): ?>

        <div class="alert alert-success">

            <strong>Purchase successfully saved!</strong>

            The medicine inventory has been updated.

        </div>

    <?php endif; ?>


    <!-- TRANSACTION INFORMATION -->

    <h5 class="mb-3">
        Transaction Information
    </h5>

    <div class="profile-grid mb-4">

        <div>

            <small>
                Transaction No.
            </small>

            <strong>
                <?= htmlspecialchars(
                    $purchase['transaction_no']
                ) ?>
            </strong>

        </div>


        <div>

            <small>
                Reference No.
            </small>

            <strong>
                <?= htmlspecialchars(
                    $purchase['reference_no']
                ) ?>
            </strong>

        </div>


        <div>

            <small>
                Purchase Date
            </small>

            <strong>
                <?= htmlspecialchars(
                    $purchase['purchase_date']
                ) ?>
            </strong>

        </div>


        <div>

            <small>
                Recorded By
            </small>

            <strong>
                <?= htmlspecialchars(
                    $purchase['recorded_by'] ?? '-'
                ) ?>
            </strong>

        </div>

    </div>


    <!-- PATIENT INFORMATION -->

    <h5 class="mb-3">
        Patient Information
    </h5>

    <div class="profile-grid mb-4">

        <div>

            <small>
                Patient ID
            </small>

            <strong>
                <?= htmlspecialchars(
                    $purchase['patient_code']
                ) ?>
            </strong>

        </div>


        <div>

            <small>
                PhilHealth ID
            </small>

            <strong>
                <?= htmlspecialchars(
                    $purchase['philhealth_id']
                ) ?>
            </strong>

        </div>


        <div class="full">

            <small>
                Patient Name
            </small>

            <strong>

                <?= htmlspecialchars(
                    $purchase['last_name']
                    . ', '
                    . $purchase['first_name']
                    . ' '
                    . ($purchase['middle_name'] ?? '')
                ) ?>

            </strong>

        </div>


        <div>

            <small>
                Birthdate
            </small>

            <strong>
                <?= htmlspecialchars(
                    $purchase['birthdate']
                ) ?>
            </strong>

        </div>


        <div>

            <small>
                Sex
            </small>

            <strong>
                <?= htmlspecialchars(
                    $purchase['sex']
                ) ?>
            </strong>

        </div>

    </div>


    <!-- PURCHASED MEDICINES -->

    <h5 class="mb-3">
        Purchased Medicines
    </h5>


    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead>

                <tr>

                    <th>#</th>

                    <th>Medicine</th>

                    <th>Generic Name</th>

                    <th>Dosage Form</th>

                    <th class="text-center">
                        Quantity
                    </th>

                    <th class="text-end">
                        Unit Price
                    </th>

                    <th class="text-end">
                        Total Amount
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php if ($items): ?>

                <?php

                $counter = 1;

                foreach ($items as $item):

                ?>

                <tr>

                    <td>
                        <?= $counter++ ?>
                    </td>


                    <td>

                        <strong>
                            <?= htmlspecialchars(
                                $item['medicine_name']
                            ) ?>
                        </strong>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $item['generic_name'] ?? '-'
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $item['dosage_form'] ?? '-'
                        ) ?>

                    </td>


                    <td class="text-center">

                        <span class="badge text-bg-primary">

                            <?= number_format(
                                (int)$item['quantity']
                            ) ?>

                        </span>

                    </td>


                    <td class="text-end">

                        ₱<?= number_format(
                            (float)$item['unit_price'],
                            2
                        ) ?>

                    </td>


                    <td class="text-end">

                        <strong>

                            ₱<?= number_format(
                                (float)$item['total_price'],
                                2
                            ) ?>

                        </strong>

                    </td>

                </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="7"
                        class="text-center text-muted">

                        No medicine items found.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>


            <!-- GRAND TOTAL -->

            <tfoot>

                <tr>

                    <th
                        colspan="4"
                        class="text-end">

                        GRAND TOTAL:

                    </th>


                    <th class="text-center">

                        <?= number_format(
                            $totalQuantity
                        ) ?>

                    </th>


                    <th></th>


                    <th class="text-end">

                        <strong class="fs-5">

                            ₱<?= number_format(
                                (float)$purchase['total_amount'],
                                2
                            ) ?>

                        </strong>

                    </th>

                </tr>

            </tfoot>

        </table>

    </div>


    <!-- BOTTOM BUTTONS -->

    <div class="mt-4 d-flex justify-content-between">

        <a
            href="/YAKAP/patients/Patientmedicine.php?id=<?= (int)$purchase['patient_id'] ?>"
            class="btn btn-outline-primary">

            💊 Patient Medicine History

        </a>


        <div class="d-flex gap-2">

            <a
                href="print.php?id=<?= $id ?>"
                target="_blank"
                class="btn btn-success">

                🖨 Print Purchase

            </a>


            <a
                href="index.php"
                class="btn btn-secondary">

                Back to Purchases

            </a>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>