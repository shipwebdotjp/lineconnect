<?php

namespace Shipweb\LineConnect\RichMenu;

use Shipweb\LineConnect\PostType\Message\Schema as MessageSchema;
use lineconnect;
use lineconnectConst;

class Schema {
    static function get_template_bounds() {
        return array(
            array(
                'id' => '3-2_3x2_3_3',
                'title' => __('3:2 3x2 row1: 3 col, row2: 3 col', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-2_3x2_3_3.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 1686,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 833,
                        'height' => 843,
                    ),
                    array(
                        'x'      => 834,
                        'y'      => 0,
                        'width'  => 833,
                        'height' => 843,
                    ),
                    array(
                        'x'      => 1667,
                        'y'      => 0,
                        'width'  => 833,
                        'height' => 843,
                    ),
                    array(
                        'x'      => 0,
                        'y'      => 843,
                        'width'  => 833,
                        'height' => 843,
                    ),
                    array(
                        'x'      => 834,
                        'y'      => 843,
                        'width'  => 833,
                        'height' => 843,
                    ),
                    array(
                        'x'      => 1667,
                        'y'      => 843,
                        'width'  => 833,
                        'height' => 843,
                    ),
                ),
            ),
            array(
                'id' => '3-2_2x2_2_2',
                'title' => __('3:2 2x2 row1: 2 col, row2: 2 col', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-2_2x2_2_2.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 1686,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 1250,
                        'height' => 843,
                    ),
                    array(
                        'x'      => 1251,
                        'y'      => 0,
                        'width'  => 1250,
                        'height' => 843,
                    ),
                    array(
                        'x'      => 0,
                        'y'      => 843,
                        'width'  => 1250,
                        'height' => 843,
                    ),
                    array(
                        'x'      => 1251,
                        'y'      => 843,
                        'width'  => 1250,
                        'height' => 843,
                    ),
                ),
            ),
            array(
                'id' => '3-2_1x1',
                'title' => __('3:2 1x1 row1: 1', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-2_1x1.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 1686,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 2500,
                        'height' => 1686,
                    ),
                ),
            ),
            array(
                'id' => '3-2_1x2_1_2',
                'title' => __('3:2 1x2 row1: 1 col, row2: 1col', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-2_1x2_1_2.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 1686,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 1250,
                        'height' => 843,
                    ),
                    array(
                        'x'      => 1251,
                        'y'      => 0,
                        'width'  => 1250,
                        'height' => 843,
                    ),
                ),
            ),
            array(
                'id' => '3-2_2x1_2',
                'title' => __('3:2 2x1 row1: 2 col', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-2_2x1_2.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 1686,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 1250,
                        'height' => 1686,
                    ),
                    array(
                        'x'      => 1251,
                        'y'      => 0,
                        'width'  => 1250,
                        'height' => 1686,
                    ),
                ),
            ),
            array(
                'id' => '3-2_3x1_2_2-1',
                'title' => __('3:2 3x1 row1: 2 col, 2:1', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-2_3x1_2_2-1.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 1686,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 1666, // 幅を調整
                        'height' => 1686,
                    ),
                    array(
                        'x'      => 1667,
                        'y'      => 0,
                        'width'  => 833, // 幅を調整
                        'height' => 1686,
                    ),
                ),
            ),
            array(
                'id' => '3-2_3x1_2_1-2',
                'title' => __('3:2 3x1 row1: 2 col, 1:2', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-2_3x1_2_1-2.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 1686,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 833, // 幅を調整
                        'height' => 1686,
                    ),
                    array(
                        'x'      => 834,
                        'y'      => 0,
                        'width'  => 1666, // 幅を調整
                        'height' => 1686,
                    ),
                ),
            ),
            array(
                'id' => '3-2_3x2_2_2-1_2_2-1',
                'title' => __('3:2 3x2 row1: 2 col (2:1), row2: 2col (2:1)', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-2_3x2_2_2-1_2_2-1.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 1686,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 1666, // 左の2／3を一つのブロック
                        'height' => 1686,
                    ),
                    array(
                        'x'      => 1667,
                        'y'      => 0,
                        'width'  => 833, // 右の1／3,上部のブロック
                        'height' => 843,
                    ),
                    array(
                        'x'      => 1667,
                        'y'      => 843,
                        'width'  => 833, // 右の1／3,下部のブロック
                        'height' => 843,
                    ),
                ),
            ),
            array(
                'id' => '3-2_3x2_1_3',
                'title' => __('3:2 3x2 row1: 1 col , row2: 3 col', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-2_3x2_1_3.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 1686,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 2500, // 上は3つぶち抜き
                        'height' => 843,
                    ),
                    array(
                        'x'      => 0,
                        'y'      => 843,
                        'width'  => 833, // 下は3つに分割
                        'height' => 843,
                    ),
                    array(
                        'x'      => 834,
                        'y'      => 843,
                        'width'  => 833, // 下は3つに分割
                        'height' => 843,
                    ),
                    array(
                        'x'      => 1667,
                        'y'      => 843,
                        'width'  => 833, // 下は3つに分割
                        'height' => 843,
                    ),
                ),
            ),
            array(
                'id' => '3-2_3x2_3_1',
                'title' => __('3:2 3x2 row1: 3 col, row2: 1 col', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-2_3x2_3_1.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 1686,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 833, // 上は3つに分割
                        'height' => 843,
                    ),
                    array(
                        'x'      => 834,
                        'y'      => 0,
                        'width'  => 833, // 上は3つに分割
                        'height' => 843,
                    ),
                    array(
                        'x'      => 1667,
                        'y'      => 0,
                        'width'  => 833, // 上は3つに分割
                        'height' => 843,
                    ),
                    array(
                        'x'      => 0,
                        'y'      => 843,
                        'width'  => 2500, // 下は3つぶち抜き
                        'height' => 843,
                    ),
                ),
            ),
            array(
                'id' => '3-2_2x2_1_2',
                'title' => __('3:2 2x2 row1: 1 col, row2: 2 col', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-2_2x2_1_2.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 1686,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 2500, // 上は2つぶち抜き
                        'height' => 843,
                    ),
                    array(
                        'x'      => 0,
                        'y'      => 843,
                        'width'  => 1250, // 下は2つに分割
                        'height' => 843,
                    ),
                    array(
                        'x'      => 1251,
                        'y'      => 843,
                        'width'  => 1250, // 下は2つに分割
                        'height' => 843,
                    ),
                ),
            ),
            array(
                'id' => '3-2_2x2_2_1',
                'title' => __('3:2 2x2 row1: 2 col, row2: 1 col', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-2_2x2_2_1.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 1686,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 1250, // 上は2つに分割
                        'height' => 843,
                    ),
                    array(
                        'x'      => 1251,
                        'y'      => 0,
                        'width'  => 1250, // 上は2つに分割
                        'height' => 843,
                    ),
                    array(
                        'x'      => 0,
                        'y'      => 843,
                        'width'  => 2500, // 下は2つぶち抜き
                        'height' => 843,
                    ),
                ),
            ),
            array(
                'id' => '3-1_3x1_3',
                'title' => __('3:1 3x1 row1: 3 col', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-1_3x1_3.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 843,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 833,
                        'height' => 843,
                    ),
                    array(
                        'x'      => 834,
                        'y'      => 0,
                        'width'  => 833,
                        'height' => 843,
                    ),
                    array(
                        'x'      => 1667,
                        'y'      => 0,
                        'width'  => 833,
                        'height' => 843,
                    ),
                ),
            ),
            array(
                'id' => '3-1_3x1_2_1-2',
                'title' => __('3:1 3x1 row1: 2 col (1:2)', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-1_3x1_2_1-2.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 843,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 833, // 1:2の比率
                        'height' => 843,
                    ),
                    array(
                        'x'      => 834,
                        'y'      => 0,
                        'width'  => 1666, // 1:2の比率
                        'height' => 843,
                    ),
                ),
            ),
            array(
                'id' => '3-1_3x1_2_2-1',
                'title' => __('3:1 3x1 row1: 2 col (2:1)', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-1_3x1_2_2-1.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 843,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 1666, // 2:1の比率
                        'height' => 843,
                    ),
                    array(
                        'x'      => 1667,
                        'y'      => 0,
                        'width'  => 833, // 2:1の比率
                        'height' => 843,
                    ),
                ),
            ),
            array(
                'id' => '3-1_2x1_2',
                'title' => __('3:1 2x1 row1: 2 col', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-1_2x1_2.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 843,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 1250,
                        'height' => 843,
                    ),
                    array(
                        'x'      => 1251,
                        'y'      => 0,
                        'width'  => 1250,
                        'height' => 843,
                    ),
                ),
            ),
            array(
                'id' => '3-1_1x1_1',
                'title' => __('3:1 1x1 row1: 1 col', lineconnect::PLUGIN_NAME),
                'image' => lineconnect::plugins_url('assets/richmenu/3-1_1x1_1.png'),
                'size' => array(
                    'width'  => 2500,
                    'height' => 843,
                ),
                'bounds' => array(
                    array(
                        'x'      => 0,
                        'y'      => 0,
                        'width'  => 2500,
                        'height' => 843,
                    ),
                ),
            ),
        );
    }

    static function get_template_defalut_data() {
        return array(
            'richMenuId' => '',
            'size' => array(),
            'selected' => true,
            'name'     => __('New richmenu', lineconnect::PLUGIN_NAME),
            'chatBarText' => __('MENU', lineconnect::PLUGIN_NAME),
            'areas'    => array(),
        );
    }

    static function get_richmenu_schema() {
        return array(
            'type'       => 'object',
            'properties' => array(
                'richMenuId' => array(
                    'type'  => 'string',
                    'title' => __('Rich menu ID', lineconnect::PLUGIN_NAME),
                    'description' => __('Unique identifier for the rich menu. Max character limit: 100', lineconnect::PLUGIN_NAME),
                    'maxLength' => 100,
                ),
                'name'     => array(
                    'type'      => 'string',
                    'title'     => __('Name', lineconnect::PLUGIN_NAME),
                    'description' => __('This value can be used to help manage your rich menus and is not displayed to users.', lineconnect::PLUGIN_NAME),
                    'maxLength' => 300,
                ),
                'size' => array(
                    'type'       => 'object',
                    'title'      => __('Size', lineconnect::PLUGIN_NAME),
                    'description' => __('The width and height of the rich menu displayed in the chat. The aspect ratio (width / height) must be at least 1.45.', lineconnect::PLUGIN_NAME),
                    'properties' => array(
                        'width'  => array(
                            'type'  => 'integer',
                            'title' => __('Width', lineconnect::PLUGIN_NAME),
                            'description' => __('Width of the rich menu. Must be between 800px and 2500px.', lineconnect::PLUGIN_NAME),
                            'minimum' => 800,
                            'maximum' => 2500,
                        ),
                        'height' => array(
                            'type'  => 'integer',
                            'title' => __('Height', lineconnect::PLUGIN_NAME),
                            'description' => __('Height of the rich menu. Must be at least 250px.', lineconnect::PLUGIN_NAME),
                            'minimum' => 250,
                        ),
                    ),
                    'required'   => array(
                        'width',
                        'height',
                    ),
                ),
                'areas'    => array(
                    'type'  => 'array',
                    'title' => __('Tap Areas', lineconnect::PLUGIN_NAME),
                    'items' => array(
                        'type'       => 'object',
                        'title'      => __('Area', lineconnect::PLUGIN_NAME),
                        'properties' => array(
                            'bounds' => array(
                                'type'       => 'object',
                                'title'      => __('Bounds', lineconnect::PLUGIN_NAME),
                                'properties' => array(
                                    'x'      => array(
                                        'type'  => 'integer',
                                        'title' => __('X', lineconnect::PLUGIN_NAME),
                                        'description' => __('Horizontal position of the tappable area’s top-left corner (min: 0).', lineconnect::PLUGIN_NAME),
                                        'minimum' => 0,
                                    ),
                                    'y'      => array(
                                        'type'  => 'integer',
                                        'title' => __('Y', lineconnect::PLUGIN_NAME),
                                        'description' => __('Vertical position of the tappable area’s top-left corner (min: 0).', lineconnect::PLUGIN_NAME),
                                        'minimum' => 0,
                                    ),
                                    'width'  => array(
                                        'type'  => 'integer',
                                        'title' => __('Width', lineconnect::PLUGIN_NAME),
                                        'description' => __('Tappable area width (max: 2500).', lineconnect::PLUGIN_NAME),
                                        'maximum' => 2500,
                                    ),
                                    'height' => array(
                                        'type'  => 'integer',
                                        'title' => __('Height', lineconnect::PLUGIN_NAME),
                                        'description' => __('Tappable area height (max: 1686).', lineconnect::PLUGIN_NAME),
                                        'maximum' => 1686,
                                    ),
                                ),
                                'required'   => array(
                                    'x',
                                    'y',
                                    'width',
                                    'height',
                                ),
                            ),
                            'action' => array(
                                '$ref' => '#/definitions/action',
                            ),
                        ),
                    ),
                    'maxItems' => 20,
                ),
                'selected' => array(
                    'type'    => 'boolean',
                    'title'   => __('Display the rich menu by default', lineconnect::PLUGIN_NAME),
                    'default' => false,
                ),
                'chatBarText' => array(
                    'type'      => 'string',
                    'title'     => __('Chat bar text', lineconnect::PLUGIN_NAME),
                    'description' => __('Text displayed in the chat bar. Max character limit: 14', lineconnect::PLUGIN_NAME),
                    'maxLength' => 14,
                ),
            ),
            'required'   => array(
                'size',
                'name',
                'chatBarText',
                'areas',
            ),
            'definitions' => array(
                'action' => MessageSchema::get_action_object_schema(),
            ),
        );
    }

    static function get_richmenu_uischema() {
        return array(
            'ui:submitButtonOptions' => array(
                'norender' => true,
            ),
            'richMenuId' => array(
                'ui:widget' => 'hidden',
            ),
            'size' => array(
                'width' => array(
                    'ui:widget' => 'updown',
                ),
                'height' => array(
                    'ui:widget' => 'updown',
                ),
            ),
            'areas' => array(
                'items' => array(
                    'ui:order' => array('bounds', 'action',),
                ),
                'ui:options' => array(
                    'addText' =>  __('Add tap areas', lineconnect::PLUGIN_NAME),
                    'copyable' => true,
                ),
            ),
        );
    }
}
