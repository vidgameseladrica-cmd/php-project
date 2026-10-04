<?php

declare(strict_types=1);

$pageTitle = 'Add Medical History';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';


// =========================================================
// GET PATIENT ID
// =========================================================

$patientId = (int)($_GET['patient_id'] ?? 0);


// =========================================================
// GET PATIENT
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

$stmt->execute([$patientId]);

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
                Add Medical History
            </h5>

            <small class="text-muted">
                Patient medical consultation record
            </small>

        </div>


        <a
            href="view.php?id=<?= $patientId ?>"
            class="btn btn-secondary">

            ← Back to Patient

        </a>

    </div>


    <!-- =====================================================
         PATIENT INFORMATION
    ====================================================== -->

    <div class="profile-grid mb-4">

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

    </div>


    <!-- =====================================================
         MEDICAL HISTORY FORM
    ====================================================== -->

    <form
        method="post"
        action="medical_history_save.php"
        autocomplete="off">


        <!-- Patient ID -->

        <input
            type="hidden"
            name="patient_id"
            value="<?= $patientId ?>">


        <div class="row g-3">


            <!-- =================================================
                 RECORD DATE
            ================================================== -->

            <div class="col-md-4">

                <label class="form-label">
                    Record Date *
                </label>

                <input
                    type="date"
                    name="record_date"
                    class="form-control"
                    value="<?= date('Y-m-d') ?>"
                    required>

            </div>


            <!-- =================================================
                 PHYSICIAN
            ================================================== -->

            <div class="col-md-4">

                <label class="form-label">
                    Physician
                </label>

                <input
                    type="text"
                    name="physician"
                    class="form-control"
                    placeholder="Enter physician name">

            </div>


            <!-- =================================================
                 DIAGNOSIS
            ================================================== -->

            <div class="col-md-4">

                <label class="form-label">
                    Diagnosis *
                </label>

                <input
                    type="text"
                    name="diagnosis"
                    class="form-control"
                    placeholder="Enter diagnosis"
                    required>

            </div>


            <!-- =================================================
                 NOTES
            ================================================== -->

            <div class="col-md-12">

                <label class="form-label">
                    Medical Notes / Findings
                </label>

                <textarea
                    name="notes"
                    class="form-control"
                    rows="5"
                    placeholder="Enter medical findings, symptoms, treatment notes, etc."></textarea>

            </div>


        </div>


        <!-- =====================================================
             BUTTONS
        ====================================================== -->

        <div class="mt-4 d-flex gap-2">

            <button
                type="submit"
                class="btn btn-primary">

                💾 Save Medical Record

            </button>


            <a
                href="view.php?id=<?= $patientId ?>"
                class="btn btn-secondary">

                Cancel

            </a>

        </div>


    </form>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>