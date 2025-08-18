<?php

use PHPUnit\Framework\TestCase;
use Shipweb\LineConnect\Interaction\InteractionHandler;
use Shipweb\LineConnect\Interaction\SessionRepository;
use Shipweb\LineConnect\Interaction\ActionRunner;
use Shipweb\LineConnect\Interaction\MessageBuilder;
use Shipweb\LineConnect\Interaction\InputNormalizer;
use Shipweb\LineConnect\Interaction\Validator;
use Shipweb\LineConnect\Interaction\InteractionDefinition;
use Shipweb\LineConnect\Interaction\StepDefinition;
use Shipweb\LineConnect\Interaction\InteractionSession;
use Shipweb\LineConnect\Interaction\ValidationResult;

class InteractionHandlerTest extends TestCase
{
    private $sessionRepositoryMock;
    private $actionRunnerMock;
    private $messageBuilderMock;
    private $normalizerMock;
    private $validatorMock;
    private $interactionDefinitionMock;
    private $sessionMock;
    private $stepMock;

    protected function setUp(): void
    {
        $this->sessionRepositoryMock = $this->createMock(SessionRepository::class);
        $this->actionRunnerMock = $this->createMock(ActionRunner::class);
        $this->messageBuilderMock = $this->createMock(MessageBuilder::class);
        $this->normalizerMock = $this->createMock(InputNormalizer::class);
        $this->validatorMock = $this->createMock(Validator::class);
        $this->interactionDefinitionMock = $this->createMock(InteractionDefinition::class);
        $this->sessionMock = $this->createMock(InteractionSession::class);
        $this->stepMock = $this->createMock(StepDefinition::class);

        // Default behavior for mocks
        $this->interactionDefinitionMock->method('get_step')->willReturn($this->stepMock);
        $this->normalizerMock->method('normalize')->will($this->returnArgument(0));
        $validationResultMock = $this->createMock(ValidationResult::class);
        $validationResultMock->method('isValid')->willReturn(true);
        $this->validatorMock->method('validate')->willReturn($validationResultMock);
    }

    private function createEvent(string $text): object
    {
        return (object) [
            'type' => 'message',
            'message' => (object) [
                'type' => 'text',
                'text' => $text,
            ],
        ];
    }

    public function testHandleWithBranchingEqualsCondition()
    {
        $event = $this->createEvent('yes');

        $this->stepMock->method('get_id')->willReturn('confirm_step');
        $this->stepMock->method('get_branches')->willReturn([
            ['type' => 'equals', 'value' => 'yes', 'nextStepId' => 'yes_step'],
            ['type' => 'equals', 'value' => 'no', 'nextStepId' => 'no_step'],
        ]);
        $this->stepMock->method('get_next_step_id')->willReturn('fallback_step');

        $this->sessionMock->method('get_current_step_id')->willReturn('confirm_step');
        $this->sessionMock->method('get_answer')->with('confirm_step')->willReturn('yes');
        
        // Expect that the session's step is updated to 'yes_step'
        $this->sessionMock->expects($this->once())
            ->method('set_current_step_id')
            ->with('yes_step');

        $handler = new InteractionHandler(
            $this->sessionRepositoryMock,
            $this->actionRunnerMock,
            $this->messageBuilderMock,
            $this->normalizerMock,
            $this->validatorMock
        );

        $handler->handle($this->sessionMock, $event, $this->interactionDefinitionMock);
    }

    public function testHandleWithBranchingContainsCondition()
    {
        $event = $this->createEvent('I want to apply');

        $this->stepMock->method('get_id')->willReturn('apply_step');
        $this->stepMock->method('get_branches')->willReturn([
            ['type' => 'contains', 'value' => 'apply', 'nextStepId' => 'application_form'],
        ]);
        $this->stepMock->method('get_next_step_id')->willReturn('fallback_step');

        $this->sessionMock->method('get_current_step_id')->willReturn('apply_step');
        $this->sessionMock->method('get_answer')->with('apply_step')->willReturn('I want to apply');

        $this->sessionMock->expects($this->once())
            ->method('set_current_step_id')
            ->with('application_form');

        $handler = new InteractionHandler(
            $this->sessionRepositoryMock,
            $this->actionRunnerMock,
            $this->messageBuilderMock,
            $this->normalizerMock,
            $this->validatorMock
        );

        $handler->handle($this->sessionMock, $event, $this->interactionDefinitionMock);
    }

    public function testHandleWithBranchingFallbackToDefault()
    {
        $event = $this->createEvent('maybe');

        $this->stepMock->method('get_id')->willReturn('confirm_step');
        $this->stepMock->method('get_branches')->willReturn([
            ['type' => 'equals', 'value' => 'yes', 'nextStepId' => 'yes_step'],
            ['type' => 'equals', 'value' => 'no', 'nextStepId' => 'no_step'],
        ]);
        $this->stepMock->method('get_next_step_id')->willReturn('fallback_step');

        $this->sessionMock->method('get_current_step_id')->willReturn('confirm_step');
        $this->sessionMock->method('get_answer')->with('confirm_step')->willReturn('maybe');

        $this->sessionMock->expects($this->once())
            ->method('set_current_step_id')
            ->with('fallback_step');

        $handler = new InteractionHandler(
            $this->sessionRepositoryMock,
            $this->actionRunnerMock,
            $this->messageBuilderMock,
            $this->normalizerMock,
            $this->validatorMock
        );

        $handler->handle($this->sessionMock, $event, $this->interactionDefinitionMock);
    }
}
