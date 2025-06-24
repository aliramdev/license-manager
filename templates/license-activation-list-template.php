<?php
defined('ABSPATH') || exit;

// دریافت کدهای فعالسازی
global $wpdb;
$table_name = $wpdb->prefix . 'lm_activation_codes';

$results = $wpdb->get_results("
    SELECT ac.*, u.display_name, p.post_title AS product_name
    FROM $table_name ac
    LEFT JOIN {$wpdb->users} u ON u.ID = ac.user_id
    LEFT JOIN {$wpdb->prefix}posts p ON p.ID = ac.product_id
    ORDER BY ac.created_at DESC
");
?>

<div class="wrap">
    <h1 class="lm-header"><i class="fas fa-key"></i> لیست کدهای فعال‌سازی</h1>

    <table class="table table-bordered table-hover table-striped">
        <thead class="table-dark">
            <tr>
                <th>کاربر</th>
                <th>محصول</th>
                <th>کد سیستم</th>
                <th>کد فعال‌سازی</th>
                <th>دامنه</th>
                <th>تاریخ انقضا</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($results): ?>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?= esc_html($row->display_name); ?></td>
                        <td><?= esc_html($row->product_name); ?></td>
                        <td><code><?= esc_html($row->system_code); ?></code></td>
                        <td><code><?= esc_html($row->activation_code); ?></code></td>
                        <td><?= esc_html($row->domain); ?></td>
                        <td><?= date('Y-m-d', strtotime($row->expires_at)); ?></td>
                        <td>
                            <?php
                                $expired = strtotime($row->expires_at) < time();
                                echo $expired ? '<span class="badge bg-danger">منقضی</span>' : '<span class="badge bg-success">معتبر</span>';
                            ?>
                        </td>
                        <td>
                            <form method="post" onsubmit="return confirm('آیا مطمئن هستید؟');" style="display:inline;">
                                <input type="hidden" name="lm_action" value="delete_activation_code">
                                <input type="hidden" name="activation_id" value="<?= esc_attr($row->id); ?>">
                                <?php submit_button('حذف', 'delete small', '', false); ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center">کدی یافت نشد.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
