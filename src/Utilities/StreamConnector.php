<?php

namespace Shipweb\LineConnect\Utilities;

use WP_Stream\Connector;
use WP_Stream\Record;
use LineConnect;

/**
 * STREAM logging connector for LINE Connect.
 */
class StreamConnector extends Connector {
    /**
     * Connector slug
     *
     * @var string
     */
    public $name = 'lineconnect';

    /**
     * Actions registered for this connector
     *
     * These are actions that My Plugin has created, we are defining them here to
     * tell Stream to run a callback each time this action is fired so we can
     * log information about what happened.
     *
     * @var array
     */
    public $actions = array(
        'callback_lineconnect_push_message',

    );

    /**
     * The minimum version required for My Plugin
     *
     * @const string
     */
    const PLUGIN_MIN_VERSION = '2.0.0';

    /**
     * Display an admin notice if plugin dependencies are not satisfied
     *
     * If My Plugin does not have the minimum required version number specified
     * in the constant above, then Stream will display an admin notice for us.
     *
     * @return bool
     */
    public function is_dependency_satisfied() {
        $version_compare = version_compare(lineconnect::VERSION, self::PLUGIN_MIN_VERSION, '>=');
        if (class_exists('lineconnect') && $version_compare) {
            return true;
        }

        return false;
    }

    /**
     * Return translated connector label
     *
     * @return string
     */
    public function get_label() {
        return __('LINE Connect', lineconnect::PLUGIN_NAME);
    }

    /**
     * Return translated context labels
     *
     * @return array
     */
    public function get_context_labels() {
        return array(
            'message'    => __('Message', lineconnect::PLUGIN_NAME),
        );
    }

    /**
     * Return translated action labels
     *
     * @return array
     */
    public function get_action_labels() {
        return array(
            'published' => __('Published', lineconnect::PLUGIN_NAME),
            'failed' => __('Failed', lineconnect::PLUGIN_NAME),
        );
    }

    /**
     * Add action links to Stream drop row in admin list screen
     *
     * This method is optional.
     *
     * @param array  $links  Previous links registered
     * @param Record $record Stream record
     *
     * @return array Action links
     */
    public function action_links($links, $record) {
        // Check if the Foo or Bar exists
        if ($record->object_id && get_post_status($record->object_id)) {
            $post_type_name = "Message"; //$this->get_post_type_name( get_post_type( $record->object_id ) );
            $action_link_text = sprintf(
                esc_html_x('Edit %s', 'Post type singular name', 'stream'),
                $post_type_name
            );
            $links[$action_link_text] = get_edit_post_link($record->object_id);
        }

        return $links;
    }

    /**
     * Track create and update actions on Foos
     *
     * @param array $foo
     * @param bool  $is_new
     *
     * @return void
     */
    public function callback_lineconnect_push_message($message, $is_error) {
        $action = __('Published', lineconnect::PLUGIN_NAME);
        if ($is_error) {
            $action = __('Failed', lineconnect::PLUGIN_NAME);
        }
        $this->log(
            // Summary message
            sprintf(
                __('"%1$s" message %2$s', lineconnect::PLUGIN_NAME),
                $message['title'],
                $action
            ),
            // This array is compacted and saved as Stream meta
            array(
                'action' => $action,
                'id'     => $message['id'],
                'title'  => $message['title'],
            ),
            $message['id'], // Object ID
            'message', // Context
            $action
        );
    }
}
