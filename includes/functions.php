<?php
require_once __DIR__ . '/../config/database.php';

// ── Database Helper Functions (Fallback if config/database.php only contains credentials) ──
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

if (!function_exists('db')) {
    function db(): mysqli {
        static $conn = null;
        if ($conn === null) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($conn->connect_error) {
                die('Database connection failed: ' . $conn->connect_error);
            }
            $conn->set_charset(DB_CHARSET);
        }
        return $conn;
    }
}

if (!function_exists('query')) {
    function query(string $sql, string $types = '', ...$params): mysqli_result|bool {
        $db   = db();
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            error_log('DB prepare error: ' . $db->error . ' | SQL: ' . $sql);
            return false;
        }
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result !== false ? $result : true;
    }
}

if (!function_exists('fetchOne')) {
    function fetchOne(string $sql, string $types = '', ...$params): ?array {
        $result = query($sql, $types, ...$params);
        if ($result instanceof mysqli_result) {
            $row = $result->fetch_assoc();
            return $row ?: null;
        }
        return null;
    }
}

if (!function_exists('fetchAll')) {
    function fetchAll(string $sql, string $types = '', ...$params): array {
        $result = query($sql, $types, ...$params);
        if ($result instanceof mysqli_result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
}

if (!function_exists('lastInsertId')) {
    function lastInsertId(): int {
        return (int) db()->insert_id;
    }
}


// ── Security ──────────────────────────────────────────────────────────────────
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}
function verify_csrf(string $token = ''): bool {
    $t = $token !== '' ? $token : ($_POST['csrf_token'] ?? '');
    return $t !== '' && hash_equals(csrf_token(), $t);
}
function generate_csrf(): string {
    return csrf_token();
}

// ── Settings ─────────────────────────────────────────────────────────────────
function setting(string $key, string $default = ''): string {
    static $cache = [];
    if (empty($cache)) {
        $rows = fetchAll('SELECT setting_key, setting_value FROM settings');
        foreach ($rows as $r) {
            $cache[$r['setting_key']] = $r['setting_value'];
        }
    }
    return $cache[$key] ?? $default;
}

// ── Slugs ─────────────────────────────────────────────────────────────────────
function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// ── Prices ────────────────────────────────────────────────────────────────────
function money(float $amount): string {
    return setting('currency_symbol', 'KSh') . ' ' . number_format($amount, 2);
}

// ── Products ──────────────────────────────────────────────────────────────────
function get_featured_products(int $limit = 8): array {
    return fetchAll(
        'SELECT * FROM products WHERE is_featured = 1 AND is_active = 1 ORDER BY created_at DESC LIMIT ?',
        'i', $limit
    );
}
function get_new_products(int $limit = 8): array {
    return fetchAll(
        'SELECT * FROM products WHERE is_new = 1 AND is_active = 1 ORDER BY created_at DESC LIMIT ?',
        'i', $limit
    );
}
function get_products(int $limit = 20, int $offset = 0, ?string $category_slug = null): array {
    if ($category_slug) {
        return fetchAll(
            'SELECT p.* FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE c.slug = ? AND p.is_active = 1
             ORDER BY p.created_at DESC LIMIT ? OFFSET ?',
            'sii', $category_slug, $limit, $offset
        );
    }
    return fetchAll(
        'SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?',
        'ii', $limit, $offset
    );
}
function get_product_by_slug(string $slug): ?array {
    return fetchOne('SELECT * FROM products WHERE slug = ? AND is_active = 1', 's', $slug);
}
function get_product_images(int $product_id): array {
    return fetchAll('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order', 'i', $product_id);
}
function product_thumb(array $product): string {
    if (!empty($product['thumbnail']) && file_exists(__DIR__ . '/../uploads/products/' . $product['thumbnail'])) {
        return BASE_URL . '/uploads/products/' . $product['thumbnail'];
    }
    return BASE_URL . '/assets/images/no-image.svg';
}
function is_on_sale(array $product): bool {
    return !empty($product['sale_price']) && (float)$product['sale_price'] < (float)$product['price'];
}
function effective_price(array $product): float {
    return is_on_sale($product) ? (float)$product['sale_price'] : (float)$product['price'];
}

// ── Categories ────────────────────────────────────────────────────────────────
function get_categories(): array {
    return fetchAll('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name');
}

// ── Carousel ──────────────────────────────────────────────────────────────────
function get_carousel_slides(): array {
    return fetchAll('SELECT * FROM carousel_slides WHERE is_active = 1 ORDER BY sort_order');
}

// ── Services ─────────────────────────────────────────────────────────────────
function get_services(bool $active_only = true): array {
    if ($active_only) {
        return fetchAll("SELECT * FROM services WHERE status='active' ORDER BY sort_order, id");
    }
    return fetchAll('SELECT * FROM services ORDER BY sort_order, id');
}
function get_featured_services(int $limit = 4): array {
    return fetchAll(
        "SELECT * FROM services WHERE is_featured=1 AND status='active' ORDER BY sort_order LIMIT ?",
        'i', $limit
    );
}
function get_service_by_slug(string $slug): ?array {
    $r = fetchOne("SELECT * FROM services WHERE slug=? AND status='active'", 's', $slug);
    return $r ?: null;
}
function service_img(array $svc): string {
    if (!empty($svc['image']) && file_exists(__DIR__ . '/../uploads/services/' . $svc['image'])) {
        return BASE_URL . '/uploads/services/' . $svc['image'];
    }
    return '';
}
function service_icon_svg(string $icon, int $size = 28): string {
    $paths = [
        'shopping_bag' => 'M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18M16 10a4 4 0 0 1-8 0',
        'briefcase'    => 'M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2M12 12v4M10 14h4',
        'gift'         => 'M20 12v10H4V12M22 7H2v5h20V7zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z',
        'truck'        => 'M1 3h15v13H1zM16 8h4l3 3v5h-7V8zM5.5 21a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM18.5 21a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z',
        'settings'     => 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z',
        'shield'       => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z',
        'star'         => 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z',
        'heart'        => 'M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z',
    ];
    $d = $paths[$icon] ?? $paths['star'];
    return '<svg width="'.$size.'" height="'.$size.'" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="'.$d.'"/></svg>';
}
function service_features(array $svc): array {
    if (empty($svc['features'])) return [];
    return array_filter(array_map('trim', explode("\n", $svc['features'])));
}
function carousel_img(array $slide): string {
    if (!empty($slide['image']) && file_exists(__DIR__ . '/../uploads/carousel/' . $slide['image'])) {
        return BASE_URL . '/uploads/carousel/' . $slide['image'];
    }
    return BASE_URL . '/assets/images/carousel-placeholder.svg';
}

// ── Cart ──────────────────────────────────────────────────────────────────────
function cart_count(): int {
    $session_id = session_id();
    $user_id    = $_SESSION['user_id'] ?? null;
    if ($user_id) {
        $row = fetchOne('SELECT SUM(quantity) as total FROM cart WHERE user_id = ?', 'i', $user_id);
    } else {
        $row = fetchOne('SELECT SUM(quantity) as total FROM cart WHERE session_id = ? AND user_id IS NULL', 's', $session_id);
    }
    return (int)($row['total'] ?? 0);
}
function get_cart_items(): array {
    $session_id = session_id();
    $user_id    = $_SESSION['user_id'] ?? null;
    if ($user_id) {
        return fetchAll(
            'SELECT c.*, p.name, p.slug, p.thumbnail, p.price, p.sale_price
             FROM cart c JOIN products p ON p.id = c.product_id
             WHERE c.user_id = ?', 'i', $user_id
        );
    }
    return fetchAll(
        'SELECT c.*, p.name, p.slug, p.thumbnail, p.price, p.sale_price
         FROM cart c JOIN products p ON p.id = c.product_id
         WHERE c.session_id = ? AND c.user_id IS NULL', 's', $session_id
    );
}
function cart_total(): float {
    $total = 0;
    foreach (get_cart_items() as $item) {
        $price = is_on_sale($item) ? (float)$item['sale_price'] : (float)$item['price'];
        $total += $price * $item['quantity'];
    }
    return $total;
}

// ── Orders ────────────────────────────────────────────────────────────────────
function generate_order_number(): string {
    return 'DKR-' . strtoupper(substr(md5(uniqid()), 0, 8));
}

// ── Coupons ───────────────────────────────────────────────────────────────────

/**
 * Validate a coupon code against the current cart subtotal.
 * Returns ['valid'=>true, 'coupon'=>$row] or ['valid'=>false, 'message'=>'...']
 */
function validate_coupon(string $code, float $subtotal): array {
    $code   = strtoupper(trim($code));
    $coupon = fetchOne('SELECT * FROM coupons WHERE code = ? AND is_active = 1', 's', $code);

    if (!$coupon) {
        return ['valid' => false, 'message' => 'Invalid coupon code.'];
    }
    $now = date('Y-m-d H:i:s');
    if (!empty($coupon['starts_at']) && $coupon['starts_at'] > $now) {
        return ['valid' => false, 'message' => 'This coupon is not active yet.'];
    }
    if (!empty($coupon['expires_at']) && $coupon['expires_at'] < $now) {
        return ['valid' => false, 'message' => 'This coupon has expired.'];
    }
    if (!is_null($coupon['max_uses']) && $coupon['uses_count'] >= $coupon['max_uses']) {
        return ['valid' => false, 'message' => 'This coupon has reached its usage limit.'];
    }
    if ($subtotal < (float)$coupon['min_order']) {
        return ['valid' => false, 'message' => 'Minimum order of ' . money((float)$coupon['min_order']) . ' required for this coupon.'];
    }
    // Per-user limit check
    $user_id = $_SESSION['user_id'] ?? null;
    if ($user_id && $coupon['per_user_limit'] > 0) {
        $user_uses = fetchOne(
            'SELECT COUNT(*) as n FROM coupon_uses WHERE coupon_id = ? AND user_id = ?',
            'ii', $coupon['id'], $user_id
        )['n'] ?? 0;
        if ($user_uses >= $coupon['per_user_limit']) {
            return ['valid' => false, 'message' => 'You have already used this coupon.'];
        }
    }
    return ['valid' => true, 'coupon' => $coupon];
}

/**
 * Calculate the discount amount for a validated coupon row + cart subtotal + shipping.
 */
function calculate_discount(array $coupon, float $subtotal, float $shipping): float {
    return match($coupon['type']) {
        'percentage'   => round($subtotal * ((float)$coupon['value'] / 100), 2),
        'fixed'        => min((float)$coupon['value'], $subtotal),
        'free_shipping'=> $shipping,
        default        => 0.0,
    };
}

/** Store applied coupon in session. */
function session_apply_coupon(array $coupon): void {
    $_SESSION['coupon'] = [
        'id'   => $coupon['id'],
        'code' => $coupon['code'],
        'type' => $coupon['type'],
        'value'=> $coupon['value'],
    ];
}

/** Remove coupon from session. */
function session_clear_coupon(): void {
    unset($_SESSION['coupon']);
}

/** Get currently applied coupon from session (re-validates against DB). */
function session_get_coupon(): ?array {
    if (empty($_SESSION['coupon']['code'])) return null;
    $coupon = fetchOne('SELECT * FROM coupons WHERE code = ? AND is_active = 1', 's', $_SESSION['coupon']['code']);
    if (!$coupon) { session_clear_coupon(); return null; }
    return $coupon;
}

/** Human-readable description of a coupon's benefit. */
function coupon_label(array $coupon): string {
    return match($coupon['type']) {
        'percentage'    => (int)$coupon['value'] . '% off',
        'fixed'         => money((float)$coupon['value']) . ' off',
        'free_shipping' => 'Free shipping',
        default         => 'Discount',
    };
}

// ── File Upload ───────────────────────────────────────────────────────────────
function upload_image(array $file, string $destination): ?string {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed)) return null;
    if ($file['size'] > 5 * 1024 * 1024) return null;
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(12)) . '.' . strtolower($ext);
    $target   = rtrim($destination, '/') . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return $filename;
    }
    return null;
}

