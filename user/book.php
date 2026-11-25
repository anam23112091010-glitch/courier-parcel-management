<?php
require_once __DIR__ . '/../config.php';
require_role('user');
$pageTitle = 'Book a Parcel';
$me = current_user();

$ust = $pdo->prepare('SELECT * FROM users WHERE id=?');
$ust->execute([$me['id']]);
$profile = $ust->fetch();

$f = [
    'sender_name' => $profile['name'], 'sender_phone' => $profile['phone'], 'sender_address' => $profile['address'],
    'receiver_name' => '', 'receiver_phone' => '', 'receiver_address' => '', 'receiver_city' => 'Dhaka',
    'parcel_type' => 'Document', 'weight' => '1', 'service_type' => 'standard',
    'payment_method' => 'prepaid', 'cod_amount' => '0', 'note' => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($f as $k => $_) {
        $f[$k] = trim($_POST[$k] ?? $f[$k]);
    }
    $weight = (float)$f['weight'];
    $cod    = $f['payment_method'] === 'cod' ? (float)$f['cod_amount'] : 0;

    $phoneRe = '/^01[3-9]\d{8}$/';
    if ($f['sender_name'] === '' || $f['sender_address'] === '') $errors[] = 'Sender name and address are required.';
    if (!preg_match($phoneRe, $f['sender_phone'])) $errors[] = 'Sender phone number is invalid.';
    if ($f['receiver_name'] === '' || $f['receiver_address'] === '') $errors[] = 'Receiver name and address are required.';
    if (!preg_match($phoneRe, $f['receiver_phone'])) $errors[] = 'Receiver phone number is invalid.';
    if (!in_array($f['receiver_city'], bd_cities(), true)) $errors[] = 'Please choose a valid delivery city.';
    if (!in_array($f['parcel_type'], parcel_types(), true)) $errors[] = 'Please choose a valid parcel type.';
    if (!isset(service_types()[$f['service_type']])) $errors[] = 'Please choose a service type.';
    if ($weight < 0.1 || $weight > 50) $errors[] = 'Weight must be between 0.1 and 50 kg.';
    if ($f['service_type'] === 'sameday' && $f['receiver_city'] !== 'Dhaka') $errors[] = 'Same Day delivery is available inside Dhaka only.';
    if ($f['payment_method'] === 'cod' && $cod <= 0) $errors[] = 'Please enter the COD amount to collect.';
    if (!in_array($f['payment_method'], ['prepaid', 'cod'], true)) $errors[] = 'Invalid payment method.';

    if (!$errors) {
        $charge = calc_charge($weight, $f['service_type'], $f['receiver_city'], $cod);
        $total  = $charge + $cod;
        $tn     = gen_tracking();

        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare('INSERT INTO parcels
                (tracking_no, user_id, sender_name, sender_phone, sender_address, receiver_name, receiver_phone, receiver_address,
                 receiver_city, parcel_type, weight, service_type, payment_method, cod_amount, delivery_charge, total_amount, note)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $st->execute([$tn, $me['id'], $f['sender_name'], $f['sender_phone'], $f['sender_address'],
                $f['receiver_name'], $f['receiver_phone'], $f['receiver_address'], $f['receiver_city'],
                $f['parcel_type'], $weight, $f['service_type'], $f['payment_method'], $cod, $charge, $total,
                $f['note'] !== '' ? $f['note'] : null]);
            $pid = (int)$pdo->lastInsertId();
            add_history($pid, 'Pending', $profile['city'], 'Booking received', $me['id']);
            $pdo->commit();
        } catch (Throwable $ex) {
            $pdo->rollBack();
            $errors[] = 'Could not save your booking. Please try again.';
        }
        if (!$errors) {
            flash('success', 'Parcel booked successfully! Your tracking number is ' . $tn);
            redirect('user/view.php?id=' . $pid);
        }
    }
}

$rates = [];
foreach (['standard', 'express', 'sameday'] as $s) {
    $rates[$s] = ['base' => (float)get_setting($s . '_base'), 'perkg' => (float)get_setting($s . '_perkg')];
}
$rates['outside'] = (float)get_setting('outside_extra');
$rates['cod']     = (float)get_setting('cod_percent');

