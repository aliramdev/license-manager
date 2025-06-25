<?php
defined('ABSPATH') or die('No script kiddies please!');

add_action('admin_menu', function () {
    $capability = 'manage_options';
    $slug = 'lm_license_manager';

    add_menu_page(
        __('License Manager', 'license-manager'),
        __('License Manager', 'license-manager'),
        $capability,
        $slug,
        function () {
            require_once LM_PLUGIN_DIR . 'templates/admin-license-list.php';
        },
        'dashicons-admin-network',
        56
    );

    add_submenu_page(
        $slug,
        __('License List', 'license-manager'),
        __('License List', 'license-manager'),
        $capability,
        $slug,
        function () {
            require_once LM_PLUGIN_DIR . 'templates/admin-license-list.php';
        }
    );

    add_submenu_page(
        $slug,
        __('Activation Codes', 'license-manager'),
        __('Activation Codes', 'license-manager'),
        $capability,
        'lm_activation_codes',
        function () {
            require_once LM_PLUGIN_DIR . 'templates/admin-activation-codes.php';
        }
    );

    add_submenu_page(
        $slug,
        __('User Registration', 'license-manager'),
        __('User Registration', 'license-manager'),
        $capability,
        'lm_user_registration',
        function () {
            require_once LM_PLUGIN_DIR . 'templates/admin-user-registration.php';
        }
    );

    add_submenu_page(
        $slug,
        __('Settings', 'license-manager'),
        __('Settings', 'license-manager'),
        $capability,
        'lm_settings',
        function () {
            require_once LM_PLUGIN_DIR . 'templates/admin-settings.php';
        }
    );
});