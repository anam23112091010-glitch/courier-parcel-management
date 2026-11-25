<?php
require_once __DIR__ . '/../config.php';
require_role(['user', 'admin']);
$me = current_user();
$id = (int)($_GET['id'] ?? 0);

if ($me['role'] === 'admin') {
    $st = $pdo->prepare('SELECT p.*, u.email AS cust_email FROM parcels p JOIN users u ON u.id = p.user_id WHERE p.id = ?');
    $st->execute([$id]);
} else {
    $st = $pdo->prepare('SELECT p.*, u.email AS cust_email FROM parcels p JOIN users u ON u.id = p.user_id WHERE p.id = ? AND p.user_id = ?');
    $st->execute([$id, $me['id']]);
}
$p = $st->fetch();
if (!$p) {
    flash('danger', 'Invoice not found.');
    redirect(dashboard_url());
}
$pageTitle = 'Invoice ' . $p['tracking_no'];
$backUrl = $me['role'] === 'admin' ? url('admin/parcel_view.php?id=' . $id) : url('user/view.php?id=' . $id);

// Invoice needs the user panel layout. Admin sees the admin layout automatically.
include __DIR__ . '/../includes/panel_header.php';
?>
<div class="text-center mb-3 no-print">
    <a href="<?= $backUrl ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
    <button onclick="window.print()" class="btn btn-orange"><i class="bi bi-printer"></i> Print Invoice</button>
</div>

<div class="invoice-box shadow-sm">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h3 class="fw-bold mb-0"><i class="bi bi-box-seam-fill text-orange"></i> <?= e(get_setting('company_name')) ?></h3>
            <small class="text-muted"><?= e(get_setting('company_address')) ?><br><?= e(get_setting('company_phone')) ?> &middot; <?= e(get_setting('company_email')) ?></small>
        </div>
        <div class="text-end">
            <h4 class="fw-bold text-orange mb-0">INVOICE</h4>
            <small class="text-muted">#INV-<?= str_pad($p['id'], 5, '0', STR_PAD_LEFT) ?><br>Date: <?= date('d M Y', strtotime($p['created_at'])) ?></small>
        </div>
    </div>
    <hr>
    <div class="row mb-4">
        <div class="col-6">
            <small class="text-muted text-uppercase">Sender</small>
            <p class="mb-0"><b><?= e($p['sender_name']) ?></b><br><?= e($p['sender_phone']) ?><br><?= e($p['sender_address']) ?></p>
        </div>
        <div class="col-6 text-end">
            <small class="text-muted text-uppercase">Receiver</small>
            <p class="mb-0"><b><?= e($p['receiver_name']) ?></b><br><?= e($p['receiver_phone']) ?><br><?= e($p['receiver_address']) ?>, <?= e($p['receiver_city']) ?></p>
        </div>
    </div>
    <div class="row mb-3 small">
        <div class="col-4"><span class="text-muted d-block">Tracking No</span><b><?= e($p['tracking_no']) ?></b></div>
        <div class="col-4"><span class="text-muted d-block">Status</span><b><?= e($p['status']) ?></b></div>
        <div class="col-4 text-end"><span class="text-muted d-block">Payment</span><b><?= $p['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Prepaid' ?> (<?= e($p['payment_status']) ?>)</b></div>
    </div>
    <table class="table table-bordered align-middle">
        <thead class="table-light"><tr><th>Description</th><th>Service</th><th class="text-end">Weight</th><th class="text-end">Amount</th></tr></thead>
        <tbody>
            <tr>
                <td><?= e($p['parcel_type']) ?> parcel to <?= e($p['receiver_city']) ?></td>
                <td><?= e(service_types()[$p['service_type']]['label']) ?></td>
                <td class="text-end"><?= e($p['weight']) ?> kg</td>
                <td class="text-end"><?= money($p['delivery_charge']) ?></td>
            </tr>
            <?php if ($p['cod_amount'] > 0): ?>
            <tr>
                <td colspan="3">Cash on Delivery amount (to be collected from receiver)</td>
                <td class="text-end"><?= money($p['cod_amount']) ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr><th colspan="3" class="text-end">Delivery Charge</th><th class="text-end"><?= money($p['delivery_charge']) ?></th></tr>
            <tr><th colspan="3" class="text-end">COD Amount</th><th class="text-end"><?= money($p['cod_amount']) ?></th></tr>
            <tr class="table-warning"><th colspan="3" class="text-end fs-6">Grand Total</th><th class="text-end fs-6"><?= money($p['total_amount']) ?></th></tr>
        </tfoot>
    </table>
    <p class="text-center text-muted small mt-4 mb-0">Thank you for choosing <?= e(get_setting('company_name')) ?>. This is a computer generated invoice and does not need a signature.</p>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