$extraJs = '<script>var RATES=' . json_encode($rates) . ';</script>' . <<<'JS'
<script>
var $ = function (id) { return document.getElementById(id); };
function money(n) { return '৳' + n.toFixed(2); }
function calc() {
    var w = Math.max(0.1, parseFloat($('weight').value) || 0.1);
    var city = $('receiver_city').value;
    // same-day only inside Dhaka
    var sd = $('service_type').querySelector('option[value=sameday]');
    sd.disabled = city !== 'Dhaka';
    if (sd.disabled && $('service_type').value === 'sameday') { $('service_type').value = 'express'; }
    var s = $('service_type').value;
    var isCod = $('payment_method').value === 'cod';
    $('codWrap').style.display = isCod ? 'block' : 'none';
    var cod = isCod ? (parseFloat($('cod_amount').value) || 0) : 0;
    var extraKg = Math.max(0, Math.ceil(w) - 1);
    var base = RATES[s].base, kg = extraKg * RATES[s].perkg;
    var out = city === 'Dhaka' ? 0 : RATES.outside;
    var codFee = cod > 0 ? Math.round(cod * RATES.cod) / 100 : 0;
    var charge = base + kg + out + codFee;
    $('c_base').textContent = money(base); $('c_kg').textContent = money(kg);
    $('c_out').textContent = money(out);   $('c_cod').textContent = money(codFee);
    $('c_charge').textContent = money(charge);
    $('c_collect').textContent = money(cod);
    $('c_total').textContent = money(charge + cod);
}
document.querySelectorAll('#bookForm input, #bookForm select').forEach(function (el) {
    el.addEventListener('input', calc); el.addEventListener('change', calc);
});
calc();
</script>
JS;
include __DIR__ . '/../includes/panel_header.php';
?>
<?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" id="bookForm">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-box mb-4">
                <div class="card-header-x"><i class="bi bi-person-up text-orange me-2"></i> Sender (Pickup) Details</div>
                <div class="card-body-x row g-3">
                    <div class="col-md-6"><label class="form-label">Sender Name</label><input type="text" name="sender_name" class="form-control" value="<?= e($f['sender_name']) ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Sender Phone</label><input type="text" name="sender_phone" class="form-control" value="<?= e($f['sender_phone']) ?>" required></div>
                    <div class="col-12"><label class="form-label">Pickup Address</label><input type="text" name="sender_address" class="form-control" value="<?= e($f['sender_address']) ?>" required></div>
                </div>
            </div>

            <div class="card-box mb-4">
                <div class="card-header-x"><i class="bi bi-person-down text-orange me-2"></i> Receiver (Delivery) Details</div>
                <div class="card-body-x row g-3">
                    <div class="col-md-6"><label class="form-label">Receiver Name</label><input type="text" name="receiver_name" class="form-control" value="<?= e($f['receiver_name']) ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Receiver Phone</label><input type="text" name="receiver_phone" class="form-control" placeholder="01XXXXXXXXX" value="<?= e($f['receiver_phone']) ?>" required></div>
                    <div class="col-md-8"><label class="form-label">Delivery Address</label><input type="text" name="receiver_address" class="form-control" value="<?= e($f['receiver_address']) ?>" required></div>
                    <div class="col-md-4"><label class="form-label">City</label>
                        <select name="receiver_city" id="receiver_city" class="form-select">
                            <?php foreach (bd_cities() as $c): ?><option <?= $f['receiver_city'] === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-box">
                <div class="card-header-x"><i class="bi bi-box-seam text-orange me-2"></i> Parcel &amp; Service</div>
                <div class="card-body-x row g-3">
                    <div class="col-md-4"><label class="form-label">Parcel Type</label>
                        <select name="parcel_type" class="form-select"><?php foreach (parcel_types() as $t): ?><option <?= $f['parcel_type'] === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Weight (kg)</label><input type="number" step="0.1" min="0.1" max="50" name="weight" id="weight" class="form-control" value="<?= e($f['weight']) ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Service Type</label>
                        <select name="service_type" id="service_type" class="form-select">
                            <?php foreach (service_types() as $k => $v): ?><option value="<?= $k ?>" <?= $f['service_type'] === $k ? 'selected' : '' ?>><?= e($v['label']) ?> - <?= e($v['days']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select">
                            <option value="prepaid" <?= $f['payment_method'] === 'prepaid' ? 'selected' : '' ?>>Prepaid (I pay the delivery charge)</option>
                            <option value="cod" <?= $f['payment_method'] === 'cod' ? 'selected' : '' ?>>Cash on Delivery (collect from receiver)</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="codWrap" style="display:none">
                        <label class="form-label">COD Amount to Collect (৳)</label>
                        <input type="number" min="0" name="cod_amount" id="cod_amount" class="form-control" value="<?= e($f['cod_amount']) ?>">
                    </div>
                    <div class="col-12"><label class="form-label">Special Note <small class="text-muted">(optional)</small></label>
                        <input type="text" name="note" maxlength="200" class="form-control" placeholder="e.g. Fragile, call before delivery" value="<?= e($f['note']) ?>"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="charge-box sticky-top" style="top:90px">
                <h6 class="mb-3"><i class="bi bi-receipt"></i> Charge Summary</h6>
                <div class="d-flex justify-content-between small mb-1"><span>Base charge</span><span id="c_base"></span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Extra weight</span><span id="c_kg"></span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Outside Dhaka</span><span id="c_out"></span></div>
                <div class="d-flex justify-content-between small mb-1"><span>COD fee</span><span id="c_cod"></span></div>
                <hr class="border-secondary">
                <div class="d-flex justify-content-between"><span>Delivery Charge</span><b id="c_charge"></b></div>
                <div class="d-flex justify-content-between small mt-1"><span>COD to collect</span><span id="c_collect"></span></div>
                <hr class="border-secondary">
                <div class="d-flex justify-content-between align-items-center"><span>Total</span><span class="total" id="c_total"></span></div>
                <button class="btn btn-orange w-100 mt-3 py-2"><i class="bi bi-check2-circle"></i> Confirm Booking</button>
                <small class="d-block text-secondary mt-2 text-center">Final price is calculated again on the server.</small>
            </div>
        </div>
    </div>
</form>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
