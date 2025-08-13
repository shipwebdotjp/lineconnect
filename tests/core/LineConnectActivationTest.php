<?php
use Shipweb\LineConnect\Core\LineConnect;
class LineConnectActivationTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function test_activation_creates_database_tables() {
        global $wpdb;
        // テーブル存在確認
        $tables = [
            $wpdb->prefix . 'lineconnect_bot_logs',
            $wpdb->prefix . 'lineconnect_line_id',
            $wpdb->prefix . 'lineconnect_line_stats',
            $wpdb->prefix . 'lineconnect_line_daily'
        ];

        foreach ($tables as $table) {
            $result = $wpdb->get_var(
                $wpdb->prepare("SHOW TABLES LIKE %s", $table)
            );
            $this->assertEquals($table, $result, "Table {$table} should exist");
        }

        // データベースバージョン確認
        $variables = get_option(Shipweb\LineConnect\Core\LineConnect::OPTION_KEY__VARIABLES);
        $this->assertEquals(
            Shipweb\LineConnect\Core\LineConnect::DB_VERSION,
            $variables['db_version'],
            'Database version should be updated'
        );
    }

    public function test_activation_is_idempotent() {
        // 1回目
        LineConnect::pluginActivation();
        // 2回目（何も壊さないはず）
        LineConnect::pluginActivation();

        // オプションが正しく保持されていることを確認
        $variables = get_option(Shipweb\LineConnect\Core\LineConnect::OPTION_KEY__VARIABLES);
        $this->assertNotEmpty($variables, 'Options should persist after reactivation');
    }
}
