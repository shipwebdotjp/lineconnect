<?php

namespace Shipweb\LineConnect\PostType\Interaction;

use Shipweb\LineConnect\Core\LineConnect;

class Interaction {
    const NAME = 'interaction';
    const CREDENTIAL_ACTION = LineConnect::PLUGIN_ID . '-nonce-action_' . self::NAME;
    const CREDENTIAL_NAME = LineConnect::PLUGIN_ID . '-nonce-name_' . self::NAME;
    const META_KEY_DATA = self::NAME . '-data';
    const META_KEY_VERSION = self::NAME . '-version';
    const PARAMETER_DATA = LineConnect::PLUGIN_PREFIX . self::META_KEY_DATA;
    const SCHEMA_VERSION = 1;
    const POST_TYPE = LineConnect::PLUGIN_PREFIX . self::NAME;

    public static function get_schema() {
        return apply_filters(
            LineConnect::FILTER_PREFIX . 'lineconnect_interaction_schema',
            Schema::get_schema()
        );
    }

    /** 
     * Return interaction data
     */
    static function get_form_data($post_id, $schema_version, $form_version) {
        $formData = get_post_meta($post_id, Interaction::META_KEY_DATA, true);
        if (isset($formData[$form_version])) {
            $formData = $formData[$form_version][0];
        }
        error_log('Interaction::get_form_data: ' . print_r($formData, true));
        if (empty($schema_version) || $schema_version == self::SCHEMA_VERSION) {
            return !empty($formData) ? $formData : new \stdClass();
        }
        // if old schema veersion, migrate and return
    }

    static function get_name_array() {
        $args          = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        );
        $posts         = get_posts($args);
        $ary = array();
        foreach ($posts as $post) {
            $ary[$post->ID] = $post->post_title;
        }
        return $ary;
    }
}
