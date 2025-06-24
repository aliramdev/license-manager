<?php
defined('ABSPATH') or die('No script kiddies please!');

function lm_render_license_list_page() {
    global $wpdb;
    $licenses_table = $wpdb->prefix . 'lm_licenses';
    $users = lm_get_users_list();
    $products = lm_get_products_list();

    $licenses = $wpdb->get_results("SELECT * FROM $licenses_table ORDER BY created_at DESC LIMIT 50");

    ?>
    <div class="wrap container mt-4">
        <h1><i class="fas fa-list"></i> لیست لایسنس‌ها</h1>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>شناسه</th>
                    <th>کاربر</th>
                    <th>محصول</th>
                    <th>کد لایسنس</th>
                    <th>وضعیت</th>
                    <th>تاریخ ایجاد</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($licenses as $license): ?>
                <tr>
                    <td><?php echo esc_html($license->id); ?></td>
                    <td><?php echo esc_html($users[$license->user_id] ?? ''); ?></td>
                    <td><?php echo esc_html($products[$license->product_id] ?? ''); ?></td>
                    <td><code><?php echo esc_html($license->license_code); ?></code></td>
                    <td><?php echo esc_html($license->status); ?></td>
                    <td><?php echo esc_html($license->created_at); ?></td>
                    <td>
                        <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=lm_delete_license&id=' . intval($license->id)), 'lm_delete_license'); ?>" class="btn btn-danger btn-sm" onclick="return confirm('آیا مطمئن هستید می‌خواهید حذف کنید؟');"><i class="fas fa-trash-alt"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
