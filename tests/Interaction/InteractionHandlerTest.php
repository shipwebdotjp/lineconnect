<?php

use PHPUnit\Framework\TestCase;
use Shipweb\LineConnect\Interaction\InteractionHandler;
use Shipweb\LineConnect\Interaction\SessionRepository;
use Shipweb\LineConnect\Interaction\ActionRunner;
use Shipweb\LineConnect\Interaction\MessageBuilder;
use Shipweb\LineConnect\Interaction\InputNormalizer;
use Shipweb\LineConnect\Interaction\Validator;

class InteractionHandlerTest extends TestCase
{
    public function testCanBeCreated()
    {
        $sessionRepositoryMock = $this->createMock(SessionRepository::class);
        $actionRunnerMock = $this->createMock(ActionRunner::class);
        $messageBuilderMock = $this->createMock(MessageBuilder::class);
        $normalizerMock = $this->createMock(InputNormalizer::class);
        $validatorMock = $this->createMock(Validator::class);

        $handler = new InteractionHandler(
            $sessionRepositoryMock,
            $actionRunnerMock,
            $messageBuilderMock,
            $normalizerMock,
            $validatorMock
        );

        $this->assertInstanceOf(InteractionHandler::class, $handler);
    }
}
