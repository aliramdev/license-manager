<?php

function lm_render_activation_code_generator() {
    if (!current_user_can('manage_options')) return;

    global $wpdb;
    $table = $wpdb->prefix . 'lm_licenses';

    // دریافت لیست کاربران
    $users = get_users(['orderby' => 'display_name']);

    $selected_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $system_code = isset($_POST['system_code']) ? sanitize_text_field($_POST['system_code']) : '';
    $selected_license_id = isset($_POST['license_id']) ? intval($_POST['license_id']) : 0;
    $activation_code = '';

    if ($selected_user_id && $selected_license_id && $system_code && isset($_POST['generate_activation'])) {
        $secret = get_option('lm_secret_key');
        $activation_code = hash('sha256', $secret . $system_code);

        // به‌روزرسانی رکورد لایسنس
        $wpdb->update($table, [
    'system_code' => $system_code,
    'activation_code' => $activation_code
], ['id' => $selected_license_id]);
    }

    ?>
    <div class="wrap">
        <h1>تولید کد فعال‌سازی از روی کد سیستم</h1>
        <form method="post" id="lm-activation-form">
            <div class="lm-form-group">
                <label>کاربر</label>
                <select name="user_id" onchange="document.getElementById('lm-activation-form').submit()">
                    <option value="">-- انتخاب کاربر --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user->ID ?>" <?= selected($selected_user_id, $user->ID) ?>>
                            <?= $user->display_name ?> (<?= $user->user_email ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($selected_user_id): ?>
                <?php
                $licenses = $wpdb->get_results(
                    $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $selected_user_id)
                );
                ?>
                <div class="lm-form-group">
                    <label>انتخاب لایسنس</label>
                    <select name="license_id" required>
                        <option value="">-- انتخاب لایسنس --</option>
                        <?php foreach ($licenses as $lic):
                            $product = wc_get_product($lic->product_id);
                            ?>
                            <option value="<?= $lic->id ?>" <?= selected($selected_license_id, $lic->id) ?>>
                                <?= $product ? $product->get_name() : 'محصول نامشخص' ?> | لایسنس: <?= $lic->license_code ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="lm-form-group">
                    <label>کد سیستم</label>
                    <input type="text" name="system_code" value="<?= esc_attr($system_code) ?>" required />
                </div>

                <button type="submit" name="generate_activation" class="button button-primary">تولید کد فعال‌سازی</button>
            <?php endif; ?>
        </form>

        <?php if ($activation_code): ?>
            <div class="updated notice" style="margin-top:20px;">
                <p><strong>کد فعال‌سازی:</strong> <code id="activation_code"><?= esc_html($activation_code) ?></code></p>
                <button class="button" onclick="copyToClipboard()">کپی</button>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .lm-form-group {
            margin-bottom: 15px;
        }

        .lm-form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .lm-form-group input,
        .lm-form-group select {
            width: 100%;
            max-width: 400px;
            padding: 8px;
        }
    </style>

    <script>
        function copyToClipboard() {
            const el = document.getElementById("activation_code");
            const temp = document.createElement("textarea");
            temp.value = el.innerText;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand("copy");
            document.body.removeChild(temp);
            alert("کد فعال‌سازی کپی شد!");
        }
    </script>
    <?php
}
