// Sidebar toggle (mobile)
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }

    // Confirm dialogs: <form data-confirm="Are you sure?"> or <a data-confirm="...">
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        var evt = el.tagName === 'FORM' ? 'submit' : 'click';
        el.addEventListener(evt, function (ev) {
            if (!confirm(el.getAttribute('data-confirm'))) {
                ev.preventDefault();
            }
        });
    });

    // Animated counters
    document.querySelectorAll('[data-count]').forEach(function (el) {
        var target = parseInt(el.getAttribute('data-count'), 10) || 0;
        var suffix = el.getAttribute('data-suffix') || '';
        var duration = 1200, start = null;
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / duration, 1);
            el.textContent = Math.floor(p * target).toLocaleString() + suffix;
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    });

    // Auto-hide alerts
    setTimeout(function () {
        document.querySelectorAll('.alert-dismissible').forEach(function (a) {
            if (window.bootstrap) { new bootstrap.Alert(a).close(); }
        });
    }, 6000);
});
