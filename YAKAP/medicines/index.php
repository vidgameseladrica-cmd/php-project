<?php

$pageTitle = 'Medicine Inventory';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';


/*
|--------------------------------------------------------------------------
| GET MEDICINE INVENTORY
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

?>

<div class="card-panel">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-3">

    <div>
        <h5 class="mb-1">
            Pharmacist Medicine Inventory
        </h5>

        <small class="text-muted">
            Medicine stock and inventory monitoring
        </small>
    </div>

    <div class="d-flex gap-2">

    <a
        href="upload.php"
        class="btn btn-outline-success">

        📥 Import CSV

    </a>

    <a
        href="print.php"
        target="_blank"
        class="btn btn-outline-primary">

        🖨 Print Report

    </a>

    <button
        class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#medicineModal">

        + Add Medicine

    </button>

</div>

</div>


    <!-- =====================================================
         INVENTORY TABLE
    ====================================================== -->

    <div class="table-responsive">

        <table class="table table-hover align-middle">


            <thead>

                <tr>

                    <th>
                        #
                    </th>

                    <th>
                        Medicine/Services
                    </th>

                    <th>
                        Generic Name/Description
                    </th>

                    <th>
                        Dosage / Form
                    </th>

                    <!-- <th class="text-center">
                        Stock
                    </th>

                    <th class="text-center">
                        Reorder Level
                    </th> -->

                    <th>
                        Unit Price
                    </th>

                    <th>
                        Status
                    </th>

                    <th class="text-center">
                        Action
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php if (empty($rows)): ?>

                <tr>

                    <td
                        colspan="9"
                        class="text-center text-muted py-4">

                        No medicine records found.

                    </td>

                </tr>


            <?php else: ?>


                <?php

                $counter = 1;

                ?>


                <?php foreach ($rows as $r): ?>


                    <?php

                    $quantity = (int)$r['quantity'];

                    $reorderLevel = (int)$r['reorder_level'];


                    /*
                    |--------------------------------------------------------------------------
                    | STOCK STATUS
                    |--------------------------------------------------------------------------
                    */

                    if ($quantity <= 0) {

                        $stockClass = 'danger';

                        $stockStatus = 'OUT OF STOCK';

                    } elseif ($quantity <= $reorderLevel) {

                        $stockClass = 'warning';

                        $stockStatus = 'LOW STOCK';

                    } else {

                        $stockClass = 'success';

                        $stockStatus = 'OK';

                    }

                    ?>


                    <tr>


                        <!-- NUMBER -->

                        <td>

                            <?= $counter++ ?>

                        </td>


                        <!-- MEDICINE NAME -->

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $r['medicine_name']
                                ) ?>

                            </strong>

                        </td>


                        <!-- GENERIC NAME -->

                        <td>

                            <?= htmlspecialchars(
                                $r['generic_name'] ?? '-'
                            ) ?>

                        </td>


                        <!-- DOSAGE -->

                        <td>

                            <?= htmlspecialchars(
                                $r['dosage_form'] ?? '-'
                            ) ?>

                        </td>


                        <!-- STOCK 

                        <td class="text-center">

                            <strong>

                                <?= number_format(
                                    $quantity
                                ) ?>

                            </strong>

                        </td> -->


                        <!-- REORDER LEVEL 

                        <td class="text-center">

                            <?= number_format(
                                $reorderLevel
                            ) ?>

                        </td> -->


                        <!-- UNIT PRICE -->

                        <td>

                            ₱<?= number_format(
                                (float)$r['unit_price'],
                                2
                            ) ?>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <span
                                class="badge text-bg-<?= $stockClass ?>">

                                <?= $stockStatus ?>

                            </span>

                        </td>


                        <!-- ACTION -->

                        <td class="text-center">

                            <a
                                href="edit.php?id=<?= (int)$r['id'] ?>"
                                class="btn btn-sm btn-outline-primary">

                                Edit

                            </a>

                        </td>


                    </tr>


                <?php endforeach; ?>


            <?php endif; ?>


            </tbody>

        </table>

    </div>

</div>


<!-- =========================================================
     ADD MEDICINE MODAL
========================================================= -->

<div
    class="modal fade"
    id="medicineModal"
    tabindex="-1"
    aria-labelledby="medicineModalLabel"
    aria-hidden="true">


    <div class="modal-dialog modal-lg">

        <div class="modal-content">


            <form
                method="post"
                action="save.php"
                autocomplete="off">


                <!-- MODAL HEADER -->

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="medicineModalLabel">

                        Add Medicine

                    </h5>


                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>

                </div>


                <!-- MODAL BODY -->

                <div class="modal-body">

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
                                placeholder="e.g. Paracetamol 500mg"
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
                                placeholder="e.g. Paracetamol">

                        </div>


                        <!-- DOSAGE -->

                        <div class="col-md-4">

                            <label class="form-label">
                                Dosage / Form
                            </label>

                            <input
                                type="text"
                                name="dosage_form"
                                class="form-control"
                                placeholder="e.g. Tablet">

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
                                value="10"
                                min="0">

                            <small class="text-muted">
                                Minimum stock level
                            </small>

                        </div>


                        <!-- INITIAL QUANTITY -->

                        <div class="col-md-4">

                            <label class="form-label">
                                Initial Quantity
                            </label>

                            <input
                                type="number"
                                name="quantity"
                                class="form-control"
                                value="0"
                                min="0">

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
                                    step="0.01"
                                    min="0"
                                    name="unit_price"
                                    class="form-control"
                                    value="0.00">

                            </div>

                        </div>


                    </div>

                </div>


                <!-- MODAL FOOTER -->

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary">

                        Save Medicine

                    </button>

                </div>


            </form>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>