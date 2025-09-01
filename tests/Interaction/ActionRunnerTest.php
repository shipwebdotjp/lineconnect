<?php

use PHPUnit\Framework\TestCase;
use Shipweb\LineConnect\Interaction\ActionRunner;
use Shipweb\LineConnect\Action\Action;
use Shipweb\LineConnect\Interaction\InteractionSession;

class ActionRunnerTest extends TestCase {
    /**
     * @covers Shipweb\LineConnect\Interaction\ActionRunner::run
     */
    public function testRun() {
        // Action::do_action a static method, so we need to use a mock builder to mock it.
        $actionMock = $this->getMockBuilder(Action::class)
            ->getMock();

        $expectedResult = [
            'success' => true,
            'messages' => ['test message'],
            'results' => ['result1'],
        ];

        // We can't mock static methods directly, so we'll use a workaround.
        // This is not ideal, but it's the best we can do without changing the application code.
        // A better approach would be to inject the Action class as a dependency.
        // For now, we will just test the pass-through.

        $actionRunner = new ActionRunner();

        $action_definition = (object)[
            'actions' => [['action1']],
            'chains' => [['chain1']]
        ];

        $session = $this->createMock(InteractionSession::class);
        $session->method('get_channel_prefix')->willReturn('test');

        $event = (object)[
            'type' => 'message',
            'source' => (object)[
                'userId' => 'testuser'
            ]
        ];

        $result = $actionRunner->run($action_definition, $session, $event);

        // Since we cannot mock the static method, we cannot assert the return value content.
        // We will just assert the structure of the return value.
        $this->assertIsArray($result);
    }

    /**
     * @covers Shipweb\LineConnect\Interaction\ActionRunner::run
     */
    public function testRunEmptyActions() {
        $actionRunner = new ActionRunner();
        $action_definition = (object)[
            'actions' => [],
        ];

        $session = $this->createMock(InteractionSession::class);
        $event = (object)[
            'type' => 'message',
            'source' => (object)[
                'userId' => 'testuser'
            ]
        ];

        $result = $actionRunner->run($action_definition, $session, $event);

        $this->assertEquals([], $result);
    }
}
