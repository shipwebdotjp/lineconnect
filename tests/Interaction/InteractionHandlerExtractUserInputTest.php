<?php

use PHPUnit\Framework\TestCase;
use Shipweb\LineConnect\Interaction\InteractionHandler;
use Shipweb\LineConnect\Interaction\SessionRepository;
use Shipweb\LineConnect\Interaction\ActionRunner;
use Shipweb\LineConnect\Interaction\MessageBuilder;
use Shipweb\LineConnect\Interaction\InputNormalizer;
use Shipweb\LineConnect\Interaction\Validator;

class InteractionHandlerExtractUserInputTest extends TestCase {
    private $sessionRepositoryMock;
    private $actionRunnerMock;
    private $messageBuilderMock;
    private $normalizerMock;
    private $validatorMock;

    protected function setUp(): void {
        $this->sessionRepositoryMock = $this->createMock(SessionRepository::class);
        $this->actionRunnerMock = $this->createMock(ActionRunner::class);
        $this->messageBuilderMock = $this->createMock(MessageBuilder::class);
        $this->normalizerMock = $this->createMock(InputNormalizer::class);
        $this->validatorMock = $this->createMock(Validator::class);
    }

    private function invokeExtractUserInput(InteractionHandler $handler, $event) {
        $ref = new ReflectionClass($handler);
        $method = $ref->getMethod('extractUserInput');
        $method->setAccessible(true);
        return $method->invoke($handler, $event);
    }

    public function testPostbackParamsDatetimePreferred() {
        $handler = new InteractionHandler(
            $this->sessionRepositoryMock,
            $this->actionRunnerMock,
            $this->messageBuilderMock,
            $this->normalizerMock,
            $this->validatorMock
        );

        $event = (object)[
            'type' => 'postback',
            'postback' => (object)[
                'params' => (object)[
                    'datetime' => '2025-01-01T10:00:00',
                    'date' => '2025-01-01',
                    'time' => '10:00',
                ],
            ],
        ];

        $this->assertSame('2025-01-01T10:00:00', $this->invokeExtractUserInput($handler, $event));
    }

    public function testPostbackParamsDateFallback() {
        $handler = new InteractionHandler(
            $this->sessionRepositoryMock,
            $this->actionRunnerMock,
            $this->messageBuilderMock,
            $this->normalizerMock,
            $this->validatorMock
        );

        $event = (object)[
            'type' => 'postback',
            'postback' => (object)[
                'params' => (object)[
                    'date' => '2025-01-02',
                ],
            ],
        ];

        $this->assertSame('2025-01-02', $this->invokeExtractUserInput($handler, $event));
    }

    public function testPostbackParamsTimeFallback() {
        $handler = new InteractionHandler(
            $this->sessionRepositoryMock,
            $this->actionRunnerMock,
            $this->messageBuilderMock,
            $this->normalizerMock,
            $this->validatorMock
        );

        $event = (object)[
            'type' => 'postback',
            'postback' => (object)[
                'params' => (object)[
                    'time' => '15:30',
                ],
            ],
        ];

        $this->assertSame('15:30', $this->invokeExtractUserInput($handler, $event));
    }

    public function testPostbackDataQueryStringValue() {
        $handler = new InteractionHandler(
            $this->sessionRepositoryMock,
            $this->actionRunnerMock,
            $this->messageBuilderMock,
            $this->normalizerMock,
            $this->validatorMock
        );

        $event = (object)[
            'type' => 'postback',
            'postback' => (object)[
                'data' => 'foo=bar&value=special_value',
            ],
        ];

        $this->assertSame('special_value', $this->invokeExtractUserInput($handler, $event));
    }

    public function testPostbackDataPlain() {
        $handler = new InteractionHandler(
            $this->sessionRepositoryMock,
            $this->actionRunnerMock,
            $this->messageBuilderMock,
            $this->normalizerMock,
            $this->validatorMock
        );

        $event = (object)[
            'type' => 'postback',
            'postback' => (object)[
                'data' => 'plain_value',
            ],
        ];

        $this->assertSame('plain_value', $this->invokeExtractUserInput($handler, $event));
    }

    public function testMessageText() {
        $handler = new InteractionHandler(
            $this->sessionRepositoryMock,
            $this->actionRunnerMock,
            $this->messageBuilderMock,
            $this->normalizerMock,
            $this->validatorMock
        );

        $event = (object)[
            'type' => 'message',
            'message' => (object)[
                'type' => 'text',
                'text' => 'hello world',
            ],
        ];

        $this->assertSame('hello world', $this->invokeExtractUserInput($handler, $event));
    }

    public function testNullEventReturnsNull() {
        $handler = new InteractionHandler(
            $this->sessionRepositoryMock,
            $this->actionRunnerMock,
            $this->messageBuilderMock,
            $this->normalizerMock,
            $this->validatorMock
        );

        $this->assertNull($this->invokeExtractUserInput($handler, null));
    }
}
