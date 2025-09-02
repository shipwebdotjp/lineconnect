<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
if (!getenv('WP_TESTS_DIR')) {
    $dotenv->load();
}
$_tests_dir = getenv('WP_TESTS_DIR');
