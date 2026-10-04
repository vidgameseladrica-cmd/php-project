<?php
$pageTitle = 'Medicine Purchase History';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
$rows = $pdo->query("SELECT p.reference_no, p.purchase_date, CONCAT(pt.last_name, ', ', pt.first_name) patient_name,
                            p.total_amount, u.full_name pharmacist
                     FROM medicine_purchases p
                     JOIN patients pt ON pt.id=p.patient_id
                     LEFT JOIN users u ON u.id=p.pharmacist_id
                     ORDER BY p.id DESC LIMIT 100")->fetchAll();
?>
<div class="card-panel">
<div class="d-flex justify-content-between align-items-center mb-3">
<h5>Patient Purchased Medicine  and Services History</h5>
<a class="btn btn-primary" href="create.php">+ New Purchase</a>
</div>
<div class="table-responsive"><table class="table table-hover">
<thead><tr><th>Reference No</th><th>Date</th><th>Patient</th><th>Pharmacist</th><th>Total</th></tr></thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr><td><?= htmlspecialchars($r['reference_no']) ?></td><td><?= htmlspecialchars($r['purchase_date']) ?></td><td><?= htmlspecialchars($r['patient_name']) ?></td><td><?= htmlspecialchars($r['pharmacist'] ?? '') ?></td><td>₱<?= number_format((float)$r['total_amount'],2) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
