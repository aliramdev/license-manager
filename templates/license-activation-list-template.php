<?php
defined('ABSPATH') || exit;
require_once plugin_dir_path(__FILE__) . '/../includes/jdf.php';

global $wpdb;
$table_name = $wpdb->prefix . 'lm_activation_codes';

$results = $wpdb->get_results("
    SELECT ac.*, u.display_name, p.post_title AS product_name
    FROM $table_name ac
    LEFT JOIN {$wpdb->users} u ON u.ID = ac.user_id
    LEFT JOIN {$wpdb->prefix}posts p ON p.ID = ac.product_id
    ORDER BY ac.created_at DESC
");


if (isset($_POST['lm_action']) && $_POST['lm_action'] === 'delete_activation_code') {
    global $wpdb;
    $activation_id = intval($_POST['activation_id'] ?? 0);

    if ($activation_id > 0) {
        $deleted = $wpdb->delete($wpdb->prefix . 'lm_activation_codes', ['id' => $activation_id]);
        if ($deleted) {
            echo '<div class="notice notice-success"><p>کد فعال‌سازی با موفقیت حذف شد.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>خطا در حذف کد فعال‌سازی.</p></div>';
        }
    } else {
        echo '<div class="notice notice-error"><p>شناسه معتبر نیست.</p></div>';
    }
}

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
                        <td>
                            <a href="#" class="view-user-info" data-user-id="<?= esc_attr($row->user_id); ?>">
                                <?= esc_html($row->display_name); ?>
                            </a>
                        </td>
                        <td><?= esc_html($row->product_name); ?></td>
                        <td>
                            <span class="copyable" data-full="<?= esc_attr($row->system_code); ?>">
                                <?= esc_html(substr($row->system_code, 0, 5)) . '...' . substr($row->system_code, -4); ?>
                            </span>
                        </td>
                        <td>
                            <span class="copyable" data-full="<?= esc_attr($row->activation_code); ?>">
                                <?= esc_html(substr($row->activation_code, 0, 5)) . '...' . substr($row->activation_code, -4); ?>
                            </span>
                        </td>
                        <td><?= esc_html($row->domain); ?></td>
                        <td><?= $row->expires_at ? jdate('Y/m/d', strtotime($row->expires_at)) : '---'; ?></td>
                        <td>
                            <?php
                                $expired = $row->expires_at && strtotime($row->expires_at) < time();
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

<!-- Modal -->
<div class="modal fade" id="userInfoModal" tabindex="-1" aria-labelledby="userInfoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="userInfoModalLabel">مشخصات کاربر</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
      </div>
      <div class="modal-body" id="userInfoContent">
        در حال بارگذاری...
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.copyable').forEach(function (el) {
        el.style.cursor = 'pointer';
        el.title = 'برای کپی کلیک کنید';

        el.addEventListener('click', function () {
            const text = el.getAttribute('data-full');
            navigator.clipboard.writeText(text).then(() => {
                el.innerHTML = '✅ کپی شد';
                setTimeout(() => {
                    const original = text.substring(0, 5) + '...' + text.slice(-4);
                    el.innerHTML = original;
                }, 1500);
            }).catch(() => {
                alert('کپی به کلیپ‌بورد ناموفق بود');
            });
        });
    });
});
</script>

<script>
     
jQuery(document).ready(function($) {
    $('.view-user-info').on('click', function(e) {
        e.preventDefault();
        const userId = $(this).data('user-id');  // گرفتن شناسه کاربر

        $('#userInfoContent').html('در حال بارگذاری...'); // نمایش در حال بارگذاری
        $('#userInfoModal').modal('show');  // نمایش مودال

        $.ajax({
            url: "<?= admin_url('admin-ajax.php'); ?>",  // این متغیر باید در صفحه شما به درستی تنظیم شود
            method: 'POST',
            data: {
                action: 'lm_get_user_info',
                user_id: userId,
                _ajax_nonce: '<?= wp_create_nonce('lm_user_info_nonce'); ?>'  // nonce به درستی استفاده شود
            },
            success: function(response) {
                if (response.success) {
                    $('#userInfoContent').html(`
                        <p><strong>نام:</strong> ${response.data.display_name}</p>
                        <p><strong>ایمیل:</strong> ${response.data.email}</p>
                        <p><strong>تلفن:</strong> ${response.data.phone || '—'}</p>
                    `);
                } else {
                    $('#userInfoContent').html('کاربر یافت نشد.');
                }
            },
            error: function() {
                $('#userInfoContent').html('خطا در دریافت اطلاعات.');
            }
        });
    });
});
</script>

