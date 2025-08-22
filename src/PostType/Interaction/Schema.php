<?php

namespace Shipweb\LineConnect\PostType\Interaction;

use Shipweb\LineConnect\Action\Action;
use Shipweb\LineConnect\Core\LineConnect;

class Schema {
    static function get_schema() {
        $schema = array(
            'type'       => 'object',
            'title'      => __('Interaction', LineConnect::PLUGIN_NAME),
            'description' => __('Interaction form schema', LineConnect::PLUGIN_NAME),
            'required'    => array(
                'version',
                'storage',
                'steps'
            ),
            'properties' => array(
                'steps' => array(
                    'type' => 'array',
                    'title' => __('Steps', LineConnect::PLUGIN_NAME),
                    'minItems' => 1,
                    'items' => array(
                        'type' => 'object',
                        'title' => __('Step', LineConnect::PLUGIN_NAME),
                        'required' => array('id'),
                        'properties' => array(
                            'id' => array(
                                'type' => 'string',
                                'title' => __('ID', LineConnect::PLUGIN_NAME),
                                'minLength' => 1,
                            ),
                            'title' => array(
                                'type' => 'string',
                                'title' => __('Title', LineConnect::PLUGIN_NAME),
                            ),
                            'description' => array(
                                'type' => 'string',
                                'title' => __('Description', LineConnect::PLUGIN_NAME),
                            ),
                            'messages' => array(
                                'type' => 'array',
                                'items' => array(
                                    'type' => 'object',
                                    'title' => __('Messages', LineConnect::PLUGIN_NAME),
                                    'properties' => array(
                                        'type' => array(
                                            'type' => 'string',
                                            'title' => __('Message Type', LineConnect::PLUGIN_NAME),
                                            'oneOf' => array(
                                                array('const' => 'text', 'title' => __('Text', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'sticker', 'title' => __('Sticker', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'image', 'title' => __('Image', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'video', 'title' => __('Video', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'audio', 'title' => __('Audio', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'location', 'title' => __('Location', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'flex', 'title' => __('Flex', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'raw', 'title' => __('Raw', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'template_button', 'title' => __('Template Button', LineConnect::PLUGIN_NAME)),
                                            ),
                                        ),
                                    ),
                                    'dependencies' => array(
                                        'type' => array(
                                            'oneOf' => array(
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'text'),
                                                        'text' => array(
                                                            'type'     => 'string',
                                                            'title'    => __('Text', LineConnect::PLUGIN_NAME),
                                                            'description' => __('Message text. Max character limit: 5000', LineConnect::PLUGIN_NAME),
                                                            'maxLength' => 5000,
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'sticker'),
                                                        'sticker' => array(
                                                            'type'     => 'object',
                                                            'title'    => __('Sticker', LineConnect::PLUGIN_NAME),
                                                            'properties' => array(
                                                                'packageId' => array(
                                                                    'type' => 'string',
                                                                    'title' => __('Package ID', LineConnect::PLUGIN_NAME),
                                                                    'description' => __('Package ID for a set of stickers.', LineConnect::PLUGIN_NAME),
                                                                ),
                                                                'stickerId' => array(
                                                                    'type' => 'string',
                                                                    'title' => __('Sticker ID', LineConnect::PLUGIN_NAME),
                                                                ),
                                                            ),
                                                            'required' => array('packageId', 'stickerId'),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'image'),
                                                        'image' => array(
                                                            'type'     => 'object',
                                                            'title'    => __('Image', LineConnect::PLUGIN_NAME),
                                                            'properties' => array(
                                                                'originalContentUrl' => array(
                                                                    'type' => 'string',
                                                                    'title' => __('Original content URL', LineConnect::PLUGIN_NAME),
                                                                    'description' => __('Image file URL. Protocol: HTTPS, Image format: JPEG or PNG, Max file size: 10 MB', LineConnect::PLUGIN_NAME),
                                                                    'maxLength' => 2000,
                                                                ),
                                                                'previewImageUrl' => array(
                                                                    'type' => 'string',
                                                                    'title' => __('Preview Image Url', LineConnect::PLUGIN_NAME),
                                                                    'description' => __('Preview image URL. Image format: JPEG or PNG, Max file size: 1 MB', LineConnect::PLUGIN_NAME),
                                                                    'maxLength' => 2000,
                                                                ),
                                                            ),
                                                            'required' => array('originalContentUrl', 'previewImageUrl'),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'video'),
                                                        'video' => array(
                                                            'type'     => 'object',
                                                            'title'    => __('Video', LineConnect::PLUGIN_NAME),
                                                            'description' => __('If the video isn\'t playing properly, make sure the video is a supported file type and the HTTP server hosting the video supports HTTP range requests.', LineConnect::PLUGIN_NAME),
                                                            'properties' => array(
                                                                'originalContentUrl' => array(
                                                                    'type' => 'string',
                                                                    'title' => __('Original content URL', LineConnect::PLUGIN_NAME),
                                                                    'description' => __('Video file URL. Protocol: HTTPS, Video format: MP4, Max file size: 200 MB', LineConnect::PLUGIN_NAME),
                                                                    'maxLength' => 2000,
                                                                ),
                                                                'previewImageUrl' => array(
                                                                    'type' => 'string',
                                                                    'title' => __('Preview image URL', LineConnect::PLUGIN_NAME),
                                                                    'description' => __('Preview image URL. Protocol: HTTPS, Image format: JPEG or PNG, Max file size: 1 MB', LineConnect::PLUGIN_NAME),
                                                                    'maxLength' => 2000,
                                                                ),
                                                                'trackingId' => array(
                                                                    'type' => 'string',
                                                                    'title' => __('Tracking ID', LineConnect::PLUGIN_NAME),
                                                                    'description' => __('ID used to identify the video when Video viewing complete event occurs. You can use the same ID in multiple messages.', LineConnect::PLUGIN_NAME),
                                                                    'maxLength' => 100,
                                                                ),
                                                            ),
                                                            'required' => array('originalContentUrl', 'previewImageUrl'),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'audio'),
                                                        'audio' => array(
                                                            'type'     => 'object',
                                                            'title'    => __('Audio', LineConnect::PLUGIN_NAME),
                                                            'properties' => array(
                                                                'originalContentUrl' => array(
                                                                    'type' => 'string',
                                                                    'title' => __('Original content URL', LineConnect::PLUGIN_NAME),
                                                                    'description' => __('Audio file URL. Protocol: HTTPS, Audio format: MP3 or MP4, Max file size: 200 MB', LineConnect::PLUGIN_NAME),
                                                                ),
                                                                'duration' => array(
                                                                    'type' => 'number',
                                                                    'title' => __('Duration', LineConnect::PLUGIN_NAME),
                                                                    'description' => __('Length of audio file (milliseconds).', LineConnect::PLUGIN_NAME),
                                                                ),
                                                            ),
                                                            'required' => array('originalContentUrl', 'duration'),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'location'),
                                                        'location' => array(
                                                            'type'     => 'object',
                                                            'title'    => __('Location', LineConnect::PLUGIN_NAME),
                                                            'properties' => array(
                                                                'title'    => array(
                                                                    'type' => 'string',
                                                                    'title' => __('Title', LineConnect::PLUGIN_NAME),
                                                                    'maxLength' => 100,
                                                                ),
                                                                'address'  => array(
                                                                    'type' => 'string',
                                                                    'title' => __('Address', LineConnect::PLUGIN_NAME),
                                                                    'maxLength' => 100,
                                                                ),
                                                                'latitude' => array(
                                                                    'type' => 'number',
                                                                    'title' => __('Latitude', LineConnect::PLUGIN_NAME),
                                                                ),
                                                                'longitude' => array(
                                                                    'type' => 'number',
                                                                    'title' => __('Longitude', LineConnect::PLUGIN_NAME),
                                                                ),
                                                            ),
                                                            'required' => array('title', 'address', 'latitude', 'longitude'),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'flex'),
                                                        'flex'     => array(
                                                            'type'     => 'object',
                                                            'title'    => __('Flex message object', LineConnect::PLUGIN_NAME),
                                                            'description' => __('Flex Messages are messages with a customizable layout. You can customize the layout freely based on the specification for CSS Flexible Box.', LineConnect::PLUGIN_NAME),
                                                            'properties' => array(
                                                                'raw' => array(
                                                                    'type' => 'string',
                                                                    'title' => __('Flex message JSON object', LineConnect::PLUGIN_NAME),
                                                                    'description' => __('Flex message JSON object such as generated by Flex Message Simulator', LineConnect::PLUGIN_NAME),
                                                                ),
                                                                'alttext' => array(
                                                                    'type' => 'string',
                                                                    'title' => __('Alt text', LineConnect::PLUGIN_NAME),
                                                                    'maxLength' => 400,
                                                                    'description' => __('Alt text of Flex message', LineConnect::PLUGIN_NAME),
                                                                ),
                                                            ),
                                                            'required' => array('raw', 'alttext'),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'raw'),
                                                        'raw' => array(
                                                            'type'     => 'string',
                                                            'title' => __('Message JSON object', LineConnect::PLUGIN_NAME),
                                                            'description' => __('Message JSON object', LineConnect::PLUGIN_NAME),
                                                        ),
                                                    ),
                                                    'required' => array('raw'),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'template_button'),
                                                        'template_button' => array(
                                                            'type' => 'object',
                                                            'title' => __('Template Button Settings', LineConnect::PLUGIN_NAME),
                                                            'properties' => array(
                                                                'text' => array(
                                                                    'type' => 'string',
                                                                    'title' => __('Template Title', LineConnect::PLUGIN_NAME),
                                                                ),
                                                                'options' => array(
                                                                    'type' => 'array',
                                                                    'title' => __('Options', LineConnect::PLUGIN_NAME),
                                                                    'minItems' => 1,
                                                                    'items' => array(
                                                                        'type' => 'object',
                                                                        'title' => __('Option', LineConnect::PLUGIN_NAME),
                                                                        'required' => array('value'),
                                                                        'properties' => array(
                                                                            'value' => array('type' => 'string', 'title' => __('Value', LineConnect::PLUGIN_NAME)),
                                                                            'label' => array('type' => 'string', 'title' => __('Label', LineConnect::PLUGIN_NAME)),
                                                                            'nextStepId' => array('type' => array('string', 'null'), 'title' => __('Next Step ID', LineConnect::PLUGIN_NAME)),
                                                                            'secondary' => array('type' => 'boolean', 'title' => __('Secondary Button Style', LineConnect::PLUGIN_NAME)),
                                                                            'width' => array('type' => 'string', 'title' => __('Width', LineConnect::PLUGIN_NAME)),
                                                                        ),
                                                                    ),
                                                                ),
                                                                'column' => array('type' => 'integer', 'title' => __('Columns', LineConnect::PLUGIN_NAME)),
                                                            ),
                                                        ),
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'normalize' => array(
                                'type' => 'object',
                                'title' => __('Normalize', LineConnect::PLUGIN_NAME),
                                'properties' => array(
                                    'trim' => array('type' => 'boolean', 'title' => __('Trim', LineConnect::PLUGIN_NAME)),
                                    'omit' => array('type' => 'string', 'title' => __('Omit', LineConnect::PLUGIN_NAME)),
                                    'HanKatatoZenKata' => array('type' => 'boolean', 'title' => __('Half-width Katakana to Zen-kaku Katakana', LineConnect::PLUGIN_NAME)), // 半角カタカナ → 全角カタカナ
                                    'HanKatatoZenKana' => array('type' => 'boolean', 'title' => __('Half-width Katakana to Zen-kaku Hiragana', LineConnect::PLUGIN_NAME)), // 半角カタカナ → 全角ひらがな
                                    'ZenKatatoZenKana' => array('type' => 'boolean', 'title' => __('Zen-kaku Katakana to Zen-kaku Hiragana', LineConnect::PLUGIN_NAME)),   // 全角カタカナ → 全角ひらがな
                                    'ZenKanatoZenKata' => array('type' => 'boolean', 'title' => __('Zen-kaku Hiragana to Zen-kaku Katakana', LineConnect::PLUGIN_NAME)),   // 全角ひらがな → 全角カタカナ
                                    'HanEisutoZenEisu' => array('type' => 'boolean', 'title' => __('Half-width Alphanumeric to Zen-kaku Alphanumeric', LineConnect::PLUGIN_NAME)), // 半角英数字 → 全角
                                    'ZenEisutoHanEisu' => array('type' => 'boolean', 'title' => __('Zen-kaku Alphanumeric to Half-width Alphanumeric', LineConnect::PLUGIN_NAME)), // 全角英数字 → 半角
                                    //'dateParseToUTC' => array('type' => 'boolean', 'title' => __('Parse Date to UTC', LineConnect::PLUGIN_NAME)),
                                ),
                            ),
                            'validate' => array(
                                'type' => 'array',
                                'title' => __('Validation', LineConnect::PLUGIN_NAME),
                                'items' => array(
                                    'type' => 'object',
                                    'properties' => array(
                                        'type' => array(
                                            'type' => 'string',
                                            'title' => __('Type', LineConnect::PLUGIN_NAME),
                                            'oneOf' => array(
                                                array('const' => 'required', 'title' => __('Required', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'number', 'title' => __('Number', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'length', 'title' => __('Length', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'email', 'title' => __('Email', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'phone', 'title' => __('Phone', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'date', 'title' => __('Date', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'time', 'title' => __('Time', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'datetime', 'title' => __('Datetime', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'url', 'title' => __('URL', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'enum', 'title' => __('Enum', LineConnect::PLUGIN_NAME)),
                                                //array('const' => 'file', 'title' => __('File', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'regex', 'title' => __('Regex', LineConnect::PLUGIN_NAME)),
                                                //array('const' => 'input_type', 'title' => __('Input Type', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'japanese', 'title' => __('Japanese', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'forbidden', 'title' => __('Forbidden Content', LineConnect::PLUGIN_NAME)),
                                            ),
                                        ),
                                    ),
                                    'dependencies' => array(
                                        'type' => array(
                                            'oneOf' => array(
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'required'),
                                                        'required' => array(
                                                            'type' => 'boolean',
                                                            'title' => __('Required', LineConnect::PLUGIN_NAME),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'number'),
                                                        'number' => array(
                                                            'type' => 'object',
                                                            'title' => __('Number Validation', LineConnect::PLUGIN_NAME),
                                                            'properties' => array(
                                                                'enabled' => array(
                                                                    'type' => 'boolean',
                                                                    'title' => __('Enable Number Validation', LineConnect::PLUGIN_NAME),
                                                                ),
                                                                'min' => array(
                                                                    'type' => array('number', 'null'),
                                                                    'title' => __('Min', LineConnect::PLUGIN_NAME),
                                                                ),
                                                                'max' => array(
                                                                    'type' => array('number', 'null'),
                                                                    'title' => __('Max', LineConnect::PLUGIN_NAME),
                                                                ),
                                                            ),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'length'),
                                                        'length' => array(
                                                            'type' => 'object',
                                                            'title' => __('Length Validation', LineConnect::PLUGIN_NAME),
                                                            'properties' => array(
                                                                'minlength' => array(
                                                                    'type' => array('integer', 'null'),
                                                                    'minimum' => 0,
                                                                    'title' => __('Min Length', LineConnect::PLUGIN_NAME),
                                                                ),
                                                                'maxlength' => array(
                                                                    'type' => array('integer', 'null'),
                                                                    'minimum' => 1,
                                                                    'title' => __('Max Length', LineConnect::PLUGIN_NAME),
                                                                ),
                                                            ),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'email'),
                                                        'email' => array(
                                                            'type' => 'boolean',
                                                            'title' => __('Email', LineConnect::PLUGIN_NAME),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'phone'),
                                                        'phone' => array(
                                                            'type' => 'boolean',
                                                            'title' => __('Phone', LineConnect::PLUGIN_NAME),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'date'),
                                                        'date' => array(
                                                            'type' => 'boolean',
                                                            'title' => __('Date', LineConnect::PLUGIN_NAME),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'time'),
                                                        'time' => array(
                                                            'type' => 'boolean',
                                                            'title' => __('Time', LineConnect::PLUGIN_NAME),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'datetime'),
                                                        'datetime' => array(
                                                            'type' => 'boolean',
                                                            'title' => __('Datetime', LineConnect::PLUGIN_NAME),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'url'),
                                                        'url' => array(
                                                            'type' => 'boolean',
                                                            'title' => __('URL', LineConnect::PLUGIN_NAME),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'enum'),
                                                        'enum' => array(
                                                            'type' => 'array',
                                                            'items' => array('type' => 'string'),
                                                            'title' => __('Enum', LineConnect::PLUGIN_NAME),
                                                        ),
                                                    ),
                                                ),
                                                // array(
                                                //     'properties' => array(
                                                //         'type' => array('const' => 'file'),
                                                //         'file' => array(
                                                //             'type' => 'object',
                                                //             'title' => __('File Validation', LineConnect::PLUGIN_NAME),
                                                //             'properties' => array(
                                                //                 'file_type' => array(
                                                //                     'type' => 'array',
                                                //                     'items' => array('type' => 'string'),
                                                //                     'title' => __('File Type', LineConnect::PLUGIN_NAME),
                                                //                 ),
                                                //                 'file_size' => array(
                                                //                     'type' => array('integer', 'null'),
                                                //                     'minimum' => 1,
                                                //                     'title' => __('File Size', LineConnect::PLUGIN_NAME),
                                                //                 ),
                                                //             ),
                                                //         ),
                                                //     ),
                                                // ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'regex'),
                                                        'regex' => array(
                                                            'type' => array('string', 'null'),
                                                            'title' => __('Regex', LineConnect::PLUGIN_NAME),
                                                        ),
                                                    ),
                                                ),
                                                // array(
                                                //     'properties' => array(
                                                //         'type' => array('const' => 'input_type'),
                                                //         'input_type' => array(
                                                //             'type' => 'string',
                                                //             'title' => __('Expect Input Type', LineConnect::PLUGIN_NAME),
                                                //             'oneOf' => array(
                                                //                 array('const' => 'none', 'title' => __('None', LineConnect::PLUGIN_NAME)),
                                                //                 array('const' => 'text', 'title' => __('Text', LineConnect::PLUGIN_NAME)),
                                                //                 array('const' => 'postback-text', 'title' => __('Postback Text', LineConnect::PLUGIN_NAME)),
                                                //                 array('const' => 'postback-date', 'title' => __('Postback Date', LineConnect::PLUGIN_NAME)),
                                                //                 array('const' => 'postback-time', 'title' => __('Postback Time', LineConnect::PLUGIN_NAME)),
                                                //                 array('const' => 'postback-datetime', 'title' => __('Postback Datetime', LineConnect::PLUGIN_NAME)),
                                                //                 array('const' => 'image', 'title' => __('Image', LineConnect::PLUGIN_NAME)),
                                                //                 array('const' => 'video', 'title' => __('Video', LineConnect::PLUGIN_NAME)),
                                                //                 array('const' => 'audio', 'title' => __('Audio', LineConnect::PLUGIN_NAME)),
                                                //                 array('const' => 'location', 'title' => __('Location', LineConnect::PLUGIN_NAME)),
                                                //                 array('const' => 'sticker', 'title' => __('Sticker', LineConnect::PLUGIN_NAME)),
                                                //                 array('const' => 'file', 'title' => __('File', LineConnect::PLUGIN_NAME)),
                                                //             ),
                                                //         ),
                                                //     ),
                                                // ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'japanese'),
                                                        'japanese' => array(
                                                            'type' => 'string',
                                                            'title' => __('Japanese Validation', LineConnect::PLUGIN_NAME),
                                                            'oneOf' => array(
                                                                array(
                                                                    'const' => 'hiragana',
                                                                    'title' => __('Hiragana Only', LineConnect::PLUGIN_NAME),
                                                                ),
                                                                array(
                                                                    'const' => 'katakana',
                                                                    'title' => __('Katakana Only', LineConnect::PLUGIN_NAME),
                                                                ),
                                                            ),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'properties' => array(
                                                        'type' => array('const' => 'forbidden'),
                                                        'forbidden' => array(
                                                            'type' => 'object',
                                                            'title' => __('Forbidden Content', LineConnect::PLUGIN_NAME),
                                                            'properties' => array(
                                                                'words' => array(
                                                                    'type' => 'array',
                                                                    'items' => array('type' => 'string'),
                                                                    'title' => __('Forbidden Words', LineConnect::PLUGIN_NAME),
                                                                ),
                                                                'patterns' => array(
                                                                    'type' => 'array',
                                                                    'items' => array('type' => 'string'),
                                                                    'title' => __('Forbidden Patterns (Regex)', LineConnect::PLUGIN_NAME),
                                                                ),
                                                            ),
                                                        ),
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'branches' => array(
                                'type' => 'array',
                                'title' => __('Branches', LineConnect::PLUGIN_NAME),
                                'description' => __('Define conditional branches based on user input. If no conditions match, the default Next Step ID will be used.', LineConnect::PLUGIN_NAME),
                                'items' => array(
                                    'type' => 'object',
                                    'title' => __('Branch', LineConnect::PLUGIN_NAME),
                                    'required' => array('type', 'value', 'nextStepId'),
                                    'properties' => array(
                                        'type' => array(
                                            'type' => 'string',
                                            'title' => __('Condition Type', LineConnect::PLUGIN_NAME),
                                            'oneOf' => array(
                                                array('const' => 'equals', 'title' => __('Equals', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'contains', 'title' => __('Contains', LineConnect::PLUGIN_NAME)),
                                                array('const' => 'regex', 'title' => __('Regex', LineConnect::PLUGIN_NAME)),
                                            ),
                                            'default' => 'equals',
                                        ),
                                        'value' => array(
                                            'type' => 'string',
                                            'title' => __('Value to Match', LineConnect::PLUGIN_NAME),
                                        ),
                                        'nextStepId' => array(
                                            'type' => 'string',
                                            'title' => __('Next Step ID for this branch', LineConnect::PLUGIN_NAME),
                                        ),
                                    ),
                                ),
                            ),
                            'nextStepId' => array(
                                'type' => array('string', 'null'),
                                'title' => __('Next Step ID', LineConnect::PLUGIN_NAME),
                            ),
                            'stop' => array(
                                'type' => 'boolean',
                                'title' => __('Stop', LineConnect::PLUGIN_NAME),
                                'default' => false,
                            ),
                            'special' => array(
                                'type' => 'string',
                                'title' => __('Special Step', LineConnect::PLUGIN_NAME),
                                'oneOf' => array(
                                    array('const' => 'confirm', 'title' => __('Confirm', LineConnect::PLUGIN_NAME)),
                                    array('const' => 'editPicker', 'title' => __('Edit Picker', LineConnect::PLUGIN_NAME)),
                                    array('const' => 'complete', 'title' => __('Complete', LineConnect::PLUGIN_NAME)),
                                    array('const' => 'cancelConfirm', 'title' => __('Cancel Confirm', LineConnect::PLUGIN_NAME)),
                                    array('const' => 'canceled', 'title' => __('Canceled', LineConnect::PLUGIN_NAME)),
                                    // array('const' => 'resumeConfirm', 'title' => __('Resume Confirm', LineConnect::PLUGIN_NAME)),
                                    array('const' => 'timeoutRemind', 'title' => __('Timeout Remind', LineConnect::PLUGIN_NAME)),
                                    array('const' => 'timeoutNotice', 'title' => __('Timeout Notice', LineConnect::PLUGIN_NAME)),
                                ),
                                'additionalProperties' => false,
                            ),
                            'beforeActions' => array(
                                'type' => 'object',
                                'title' => __('Before Actions', LineConnect::PLUGIN_NAME),
                                'properties' => array(
                                    'actions'  => array(
                                        'title' => __('Action', lineconnect::PLUGIN_NAME),
                                        'type'  => 'array',
                                        'items' => array(
                                            'type'     => 'object',
                                            'oneOf'    => array(),
                                            'required' => array(
                                                'parameters',
                                            ),
                                        ),
                                    ),
                                    'chains' => array(
                                        'type' => 'array',
                                        'title' => __('Action chain', lineconnect::PLUGIN_NAME),
                                        'items' => array(
                                            'type'     => 'object',
                                            'properties' => array(
                                                'to' => array(
                                                    'type' => 'string',
                                                    'title' => __('Destination argument to', lineconnect::PLUGIN_NAME),
                                                    'description' => __('Injection Destination Argument Path. e.g. 2.message', lineconnect::PLUGIN_NAME),
                                                ),
                                                'data' => array(
                                                    'type' => 'string',
                                                    'title' => __('Data', lineconnect::PLUGIN_NAME),
                                                    'description' => __('Injection Data. You can use return value of previous action. e.g. {{$.return.1}}', lineconnect::PLUGIN_NAME),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'afterActions' => array(
                                'type' => 'object',
                                'title' => __('After Actions', LineConnect::PLUGIN_NAME),
                                'properties' => array(
                                    'actions'  => array(
                                        'title' => __('Action', lineconnect::PLUGIN_NAME),
                                        'type'  => 'array',
                                        'items' => array(
                                            'type'     => 'object',
                                            'oneOf'    => array(),
                                            'required' => array(
                                                'parameters',
                                            ),
                                        ),
                                    ),
                                    'chains' => array(
                                        'type' => 'array',
                                        'title' => __('Action chain', lineconnect::PLUGIN_NAME),
                                        'items' => array(
                                            'type'     => 'object',
                                            'properties' => array(
                                                'to' => array(
                                                    'type' => 'string',
                                                    'title' => __('Destination argument to', lineconnect::PLUGIN_NAME),
                                                    'description' => __('Injection Destination Argument Path. e.g. 2.message', lineconnect::PLUGIN_NAME),
                                                ),
                                                'data' => array(
                                                    'type' => 'string',
                                                    'title' => __('Data', lineconnect::PLUGIN_NAME),
                                                    'description' => __('Injection Data. You can use return value of previous action. e.g. {{$.return.1}}', lineconnect::PLUGIN_NAME),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        'additionalProperties' => false,
                    ),
                ),
                'timeoutMinutes' => array(
                    'type' => 'integer',
                    'title' => __('Timeout (minutes)', LineConnect::PLUGIN_NAME),
                    'description' => __('The number of minutes from the last update to the timeout. If 0, there is no timeout.', LineConnect::PLUGIN_NAME),
                    'minimum' => 0,
                    'default' => 0,
                ),
                'timeoutRemind' => array(
                    'type' => 'integer',
                    'title' => __('Send timeout reminder', LineConnect::PLUGIN_NAME),
                    'description' => __('Set how many minutes before the timeout to send a reminder. 0 means no reminder.', LineConnect::PLUGIN_NAME),
                    'minimum' => 0,
                    'default' => 0,
                ),
                'onTimeout' => array(
                    'type' => 'string',
                    'title' => __('On Timeout', LineConnect::PLUGIN_NAME),
                    'anyOf' => array(
                        array('const' => 'delete_session', 'title' => __('Delete Session', LineConnect::PLUGIN_NAME)),
                        array('const' => 'mark_timeout', 'title' => __('Mark as Timeout', LineConnect::PLUGIN_NAME)),
                    ),
                    'default' => 'mark_timeout',
                ),
                'runPolicy' => array(
                    'type' => 'string',
                    'title' => __('Run Policy', LineConnect::PLUGIN_NAME),
                    'anyOf' => array(
                        array('const' => 'single_forbid', 'title' => __("Don't allow", LineConnect::PLUGIN_NAME)),
                        array('const' => 'single_latest_only', 'title' => __('Allow (keep latest only)', LineConnect::PLUGIN_NAME)),
                        array('const' => 'multi_keep_history', 'title' => __('Allow (keep history)', LineConnect::PLUGIN_NAME)),
                    ),
                    'default' => 'single_latest_only',
                ),
                'overridePolicy' => array(
                    'type' => 'string',
                    'title' => __('Override Policy', LineConnect::PLUGIN_NAME),
                    'anyOf' => array(
                        array('const' => 'reject', 'title' => __('Reject', LineConnect::PLUGIN_NAME)),
                        array('const' => 'restart_same', 'title' => __('Restart only same', LineConnect::PLUGIN_NAME)),
                        array('const' => 'restart_diff', 'title' => __('Restart only different', LineConnect::PLUGIN_NAME)),
                        array('const' => 'restart_always', 'title' => __('Always restart', LineConnect::PLUGIN_NAME)),
                        array('const' => 'stack', 'title' => __('Stack', LineConnect::PLUGIN_NAME)),
                    ),
                    'default' => 'stack',
                ),
                'version' => array(
                    'type' => 'integer',
                    'title' => __('Version', LineConnect::PLUGIN_NAME),
                    'description' => __('The version of the interaction form. If you need to make changes to the form structure, increment this version number.', LineConnect::PLUGIN_NAME),
                    'minimum' => 1,
                    'default' => 1,
                ),
                'storage' => array(
                    'type' => 'string',
                    'title' => __('Storage', LineConnect::PLUGIN_NAME),
                    'description' => __('Where the interaction data is stored', LineConnect::PLUGIN_NAME),
                    'anyOf' => array(
                        array('const' => 'profile', 'title' => __('Bind to Profile', LineConnect::PLUGIN_NAME)),
                        array('const' => 'interactions', 'title' => __('Interactions', LineConnect::PLUGIN_NAME)),
                    ),
                    'default' => 'interactions',
                ),
                'excludeSteps' => array(
                    'type' => 'array',
                    'title' => __('Exclude Steps', LineConnect::PLUGIN_NAME),
                    'items' => array(
                        'type' => 'string',
                    )
                ),
            ),
            'additionalProperties' => false,
        );
        Action::build_action_schema_items(
            $schema['properties']['steps']['items']['properties']['beforeActions']['properties']['actions']['items']['oneOf']
        );
        Action::build_action_schema_items(
            $schema['properties']['steps']['items']['properties']['afterActions']['properties']['actions']['items']['oneOf']
        );
        return $schema;
    }
    static function get_uischema() {
        return array(
            'ui:submitButtonOptions' => array(
                'norender' => true,
            ),
            'steps' => array(
                'items' => array(
                    'messages' => array(
                        'items' => array(
                            'template_button' => array(
                                'options' => array(
                                    'ui:options' => array(
                                        'addText' => __('Add option', LineConnect::PLUGIN_NAME),
                                    ),
                                ),
                            ),
                        ),
                        'ui:options' => array(
                            'addText' => __('Add message', LineConnect::PLUGIN_NAME),
                        ),
                    ),
                    'validate' => array(
                        'items' => array(
                            'enum' => array(
                                'ui:options' => array(
                                    'addText' => __('Add enum', LineConnect::PLUGIN_NAME),
                                ),
                            ),
                        ),
                        'ui:options' => array(
                            'addText' => __('Add validation', LineConnect::PLUGIN_NAME),
                        ),
                    ),
                    'beforeActions' => array(
                        'actions' => array(
                            'ui:options' => array(
                                'addText' => __('Add action', LineConnect::PLUGIN_NAME),
                            ),
                        ),
                        'chains' => array(
                            'ui:options' => array(
                                'addText' => __('Add chain', LineConnect::PLUGIN_NAME),
                            ),
                        ),
                    ),
                    'afterActions' => array(
                        'actions' => array(
                            'ui:options' => array(
                                'addText' => __('Add action', LineConnect::PLUGIN_NAME),
                            ),
                        ),
                        'chains' => array(
                            'ui:options' => array(
                                'addText' => __('Add chain', LineConnect::PLUGIN_NAME),
                            ),
                        ),
                    ),
                ),
                'ui:options' => array(
                    'addText' => __('Add steps', LineConnect::PLUGIN_NAME),
                ),
            ),
        );
    }
}
