<?php
$pageTitle = 'New Medicine Purchase';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
$patients = $pdo->query("SELECT id, patient_code, philhealth_id, first_name, last_name FROM patients WHERE status='Active' ORDER BY last_name")->fetchAll();
$medicines = $pdo->query("SELECT m.id,m.medicine_name,m.unit_price,i.quantity FROM medicines m JOIN medicine_inventory i ON i.medicine_id=m.id WHERE m.status='Active' ORDER BY m.medicine_name")->fetchAll();
?>
<div class="card-panel">
<form method="post" action="save.php">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Patient</label>
<select name="patient_id" class="form-select" required><option value="">Select patient...</option>
<?php foreach($patients as $p): ?><option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['last_name'].', '.$p['first_name'].' — '.$p['philhealth_id']) ?></option><?php endforeach; ?>
</select></div>
<div class="col-md-6"><label class="form-label">Medicine</label>
<select name="medicine_id" class="form-select" required><option value="">Select medicine...</option>
<?php foreach($medicines as $m): ?><option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['medicine_name']) ?> — Stock: <?= (int)$m['quantity'] ?></option><?php endforeach; ?>
</select></div>
<div class="col-md-3"><label class="form-label">Quantity</label><input type="number" name="quantity" class="form-control" min="1" required></div>
<div class="col-md-3"><label class="form-label">Reference No.</label><input name="reference_no" class="form-control"></div>
<div class="col-md-6 d-flex align-items-end"><button class="btn btn-primary me-2">Save Purchase</button><a class="btn btn-secondary" href="index.php">Cancel</a></div>
</div>
</form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
