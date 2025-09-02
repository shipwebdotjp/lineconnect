<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$_tests_dir = $_ENV['WP_TESTS_DIR'];
