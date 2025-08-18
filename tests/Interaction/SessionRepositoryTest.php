<?php

use Shipweb\LineConnect\Interaction\InteractionSession;
use Shipweb\LineConnect\Interaction\SessionRepository;

class SessionRepositoryTest extends WP_UnitTestCase
{
    private $wpdb_mock;
    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->wpdb_mock = $this->getMockBuilder('wpdb')
            ->disableOriginalConstructor()
            ->setMethods(['get_row', 'insert', 'update', 'prepare'])
            ->getMock();
        
        // Make the prepare method just return the query.
        $this->wpdb_mock->method('prepare')->will($this->returnArgument(0));

        // Inject the mock into the repository
        global $wpdb;
        $wpdb = $this->wpdb_mock;
        $this->repository = new SessionRepository();
    }

    public function test_find_active()
    {
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

    public function test_save_insert()
    {
        $session_mock = $this->createMock(InteractionSession::class);
        $session_mock->method('get_id')->willReturn(null); // It's a new session
        $session_mock->method('to_db_array')->willReturn(['status' => 'active']);

        $this->wpdb_mock->expects($this->once())
            ->method('insert')
            ->with($this->anything(), ['status' => 'active'])
            ->willReturn(1); // Success

        $this->repository->save($session_mock);
    }

    public function test_save_update()
    {
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
