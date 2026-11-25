<?php
require_once __DIR__ . '/../config.php';
require_role('admin');
$pageTitle = 'Messages';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'read')   $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$id]);
    if ($action === 'unread') $pdo->prepare('UPDATE messages SET is_read = 0 WHERE id = ?')->execute([$id]);
    if ($action === 'delete') { $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]); flash('success', 'Message deleted.'); }
    if ($action === 'readall') { $pdo->exec('UPDATE messages SET is_read = 1'); flash('success', 'All messages marked as read.'); }
    redirect('admin/messages.php');
}
$rows = $pdo->query('SELECT * FROM messages ORDER BY is_read ASC, created_at DESC')->fetchAll();
include __DIR__ . '/../includes/panel_header.php';
?>
<div class="card-box">
    <div class="card-header-x">Contact Messages <span class="badge bg-secondary ms-2"><?= count($rows) ?></span>
        <form method="post" class="ms-auto"><?= csrf_field() ?><input type="hidden" name="action" value="readall"><button class="btn btn-sm btn-outline-secondary">Mark all as read</button></form>
    </div>
    <div class="list-group list-group-flush">
        <?php foreach ($rows as $m): ?>
            <div class="list-group-item p-4 <?= $m['is_read'] ? '' : 'bg-warning-subtle' ?>">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <div>
                        <h6 class="mb-0"><?= $m['is_read'] ? '' : '<span class="badge bg-danger me-2">NEW</span>' ?><?= e($m['subject']) ?></h6>
                        <small class="text-muted"><?= e($m['name']) ?> &lt;<a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>&gt; &middot; <?= date('d M Y, h:i A', strtotime($m['created_at'])) ?></small>
                    </div>
                    <div class="d-flex gap-1">
                        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $m['id'] ?>"><input type="hidden" name="action" value="<?= $m['is_read'] ? 'unread' : 'read' ?>">
                            <button class="btn btn-sm btn-outline-secondary"><?= $m['is_read'] ? 'Mark unread' : 'Mark read' ?></button></form>
                        <a href="mailto:<?= e($m['email']) ?>?subject=Re: <?= rawurlencode($m['subject']) ?>" class="btn btn-sm btn-outline-orange">Reply</a>
                        <form method="post" data-confirm="Delete this message?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $m['id'] ?>"><input type="hidden" name="action" value="delete">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </div>
                </div>
                <p class="mt-3 mb-0"><?= nl2br(e($m['message'])) ?></p>
            </div>
        <?php endforeach; ?>
        <?php if (!$rows): ?><div class="p-5 text-center text-muted">No messages yet.</div><?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
