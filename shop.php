<?php
require_once __DIR__ . '/includes/init.php';

$category_slug = trim($_GET['category'] ?? '');
$q             = trim($_GET['q'] ?? '');
$sort          = $_GET['sort'] ?? 'newest';
$page_num      = max(1, (int)($_GET['page'] ?? 1));
$per_page      = 12;
$offset        = ($page_num - 1) * $per_page;

// Handle both ?filter=new (nav links) and ?filter[]=new (sidebar checkboxes)
$filters = array_filter((array)($_GET['filter'] ?? []));

// Build query
$where_parts = ['p.is_active = 1'];
$params      = [];
$types       = '';

if ($category_slug) {
    $where_parts[] = 'c.slug = ?';
    $params[]      = $category_slug;
    $types        .= 's';
}
if ($q) {
    $where_parts[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[]      = "%$q%";
    $params[]      = "%$q%";
    $types        .= 'ss';
}
if (in_array('new', $filters)) {
    $where_parts[] = 'p.is_new = 1';
}
if (in_array('featured', $filters)) {
    $where_parts[] = 'p.is_featured = 1';
}
if (in_array('sale', $filters)) {
    $where_parts[] = 'p.sale_price IS NOT NULL AND p.sale_price > 0';
}

$where_sql = implode(' AND ', $where_parts);
$join_sql  = $category_slug ? 'JOIN categories c ON c.id = p.category_id' : 'LEFT JOIN categories c ON c.id = p.category_id';

$order_sql = match($sort) {
    'price_asc'  => 'ORDER BY COALESCE(p.sale_price, p.price) ASC',
    'price_desc' => 'ORDER BY COALESCE(p.sale_price, p.price) DESC',
    'name_asc'   => 'ORDER BY p.name ASC',
    'popular'    => 'ORDER BY p.views DESC',
    default      => 'ORDER BY p.created_at DESC',
};

// Count
$count_row = fetchOne(
    "SELECT COUNT(*) as total FROM products p $join_sql WHERE $where_sql",
    $types, ...$params
);
$total      = (int)($count_row['total'] ?? 0);
$pagination = paginate($total, $per_page, $page_num);

// Products
$products = fetchAll(
    "SELECT p.*, c.name as category_name FROM products p $join_sql WHERE $where_sql $order_sql LIMIT ? OFFSET ?",
    $types . 'ii', ...[...$params, $per_page, $offset]
);

$current_category = $category_slug ? fetchOne('SELECT * FROM categories WHERE slug = ?', 's', $category_slug) : null;
$filter_labels = ['new' => 'New Arrivals', 'sale' => 'On Sale', 'featured' => 'Featured'];
$filter_title  = count($filters) === 1 ? ($filter_labels[reset($filters)] ?? null) : null;
$page_title    = $current_category ? $current_category['name'] : ($q ? "Search: $q" : ($filter_title ?? 'Shop'));
$active_page      = 'shop';
$all_categories   = get_categories();

include __DIR__ . '/includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb__list">
            <li><a href="<?= BASE_URL ?>/">Home</a></li>
            <li><a href="shop.php">Shop</a></li>
            <?php if ($current_category): ?>
            <li><?= e($current_category['name']) ?></li>
            <?php elseif ($q): ?>
            <li>Search: <?= e($q) ?></li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<div class="page-hero">
    <div class="container">
        <h1><?= e($page_title) ?></h1>
        <?php if ($current_category && !empty($current_category['description'])): ?>
        <p><?= e($current_category['description']) ?></p>
        <?php endif; ?>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="shop-layout">
            <!-- Sidebar filters -->
            <aside class="shop-sidebar">
                <div class="sidebar-widget">
                    <h3 class="sidebar-title">Categories</h3>
                    <ul class="filter-list">
                        <li class="filter-item">
                            <a href="shop.php" class="<?= !$category_slug ? 'text-green' : '' ?>" style="font-size:.9rem;color:var(--text)">All Products</a>
                        </li>
                        <?php foreach ($all_categories as $cat): ?>
                        <li class="filter-item">
                            <a href="shop.php?category=<?= e($cat['slug']) ?>"
                               style="font-size:.9rem;color:<?= $category_slug === $cat['slug'] ? 'var(--gold)' : 'var(--text)' ?>;font-weight:<?= $category_slug === $cat['slug'] ? '600' : '400' ?>">
                                <?= e($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="sidebar-widget">
                    <h3 class="sidebar-title">Filter by Type</h3>
                    <form method="get" action="shop.php">
                        <?php if ($category_slug): ?>
                        <input type="hidden" name="category" value="<?= e($category_slug) ?>">
                        <?php endif; ?>
                        <ul class="filter-list" style="margin-bottom:16px">
                            <li class="filter-item">
                                <input type="checkbox" name="filter[]" value="new" id="f_new" <?= in_array('new', $filters) ? 'checked' : '' ?>>
                                <label for="f_new">New Arrivals</label>
                            </li>
                            <li class="filter-item">
                                <input type="checkbox" name="filter[]" value="sale" id="f_sale" <?= in_array('sale', $filters) ? 'checked' : '' ?>>
                                <label for="f_sale">On Sale</label>
                            </li>
                            <li class="filter-item">
                                <input type="checkbox" name="filter[]" value="featured" id="f_featured" <?= in_array('featured', $filters) ? 'checked' : '' ?>>
                                <label for="f_featured">Featured</label>
                            </li>
                        </ul>
                        <button type="submit" class="btn btn-green btn-sm btn-block">Apply Filters</button>
                    </form>
                </div>

                <div class="sidebar-widget">
                    <h3 class="sidebar-title">Search</h3>
                    <form method="get" action="shop.php" style="display:flex;gap:8px">
                        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search…" class="form-control" style="flex:1;font-size:.85rem">
                        <button type="submit" class="btn btn-gold btn-sm">Go</button>
                    </form>
                </div>
            </aside>

            <!-- Products -->
            <div class="shop-products">
                <div class="shop-sort">
                    <label for="sortSelect">Sort by:</label>
                    <select id="sortSelect" onchange="window.location=this.value" class="form-control" style="width:auto">
                        <?php
                        $qs = array_merge($_GET, []);
                        function build_sort_url($s, $qs) {
                            $qs['sort'] = $s; unset($qs['page']);
                            return 'shop.php?' . http_build_query($qs);
                        }
                        $sorts = ['newest' => 'Newest', 'popular' => 'Most Popular', 'price_asc' => 'Price: Low–High', 'price_desc' => 'Price: High–Low', 'name_asc' => 'Name A–Z'];
                        foreach ($sorts as $val => $label):
                        ?>
                        <option value="<?= e(build_sort_url($val, $qs)) ?>" <?= $sort === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="shop-count"><?= $total ?> product<?= $total !== 1 ? 's' : '' ?></span>
                </div>

                <?php if (!empty($products)): ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-card__image">
                            <a href="product.php?slug=<?= e($product['slug']) ?>">
                                <img src="<?= product_thumb($product) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                            </a>
                            <div class="product-card__badges">
                                <?php if ($product['is_new']): ?><span class="badge badge-new">New</span><?php endif; ?>
                                <?php if (is_on_sale($product)): ?><span class="badge badge-sale">Sale</span><?php endif; ?>
                            </div>
                            <button class="product-card__wishlist btn-wishlist" data-id="<?= $product['id'] ?>" aria-label="Wishlist">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            </button>
                        </div>
                        <div class="product-card__body">
                            <p class="product-card__category"><?= e($product['category_name'] ?? '') ?></p>
                            <p class="product-card__name"><a href="product.php?slug=<?= e($product['slug']) ?>"><?= e($product['name']) ?></a></p>
                            <?php if ((float)($product['avg_rating'] ?? 0) > 0): ?>
                            <div class="product-card__rating">
                                <?= render_stars((float)$product['avg_rating'], '13') ?>
                                <span class="product-card__rating-count">(<?= $product['review_count'] ?>)</span>
                            </div>
                            <?php endif; ?>
                            <div class="product-card__price">
                                <?php if (is_on_sale($product)): ?>
                                    <span class="price-sale"><?= money((float)$product['sale_price']) ?></span>
                                    <span class="price-original"><?= money((float)$product['price']) ?></span>
                                <?php else: ?>
                                    <span class="price-current"><?= money((float)$product['price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-card__footer">
                                <a href="product.php?slug=<?= e($product['slug']) ?>" class="btn btn-outline-green btn-sm">View</a>
                                <button class="btn btn-green btn-sm btn-add-cart" data-id="<?= $product['id'] ?>">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['pages'] > 1): ?>
                <nav class="pagination" aria-label="Products pagination">
                    <?php if ($page_num > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page_num - 1])) ?>" class="page-link">&lsaquo;</a>
                    <?php endif; ?>
                    <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
                       class="page-link <?= $p === $page_num ? 'active' : '' ?>"><?= $p ?></a>
                    <?php endfor; ?>
                    <?php if ($page_num < $pagination['pages']): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page_num + 1])) ?>" class="page-link">&rsaquo;</a>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>

                <?php else: ?>
                <div class="empty-state">
                    <svg width="60" height="60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <h3>No products found</h3>
                    <p>Try adjusting your search or filters.</p>
                    <a href="shop.php" class="btn btn-green">View All Products</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>window.__csrf = "<?= e(csrf_token()) ?>";</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
