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

class InteractionHandlerBranchByPostbackTest extends TestCase {
    private $sessionRepositoryMock;
    private $actionRunnerMock;
    private $messageBuilderMock;
    private $normalizerMock;
    private $validatorMock;
    private $interactionDefinitionMock;
    private $sessionMock;
    private $stepMock;

    protected function setUp(): void {
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

    private function createPostbackEvent(string $data, ?array $params = null): object {
        $postback = (object)['data' => $data];
        if ($params !== null) {
            $postback->params = (object)$params;
        }
        return (object)[
            'type' => 'postback',
            'postback' => $postback,
        ];
    }

    public function testHandleWithPostbackQueryStringNextStepId() {
        // postback->dataに nextStepId=post_step が含まれている場合、それが優先される
        $event = $this->createPostbackEvent('nextStepId=post_step&value=foo');

        $this->stepMock->method('get_id')->willReturn('start');
        // branches があっても無視されるはず
        $this->stepMock->method('get_branches')->willReturn([
            ['type' => 'equals', 'value' => 'opt', 'nextStepId' => 'opt_step'],
        ]);
        $this->stepMock->method('get_next_step_id')->willReturn('fallback_step');

        $this->sessionMock->method('get_current_step_id')->willReturn('start');
        $this->sessionMock->method('get_answer')->with('start')->willReturn(null);

        $this->sessionMock->expects($this->once())
            ->method('set_current_step_id')
            ->with('post_step');

        $handler = new InteractionHandler(
            $this->sessionRepositoryMock,
            $this->actionRunnerMock,
            $this->messageBuilderMock,
            $this->normalizerMock,
            $this->validatorMock
        );

        $handler->handle($this->sessionMock, $event, $this->interactionDefinitionMock);
    }

    public function testHandleWithPostbackPlainDataMatchesBranchEquals() {
        // postback->data がプレーンな 'opt1' の場合、branches の equals にマッチする
        $event = $this->createPostbackEvent('opt1');

        $this->stepMock->method('get_id')->willReturn('choice_step');
        $this->stepMock->method('get_branches')->willReturn([
            ['type' => 'equals', 'value' => 'opt1', 'nextStepId' => 'opt1_step'],
            ['type' => 'equals', 'value' => 'opt2', 'nextStepId' => 'opt2_step'],
        ]);
        $this->stepMock->method('get_next_step_id')->willReturn('fallback_step');

        $this->sessionMock->method('get_current_step_id')->willReturn('choice_step');
        $this->sessionMock->method('get_answer')->with('choice_step')->willReturn('opt1');

        $this->sessionMock->expects($this->once())
            ->method('set_current_step_id')
            ->with('opt1_step');

        $handler = new InteractionHandler(
            $this->sessionRepositoryMock,
            $this->actionRunnerMock,
            $this->messageBuilderMock,
            $this->normalizerMock,
            $this->validatorMock
        );

        $handler->handle($this->sessionMock, $event, $this->interactionDefinitionMock);
    }

    public function testHandleWithPostbackFallbackToDefault() {
        // postback->data が branches にマッチしない場合、get_next_step_id が選ばれる
        $event = $this->createPostbackEvent('unknown');

        $this->stepMock->method('get_id')->willReturn('confirm_step');
        $this->stepMock->method('get_branches')->willReturn([
            ['type' => 'equals', 'value' => 'yes', 'nextStepId' => 'yes_step'],
            ['type' => 'equals', 'value' => 'no', 'nextStepId' => 'no_step'],
        ]);
        $this->stepMock->method('get_next_step_id')->willReturn('fallback_step');

        $this->sessionMock->method('get_current_step_id')->willReturn('confirm_step');
        $this->sessionMock->method('get_answer')->with('confirm_step')->willReturn('unknown');

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
