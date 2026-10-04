<?php

$pageTitle = 'Edit Medicine';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';


/*
|--------------------------------------------------------------------------
| GET MEDICINE ID
|--------------------------------------------------------------------------
*/

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    exit('Invalid medicine ID.');

}


/*
|--------------------------------------------------------------------------
| GET MEDICINE + INVENTORY
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT

        m.id,
        m.medicine_name,
        m.generic_name,
        m.dosage_form,
        m.unit_price,
        m.status,

        COALESCE(
            i.quantity,
            0
        ) AS quantity,

        COALESCE(
            i.reorder_level,
            10
        ) AS reorder_level

    FROM medicines AS m

    LEFT JOIN medicine_inventory AS i
        ON i.medicine_id = m.id

    WHERE m.id = ?

    LIMIT 1
");

$stmt->execute([$id]);

$medicine = $stmt->fetch();


if (!$medicine) {

    exit('Medicine not found.');

}

?>

<div class="card-panel">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h5 class="mb-1">
                Update medicine and inventory information
            </h5>
        </div>


        <a
            href="index.php"
            class="btn btn-secondary">

            ← Back to Inventory

        </a>

    </div>


    <!-- =====================================================
         EDIT FORM
    ====================================================== -->

    <form
        method="post"
        action="update.php"
        autocomplete="off">


        <!-- MEDICINE ID -->

        <input
            type="hidden"
            name="id"
            value="<?= (int)$medicine['id'] ?>"
        >


        <div class="row g-3">


            <!-- MEDICINE NAME -->

            <div class="col-md-6">

                <label class="form-label">
                    Medicine Name/Services *
                </label>

                <input
                    type="text"
                    name="medicine_name"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $medicine['medicine_name']
                    ) ?>"
                    required>

            </div>


            <!-- GENERIC NAME -->

            <div class="col-md-6">

                <label class="form-label">
                    Generic Name/Description
                </label>

                <input
                    type="text"
                    name="generic_name"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $medicine['generic_name'] ?? ''
                    ) ?>">

            </div>


            <!-- DOSAGE / FORM -->

            <div class="col-md-4">

                <label class="form-label">
                    Dosage / Form
                </label>

                <input
                    type="text"
                    name="dosage_form"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $medicine['dosage_form'] ?? ''
                    ) ?>">

            </div>


            <!-- QUANTITY -->

            <div class="col-md-4">

                <label class="form-label">
                    Current Stock
                </label>

                <input
                    type="number"
                    name="quantity"
                    class="form-control"
                    value="<?= (int)$medicine['quantity'] ?>"
                    min="0"
                    required>

                <small class="text-muted">
                    Current medicine stock
                </small>

            </div>


            <!-- REORDER LEVEL -->

            <div class="col-md-4">

                <label class="form-label">
                    Reorder Level
                </label>

                <input
                    type="number"
                    name="reorder_level"
                    class="form-control"
                    value="<?= (int)$medicine['reorder_level'] ?>"
                    min="0"
                    required>

                <small class="text-muted">
                    Minimum stock level
                </small>

            </div>


            <!-- UNIT PRICE -->

            <div class="col-md-4">

                <label class="form-label">
                    Unit Price
                </label>

                <div class="input-group">

                    <span class="input-group-text">
                        ₱
                    </span>

                    <input
                        type="number"
                        name="unit_price"
                        class="form-control"
                        value="<?= number_format(
                            (float)$medicine['unit_price'],
                            2,
                            '.',
                            ''
                        ) ?>"
                        step="0.01"
                        min="0"
                        required>

                </div>

            </div>


            <!-- STATUS -->

            <div class="col-md-4">

                <label class="form-label">
                    Medicine Status
                </label>

                <select
                    name="status"
                    class="form-select"
                    required>

                    <option
                        value="Active"
                        <?= $medicine['status'] === 'Active'
                            ? 'selected'
                            : '' ?>>

                        Active

                    </option>

                    <option
                        value="Inactive"
                        <?= $medicine['status'] === 'Inactive'
                            ? 'selected'
                            : '' ?>>

                        Inactive

                    </option>

                </select>

            </div>


        </div>


        <hr class="my-4">


        <!-- BUTTONS -->

        <div class="d-flex gap-2">

            <button
                type="submit"
                class="btn btn-primary">

                Save Changes

            </button>


            <a
                href="index.php"
                class="btn btn-secondary">

                Cancel

            </a>

        </div>


    </form>


</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>