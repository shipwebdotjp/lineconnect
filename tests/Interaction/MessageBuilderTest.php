<?php

namespace Shipweb\LineConnect\Tests\Interaction;

use WP_UnitTestCase;
use Shipweb\LineConnect\Interaction\MessageBuilder;
use Shipweb\LineConnect\Interaction\StepDefinition;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;

class MessageBuilderTest extends WP_UnitTestCase
{
    private $messageBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->messageBuilder = new MessageBuilder();
    }

    public function testBuildTemplateButtonMessage()
    {
        // 1. Setup
        $message_definition = [
            'type' => 'template_button',
            'text' => 'Select an option:',
            'options' => [
                [
                    'value' => 'option1',
                    'label' => 'Option 1',
                    'nextStepId' => 'step2',
                ],
                [
                    'value' => 'option2',
                    'label' => 'Option 2',
                    'nextStepId' => null,
                ],
                [
                    'value' => 'option3',
                    'label' => 'Option 3',
                ],
            ],
            'column' => 2,
        ];

        $stepDefinition = $this->createMockStepDefinition([$message_definition], 'test_step_1');

        // 2. Execute
        $result = $this->messageBuilder->build($stepDefinition);

        // 3. Assert
        $this->assertInstanceOf(MultiMessageBuilder::class, $result);

        $builtMessages = $result->buildMessage();
        $this->assertCount(1, $builtMessages);

        $flexMessage_array = $builtMessages[0];

        $this->assertEquals('flex', $flexMessage_array['type']);
        $this->assertEquals('Select an option:', $flexMessage_array['altText']);

        $contents = $flexMessage_array['contents'];
        $this->assertEquals('bubble', $contents['type']);

        $body = $contents['body'];
        $this->assertEquals('vertical', $body['layout']);

        $bodyContents = $body['contents'];
        $this->assertCount(2, $bodyContents); // Text + Box for button rows

        // Assert Text Component
        $textComponent = $bodyContents[0];
        $this->assertEquals('text', $textComponent['type']);
        $this->assertEquals('Select an option:', $textComponent['text']);

        // Assert Box holding button rows
        $buttonRowsContainer = $bodyContents[1];
        $this->assertEquals('box', $buttonRowsContainer['type']);
        
        $buttonRows = $buttonRowsContainer['contents'];
        $this->assertCount(2, $buttonRows);

        // Assert Button Row 1
        $buttonRow1 = $buttonRows[0];
        $this->assertEquals('box', $buttonRow1['type']);
        $this->assertEquals('horizontal', $buttonRow1['layout']);
        $this->assertCount(2, $buttonRow1['contents']);

        // Assert Button Row 2
        $buttonRow2 = $buttonRows[1];
        $this->assertEquals('box', $buttonRow2['type']);
        $this->assertEquals('horizontal', $buttonRow2['layout']);
        $this->assertCount(1, $buttonRow2['contents']);

        // Assert Button 1
        $button1 = $buttonRow1['contents'][0];
        $action1 = $button1['action'];
        $this->assertEquals('postback', $action1['type']);
        $this->assertEquals('Option 1', $action1['label']);
        $this->assertEquals('mode=interaction&step=test_step_1&value=option1&nextStepId=step2', $action1['data']);

        // Assert Button 2
        $button2 = $buttonRow1['contents'][1];
        $action2 = $button2['action'];
        $this->assertEquals('postback', $action2['type']);
        $this->assertEquals('Option 2', $action2['label']);
        $this->assertEquals('mode=interaction&step=test_step_1&value=option2', $action2['data']);

        // Assert Button 3
        $button3 = $buttonRow2['contents'][0];
        $action3 = $button3['action'];
        $this->assertEquals('postback', $action3['type']);
        $this->assertEquals('Option 3', $action3['label']);
        $this->assertEquals('mode=interaction&step=test_step_1&value=option3', $action3['data']);
    }

    public function testBuildStickerMessage()
    {
        $message_definition = [
            'type' => 'sticker',
            'packageId' => '1',
            'stickerId' => '1',
        ];
        $stepDefinition = $this->createMockStepDefinition([$message_definition]);
        $result = $this->messageBuilder->build($stepDefinition);
        $built = $result->buildMessage();
        $this->assertEquals('sticker', $built[0]['type']);
        $this->assertEquals('1', $built[0]['packageId']);
        $this->assertEquals('1', $built[0]['stickerId']);
    }

    public function testBuildImageMessage()
    {
        $message_definition = [
            'type' => 'image',
            'originalContentUrl' => 'https://example.com/original.jpg',
            'previewImageUrl' => 'https://example.com/preview.jpg',
        ];
        $stepDefinition = $this->createMockStepDefinition([$message_definition]);
        $result = $this->messageBuilder->build($stepDefinition);
        $built = $result->buildMessage();
        $this->assertEquals('image', $built[0]['type']);
        $this->assertEquals('https://example.com/original.jpg', $built[0]['originalContentUrl']);
        $this->assertEquals('https://example.com/preview.jpg', $built[0]['previewImageUrl']);
    }

    public function testBuildVideoMessage()
    {
        $message_definition = [
            'type' => 'video',
            'originalContentUrl' => 'https://example.com/original.mp4',
            'previewImageUrl' => 'https://example.com/preview.jpg',
        ];
        $stepDefinition = $this->createMockStepDefinition([$message_definition]);
        $result = $this->messageBuilder->build($stepDefinition);
        $built = $result->buildMessage();
        $this->assertEquals('video', $built[0]['type']);
        $this->assertEquals('https://example.com/original.mp4', $built[0]['originalContentUrl']);
        $this->assertEquals('https://example.com/preview.jpg', $built[0]['previewImageUrl']);
    }

    public function testBuildAudioMessage()
    {
        $message_definition = [
            'type' => 'audio',
            'originalContentUrl' => 'https://example.com/original.m4a',
            'duration' => 60000,
        ];
        $stepDefinition = $this->createMockStepDefinition([$message_definition]);
        $result = $this->messageBuilder->build($stepDefinition);
        $built = $result->buildMessage();
        $this->assertEquals('audio', $built[0]['type']);
        $this->assertEquals('https://example.com/original.m4a', $built[0]['originalContentUrl']);
        $this->assertEquals(60000, $built[0]['duration']);
    }

    public function testBuildLocationMessage()
    {
        $message_definition = [
            'type' => 'location',
            'title' => 'my location',
            'address' => 'some address',
            'latitude' => 35.6586,
            'longitude' => 139.7454,
        ];
        $stepDefinition = $this->createMockStepDefinition([$message_definition]);
        $result = $this->messageBuilder->build($stepDefinition);
        $built = $result->buildMessage();
        $this->assertEquals('location', $built[0]['type']);
        $this->assertEquals('my location', $built[0]['title']);
        $this->assertEquals('some address', $built[0]['address']);
        $this->assertEquals(35.6586, $built[0]['latitude']);
        $this->assertEquals(139.7454, $built[0]['longitude']);
    }

    public function testBuildFlexMessage()
    {
        $flexContent = ['type' => 'bubble', 'body' => ['type' => 'box', 'layout' => 'vertical', 'contents' => [['type' => 'text', 'text' => 'hello']]]];
        $message_definition = [
            'type' => 'flex',
            'altText' => 'this is a flex message',
            'contents' => $flexContent,
        ];
        $stepDefinition = $this->createMockStepDefinition([$message_definition]);
        $result = $this->messageBuilder->build($stepDefinition);
        $built = $result->buildMessage();
        $this->assertEquals('flex', $built[0]['type']);
        $this->assertEquals('this is a flex message', $built[0]['altText']);
        $this->assertEquals($flexContent, $built[0]['contents']);
    }

    public function testBuildRawMessage()
    {
        $rawContent = ['type' => 'text', 'text' => 'raw message'];
        $message_definition = [
            'type' => 'raw',
            'raw' => $rawContent,
        ];
        $stepDefinition = $this->createMockStepDefinition([$message_definition]);
        $result = $this->messageBuilder->build($stepDefinition);
        $built = $result->buildMessage();
        $this->assertEquals($rawContent, $built[0]);
    }

    private function createMockStepDefinition(array $messages, string $step_id = 'test_step')
    {
        $stepDefinition = $this->getMockBuilder(StepDefinition::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stepDefinition->method('get_messages')->willReturn($messages);
        $stepDefinition->method('get_id')->willReturn($step_id);
        return $stepDefinition;
    }
}