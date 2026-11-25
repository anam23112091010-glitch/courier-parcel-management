<?php
/* ============ Helper functions ============ */

function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function url($path = '')
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect($path)
{
    if (!preg_match('#^https?://#', $path)) {
        $path = url($path);
    }
    header('Location: ' . $path);
    exit;
}

function money($v)
{
    return '৳' . number_format((float)$v, 2);
}

/* ---------- Flash messages ---------- */
function flash($type = null, $msg = null)
{
    if ($type !== null) {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
        return null;
    }
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function render_flash()
{
    $f = flash();
    if ($f) {
        echo '<div class="alert alert-' . e($f['type']) . ' alert-dismissible fade show" role="alert">'
           . e($f['msg'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

/* ---------- Auth ---------- */
function is_logged_in()
{
    return !empty($_SESSION['user']);
}

function current_user()
{
    return $_SESSION['user'] ?? null;
}

function dashboard_url($role = null)
{
    $role = $role ?: ($_SESSION['user']['role'] ?? 'user');
    return url($role . '/dashboard.php');
}

function require_role($roles)
{
    $roles = (array)$roles;
    if (!is_logged_in()) {
        flash('warning', 'Please login to continue.');
        redirect('login.php');
    }
    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        flash('danger', 'You do not have permission to access that page.');
        redirect(dashboard_url());
    }
}

/* ---------- CSRF ---------- */
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf']) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
            http_response_code(419);
            die('Invalid or expired form token. Please go back and try again.');
        }
    }
}

/* ---------- Settings ---------- */
function get_setting($key, $default = '')
{
    global $pdo;
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach ($pdo->query('SELECT skey, svalue FROM settings') as $r) {
            $cache[$r['skey']] = $r['svalue'];
        }
    }
    return $cache[$key] ?? $default;
}

/* ---------- Parcel helpers ---------- */
function parcel_statuses()
{
    return ['Pending', 'Confirmed', 'Picked Up', 'In Transit', 'Out for Delivery', 'Delivered', 'Cancelled', 'Returned'];
}

function status_color($s)
{
    $map = [
        'Pending'          => 'warning',
        'Confirmed'        => 'info',
        'Picked Up'        => 'primary',
        'In Transit'       => 'primary',
        'Out for Delivery' => 'orange',
        'Delivered'        => 'success',
        'Cancelled'        => 'danger',
        'Returned'         => 'secondary',
    ];
    return $map[$s] ?? 'secondary';
}

function status_badge($s)
{
    return '<span class="badge badge-status bg-' . status_color($s) . '">' . e($s) . '</span>';
}

function gen_tracking()
{
    global $pdo;
    do {
        $t = 'CPM' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $st = $pdo->prepare('SELECT id FROM parcels WHERE tracking_no = ?');
        $st->execute([$t]);
    } while ($st->fetch());
    return $t;
}

function add_history($parcelId, $status, $location, $remarks = '', $by = null)
{
    global $pdo;
    $st = $pdo->prepare('INSERT INTO tracking_history (parcel_id, status, location, remarks, updated_by) VALUES (?,?,?,?,?)');
    $st->execute([$parcelId, $status, $location, $remarks, $by]);
}

function bd_cities()
{
    return ['Dhaka', 'Chattogram', 'Sylhet', 'Rajshahi', 'Khulna', 'Barishal', 'Rangpur', 'Mymensingh',
            'Cumilla', 'Gazipur', 'Narayanganj', 'Bogura', 'Cox\'s Bazar', 'Jashore', 'Dinajpur', 'Tangail'];
}

function parcel_types()
{
    return ['Document', 'Clothing', 'Electronics', 'Food Item', 'Medicine', 'Cosmetics', 'Books', 'Fragile Item', 'Other'];
}

function service_types()
{
    return [
        'standard' => ['label' => 'Standard Delivery', 'days' => '3-5 days'],
        'express'  => ['label' => 'Express Delivery',  'days' => '1-2 days'],
        'sameday'  => ['label' => 'Same Day Delivery', 'days' => 'Within 24 hours'],
    ];
}

/**
 * Delivery charge = base + extra per kg (after first kg) + outside-city fee + COD fee
 */
function calc_charge($weight, $service, $city, $cod = 0)
{
    $weight  = max(0.1, (float)$weight);
    $base    = (float)get_setting($service . '_base', 60);
    $perKg   = (float)get_setting($service . '_perkg', 20);
    $outside = strtolower($city) === 'dhaka' ? 0 : (float)get_setting('outside_extra', 50);
    $extraKg = max(0, (int)ceil($weight) - 1);
    $codFee  = $cod > 0 ? round($cod * ((float)get_setting('cod_percent', 1) / 100), 2) : 0;
    return round($base + ($extraKg * $perKg) + $outside + $codFee, 2);
}

/* ---------- Pagination ---------- */
function paginate_links($total, $perPage, $page, $params = [])
{
    $pages = (int)ceil($total / $perPage);
    if ($pages <= 1) {
        return '';
    }
    unset($params['page']);
    $q = http_build_query($params);
    $q = $q ? $q . '&' : '';
    $html = '<nav><ul class="pagination pagination-sm justify-content-center mb-0">';
    $html .= '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '"><a class="page-link" href="?' . $q . 'page=' . ($page - 1) . '">&laquo;</a></li>';
    $start = max(1, $page - 2);
    $end   = min($pages, $page + 2);
    for ($i = $start; $i <= $end; $i++) {
        $html .= '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?' . $q . 'page=' . $i . '">' . $i . '</a></li>';
    }
    $html .= '<li class="page-item ' . ($page >= $pages ? 'disabled' : '') . '"><a class="page-link" href="?' . $q . 'page=' . ($page + 1) . '">&raquo;</a></li>';
    return $html . '</ul></nav>';
}

function unread_messages()
{
    global $pdo;
    return (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
}
