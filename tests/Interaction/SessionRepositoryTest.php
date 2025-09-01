<?php

use Shipweb\LineConnect\Interaction\InteractionSession;
use Shipweb\LineConnect\Interaction\SessionRepository;

class SessionRepositoryTest extends WP_UnitTestCase {
    private $wpdb_mock;
    private $repository;
    private $original_wpdb;

    public function setUp(): void {
        parent::setUp();

        // Preserve original global $wpdb and replace with mock to avoid polluting other tests
        $this->original_wpdb = $GLOBALS['wpdb'];

        $this->wpdb_mock = $this->getMockBuilder('wpdb')
            ->disableOriginalConstructor()
            ->setMethods(['get_row', 'insert', 'update', 'prepare'])
            ->getMock();

        // Make the prepare method just return the query.
        $this->wpdb_mock->method('prepare')->will($this->returnArgument(0));

        // Provide minimal properties expected by WP code to avoid null/property issues
        $this->wpdb_mock->dbhost = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
        $this->wpdb_mock->prefix = property_exists($this->original_wpdb, 'prefix') ? $this->original_wpdb->prefix : 'wptests_';

        // Inject the mock into the repository
        $GLOBALS['wpdb'] = $this->wpdb_mock;
        $this->repository = new SessionRepository();
    }

    public function tearDown(): void {
        // Restore original global $wpdb to prevent side effects on other tests
        $GLOBALS['wpdb'] = $this->original_wpdb;
        parent::tearDown();
    }

    public function test_find_active() {
        $row = (object) [
            'id' => 1,
            'channel_prefix' => 'chan',
            'line_user_id' => 'user123',
            'interaction_id' => 1,
            'interaction_version' => 1,
            'status' => 'active',
            'current_step_id' => 'step1',
            'previous_step_id' => null,
            'answers' => '[]',
            'remind_at' => null,
            'reminder_sent_at' => null,
            'expires_at' => null,
            'created_at' => '2025-08-16 12:00:00',
            'updated_at' => '2025-08-16 12:05:00',
        ];

        $this->wpdb_mock->expects($this->once())
            ->method('get_row')
            ->willReturn($row);

        $session = $this->repository->find_active('chan', 'user123');

        $this->assertInstanceOf(InteractionSession::class, $session);
        $this->assertEquals(1, $session->get_id());
    }

    public function test_save_insert() {
        $session_mock = $this->createMock(InteractionSession::class);
        $session_mock->method('get_id')->willReturn(null); // It's a new session
        $session_mock->method('to_db_array')->willReturn(['status' => 'active']);

        $this->wpdb_mock->expects($this->once())
            ->method('insert')
            ->with($this->anything(), ['status' => 'active'])
            ->willReturn(1); // Success

        $this->repository->save($session_mock);
    }

    public function test_save_update() {
        $session_mock = $this->createMock(InteractionSession::class);
        $session_mock->method('get_id')->willReturn(10); // Existing session
        $session_mock->method('to_db_array')->willReturn(['status' => 'completed']);

        $this->wpdb_mock->expects($this->once())
            ->method('update')
            ->with($this->anything(), ['status' => 'completed'], ['id' => 10])
            ->willReturn(1); // Success

        $this->repository->save($session_mock);
    }
}
