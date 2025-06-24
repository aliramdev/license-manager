<?php
defined('ABSPATH') || exit;

if (!current_user_can('manage_options')) {
    wp_die('دسترسی غیرمجاز!');
}

global $wpdb;
$table_name = $wpdb->prefix . 'lm_licenses';

// پردازش حذف لایسنس
if (isset($_POST['delete_license_id']) && check_admin_referer('lm_delete_license', 'lm_delete_license_nonce')) {
    $license_id = intval($_POST['delete_license_id']);
    $wpdb->delete($table_name, ['id' => $license_id]);
    echo '<div class="notice notice-success is-dismissible"><p>لایسنس با موفقیت حذف شد.</p></div>';
}

// واکشی داده‌ها
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$query = "SELECT l.*, u.user_email, p.post_title as product_name 
          FROM {$table_name} l
          LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID
          LEFT JOIN {$wpdb->prefix}posts p ON l.product_id = p.ID
          WHERE 1=1 ";

if ($search) {
    $search_like = '%' . $wpdb->esc_like($search) . '%';
    $query .= $wpdb->prepare(" AND (u.user_email LIKE %s OR p.post_title LIKE %s OR l.system_code LIKE %s)", $search_like, $search_like, $search_like);
}

$query .= " ORDER BY l.id DESC LIMIT 100"; // محدودیت 100 رکورد

$licenses = $wpdb->get_results($query);

?>

<div class="wrap">
    <h1><i class="fas fa-key"></i> مدیریت لایسنس‌ها</h1>

    <form method="get" class="mb-3">
        <input type="hidden" name="page" value="lm_license_list">
        <input type="search" name="s" class="regular-text" placeholder="جستجو بر اساس ایمیل، محصول، کد سیستم..." value="<?= esc_attr($search) ?>">
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
                <th>تاریخ انقضا</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($licenses): foreach ($licenses as $license): ?>
                <tr>
                    <td><?= esc_html($license->id) ?></td>
                    <td><?= esc_html($license->user_email) ?></td>
                    <td><?= esc_html($license->product_name) ?></td>
                    <td><?= esc_html($license->system_code) ?></td>
                    <td><?= esc_html($license->activation_code) ?></td>
                    <td><?= esc_html($license->expires_at) ?></td>
                    <td>
                        <?php
                        $now = current_time('mysql');
                        if ($license->expires_at && $license->expires_at < $now) {
                            echo '<span style="color:red;">منقضی شده</span>';
                        } else {
                            echo '<span style="color:green;">فعال</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <form method="post" style="display:inline-block;">
                            <?php wp_nonce_field('lm_delete_license', 'lm_delete_license_nonce'); ?>
                            <input type="hidden" name="delete_license_id" value="<?= esc_attr($license->id) ?>">
                            <button type="submit" class="button button-danger" onclick="return confirm('آیا از حذف این لایسنس مطمئنید؟');">
                                <i class="fas fa-trash"></i> حذف
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="8">لایسنسی پیدا نشد.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
