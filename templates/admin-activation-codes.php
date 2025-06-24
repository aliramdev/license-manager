<?php
defined('ABSPATH') || exit;

if (!current_user_can('manage_options')) {
    wp_die('دسترسی غیرمجاز!');
}

global $wpdb;
$activation_table = $wpdb->prefix . 'lm_activation_codes';
$license_table = $wpdb->prefix . 'lm_licenses';
$users_table = $wpdb->users;
$posts_table = $wpdb->prefix . 'posts';

// حذف کد فعالسازی
if (isset($_POST['delete_activation_id']) && check_admin_referer('lm_delete_activation', 'lm_delete_activation_nonce')) {
    $activation_id = intval($_POST['delete_activation_id']);
    $wpdb->delete($activation_table, ['id' => $activation_id]);
    echo '<div class="notice notice-success is-dismissible"><p>کد فعالسازی با موفقیت حذف شد.</p></div>';
}

// جستجو
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$query = "SELECT a.*, l.product_id, l.system_code, u.user_email, p.post_title as product_name
          FROM {$activation_table} a
          LEFT JOIN {$license_table} l ON a.license_id = l.id
          LEFT JOIN {$users_table} u ON l.user_id = u.ID
          LEFT JOIN {$posts_table} p ON l.product_id = p.ID
          WHERE 1=1 ";

if ($search) {
    $search_like = '%' . $wpdb->esc_like($search) . '%';
    $query .= $wpdb->prepare(" AND (u.user_email LIKE %s OR p.post_title LIKE %s OR l.system_code LIKE %s OR a.activation_code LIKE %s OR a.domain LIKE %s)", $search_like, $search_like, $search_like, $search_like, $search_like);
}

$query .= " ORDER BY a.id DESC LIMIT 100";

$activations = $wpdb->get_results($query);
?>

<div class="wrap">
    <h1><i class="fas fa-file-signature"></i> مدیریت کدهای فعالسازی</h1>

    <form method="get" class="mb-3">
        <input type="hidden" name="page" value="lm_activation_codes">
        <input type="search" name="s" class="regular-text" placeholder="جستجو بر اساس ایمیل، محصول، کد سیستم، کد فعالسازی یا دامنه..." value="<?= esc_attr($search) ?>">
        <button type="submit" class="button"><i class="fas fa-search"></i> جستجو</button>
    </form>

    <table class="wp-list-table widefat fixed striped table-bordered table-hover">
        <thead>
            <tr>
                <th>شناسه</th>
                <th>کاربر (ایمیل)</th>
                <th>محصول</th>
                <th>کد سیستم</th>
                <th>کد فعالسازی</th>
                <th>دامنه</th>
                <th>تاریخ انقضا</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($activations): foreach ($activations as $activation): ?>
                <tr>
                    <td><?= esc_html($activation->id) ?></td>
                    <td><?= esc_html($activation->user_email) ?></td>
                    <td><?= esc_html($activation->product_name) ?></td>
                    <td><?= esc_html($activation->system_code) ?></td>
                    <td><?= esc_html($activation->activation_code) ?></td>
                    <td><?= esc_html($activation->domain) ?></td>
                    <td><?= esc_html($activation->expires_at) ?></td>
                    <td>
                        <?php
                        $now = current_time('mysql');
                        if ($activation->expires_at && $activation->expires_at < $now) {
                            echo '<span style="color:red;">منقضی شده</span>';
                        } else {
                            echo '<span style="color:green;">فعال</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <form method="post" style="display:inline-block;">
                            <?php wp_nonce_field('lm_delete_activation', 'lm_delete_activation_nonce'); ?>
                            <input type="hidden" name="delete_activation_id" value="<?= esc_attr($activation->id) ?>">
                            <button type="submit" class="button button-danger" onclick="return confirm('آیا از حذف این کد فعالسازی مطمئنید؟');">
                                <i class="fas fa-trash"></i> حذف
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="9">هیچ کد فعالسازی پیدا نشد.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
