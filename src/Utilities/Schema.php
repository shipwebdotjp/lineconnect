<?php

namespace Shipweb\LineConnect\Utilities;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\RichMenu\RichMenu;
use Shipweb\LineConnect\Scenario\Scenario;
use Shipweb\LineConnect\PostType\Audience\Audience;
use Shipweb\LineConnect\PostType\Message\Message as SLCMessage;
use Shipweb\LineConnect\PostType\Interaction\Interaction;

class Schema {

    /**
     * get parameter schema
     */
    static function get_parameter_schema($title, $parameter) {
        $schema      = array();
        if (!empty($title)) {
            $schema['title'] = $title;
        }
        if (! empty($parameter['title'])) {
            $schema['title'] = $parameter['title'];
        }
        $actual_type = $parameter['type'];
        if (! empty($parameter['description'])) {
            $schema['description'] = $parameter['description'];
        }
        if (! empty($parameter['oneOf'])) {
            $schema['oneOf'] = $parameter['oneOf'];
        }
        if ($parameter['type'] == 'object') {
            if (! empty($parameter['properties'])) {
                $schema['properties'] = array();
                foreach ($parameter['properties'] as $key => $val) {
                    $schema['properties'][$key] = self::get_parameter_schema($key, $val);
                }
            }
            if (! empty($parameter['additionalProperties'])) {
                $schema['additionalProperties'] = self::get_parameter_schema(null, $parameter['additionalProperties']);
            }
        } elseif ($parameter['type'] == 'array') {
            $schema['items'] = self::get_parameter_schema('parameter', $parameter['items']);
        } elseif ($parameter['type'] == 'slc_message') {
            $actual_type     = 'integer';
            $schema['oneOf'] = array();
            foreach (SLCMessage::get_lineconnect_message_name_array() as $post_id => $title) {
                $schema['oneOf'][] = array(
                    'const' => $post_id,
                    'title' => $title,
                );
            }
            // if count == 0, add empty
            if (count($schema['oneOf']) == 0) {
                $schema['oneOf'][] = array(
                    'const' => 0,
                    'title' => __('Please add message first', lineconnect::PLUGIN_NAME),
                );
            }
        } elseif ($parameter['type'] == 'slc_channel') {
            $actual_type     = 'string';
            $schema['oneOf'] = array();
            foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
                $schema['oneOf'][] = array(
                    'const' => $channel['prefix'],
                    'title' => $channel['name'],
                );
            }
            if (count($schema['oneOf']) == 0) {
                $schema['oneOf'][] = array(
                    'const' => '',
                    'title' => __('Please add channel first', lineconnect::PLUGIN_NAME),
                );
            }
        } elseif ($parameter['type'] == 'slc_richmenu') {
            $actual_type     = 'string';
            $schema['oneOf'] = array();
            foreach (RichMenu::get_richmenus() as $richmenu_id => $richmenu) {
                $schema['oneOf'][] = array(
                    'const' => $richmenu_id,
                    'title' => $richmenu,
                );
            }
            if (count($schema['oneOf']) == 0) {
                $schema['oneOf'][] = array(
                    'const' => '',
                    'title' => __('Please add richmenu first', lineconnect::PLUGIN_NAME),
                );
            }
        } elseif ($parameter['type'] == 'slc_richmenualias') {
            $actual_type     = 'string';
            $schema['oneOf'] = array();
            foreach (RichMenu::get_richmenu_aliases() as $alias_id => $richmenu_id) {
                $schema['oneOf'][] = array(
                    'const' => $alias_id,
                );
            }
            if (count($schema['oneOf']) == 0) {
                $schema['oneOf'][] = array(
                    'const' => '',
                    'title' => __('Please add richmenu alias first', lineconnect::PLUGIN_NAME),
                );
            }
        } elseif ($parameter['type'] == 'slc_audience') {
            $actual_type     = 'integer';
            $schema['oneOf'] = array();
            foreach (Audience::get_lineconnect_audience_name_array() as $audience_id => $audience) {
                $schema['oneOf'][] = array(
                    'const' => $audience_id,
                    'title' => $audience,
                );
            }
            if (count($schema['oneOf']) == 0) {
                $schema['oneOf'][] = array(
                    'const' => '',
                    'title' => __('Please add audience first', lineconnect::PLUGIN_NAME),
                );
            }
        } elseif ($parameter['type'] == 'slc_scenario') {
            $actual_type     = 'integer';
            $schema['oneOf'] = array();
            foreach (Scenario::get_scenario_name_array() as $scenario_id => $scenario) {
                $schema['oneOf'][] = array(
                    'const' => $scenario_id,
                    'title' => $scenario,
                );
            }
            if (count($schema['oneOf']) == 0) {
                $schema['oneOf'][] = array(
                    'const' => '',
                    'title' => __('Please add scenario first', lineconnect::PLUGIN_NAME),
                );
            }
        } elseif ($parameter['type'] == 'slc_interaction') {
            $actual_type     = 'integer';
            $schema['oneOf'] = array();
            foreach (Interaction::get_name_array() as $interaction_id => $interaction) {
                $schema['oneOf'][] = array(
                    'const' => $interaction_id,
                    'title' => $interaction,
                );
            }
            if (count($schema['oneOf']) == 0) {
                $schema['oneOf'][] = array(
                    'const' => '',
                    'title' => __('Please add interaction first', lineconnect::PLUGIN_NAME),
                );
            }
        }

        if (! empty($parameter['enum'])) {
            $schema['enum'] = $parameter['enum'];
        }
        //uniqueItems
        if (! empty($parameter['uniqueItems'])) {
            $schema['uniqueItems'] = $parameter['uniqueItems'];
        }

        $schema['type'] = $actual_type;
        return $schema;
    }
}
