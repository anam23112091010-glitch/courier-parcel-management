<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Pricing & Calculator';

$rates = [];
foreach (['standard', 'express', 'sameday'] as $s) {
    $rates[$s] = ['base' => (float)get_setting($s . '_base'), 'perkg' => (float)get_setting($s . '_perkg')];
}
$rates['outside'] = (float)get_setting('outside_extra');
$rates['cod']     = (float)get_setting('cod_percent');

$extraJs = '<script>var RATES=' . json_encode($rates) . ';</script>' . <<<'JS'
<script>
function calc() {
    var w = Math.max(0.1, parseFloat(document.getElementById('weight').value) || 0.1);
    var s = document.getElementById('service').value;
    var city = document.getElementById('city').value;
    var cod = parseFloat(document.getElementById('cod').value) || 0;
    var extraKg = Math.max(0, Math.ceil(w) - 1);
    var base = RATES[s].base, perkg = extraKg * RATES[s].perkg;
    var outside = city === 'Dhaka' ? 0 : RATES.outside;
    var codFee = cod > 0 ? Math.round(cod * RATES.cod) / 100 : 0;
    var total = base + perkg + outside + codFee;
    document.getElementById('r_base').textContent = '৳' + base.toFixed(2);
    document.getElementById('r_kg').textContent = '৳' + perkg.toFixed(2);
    document.getElementById('r_out').textContent = '৳' + outside.toFixed(2);
    document.getElementById('r_cod').textContent = '৳' + codFee.toFixed(2);
    document.getElementById('r_total').textContent = '৳' + total.toFixed(2);
}
document.querySelectorAll('#calcForm input, #calcForm select').forEach(function (el) {
    el.addEventListener('input', calc); el.addEventListener('change', calc);
});
calc();
</script>
JS;
include __DIR__ . '/includes/header.php';
?>
<section class="page-banner text-center">
    <div class="container"><h1>Pricing &amp; Calculator</h1><p class="mb-0 text-light">Know your delivery cost before you book</p></div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card-box p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-calculator text-orange"></i> Delivery Charge Calculator</h5>
                    <form id="calcForm" onsubmit="return false">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Parcel Weight (kg)</label>
                                <input type="number" step="0.1" min="0.1" value="1" id="weight" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Service Type</label>
                                <select id="service" class="form-select">
                                    <?php foreach (service_types() as $k => $v): ?>
                                        <option value="<?= $k ?>"><?= e($v['label']) ?> (<?= e($v['days']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Delivery City</label>
                                <select id="city" class="form-select">
                                    <?php foreach (bd_cities() as $c): ?>
                                        <option><?= e($c) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">COD Amount (৳) <small class="text-muted">optional</small></label>
                                <input type="number" min="0" value="0" id="cod" class="form-control">
                            </div>
                        </div>
                    </form>
                    <div class="charge-box mt-4">
                        <div class="d-flex justify-content-between small"><span>Base charge</span><span id="r_base"></span></div>
                        <div class="d-flex justify-content-between small"><span>Extra weight</span><span id="r_kg"></span></div>
                        <div class="d-flex justify-content-between small"><span>Outside Dhaka fee</span><span id="r_out"></span></div>
                        <div class="d-flex justify-content-between small"><span>COD fee</span><span id="r_cod"></span></div>
                        <hr class="border-secondary">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Estimated Total</span><span class="total" id="r_total"></span>
                        </div>
                    </div>
                    <a href="<?= url('user/book.php') ?>" class="btn btn-orange w-100 mt-3 py-2">Book This Parcel</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card-box p-4 mb-4">
                    <h5 class="fw-bold mb-3">Rate Card</h5>
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Service</th><th>Base (1 kg)</th><th>Each extra kg</th><th>Time</th></tr></thead>
                        <tbody>
                        <?php foreach (service_types() as $k => $v): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($v['label']) ?></td>
                                <td><?= money(get_setting($k . '_base')) ?></td>
                                <td><?= money(get_setting($k . '_perkg')) ?></td>
                                <td><small><?= e($v['days']) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-box p-4">
                    <h5 class="fw-bold mb-3">Good to know</h5>
                    <ul class="mb-0 small text-muted">
                        <li class="mb-2">Parcels outside Dhaka have an extra fee of <b><?= money(get_setting('outside_extra')) ?></b>.</li>
                        <li class="mb-2">Cash on delivery service fee is <b><?= e(get_setting('cod_percent')) ?>%</b> of the COD amount.</li>
                        <li class="mb-2">Weight is rounded up to the next full kilogram.</li>
                        <li>Same Day delivery is available inside Dhaka only.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
