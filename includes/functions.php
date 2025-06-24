<?php
function lm_generate_activation_hash($system_code) {
  $key = get_option('lm_secret_key', 'changeme');
  return hash('sha256', $system_code . $key);
}

function lm_get_expiry_date($months = 1) {
  return date('Y-m-d H:i:s', strtotime("+$months months"));
}

function lm_get_license_duration($product_id) {
  $meta = get_post_meta($product_id, '_license_duration', true);
  return $meta ? intval($meta) : intval(get_option('lm_default_months', 1));
}
