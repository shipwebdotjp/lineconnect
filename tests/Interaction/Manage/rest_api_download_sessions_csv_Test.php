<?php

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;
use Shipweb\LineConnect\Interaction\Manage\InteractionSessionDownload;

/**
 * Class rest_api_download_sessions_csv_Test
 * @package Shipweb\LineConnect\Tests\Interaction\Manage
 * @group interaction-manage-csv
 */
class rest_api_download_sessions_csv_Test extends WP_UnitTestCase {

    protected $admin_user_id;
    protected $interaction_post_id;
    protected $session_table;

    protected static $init_result;
    protected static $interaction_datas;
    protected static $interaction_ids;

    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory): void {
        self::$init_result = lineconnectTest::init();
        self::$interaction_datas = [
            "csv-test-interaction" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => 'interactions',
                        "steps" => [
                            [ "id" => "step1", "title" => "Step 1", "nextStepId" => "step2" ],
                            [ "id" => "step2", "title" => "Step 2", "stop" => true ],
                        ]
                    ]
                ],
                "2" => [
                    [
                        "version" => "2",
                        "storage" => 'interactions',
                        "steps" => [
                            [ "id" => "step1", "title" => "Step 1 v2", "nextStepId" => "step3" ],
                            [ "id" => "step3", "title" => "Step 3", "stop" => true ],
                        ]
                    ]
                ],
            ],
        ];
        self::$interaction_ids = [];
        foreach (self::$interaction_datas as $interaction_name => $interaction_data) {
            $post_id = wp_insert_post([
                'post_title'   => $interaction_name,
                'post_type' => InteractionCPT::POST_TYPE,
                'post_status' => 'publish',
            ]);
            update_post_meta($post_id, InteractionCPT::META_KEY_VERSION, 2);
            update_post_meta($post_id, InteractionCPT::META_KEY_DATA, $interaction_data);
            update_post_meta($post_id, LineConnect::META_KEY__SCHEMA_VERSION, InteractionCPT::SCHEMA_VERSION);
            self::$interaction_ids[$interaction_name] = $post_id;
        }
    }

    public function setUp(): void {
        parent::setUp();
        global $wpdb;
        $this->session_table = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;

        $this->admin_user_id = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($this->admin_user_id);

        $interaction_id = self::$interaction_ids['csv-test-interaction'];

        $wpdb->insert($this->session_table, [
            'interaction_id' => $interaction_id,
            'interaction_version' => 1,
            'channel_prefix' => '04f7',
            'line_user_id' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d',
            'status' => 'completed',
            'answers' => json_encode(['step1' => 'Answer 1', 'step2' => 'Answer 2']),
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 11:00:00',
        ]);
        $wpdb->insert($this->session_table, [
            'interaction_id' => $interaction_id,
            'interaction_version' => 2,
            'channel_prefix' => '04f7',
            'line_user_id' => 'U_PLACEHOLDER_USERIDc3f457cdefcc9',
            'status' => 'active',
            'answers' => json_encode(['step1' => 'Answer 1 v2', 'step3' => 'Answer 3']),
            'created_at' => '2024-01-02 12:00:00',
            'updated_at' => '2024-01-02 13:00:00',
        ]);
    }

    public function tearDown(): void {
        parent::tearDown();
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$this->session_table}");
        // clear request globals to avoid cross-test contamination
        $_GET = $_POST = $_REQUEST = [];
    }

    /**
     * Test CSV generation (no filters) using build_sessions_csv
     */
    public function test_build_sessions_csv_no_filters() {
        $interaction_id = self::$interaction_ids['csv-test-interaction'];

        // Call the CSV builder directly
        $res = InteractionSessionDownload::build_sessions_csv($interaction_id, []);
        // Debug: write CSV to temp file for inspection during test runs
        @file_put_contents('/tmp/lineconnect_debug_all.csv', $res['csv']);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('filename', $res);
        $this->assertArrayHasKey('csv', $res);

        // Filename check
        $interaction = get_post($interaction_id);
        $expected_slug = $interaction ? sanitize_title($interaction->post_title) : 'sessions';
        $expected_filename = "{$expected_slug}_sessions_" . date('Y-m-d') . ".csv";
        $this->assertEquals($expected_filename, $res['filename']);

        // Parse CSV
        $csv_data = $res['csv'];
        // split lines in a cross-platform way and remove empty lines
        $lines = preg_split('/\r\n|\r|\n/', trim($csv_data));
        $lines = array_values(array_filter($lines, function ($l) { return strlen(trim($l)) > 0; }));
        $csv = array_map(function ($l) {
            return str_getcsv($l, ',', '"', '\\');
        }, $lines);

        // Header check
        $expected_headers = [
            'Session ID', 'Version', 'Channel', 'LINE User ID', 'Status', 'Current Step', 'Updated At', 'Created At',
            'Step 1', 'Step 2', 'Step 3' // from both versions
        ];
        $this->assertEquals($expected_headers, $csv[0]);

        // Data check (2 rows)
        $this->assertCount(3, $csv); // 1 header + 2 data rows

        // Row 1 (U_PLACEHOLDER_USERIDc3f457cdefcc9, most recent)
        $this->assertEquals('U_PLACEHOLDER_USERIDc3f457cdefcc9', $csv[1][3]);
        $this->assertEquals('Answer 1 v2', $csv[1][8]); // Step 1
        $this->assertEquals('', $csv[1][9]); // Step 2 (not in this version)
        $this->assertEquals('Answer 3', $csv[1][10]); // Step 3

        // Row 2 (U_PLACEHOLDER_USERID4e7a9902e5e7d)
        $this->assertEquals('U_PLACEHOLDER_USERID4e7a9902e5e7d', $csv[2][3]);
        $this->assertEquals('Answer 1', $csv[2][8]); // Step 1
        $this->assertEquals('Answer 2', $csv[2][9]); // Step 2
        $this->assertEquals('', $csv[2][10]); // Step 3 (not in this version)
    }

    /**
     * Test CSV generation with version filter using build_sessions_csv
     */
    public function test_build_sessions_csv_with_version_filter() {
        $interaction_id = self::$interaction_ids['csv-test-interaction'];

        $options = [
            'version' => [1],
        ];

        $res = InteractionSessionDownload::build_sessions_csv($interaction_id, $options);
        // Debug: write CSV to temp file for inspection during test runs
        @file_put_contents('/tmp/lineconnect_debug_version.csv', $res['csv']);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('filename', $res);
        $this->assertArrayHasKey('csv', $res);

        // Filename check
        $interaction = get_post($interaction_id);
        $expected_slug = $interaction ? sanitize_title($interaction->post_title) : 'sessions';
        $expected_filename = "{$expected_slug}_sessions_" . date('Y-m-d') . ".csv";
        $this->assertEquals($expected_filename, $res['filename']);

        $csv_data = $res['csv'];
        // split lines in a cross-platform way and remove empty lines
        $lines = preg_split('/\r\n|\r|\n/', trim($csv_data));
        $lines = array_values(array_filter($lines, function ($l) { return strlen(trim($l)) > 0; }));
        $csv = array_map(function ($l) {
            return str_getcsv($l, ',', '"', '\\');
        }, $lines);

        // Header check for version 1
        $expected_headers = [
            'Session ID', 'Version', 'Channel', 'LINE User ID', 'Status', 'Current Step', 'Updated At', 'Created At',
            'Step 1', 'Step 2'
        ];
        $this->assertEquals($expected_headers, $csv[0]);

        // Data check (1 row for version 1)
        $this->assertCount(2, $csv);
        $this->assertEquals('U_PLACEHOLDER_USERID4e7a9902e5e7d', $csv[1][3]);
        $this->assertEquals('Answer 1', $csv[1][8]);
        $this->assertEquals('Answer 2', $csv[1][9]);
    }
}
