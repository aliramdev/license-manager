<?php
defined('ABSPATH') or die('No script kiddies please!');

function lm_render_activation_list_page() {
    global $wpdb;
    $activations_table = $wpdb->prefix . 'lm_activation_codes';
    $users = lm_get_users_list();
    $products = lm_get_products_list();

    $activations = $wpdb->get_results("SELECT * FROM $activations_table ORDER BY created_at DESC LIMIT 50");

    ?>
    <div class="wrap container mt-4">
        <h1><i class="fas fa-key"></i> لیست کدهای فعالسازی</h1>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>شناسه</th>
                    <th>کاربر</th>
                    <th>محصول</th>
                    <th>کد سیستم</th>
                    <th>کد فعالسازی</th>
                    <th>دامنه</th>
                    <th>تاریخ انقضا</th>
                    <th>وضعیت</th>
                    <th>تاریخ ایجاد</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activations as $act): ?>
                <tr>
                    <td><?php echo esc_html($act->id); ?></td>
                    <td><?php echo esc_html($users[$act->user_id] ?? ''); ?></td>
                    <td><?php echo esc_html($products[$act->product_id] ?? ''); ?></td>
                    <td><code><?php echo esc_html($act->system_code); ?></code></td>
                    <td><code><?php echo esc_html($act->activation_code); ?></code></td>
                    <td><?php echo esc_html($act->domain); ?></td>
                    <td><?php echo esc_html($act->expires_at); ?></td>
                    <td><?php echo esc_html($act->status); ?></td>
                    <td><?php echo esc_html($act->created_at); ?></td>
                    <td>
                        <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=lm_delete_activation&id=' . intval($act->id)), 'lm_delete_activation'); ?>" class="btn btn-danger btn-sm" onclick="return confirm('آیا مطمئن هستید می‌خواهید حذف کنید؟');"><i class="fas fa-trash-alt"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
