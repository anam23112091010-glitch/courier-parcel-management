<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Contact Us';

$errors = [];
$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($old as $k => $_) {
        $old[$k] = trim($_POST[$k] ?? '');
    }
    if ($old['name'] === '' || $old['subject'] === '' || $old['message'] === '') {
        $errors[] = 'Please fill in all the fields.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!$errors) {
        $st = $pdo->prepare('INSERT INTO messages (name, email, subject, message) VALUES (?,?,?,?)');
        $st->execute([$old['name'], $old['email'], $old['subject'], $old['message']]);
        flash('success', 'Thank you! Your message has been sent. We will reply soon.');
        redirect('contact.php');
    }
}
include __DIR__ . '/includes/header.php';
?>
<section class="page-banner text-center">
    <div class="container"><h1>Contact Us</h1><p class="mb-0 text-light">We would love to hear from you</p></div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="feature-card mb-3"><div class="d-flex"><i class="bi bi-geo-alt-fill text-orange fs-3 me-3"></i><div><b>Head Office</b><br><small class="text-muted"><?= e(get_setting('company_address')) ?></small></div></div></div>
                <div class="feature-card mb-3"><div class="d-flex"><i class="bi bi-telephone-fill text-orange fs-3 me-3"></i><div><b>Phone</b><br><small class="text-muted"><?= e(get_setting('company_phone')) ?></small></div></div></div>
                <div class="feature-card mb-3"><div class="d-flex"><i class="bi bi-envelope-fill text-orange fs-3 me-3"></i><div><b>Email</b><br><small class="text-muted"><?= e(get_setting('company_email')) ?></small></div></div></div>
                <div class="feature-card"><div class="d-flex"><i class="bi bi-clock-fill text-orange fs-3 me-3"></i><div><b>Working Hours</b><br><small class="text-muted">Saturday - Thursday, 9:00 AM - 9:00 PM</small></div></div></div>
            </div>
            <div class="col-lg-7">
                <div class="card-box p-4">
                    <h5 class="fw-bold mb-3">Send us a message</h5>
                    <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Your Name</label><input type="text" name="name" class="form-control" value="<?= e($old['name']) ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>" required></div>
                            <div class="col-12"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" value="<?= e($old['subject']) ?>" required></div>
                            <div class="col-12"><label class="form-label">Message</label><textarea name="message" rows="5" class="form-control" required><?= e($old['message']) ?></textarea></div>
                            <div class="col-12"><button class="btn btn-orange px-5 py-2"><i class="bi bi-send"></i> Send Message</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
