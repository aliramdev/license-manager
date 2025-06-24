<?php
defined('ABSPATH') || exit;

$users = get_users(['fields' => ['ID', 'display_name', 'user_email']]);
$products = wc_get_products(['limit' => -1, 'status' => 'publish']);
?>

<div class="wrap">
    <h1 class="lm-header"><i class="fas fa-key"></i> تولید لایسنس جدید</h1>

    <form method="post" action="" class="lm-form">
        <input type="hidden" name="lm_action" value="generate_license">

        <div class="lm-box">
            <label for="user_id">کاربر</label>
            <select name="user_id" id="user_id" class="form-select" required>
                <option value="">انتخاب کنید...</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= esc_attr($user->ID); ?>">
                        <?= esc_html($user->display_name . " ({$user->user_email})"); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="lm-box">
            <label for="product_id">محصول ووکامرس</label>
            <select name="product_id" id="product_id" class="form-select" required>
                <option value="">انتخاب کنید...</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= esc_attr($product->get_id()); ?>">
                        <?= esc_html($product->get_name()); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php submit_button('تولید لایسنس', 'primary lm-btn'); ?>
    </form>
</div>
