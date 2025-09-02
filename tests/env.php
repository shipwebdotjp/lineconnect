<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
if (!getenv('WP_TESTS_DIR')) {
    error_log('Loading .env file for tests');
    $dotenv->load();
    error_log($_ENV['WP_TESTS_DIR']);
}
$_tests_dir = $_ENV['WP_TESTS_DIR'];
