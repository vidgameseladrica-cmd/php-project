<?php

declare(strict_types=1);

$pageTitle = 'Patient Profile';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';


// =========================================================
// GET PATIENT ID
// =========================================================

$id = (int)($_GET['id'] ?? 0);


// =========================================================
// GET PATIENT INFORMATION
// =========================================================

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


// =========================================================
// CHECK PATIENT
// =========================================================

if (!$patient) {

    echo '
        <div class="alert alert-danger">
            Patient not found.
        </div>
    ';

    require_once __DIR__ . '/../includes/footer.php';

    exit;
}


// =========================================================
// MEDICAL HISTORY
// =========================================================

$stmt = $pdo->prepare("
    SELECT
        mr.*,
        u.full_name
    FROM medical_records mr
    LEFT JOIN users u
        ON u.id = mr.created_by
    WHERE mr.patient_id = ?
    ORDER BY mr.record_date DESC
");

$stmt->execute([$id]);

$history = $stmt->fetchAll();


// =========================================================
// PATIENT NAME
// =========================================================

$patientName =
    $patient['last_name']
    . ', '
    . $patient['first_name']
    . ' '
    . ($patient['middle_name'] ?? '');

?>

<div class="card-panel">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h5 class="mb-1">
                Medical Patient Information
            </h5>

            </div>


        <div class="d-flex gap-2">

            <a
                href="edit.php?id=<?= $id ?>"
                class="btn btn-warning">

                ✏️ Edit

            </a>


            <a
                href="Patientmedicine.php?id=<?= $id ?>"
                class="btn btn-primary">

                💊 Medicine History

            </a>


            <a
                href="print.php?id=<?= $id ?>"
                target="_blank"
                class="btn btn-outline-primary">

                🖨 Print

            </a>

        </div>

    </div>


    <!-- =====================================================
         PATIENT INFORMATION
    ====================================================== -->

    <div class="profile-grid mb-4">


        <!-- Patient ID -->

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


        <!-- PhilHealth ID -->

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


        <!-- Name -->

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


        <!-- Age -->

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


        <!-- Birthdate -->

        <div>

            <small>
                Birthdate
            </small>

            <strong>
                <?= htmlspecialchars(
                    $patient['birthdate']
                ) ?>
            </strong>

        </div>


        <!-- Sex -->

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


        <!-- Address -->

        <div class="full">

            <small>
                Address
            </small>

            <strong>
                <?= htmlspecialchars(
                    $patient['address'] ?? '-'
                ) ?>
            </strong>

        </div>


        <!-- Contact -->

        <div>

            <small>
                Contact Number
            </small>

            <strong>
                <?= htmlspecialchars(
                    $patient['contact_number'] ?? '-'
                ) ?>
            </strong>

        </div>


        <!-- Status -->

        <div>

            <small>
                Status
            </small>

            <?php if ($patient['status'] === 'Active'): ?>

                <strong>
                    <span class="badge text-bg-success">
                        Active
                    </span>
                </strong>

            <?php else: ?>

                <strong>
                    <span class="badge text-bg-secondary">
                        Inactive
                    </span>
                </strong>

            <?php endif; ?>

        </div>

    </div>


    <!-- =====================================================
         MEDICAL HISTORY
    ====================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <h5 class="mb-1">
                Medical History
            </h5>

            <small class="text-muted">
                Patient medical records and consultations
            </small>

        </div>


        <a
            href="medical_history_add.php?patient_id=<?= $id ?>"
            class="btn btn-sm btn-primary">

            + Add Medical Record

        </a>

    </div>


    <?php if (!$history): ?>

        <!-- EMPTY STATE -->

        <div class="empty-state">

            <div class="mb-2">
                🩺
            </div>

            <strong>
                No medical history recorded.
            </strong>

            <div class="small mt-1">
                There are currently no medical records
                for this patient.
            </div>

        </div>


    <?php else: ?>


        <!-- MEDICAL HISTORY RECORDS -->

        <?php foreach ($history as $record): ?>

            <div class="history-item">


                <!-- Date / Created By -->

                <div class="history-date">

                    <?= htmlspecialchars(
                        $record['record_date']
                    ) ?>

                    ·

                    <?= htmlspecialchars(
                        $record['full_name'] ?? 'Unknown User'
                    ) ?>

                </div>


                <!-- Diagnosis -->

                <strong>

                    <?= htmlspecialchars(
                        $record['diagnosis']
                        ?? 'No diagnosis'
                    ) ?>

                </strong>


                <!-- Notes -->

                <?php if (!empty($record['notes'])): ?>

                    <div class="mt-1">

                        <?= nl2br(
                            htmlspecialchars(
                                $record['notes']
                            )
                        ) ?>

                    </div>

                <?php endif; ?>


                <!-- Physician -->

                <?php if (!empty($record['physician'])): ?>

                    <small class="d-block mt-2">

                        <strong>
                            Physician:
                        </strong>

                        <?= htmlspecialchars(
                            $record['physician']
                        ) ?>

                    </small>

                <?php endif; ?>


            </div>

        <?php endforeach; ?>


    <?php endif; ?>


</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>