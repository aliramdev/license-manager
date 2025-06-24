<?php
defined('ABSPATH') || exit;

if (!current_user_can('manage_options')) {
    wp_die('دسترسی غیرمجاز!');
}

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('lm_user_registration_save', 'lm_user_registration_nonce')) {
    // گرفتن و اعتبارسنجی فیلدها
    $user_login = sanitize_user($_POST['user_login'] ?? '');
    $user_email = sanitize_email($_POST['user_email'] ?? '');
    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name = sanitize_text_field($_POST['last_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $billing_phone = sanitize_text_field($_POST['billing_phone'] ?? '');
    $billing_address_1 = sanitize_text_field($_POST['billing_address_1'] ?? '');
    $billing_city = sanitize_text_field($_POST['billing_city'] ?? '');
    $billing_postcode = sanitize_text_field($_POST['billing_postcode'] ?? '');
    $billing_country = sanitize_text_field($_POST['billing_country'] ?? '');
    $billing_state = sanitize_text_field($_POST['billing_state'] ?? '');

    if (empty($user_login)) {
        $errors[] = 'نام کاربری الزامی است.';
    } elseif (username_exists($user_login)) {
        $errors[] = 'نام کاربری قبلاً استفاده شده است.';
    }

    if (!is_email($user_email)) {
        $errors[] = 'ایمیل معتبر نیست.';
    } elseif (email_exists($user_email)) {
        $errors[] = 'این ایمیل قبلاً ثبت شده است.';
    }

    if (empty($password)) {
        $errors[] = 'رمز عبور الزامی است.';
    }

    if (empty($errors)) {
        // ایجاد کاربر
        $user_id = wp_create_user($user_login, $password, $user_email);

        if (is_wp_error($user_id)) {
            $errors[] = 'خطا در ایجاد کاربر: ' . $user_id->get_error_message();
        } else {
            // ذخیره فیلدهای اضافی ووکامرس (billing)
            update_user_meta($user_id, 'first_name', $first_name);
            update_user_meta($user_id, 'last_name', $last_name);
            update_user_meta($user_id, 'billing_phone', $billing_phone);
            update_user_meta($user_id, 'billing_address_1', $billing_address_1);
            update_user_meta($user_id, 'billing_city', $billing_city);
            update_user_meta($user_id, 'billing_postcode', $billing_postcode);
            update_user_meta($user_id, 'billing_country', $billing_country);
            update_user_meta($user_id, 'billing_state', $billing_state);

            $message = 'کاربر با موفقیت ایجاد شد.';
        }
    }
}

?>

<div class="wrap">
    <h1><i class="fas fa-user-plus"></i> ثبت نام کاربر جدید</h1>

    <?php if ($message): ?>
        <div class="notice notice-success is-dismissible"><p><?= esc_html($message); ?></p></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="notice notice-error"><ul>
            <?php foreach ($errors as $error): ?>
                <li><?= esc_html($error); ?></li>
            <?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field('lm_user_registration_save', 'lm_user_registration_nonce'); ?>

        <table class="form-table">
            <tr>
                <th><label for="user_login">نام کاربری <span style="color:red;">*</span></label></th>
                <td><input type="text" name="user_login" id="user_login" class="regular-text" required value="<?= esc_attr($_POST['user_login'] ?? '') ?>"></td>
            </tr>

            <tr>
                <th><label for="user_email">ایمیل <span style="color:red;">*</span></label></th>
                <td><input type="email" name="user_email" id="user_email" class="regular-text" required value="<?= esc_attr($_POST['user_email'] ?? '') ?>"></td>
            </tr>

            <tr>
                <th><label for="password">رمز عبور <span style="color:red;">*</span></label></th>
                <td><input type="password" name="password" id="password" class="regular-text" required></td>
            </tr>

            <tr>
                <th><label for="first_name">نام</label></th>
                <td><input type="text" name="first_name" id="first_name" class="regular-text" value="<?= esc_attr($_POST['first_name'] ?? '') ?>"></td>
            </tr>

            <tr>
                <th><label for="last_name">نام خانوادگی</label></th>
                <td><input type="text" name="last_name" id="last_name" class="regular-text" value="<?= esc_attr($_POST['last_name'] ?? '') ?>"></td>
            </tr>

            <tr>
                <th><label for="billing_phone">تلفن تماس</label></th>
                <td><input type="text" name="billing_phone" id="billing_phone" class="regular-text" value="<?= esc_attr($_POST['billing_phone'] ?? '') ?>"></td>
            </tr>

            <tr>
                <th><label for="billing_address_1">آدرس</label></th>
                <td><input type="text" name="billing_address_1" id="billing_address_1" class="regular-text" value="<?= esc_attr($_POST['billing_address_1'] ?? '') ?>"></td>
            </tr>

            <tr>
                <th><label for="billing_city">شهر</label></th>
                <td><input type="text" name="billing_city" id="billing_city" class="regular-text" value="<?= esc_attr($_POST['billing_city'] ?? '') ?>"></td>
            </tr>

            <tr>
                <th><label for="billing_postcode">کد پستی</label></th>
                <td><input type="text" name="billing_postcode" id="billing_postcode" class="regular-text" value="<?= esc_attr($_POST['billing_postcode'] ?? '') ?>"></td>
            </tr>

            <tr>
                <th><label for="billing_country">کشور</label></th>
                <td><input type="text" name="billing_country" id="billing_country" class="regular-text" value="<?= esc_attr($_POST['billing_country'] ?? '') ?>"></td>
            </tr>

            <tr>
                <th><label for="billing_state">استان</label></th>
                <td><input type="text" name="billing_state" id="billing_state" class="regular-text" value="<?= esc_attr($_POST['billing_state'] ?? '') ?>"></td>
            </tr>
        </table>

        <?php submit_button('ثبت کاربر جدید'); ?>
    </form>
</div>
