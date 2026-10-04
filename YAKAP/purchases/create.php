<?php

declare(strict_types=1);

$pageTitle = 'Create Medicine Purchase';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';


// =====================================================
// GET PATIENTS
// =====================================================

$patients = $pdo->query("
    SELECT
        id,
        patient_code,
        philhealth_id,
        first_name,
        middle_name,
        last_name
    FROM patients
    WHERE status = 'Active'
    ORDER BY last_name, first_name
")->fetchAll();


// =====================================================
// GET MEDICINES WITH INVENTORY
// =====================================================

$medicines = $pdo->query("
    SELECT
        m.id,
        m.medicine_name,
        m.generic_name,
        m.dosage_form,
        m.unit_price,
        COALESCE(i.quantity, 0) AS stock
    FROM medicines m

    LEFT JOIN medicine_inventory i
        ON i.medicine_id = m.id

    WHERE m.status = 'Active'

    ORDER BY m.medicine_name
")->fetchAll();

?>

<div class="card-panel">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h5 class="mb-1">
                Create Medicine Purchase
            </h5>

            <small class="text-muted">
                Select patient and add purchased medicines
            </small>
        </div>

        <a
            href="index.php"
            class="btn btn-secondary">

            Back

        </a>

    </div>


    <!-- =====================================================
         PURCHASE INFORMATION
    ====================================================== -->

    <div class="row g-3 mb-4">

        <div class="col-md-6">

            <label class="form-label">
                Patient *
            </label>

            <select
                id="patient_id"
                class="form-select"
                required>

                <option value="">
                    -- Select Patient --
                </option>

                <?php foreach ($patients as $p): ?>

                    <option value="<?= (int)$p['id'] ?>">

                        <?= htmlspecialchars(
                            $p['patient_code']
                        ) ?>

                        -
                        <?= htmlspecialchars(
                            $p['last_name']
                            . ', '
                            . $p['first_name']
                            . ' '
                            . ($p['middle_name'] ?? '')
                        ) ?>

                        -

                        <?= htmlspecialchars(
                            $p['philhealth_id']
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <div class="col-md-3">

            <label class="form-label">
                Reference No. *
            </label>

            <input
                type="text"
                id="reference_no"
                class="form-control"
                placeholder="e.g. YK-2026-0001"
                maxlength="100"
                required>

        </div>


        <div class="col-md-3">

            <label class="form-label">
                Purchase Date *
            </label>

            <input
                type="date"
                id="purchase_date"
                class="form-control"
                value="<?= date('Y-m-d') ?>"
                required>

        </div>

    </div>


    <hr>


    <!-- =====================================================
         ADD MEDICINE
    ====================================================== -->

    <h5 class="mb-3">
        Add Medicine
    </h5>


    <div class="row g-3 align-items-end">

        <div class="col-md-6">

            <label class="form-label">
                Medicine
            </label>

            <select
                id="medicine_id"
                class="form-select">

                <option value="">
                    -- Select Medicine --
                </option>

                <?php foreach ($medicines as $m): ?>

                    <option
                        value="<?= (int)$m['id'] ?>"
                        data-price="<?= htmlspecialchars(
                            (string)$m['unit_price']
                        ) ?>"
                        data-stock="<?= (int)$m['stock'] ?>">

                        <?= htmlspecialchars(
                            $m['medicine_name']
                        ) ?>

                        <?php if (!empty($m['generic_name'])): ?>

                            -
                            <?= htmlspecialchars(
                                $m['generic_name']
                            ) ?>

                        <?php endif; ?>

                        | Stock:
                        <?= number_format(
                            (int)$m['stock']
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <div class="col-md-2">

            <label class="form-label">
                Unit Price
            </label>

            <input
                type="text"
                id="unit_price_display"
                class="form-control"
                readonly
                value="₱0.00">

        </div>


        <div class="col-md-2">

            <label class="form-label">
                Quantity
            </label>

            <input
                type="number"
                id="quantity"
                class="form-control"
                min="1"
                value="1">

        </div>


        <div class="col-md-2">

            <button
                type="button"
                id="addMedicineBtn"
                class="btn btn-primary w-100">

                + Add Medicine

            </button>

        </div>

    </div>


    <!-- =====================================================
         PURCHASE ITEMS
    ====================================================== -->

    <div class="table-responsive mt-4">

        <table
            class="table table-hover align-middle"
            id="purchaseTable">

            <thead>

                <tr>

                    <th width="5%">
                        #
                    </th>

                    <th>
                        Medicine
                    </th>

                    <th>
                        Generic Name
                    </th>

                    <th>
                        Unit Price
                    </th>

                    <th>
                        Quantity
                    </th>

                    <th>
                        Total
                    </th>

                    <th width="8%">
                        Action
                    </th>

                </tr>

            </thead>


            <tbody id="purchaseItems">

                <tr id="emptyRow">

                    <td
                        colspan="7"
                        class="text-center text-muted py-4">

                        No medicine added yet.

                    </td>

                </tr>

            </tbody>


            <tfoot>

                <tr>

                    <th
                        colspan="4"
                        class="text-end">

                        TOTAL:

                    </th>

                    <th>

                        <span id="totalQuantity">
                            0
                        </span>

                    </th>

                    <th>

                        ₱<span id="totalAmount">
                            0.00
                        </span>

                    </th>

                    <th></th>

                </tr>

            </tfoot>

        </table>

    </div>


    <!-- =====================================================
         SUBMIT PURCHASE
    ====================================================== -->

    <div class="mt-4 d-flex justify-content-end gap-2">

        <a
            href="index.php"
            class="btn btn-secondary">

            Cancel

        </a>


        <button
            type="button"
            id="submitPurchase"
            class="btn btn-primary">

            💾 Submit Purchase

        </button>

    </div>

</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>

let purchaseItems = [];


// =====================================================
// MEDICINE SELECT
// =====================================================

document
.getElementById('medicine_id')
.addEventListener('change', function () {

    const option =
        this.options[this.selectedIndex];

    const price =
        parseFloat(
            option.dataset.price || 0
        );

    document
        .getElementById('unit_price_display')
        .value =
        '₱' + price.toFixed(2);

});


// =====================================================
// ADD MEDICINE
// =====================================================

document
.getElementById('addMedicineBtn')
.addEventListener('click', function () {

    const medicineSelect =
        document.getElementById(
            'medicine_id'
        );

    const medicineId =
        medicineSelect.value;

    const quantity =
        parseInt(
            document.getElementById(
                'quantity'
            ).value
        );


    if (!medicineId) {

        alert(
            'Please select a medicine.'
        );

        return;

    }


    if (!quantity || quantity <= 0) {

        alert(
            'Please enter a valid quantity.'
        );

        return;

    }


    const option =
        medicineSelect.options[
            medicineSelect.selectedIndex
        ];


    const medicineName =
        option.text.split(' | Stock:')[0];


    const genericName =
        medicineName;


    const price =
        parseFloat(
            option.dataset.price || 0
        );


    const stock =
        parseInt(
            option.dataset.stock || 0
        );


    if (quantity > stock) {

        alert(
            'Insufficient stock.\n\n' +
            'Available stock: ' +
            stock
        );

        return;

    }


    // Check if already added

    const existing =
        purchaseItems.find(
            item =>
                item.medicine_id ==
                medicineId
        );


    if (existing) {

        const newQuantity =
            existing.quantity +
            quantity;


        if (newQuantity > stock) {

            alert(
                'Total quantity exceeds available stock.\n\n' +
                'Available stock: ' +
                stock
            );

            return;

        }


        existing.quantity =
            newQuantity;

    } else {

        purchaseItems.push({

            medicine_id:
                parseInt(medicineId),

            medicine_name:
                medicineName,

            generic_name:
                genericName,

            unit_price:
                price,

            quantity:
                quantity

        });

    }


    renderItems();

    medicineSelect.value = '';

    document
        .getElementById(
            'unit_price_display'
        )
        .value = '₱0.00';

    document
        .getElementById(
            'quantity'
        )
        .value = 1;

});


// =====================================================
// DISPLAY ITEMS
// =====================================================

function renderItems() {

    const tbody =
        document.getElementById(
            'purchaseItems'
        );


    tbody.innerHTML = '';


    if (purchaseItems.length === 0) {

        tbody.innerHTML = `

            <tr>

                <td
                    colspan="7"
                    class="text-center text-muted py-4">

                    No medicine added yet.

                </td>

            </tr>

        `;

        updateTotals();

        return;

    }


    purchaseItems.forEach(
        (item, index) => {

            const total =
                item.unit_price *
                item.quantity;


            const row =
                document.createElement(
                    'tr'
                );


            row.innerHTML = `

                <td>
                    ${index + 1}
                </td>


                <td>

                    <strong>
                        ${escapeHtml(
                            item.medicine_name
                        )}
                    </strong>

                </td>


                <td>
                    ${escapeHtml(
                        item.generic_name
                    )}
                </td>


                <td>
                    ₱${item.unit_price.toFixed(2)}
                </td>


                <td>

                    <input
                        type="number"
                        min="1"
                        value="${item.quantity}"
                        class="form-control form-control-sm"
                        style="width:90px"
                        onchange="changeQuantity(
                            ${index},
                            this.value
                        )">

                </td>


                <td>

                    <strong>
                        ₱${total.toFixed(2)}
                    </strong>

                </td>


                <td>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger"
                        onclick="removeMedicine(
                            ${index}
                        )">

                        Remove

                    </button>

                </td>

            `;


            tbody.appendChild(row);

        }
    );


    updateTotals();

}


// =====================================================
// CHANGE QUANTITY
// =====================================================

function changeQuantity(
    index,
    value
) {

    const quantity =
        parseInt(value);


    if (!quantity || quantity <= 0) {

        alert(
            'Invalid quantity.'
        );

        renderItems();

        return;

    }


    purchaseItems[index].quantity =
        quantity;


    renderItems();

}


// =====================================================
// REMOVE MEDICINE
// =====================================================

function removeMedicine(index) {

    purchaseItems.splice(
        index,
        1
    );

    renderItems();

}


// =====================================================
// TOTALS
// =====================================================

function updateTotals() {

    let totalQuantity = 0;

    let totalAmount = 0;


    purchaseItems.forEach(
        item => {

            totalQuantity +=
                item.quantity;


            totalAmount +=
                item.unit_price *
                item.quantity;

        }
    );


    document
        .getElementById(
            'totalQuantity'
        )
        .textContent =
        totalQuantity.toLocaleString();


    document
        .getElementById(
            'totalAmount'
        )
        .textContent =
        totalAmount.toLocaleString(
            undefined,
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );

}


// =====================================================
// SUBMIT PURCHASE
// =====================================================

document
.getElementById('submitPurchase')
.addEventListener('click', function () {

    const patientId =
        document.getElementById(
            'patient_id'
        ).value;


    const referenceNo =
        document.getElementById(
            'reference_no'
        ).value.trim();


    const purchaseDate =
        document.getElementById(
            'purchase_date'
        ).value;


    if (!patientId) {

        alert(
            'Please select a patient.'
        );

        return;

    }


    if (!referenceNo) {

        alert(
            'Please enter the Reference No.'
        );

        return;

    }


    if (!purchaseDate) {

        alert(
            'Please select the purchase date.'
        );

        return;

    }


    if (purchaseItems.length === 0) {

        alert(
            'Please add at least one medicine.'
        );

        return;

    }


    if (!confirm(
        'Are you sure you want to save this purchase?'
    )) {

        return;

    }


    // Create form

    const form =
        document.createElement(
            'form'
        );


    form.method = 'POST';

    form.action = 'save.php';


    // Patient

    addHidden(
        form,
        'patient_id',
        patientId
    );


    // Reference

    addHidden(
        form,
        'reference_no',
        referenceNo
    );


    // Date

    addHidden(
        form,
        'purchase_date',
        purchaseDate
    );


    // Items

    purchaseItems.forEach(
        (item, index) => {

            addHidden(
                form,
                `medicine_id[${index}]`,
                item.medicine_id
            );


            addHidden(
                form,
                `quantity[${index}]`,
                item.quantity
            );


            addHidden(
                form,
                `unit_price[${index}]`,
                item.unit_price
            );

        }
    );


    document.body.appendChild(
        form
    );


    form.submit();

});


// =====================================================
// HIDDEN INPUT
// =====================================================

function addHidden(
    form,
    name,
    value
) {

    const input =
        document.createElement(
            'input'
        );


    input.type = 'hidden';

    input.name = name;

    input.value = value;


    form.appendChild(
        input
    );

}


// =====================================================
// HTML ESCAPE
// =====================================================

function escapeHtml(text) {

    const div =
        document.createElement(
            'div'
        );

    div.textContent =
        text ?? '';

    return div.innerHTML;

}

</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>