// ── Pagination ────────────────────────────────────────────────────────────────
function paginate(int $total, int $per_page, int $current): array {
    $pages = (int) ceil($total / $per_page);
    return ['total' => $total, 'per_page' => $per_page, 'current' => $current, 'pages' => $pages];
}

// ── Inventory / Stock ─────────────────────────────────────────────────────────
function log_stock_change(int $product_id, int $change, string $type, string $note = '', ?int $admin_id = null): void {
    $before = (int)(fetchOne('SELECT stock FROM products WHERE id = ?', 'i', $product_id)['stock'] ?? 0);
    $after  = max(0, $before + $change);
    query(
        'INSERT INTO stock_logs (product_id, admin_id, type, quantity_change, quantity_before, quantity_after, note)
         VALUES (?,?,?,?,?,?,?)',
        'iisiiii',
        $product_id, $admin_id, $type, $change, $before, $after, $note
    );
}

function adjust_stock(int $product_id, int $new_stock, string $type, string $note = '', ?int $admin_id = null): void {
    $before = (int)(fetchOne('SELECT stock FROM products WHERE id = ?', 'i', $product_id)['stock'] ?? 0);
    $change = $new_stock - $before;
    query('UPDATE products SET stock = ? WHERE id = ?', 'ii', max(0, $new_stock), $product_id);
    query(
        'INSERT INTO stock_logs (product_id, admin_id, type, quantity_change, quantity_before, quantity_after, note)
         VALUES (?,?,?,?,?,?,?)',
        'iisiiii',
        $product_id, $admin_id, $type, $change, $before, max(0, $new_stock), $note
    );
}

