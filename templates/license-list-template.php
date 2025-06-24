<?php
defined('ABSPATH') || exit;

global $wpdb;
$table_name = $wpdb->prefix . 'lm_licenses';
$licenses = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY id DESC");
?>

<div class="wrap">
    <h1 class="lm-header"><i class="fas fa-list"></i> لیست لایسنس‌ها</h1>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>کاربر</th>
                    <th>محصول</th>
                    <th>کد لایسنس</th>
                    <th>تاریخ</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($licenses)): ?>
                    <?php foreach ($licenses as $index => $license): ?>
                        <?php
                            $user_info = get_userdata($license->user_id);
                            $product = wc_get_product($license->product_id);
                        ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= esc_html($user_info ? $user_info->display_name : '—') ?></td>
                            <td><?= esc_html($product ? $product->get_name() : '—') ?></td>
                            <td><code><?= esc_html($license->license_key) ?></code></td>
                            <td><?= esc_html(date('Y-m-d', strtotime($license->created_at))) ?></td>
                            <td>
                                <?php if ($license->is_active): ?>
                                    <span class="badge bg-success">فعال</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" style="display:inline-block;" onsubmit="return confirm('آیا مطمئن هستید؟');">
                                    <input type="hidden" name="lm_action" value="delete_license">
                                    <input type="hidden" name="license_id" value="<?= esc_attr($license->id) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash-alt"></i> حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted">لایسنسی یافت نشد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
