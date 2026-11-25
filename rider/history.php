<?php
require_once __DIR__ . '/../config.php';
require_role('rider');
$pageTitle = 'Delivery History';
$rid = current_user()['id'];

$st = $pdo->prepare("SELECT * FROM parcels WHERE rider_id = ? AND status IN ('Delivered','Returned') ORDER BY updated_at DESC LIMIT 100");
$st->execute([$rid]);
$rows = $st->fetchAll();
$cod = 0;
foreach ($rows as $r) {
    if ($r['status'] === 'Delivered' && $r['payment_method'] === 'cod') $cod += $r['cod_amount'];
}
include __DIR__ . '/../includes/panel_header.php';
?>
<div class="card-box">
    <div class="card-header-x">Completed Deliveries <span class="badge bg-secondary ms-2"><?= count($rows) ?></span>
        <span class="ms-auto small text-muted">Total COD collected: <b class="text-orange"><?= money($cod) ?></b></span></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Tracking</th><th>Receiver</th><th>City</th><th>COD</th><th>Completed</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="fw-semibold"><?= e($r['tracking_no']) ?></td>
                    <td><?= e($r['receiver_name']) ?><br><small class="text-muted"><?= e($r['receiver_phone']) ?></small></td>
                    <td><?= e($r['receiver_city']) ?></td>
                    <td><?= $r['cod_amount'] > 0 ? money($r['cod_amount']) : '-' ?></td>
                    <td><small><?= date('d M Y, h:i A', strtotime($r['updated_at'])) ?></small></td>
                    <td><?= status_badge($r['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-5">No completed deliveries yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
