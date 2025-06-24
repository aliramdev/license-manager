<?php
defined('ABSPATH') or die('No script kiddies please!');

function lm_render_user_management_page() {
    // پیام‌های موفقیت/خطا
    if (isset($_POST['lm_add_user_nonce']) && wp_verify_nonce($_POST['lm_add_user_nonce'], 'lm_add_user')) {
        $userdata = [
            'user_login' => sanitize_text_field($_POST['user_login']),
            'user_email' => sanitize_email($_POST['user_email']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'user_pass' => $_POST['user_pass'],
            'role' => 'customer',
        ];

        // بررسی وجود ایمیل یا نام کاربری
        if (username_exists($userdata['user_login'])) {
            echo '<div class="notice notice-error"><p>نام کاربری قبلاً استفاده شده است.</p></div>';
        } elseif (email_exists($userdata['user_email'])) {
            echo '<div class="notice notice-error"><p>ایمیل قبلاً استفاده شده است.</p></div>';
        } else {
            $user_id = wp_insert_user($userdata);
            if (!is_wp_error($user_id)) {
                echo '<div class="notice notice-success"><p>کاربر با موفقیت افزوده شد.</p></div>';
                // می‌توانید اینجا ارسال ایمیل خوش‌آمدگویی هم اضافه کنید
            } else {
                echo '<div class="notice notice-error"><p>خطا در افزودن کاربر.</p></div>';
            }
        }
    }

    // دریافت لیست کاربران (مشتریان ووکامرس)
    $args = [
        'role' => 'customer',
        'orderby' => 'registered',
        'order' => 'DESC',
        'number' => 20,
        'paged' => max(1, intval($_GET['paged'] ?? 1)),
    ];
    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();
    $total_users = $user_query->get_total();
    $paged = $args['paged'];
    $per_page = $args['number'];
    $total_pages = ceil($total_users / $per_page);
    ?>
    <div class="wrap">
        <h1><i class="fas fa-users"></i> مدیریت کاربران</h1>

        <h2>افزودن کاربر جدید</h2>
        <form method="post" class="mb-5 row gx-3 gy-3 align-items-center" style="max-width:600px;">
            <?php wp_nonce_field('lm_add_user', 'lm_add_user_nonce'); ?>

            <div class="col-12">
                <label for="user_login" class="form-label">نام کاربری</label>
                <input type="text" id="user_login" name="user_login" class="form-control" required>
            </div>
            <div class="col-12">
                <label for="user_email" class="form-label">ایمیل</label>
                <input type="email" id="user_email" name="user_email" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="first_name" class="form-label">نام</label>
                <input type="text" id="first_name" name="first_name" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="last_name" class="form-label">نام خانوادگی</label>
                <input type="text" id="last_name" name="last_name" class="form-control">
            </div>
            <div class="col-12">
                <label for="user_pass" class="form-label">رمز عبور</label>
                <input type="password" id="user_pass" name="user_pass" class="form-control" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success"><i class="fas fa-user-plus"></i> افزودن کاربر</button>
            </div>
        </form>

        <h2>لیست کاربران</h2>
        <table class="table table-striped table-bordered table-responsive">
            <thead class="table-light">
                <tr>
                    <th>نام کاربری</th>
                    <th>نام و نام خانوادگی</th>
                    <th>ایمیل</th>
                    <th>تاریخ ثبت‌نام</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$users): ?>
                    <tr><td colspan="4" class="text-center">کاربری یافت نشد.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo esc_html($user->user_login); ?></td>
                            <td><?php echo esc_html($user->first_name . ' ' . $user->last_name); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo esc_html(date_i18n('Y/m/d H:i', strtotime($user->user_registered))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <li class="page-item <?php echo $p === $paged ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo esc_url(add_query_arg(['paged' => $p])); ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    <?php
}