function get_stock_log(int $product_id, int $limit = 50): array {
    return fetchAll(
        'SELECT l.*, u.first_name, u.last_name
         FROM stock_logs l
         LEFT JOIN users u ON u.id = l.admin_id
         WHERE l.product_id = ?
         ORDER BY l.created_at DESC LIMIT ?',
        'ii', $product_id, $limit
    );
}

function get_low_stock_products(?int $threshold = null): array {
    if ($threshold === null) $threshold = (int)setting('low_stock_threshold', '5');
    return fetchAll(
        'SELECT p.*, c.name AS category_name
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.is_active = 1 AND p.stock <= p.low_stock_threshold
         ORDER BY p.stock ASC'
    );
}

function stock_status(int $stock, int $threshold): string {
    if ($stock === 0) return 'out';
    if ($stock <= $threshold) return 'low';
    return 'ok';
}

// ── Reviews ───────────────────────────────────────────────────────────────────
function get_product_reviews(int $product_id, bool $approved_only = true): array {
    $sql = 'SELECT r.*, u.first_name, u.last_name
            FROM product_reviews r
            LEFT JOIN users u ON u.id = r.user_id
            WHERE r.product_id = ?';
    if ($approved_only) $sql .= ' AND r.is_approved = 1';
    $sql .= ' ORDER BY r.created_at DESC';
    return fetchAll($sql, 'i', $product_id);
}

