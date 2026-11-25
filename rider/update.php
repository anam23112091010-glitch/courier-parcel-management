<?php
require_once __DIR__ . '/../config.php';
require_role('rider');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('rider/dashboard.php');
}
csrf_check();

$rid     = current_user()['id'];
$id      = (int)($_POST['id'] ?? 0);
$status  = $_POST['status'] ?? '';
$loc     = trim($_POST['location'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');

$allowed = ['Picked Up', 'In Transit', 'Out for Delivery', 'Delivered', 'Returned'];
if (!in_array($status, $allowed, true)) {
    flash('danger', 'Please choose a valid status.');
    redirect('rider/dashboard.php');
}

$st = $pdo->prepare('SELECT * FROM parcels WHERE id = ? AND rider_id = ?');
$st->execute([$id, $rid]);
$p = $st->fetch();
if (!$p) {
    flash('danger', 'This parcel is not assigned to you.');
    redirect('rider/dashboard.php');
}
if (in_array($p['status'], ['Delivered', 'Cancelled', 'Returned'], true)) {
    flash('warning', 'This parcel is already closed.');
    redirect('rider/dashboard.php');
}

$pay = $p['payment_status'];
if ($status === 'Delivered') {
    $pay = 'Paid';
}
$pdo->prepare('UPDATE parcels SET status = ?, payment_status = ? WHERE id = ?')->execute([$status, $pay, $id]);

if ($loc === '') {
    $loc = in_array($status, ['Out for Delivery', 'Delivered'], true) ? $p['receiver_city'] : 'On the way';
}
if ($remarks === '') {
    $defaults = [
        'Picked Up'        => 'Parcel picked up by rider ' . current_user()['name'],
        'In Transit'       => 'Parcel is in transit',
        'Out for Delivery' => 'Out for delivery',
        'Delivered'        => $p['payment_method'] === 'cod' ? 'Delivered. COD amount collected' : 'Delivered to receiver',
        'Returned'         => 'Parcel could not be delivered and is being returned',
    ];
    $remarks = $defaults[$status];
}
add_history($id, $status, $loc, $remarks, $rid);

flash('success', 'Parcel ' . $p['tracking_no'] . ' marked as ' . $status . '.');
redirect('rider/dashboard.php');
