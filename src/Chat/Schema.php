<?php

namespace Shipweb\LineConnect\Chat;

use Shipweb\LineConnect\Core\LineConnect;

class Schema {


    static function get_userdata_type_items() {
        return array(
            'profile' => array(
                'type' => 'object',
                'title' => __('Profile', lineconnect::PLUGIN_NAME),
                'properties' => array(
                    'displayName' => array(
                        'type' => 'string',
                        'title' => __('Display Name', lineconnect::PLUGIN_NAME),
                    ),
                    'language' => array(
                        'type' => 'string',
                        'title' => __('Language', lineconnect::PLUGIN_NAME),
                    ),
                    'pictureUrl' => array(
                        'type' => 'string',
                        'title' => __('Picture URL', lineconnect::PLUGIN_NAME),
                    ),
                    'statusMessage' => array(
                        'type' => 'string',
                        'title' => __('Status Message', lineconnect::PLUGIN_NAME),
                    ),
                ),
                'additionalProperties' => array(
                    'type' => 'string',
                ),
            ),
            'tags' => array(
                'type' => 'array',
                'title' => __('Tags', lineconnect::PLUGIN_NAME),
                'items' => array(
                    'type' => 'string',
                ),
            ),
            'scenarios' => array(
                'type' => 'object',
                'patternProperties' => array(
                    '^[0-9]+$' => array(
                        'type' => 'object',
                        'properties' => array(
                            'id' => array(
                                'type' => 'integer',
                                'title' => __('Scenario ID', lineconnect::PLUGIN_NAME),
                            ),
                            'logs' => array(
                                'type' => 'array',
                                'items' => array(
                                    'type' => 'object',
                                    'properties' => array(
                                        'date' => array(
                                            'type' => 'string',
                                            'format' => 'date-time',
                                            'title' => __('Log Date', lineconnect::PLUGIN_NAME),
                                        ),
                                        'step' => array(
                                            'type' => 'string',
                                            'title' => __('Step', lineconnect::PLUGIN_NAME),
                                        ),
                                        'result' => array(
                                            'type' => 'string',
                                            'title' => __('Result', lineconnect::PLUGIN_NAME),
                                        ),
                                        'message' => array(
                                            'type' => 'string',
                                            'title' => __('Message', lineconnect::PLUGIN_NAME),
                                        ),
                                    ),
                                    'required' => array('date', 'step', 'result', 'message'),
                                ),
                            ),
                            'status' => array(
                                'type' => 'string',
                                'title' => __('Status', lineconnect::PLUGIN_NAME),
                            ),
                            'started_at' => array(
                                'type' => 'string',
                                'format' => 'date-time',
                                'title' => __('Started At', lineconnect::PLUGIN_NAME),
                            ),
                            'updated_at' => array(
                                'type' => 'string',
                                'format' => 'date-time',
                                'title' => __('Updated At', lineconnect::PLUGIN_NAME),
                            ),
                        ),
                        'required' => array('id'),
                    ),
                ),
                'additionalProperties' => false,
                'title' => __('Scenarios', lineconnect::PLUGIN_NAME),
            ),
        );
    }

    static function get_userdata_uischema() {
        return array(
            // 'ui:submitButtonOptions' => array(
            //     'norender' => true,
            // ),
            'profile' => array(
                'ui:options' => array(
                    'addText' =>  __('Add profile', lineconnect::PLUGIN_NAME),
                    'copyable' => true,
                ),
            ),
            'tags' => array(
                'ui:options' => array(
                    'addText' =>  __('Add tags', lineconnect::PLUGIN_NAME),
                    'copyable' => true,
                ),
            ),
        );
    }
}