function get_review_summary(int $product_id): array {
    $row = fetchOne(
        'SELECT AVG(rating) AS avg_rating, COUNT(*) AS total
         FROM product_reviews WHERE product_id = ? AND is_approved = 1',
        'i', $product_id
    );
    $dist = [];
    for ($i = 1; $i <= 5; $i++) $dist[$i] = 0;
    $rows = fetchAll(
        'SELECT rating, COUNT(*) AS cnt FROM product_reviews
         WHERE product_id = ? AND is_approved = 1 GROUP BY rating',
        'i', $product_id
    );
    foreach ($rows as $r) $dist[(int)$r['rating']] = (int)$r['cnt'];
    return [
        'avg'   => round((float)($row['avg_rating'] ?? 0), 1),
        'total' => (int)($row['total'] ?? 0),
        'dist'  => $dist,
    ];
}

function update_product_rating(int $product_id): void {
    $row = fetchOne(
        'SELECT AVG(rating) AS avg, COUNT(*) AS cnt FROM product_reviews
         WHERE product_id = ? AND is_approved = 1',
        'i', $product_id
    );
    query(
        'UPDATE products SET avg_rating = ?, review_count = ? WHERE id = ?',
        'dii', round((float)($row['avg'] ?? 0), 2), (int)($row['cnt'] ?? 0), $product_id
    );
}

function can_review(int $product_id): bool {
    $user = current_user();
    if (!$user) return true;
    $existing = fetchOne(
        'SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?',
        'ii', $product_id, $user['id']
    );
    return !$existing;
}

function render_stars(float $rating, string $size = '16'): string {
    $full  = floor($rating);
    $half  = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    $out   = '<span class="stars" aria-label="' . $rating . ' out of 5 stars">';
    for ($i = 0; $i < $full;  $i++) $out .= '<svg class="star star--full"  width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
    if ($half)                $out .= '<svg class="star star--half"  width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24"><defs><clipPath id="h"><rect width="12" height="24"/></clipPath></defs><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="currentColor" clip-path="url(#h)"/></svg>';
    for ($i = 0; $i < $empty; $i++) $out .= '<svg class="star star--empty" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
    $out .= '</span>';
    return $out;
}

function review_display_name(array $review): string {
    if (!empty($review['first_name'])) {
        return e($review['first_name'] . ' ' . substr($review['last_name'] ?? '', 0, 1) . '.');
    }
    return e($review['guest_name'] ?? 'Anonymous');
}

// ── Flash messages ────────────────────────────────────────────────────────────
function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}
function render_flash(): void {
    if (empty($_SESSION['flash'])) return;
    foreach ($_SESSION['flash'] as $f) {
        $cls = $f['type'] === 'success' ? 'alert-success' : ($f['type'] === 'error' ? 'alert-error' : 'alert-info');
        echo '<div class="alert ' . $cls . '">' . e($f['message']) . '</div>';
    }
    unset($_SESSION['flash']);
}
