<?php
defined('ABSPATH') || exit;
global $wpdb;
$table_name = $wpdb->prefix . 'lm_licenses';
$results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");

$nonce_toggle = wp_create_nonce('lm_toggle_status_nonce');
$nonce_delete = wp_create_nonce('lm_delete_license_nonce');

// بررسی زبان فارسی و بارگذاری jdf
$is_fa = strpos(get_locale(), 'fa') === 0;
if ($is_fa && !function_exists('jdate')) {
    require_once LM_PLUGIN_PATH . 'includes/jdf.php';
}
?>

<div class="container mt-4">
    <h2><i class="fa fa-key"></i> لیست لایسنس‌ها</h2>

    <table class="table table-striped table-bordered mt-3" id="licenses-table">
        <thead class="table-dark">
            <tr>
                <th>کد لایسنس</th>
                <th>کاربر</th>
                <th>محصول</th>
                <th>تاریخ تولید</th>
                <th>تاریخ انقضا</th>
                <th>وضعیت فعال</th>
                <th>وضعیت اعتبار</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row):
                $user = get_userdata($row->user_id);
                $product = get_the_title($row->product_id);
                $license_short = substr($row->license_code, 0, 5) . '...' . substr($row->license_code, -5);

                // فقط تاریخ (بدون ساعت)
                $created_date = $is_fa ? jdate('Y/m/d', strtotime($row->created_at)) : date('Y/m/d', strtotime($row->created_at));

                if (!empty($row->expiry_date)) {
                    $expiry_ts = strtotime($row->expiry_date);
                    $expire_date = $is_fa ? jdate('Y/m/d', $expiry_ts) : date('Y/m/d', $expiry_ts);

                    $is_expired = ($expiry_ts < time());
                    $status_text_expire = $is_expired ? 'منقضی' : 'معتبر';
                    $status_class_expire = $is_expired ? 'danger' : 'success';
                } else {
                    $expire_date = '---';
                    $status_text_expire = 'نامشخص';
                    $status_class_expire = 'secondary';
                }

                // وضعیت فعال/غیرفعال
                $status_active = $row->status === 'active' ? 'فعال' : 'غیرفعال';
                $status_class_active = $row->status === 'active' ? 'success' : 'secondary';
                ?>
                <tr>
                    <td>
                        <span class="badge bg-info text-dark copy-license"
                            data-license="<?= esc_attr($row->license_code) ?>" style="cursor: pointer;">
                            <?= esc_html($license_short) ?>
                        </span>
                    </td>
                    <td>
                        <a href="#" class="view-user-info" data-user-id="<?= esc_attr($row->user_id); ?>">
                            <?= esc_html($user->display_name); ?>
                        </a>
                    </td>
                    <td><?= esc_html($product) ?></td>
                    <td><?= esc_html($created_date) ?></td>
                    <td><?= esc_html($expire_date) ?></td>

                    <td>
                        <span class="badge bg-<?= esc_attr($status_class_active) ?> toggle-status"
                            data-id="<?= esc_attr($row->id) ?>" data-status="<?= esc_attr($row->status) ?>"
                            style="cursor:pointer;">
                            <?= esc_html($status_active) ?>
                        </span>
                    </td>

                    <td>
                        <span class="badge bg-<?= esc_attr($status_class_expire) ?>">
                            <?= esc_html($status_text_expire) ?>
                        </span>
                    </td>

                    <td>
                        <button class="btn btn-sm btn-outline-danger delete-license" data-id="<?= esc_attr($row->id) ?>"><i
                                class="fa fa-trash"></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    jQuery(function ($) {
        // کپی کد لایسنس
        $('.copy-license').on('click', function () {
            navigator.clipboard.writeText($(this).data('license')).then(() => {
                alert('کد لایسنس کپی شد');
            });
        });

        // تغییر وضعیت فعال/غیرفعال
        $('.toggle-status').on('click', function () {
            const el = $(this);
            const id = el.data('id');
            const currentStatus = el.data('status');
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

            $.post(ajaxurl, {
                action: 'lm_toggle_license_status',
                license_id: id,
                new_status: newStatus,
                _ajax_nonce: '<?= $nonce_toggle ?>'
            }, function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert('خطا در تغییر وضعیت');
                }
            });
        });

        // حذف لایسنس
        $('.delete-license').on('click', function () {
            if (!confirm('آیا از حذف این لایسنس مطمئن هستید؟')) return;
            const id = $(this).data('id');
            $.post(ajaxurl, {
                action: 'lm_delete_license',
                license_id: id,
                _ajax_nonce: '<?= $nonce_delete ?>'
            }, function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert('خطا در حذف');
                }
            });
        });
    });
</script>


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

    jQuery(document).ready(function ($) {
        $('.view-user-info').on('click', function (e) {
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
                success: function (response) {
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
                error: function () {
                    $('#userInfoContent').html('خطا در دریافت اطلاعات.');
                }
            });
        });
    });
</script>