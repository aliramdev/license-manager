<?php
defined('ABSPATH') or die('No script kiddies please!');

// اگر فرم ارسال شده است
$result = null;
if (isset($_POST['lm_generate_license'])) {
    $user_id = intval($_POST['user_id']);
    $product_id = intval($_POST['product_id']);
    $start_date = !empty($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : null;
    $expiry_date = !empty($_POST['expiry_date']) ? sanitize_text_field($_POST['expiry_date']) : null;

    $result = lm_generate_license($user_id, $product_id, $start_date, $expiry_date);
}

// دریافت کاربران و محصولات
$users = get_users();
$products = wc_get_products(['limit' => -1]);
?>

<div class="wrap">
    <h1>تولید لایسنس</h1>

    <?php if ($result): ?>
        <div class="notice <?php echo $result['success'] ? 'notice-success' : 'notice-error'; ?> is-dismissible">
            <p>
                <?php echo esc_html($result['message']); ?>
                <?php if (!empty($result['license_key'])): ?>
                    <strong id="license-code"><?php echo esc_html($result['license_key']); ?></strong>
                    <button class="button" onclick="copyLicense()">کپی در حافظه</button>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <form method="post" class="license-form" style="max-width: 600px; margin-top: 30px;">
        <table class="form-table">
            <tr>
                <th><label for="user_id">کاربر</label></th>
                <td>
                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="">-- انتخاب کاربر --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo esc_attr($user->ID); ?>">
                                <?php echo esc_html($user->display_name . ' (' . $user->user_email . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="product_id">محصول</label></th>
                <td>
                    <select name="product_id" id="product_id" class="form-control" required>
                        <option value="">-- انتخاب محصول --</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo esc_attr($product->get_id()); ?>">
                                <?php echo esc_html($product->get_name()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="start_date">تاریخ شروع</label></th>
                <td><input type="datetime-local" name="start_date" id="start_date" class="form-control"></td>
            </tr>
            <tr>
                <th><label for="expiry_date">تاریخ انقضا</label></th>
                <td><input type="datetime-local" name="expiry_date" id="expiry_date" class="form-control"></td>
            </tr>
        </table>

        <p><button type="submit" name="lm_generate_license" class="button button-primary">تولید لایسنس</button></p>
    </form>
</div>

<script>
function copyLicense() {
    var code = document.getElementById("license-code").innerText;
    navigator.clipboard.writeText(code).then(function () {
        alert("کد لایسنس در حافظه کپی شد");
    });
}
</script>