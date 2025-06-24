<?php
defined('ABSPATH') or die('No script kiddies please!');

function lm_render_activation_codes_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'lm_activation_codes';

    // پردازش حذف یا ابطال
    if (isset($_POST['action']) && isset($_POST['activation_id']) && check_admin_referer('lm_activation_list_nonce')) {
        $activation_id = intval($_POST['activation_id']);
        if ($_POST['action'] === 'delete') {
            $wpdb->delete($table, ['id' => $activation_id]);
            echo '<div class="updated notice"><p>کد فعالسازی حذف شد.</p></div>';
        } elseif ($_POST['action'] === 'revoke') {
            $wpdb->update($table, ['status' => 'revoked'], ['id' => $activation_id]);
            echo '<div class="updated notice"><p>کد فعالسازی ابطال شد.</p></div>';
        }
    }

    // جستجو
    $search = $_GET['s'] ?? '';

    // صفحه بندی
    $paged = max(1, intval($_GET['paged'] ?? 1));
    $per_page = 20;
    $offset = ($paged - 1) * $per_page;

    $where_sql = "";
    $params = [];
    if ($search) {
        $where_sql = " WHERE activation_code LIKE %s OR system_code LIKE %s OR domain LIKE %s ";
        $like_search = '%' . $wpdb->esc_like($search) . '%';
        $params = [$like_search, $like_search, $like_search];
    }

    $total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table" . $where_sql, ...$params));
    $activations = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table" . $where_sql . " ORDER BY created_at DESC LIMIT %d OFFSET %d", ...$params, $per_page, $offset));

    $total_pages = ceil($total / $per_page);

    ?>
    <div class="wrap">
        <h1><i class="fas fa-toggle-on"></i> لیست کدهای فعالسازی</h1>

        <form method="get" class="mb-3 row gx-2 gy-2 align-items-center">
            <input type="hidden" name="page" value="lm-activation-codes">
            <div class="col-auto">
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" class="form-control" placeholder="جستجو بر اساس کد فعالسازی، کد سیستم، دامنه">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> جستجو</button>
            </div>
        </form>

        <table class="table table-striped table-bordered table-responsive">
            <thead class="table-light">
                <tr>
                    <th>کد فعالسازی</th>
                    <th>کاربر</th>
                    <th>محصول</th>
                    <th>کد سیستم</th>
                    <th>دامنه</th>
                    <th>وضعیت</th>
                    <th>تاریخ انقضا</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$activations): ?>
                    <tr><td colspan="8" class="text-center">موردی یافت نشد.</td></tr>
                <?php else: ?>
                    <?php foreach ($activations as $activation): ?>
                        <?php
                        $user = get_userdata($activation->user_id);
                        $product = wc_get_product($activation->product_id);
                        ?>
                        <tr>
                            <td><code><?php echo esc_html($activation->activation_code); ?></code></td>
                            <td><?php echo esc_html($user ? $user->display_name : 'کاربر حذف شده'); ?></td>
                            <td><?php echo esc_html($product ? $product->get_name() : 'محصول حذف شده'); ?></td>
                            <td><?php echo esc_html($activation->system_code); ?></td>
                            <td><?php echo esc_html($activation->domain); ?></td>
                            <td><?php echo esc_html($activation->status ?? 'active'); ?></td>
                            <td><?php echo esc_html($activation->expires_at); ?></td>
                            <td>
                                <form method="post" style="display:inline-block;" onsubmit="return confirm('آیا مطمئنید؟');">
                                    <?php wp_nonce_field('lm_activation_list_nonce'); ?>
                                    <input type="hidden" name="activation_id" value="<?php echo intval($activation->id); ?>">
                                    <button type="submit" name="action" value="revoke" class="btn btn-warning btn-sm" title="ابطال"><i class="fas fa-ban"></i></button>
                                    <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" title="حذف"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <li class="page-item <?php echo $p === $paged ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo esc_url(add_query_arg(['paged' => $p, 's' => $search, 'page' => 'lm-activation-codes'])); ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    <?php
}
