        </main>
        <footer class="panel-footer">
            &copy; <?= date('Y') ?> <?= e(get_setting('company_name', 'SwiftParcel Courier')) ?> &middot; Courier &amp; Parcel Management System
        </footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/js/main.js') ?>"></script>
<?php if (!empty($extraJs)) echo $extraJs; ?>
</body>
</html>
