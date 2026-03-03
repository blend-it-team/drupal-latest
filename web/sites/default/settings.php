<?php
$databases['default']['default'] = [
  'driver' => 'mysql',
  'database' => getenv('DB_NAME') ?: 'drupal',
  'username' => getenv('DB_USER') ?: 'drupal',
  'password' => getenv('DB_PASSWORD') ?: '',
  'host' => getenv('DB_HOST') ?: 'db',
  'port' => '3306',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
];

$settings['hash_salt'] = getenv('HASH_SALT') ?: $settings['hash_salt'];

# If you terminate TLS at Coolify/Traefik, Drupal may need proxy awareness.
# (Keep this conservative; only enable if you see redirect loops / wrong scheme.)
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
  $_SERVER['HTTPS'] = 'on';
}
?>
