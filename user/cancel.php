<?php
require_once __DIR__ . '/../config.php';
require_role('user');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('user/parcels.php');
}
csrf_check();

$uid = current_user()['id'];
$id  = (int)($_POST['id'] ?? 0);

$st = $pdo->prepare('SELECT * FROM parcels WHERE id = ? AND user_id = ?');
$st->execute([$id, $uid]);
$p = $st->fetch();

if (!$p) {
    flash('danger', 'Parcel not found.');
    redirect('user/parcels.php');
}
if (!in_array($p['status'], ['Pending', 'Confirmed'], true)) {
    flash('warning', 'This parcel can no longer be cancelled because it has already been picked up.');
    redirect('user/view.php?id=' . $id);
}

$pdo->prepare("UPDATE parcels SET status = 'Cancelled', rider_id = NULL WHERE id = ?")->execute([$id]);
add_history($id, 'Cancelled', $p['receiver_city'], 'Cancelled by customer', $uid);

flash('success', 'Booking ' . $p['tracking_no'] . ' has been cancelled.');
redirect('user/view.php?id=' . $id);
