<?php

namespace Shipweb\LineConnect\Admin\Setting;

use Shipweb\LineConnect\Core\LineConnect;

class Constants {

    public static function get_settings_option() {
        return apply_filters(
            lineconnect::FILTER_PREFIX . 'settings_option',
            array(
                'channel' => array(
                    'prefix' => '1',
                    'name'   => __('Channel', lineconnect::PLUGIN_NAME),
                    'fields' => array(),
                ),
                'connect' => array(
                    'prefix' => '2',
                    'name'   => __('Link', lineconnect::PLUGIN_NAME),
                    'fields' => array(
                        'login_page_url'        => array(
                            'type'     => 'text',
                            'label'    => __('Login page URL', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'default'  => 'wp-login.php',
                            'hint'     => __('Enter the URL of the login page as a path relative to the site URL.', lineconnect::PLUGIN_NAME),
                        ),
                        'enable_link_autostart' => array(
                            'type'     => 'select',
                            'label'    => __('Automatically initiate linkage', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'list'     => array(
                                'off' => __('Disabled', lineconnect::PLUGIN_NAME),
                                'on'  => __('Enabled', lineconnect::PLUGIN_NAME),
                            ),
                            'default'  => 'on',
                            'hint'     => __('This setting determines whether or not to automatically initiate linkage When user add an official account as a friend.', lineconnect::PLUGIN_NAME),
                        ),
                        'link_start_keyword'    => array(
                            'type'     => 'text',
                            'label'    => __('Account link/unlink start keywords', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'default'  => __('Account link', lineconnect::PLUGIN_NAME),
                        ),
                        'link_start_title'      => array(
                            'type'     => 'text',
                            'label'    => __('Message title for account linkage initiation', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'default'  => __('Start account linkage', lineconnect::PLUGIN_NAME),
                        ),
                        'link_start_body'       => array(
                            'type'     => 'text',
                            'label'    => __('Message body for account linkage initiation', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'size'     => 60,
                            'default'  => __('Start the linkage. Please login at the link.', lineconnect::PLUGIN_NAME),
                        ),
                        'link_start_button'     => array(
                            'type'     => 'text',
                            'label'    => __('Message button label to start account linkage', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'default'  => __('Start linkage', lineconnect::PLUGIN_NAME),
                        ),
                        'link_finish_body'      => array(
                            'type'     => 'text',
                            'label'    => __('Account Linkage Completion Message', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'size'     => 60,
                            'default'  => __('Account linkage completed.', lineconnect::PLUGIN_NAME),
                        ),
                        'link_failed_body'      => array(
                            'type'     => 'text',
                            'label'    => __('Account Linkage Failure Messages', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'size'     => 60,
                            'default'  => __('Account linkage failed.', lineconnect::PLUGIN_NAME),
                        ),
                        'unlink_start_title'    => array(
                            'type'     => 'text',
                            'label'    => __('Message title for account unlinking initiation', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'default'  => __('Unlink account', lineconnect::PLUGIN_NAME),
                        ),
                        'unlink_start_body'     => array(
                            'type'     => 'text',
                            'label'    => __('Message body for account unlinking initiation', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'size'     => 60,
                            'default'  => __('Would you like to unlink your account?', lineconnect::PLUGIN_NAME),
                        ),
                        'unlink_start_button'   => array(
                            'type'     => 'text',
                            'label'    => __('Message button label to start account unlinking', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'default'  => __('Unlink account', lineconnect::PLUGIN_NAME),
                        ),
                        'unlink_finish_body'    => array(
                            'type'     => 'text',
                            'label'    => __('Account Unlinking Completion Message', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'size'     => 60,
                            'default'  => __('Account linkage has been successfully unlinked.', lineconnect::PLUGIN_NAME),
                        ),
                        'unlink_failed_body'    => array(
                            'type'     => 'text',
                            'label'    => __('Account Unlinking Failure Message', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'size'     => 60,
                            'default'  => __('Failed to unlink the account.', lineconnect::PLUGIN_NAME),
                        ),
                    ),
                ),
                'publish' => array(
                    'prefix' => '3',
                    'name'   => __('Update Notification', lineconnect::PLUGIN_NAME),
                    'fields' => array(
                        'send_post_types'       => array(
                            'type'     => 'multiselect',
                            'label'    => __('Post types', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'list'     => array(
                                'post' => __('Post', lineconnect::PLUGIN_NAME),
                                'page' => __('Page', lineconnect::PLUGIN_NAME),
                            ),
                            'default'  => array('post'),
                            'isMulti'  => true,
                            'hint'     => __('The post type to be notified. The Send LINE checkbox will appear on the edit screen of the selected post type.', lineconnect::PLUGIN_NAME),
                        ),
                        'default_send_checkbox' => array(
                            'type'     => 'select',
                            'label'    => __('Default value of "Send update notification" checkbox', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'list'     => array(
                                'on'  => __('Checked', lineconnect::PLUGIN_NAME),
                                'off' => __('Unchecked', lineconnect::PLUGIN_NAME),
                                'new' => __('Unchecked if published', lineconnect::PLUGIN_NAME),
                            ),
                            'default'  => 'new',
                            'hint'     => __('Default value setting for the "Send update notification" check box when editing an article.', lineconnect::PLUGIN_NAME),
                        ),
                        'default_send_template' => array(
                            'type'     => 'select',
                            'label'    => __('Default template of notification message.', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'list'     => array(
                                0  => __('Default template', lineconnect::PLUGIN_NAME),
                            ),
                            'default'  => 0,
                            'hint'     => __('Default value setting for the "Message template" select box when editing an article.', lineconnect::PLUGIN_NAME),
                        ),
                        'more_label'            => array(
                            'type'     => 'text',
                            'label'    => __('"More" link label', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'default'  => __('Read more', lineconnect::PLUGIN_NAME),
                        ),
                        'send_new_comment'      => array(
                            'type'     => 'checkbox',
                            'label'    => __('Send notification to posters when comments are received', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'default'  => false,
                            'hint'     => __('This setting determines whether or not to notify the poster of an article when there is a comment on the article.', lineconnect::PLUGIN_NAME),
                        ),
                        'comment_read_label'    => array(
                            'type'     => 'text',
                            'label'    => __('"Read comment" link label', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'default'  => __('Read comment', lineconnect::PLUGIN_NAME),
                        ),
                    ),
                ),
                'style'   => array(
                    'prefix' => '4',
                    'name'   => __('Style', lineconnect::PLUGIN_NAME),
                    'fields' => array(
                        'image_aspectmode'             => array(
                            'type'     => 'select',
                            'label'    => __('Image fit mode', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'list'     => array(
                                'cover' => __('cover', lineconnect::PLUGIN_NAME),
                                'fit'   => __('contain', lineconnect::PLUGIN_NAME),
                            ),
                            'default'  => 'cover',
                            'hint'     => __('cover: The replaced content is sized to maintain its aspect ratio while filling the image area. If the image\'s aspect ratio does not match the aspect ratio of its area, then the image will be clipped to fit. \n contain: The replaced image is scaled to maintain its aspect ratio while fitting within the image area. The entire image is made to fill the box, while preserving its aspect ratio, so the image will be "letterboxed" if its aspect ratio does not match the aspect ratio of the area.', lineconnect::PLUGIN_NAME),
                        ),
                        'image_aspectrate'             => array(
                            'type'     => 'text',
                            'label'    => __('Image area aspect ratio', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'default'  => '2:1',
                            'regex'    => '/^[1-9]+[0-9]*:[1-9]+[0-9]*$/',
                            'hint'     => __('The aspect ratio of the image area. The height cannot be greater than three times the width.', lineconnect::PLUGIN_NAME),
                        ),
                        'title_backgraound_color'      => array(
                            'type'     => 'color',
                            'label'    => __('Background color of the message', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'default'  => '#FFFFFF',
                            'hint'     => __('The background color of the notification message.', lineconnect::PLUGIN_NAME),
                        ),
                        'title_text_color'             => array(
                            'type'     => 'color',
                            'label'    => __('Title text color', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'default'  => '#000000',
                            'hint'     => __('The title text color of the notification message.', lineconnect::PLUGIN_NAME),
                        ),
                        'body_text_color'              => array(
                            'type'     => 'color',
                            'label'    => __('Body text color', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'default'  => '#000000',
                            'hint'     => __('The body text color of the notification message.', lineconnect::PLUGIN_NAME),
                        ),
                        'link_button_style'            => array(
                            'type'     => 'select',
                            'label'    => __('Link style', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'list'     => array(
                                'button' => __('Button', lineconnect::PLUGIN_NAME),
                                'link'   => __('HTML Link', lineconnect::PLUGIN_NAME),
                            ),
                            'default'  => 'link',
                            'hint'     => __('Button: button style. Link: HTML link style', lineconnect::PLUGIN_NAME),
                        ),
                        'link_text_color'              => array(
                            'type'     => 'color',
                            'label'    => __('Link text color', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'default'  => '#1e90ff',
                            'hint'     => __('The link text color of the notification message.', lineconnect::PLUGIN_NAME),
                        ),
                        'link_button_background_color' => array(
                            'type'     => 'color',
                            'label'    => __('Link button background color', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'default'  => '#00ff00',
                            'hint'     => __('The link button background color of the notification message.', lineconnect::PLUGIN_NAME),
                        ),
                        'title_rows'                   => array(
                            'type'     => 'spinner',
                            'label'    => __('Max title lines', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'default'  => 3,
                            'hint'     => __('This is the setting for the maximum number of lines of title to be displayed in the notification message.', lineconnect::PLUGIN_NAME),
                        ),
                        'body_rows'                    => array(
                            'type'     => 'spinner',
                            'label'    => __('Max body lines', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'default'  => 5,
                            'hint'     => __('This is the setting for the maximum number of lines of text to be displayed in the notification message. Apart from this, it can be truncated to a maximum of 500 characters.', lineconnect::PLUGIN_NAME),
                        ),
                    ),
                ),
                'chat'    => array(
                    'prefix' => '5',
                    'name'   => __('AI Chat', lineconnect::PLUGIN_NAME),
                    'fields' => array(
                        'enableChatbot'            => array(
                            'type'     => 'select',
                            'label'    => __('Auto response by AI', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'list'     => array(
                                'off' => __('Disabled', lineconnect::PLUGIN_NAME),
                                'on'  => __('Enabled', lineconnect::PLUGIN_NAME),
                            ),
                            'default'  => 'off',
                            'hint'     => __('This setting determines whether or not to use AI auto-response for messages sent to official line account.', lineconnect::PLUGIN_NAME),
                        ),
                        'openai_endpoint' => array(
                            'type'     => 'text',
                            'label'    => __('OpenAI API Endpoint', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'default'  => 'https://api.openai.com/v1/chat/completions',
                            'size'     => 60,
                            'hint'     => __('Enter your OpenAI (or Compatible) API Endpoint.', lineconnect::PLUGIN_NAME),
                        ),
                        'openai_secret'            => array(
                            'type'     => 'text',
                            'label'    => __('OpenAI API Key', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'default'  => '',
                            'size'     => 60,
                            'hint'     => __('Enter your OpenAI (or Compatible) API Key.', lineconnect::PLUGIN_NAME),
                        ),
                        'openai_model'             => array(
                            'type'     => 'text',
                            'label'    => __('Model', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            /*
                        'list'     => array(
                            'gpt-3.5-turbo'     => 'GPT-3.5 turbo',
                            'gpt-3.5-turbo-16k' => 'GPT-3.5 turbo 16k(Legacy)',
                            'gpt-4'             => 'GPT-4',
                            'gpt-4-32k'         => 'GPT-4 32k',
                            'gpt-4-turbo-preview'         => 'GPT-4 turbo',
                            'gpt-4o'             => 'GPT-4o',
                            'gpt-4o-mini'             => 'GPT-4o mini',
                            'gpt-4.1'			 => 'GPT-4.1',
                            'gpt-4.1-mini'			 => 'GPT-4.1 mini',
                            'gpt-4.1-nano'			 => 'GPT-4.1 nano',
                            'o3'			 => 'o3',
                            'o4-mini'			 => 'o4-mini',
                        ),
                        */
                            'default'  => 'gpt-4o-mini',
                            'size'     => 60,
                            'hint'     => __('This is a setting for which model to use. Such as gpt-4o-mini', lineconnect::PLUGIN_NAME),
                        ),
                        'openai_system'            => array(
                            'type'     => 'textarea',
                            'label'    => __('System prompt', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'default'  => '',
                            'rows'     => 7,
                            'cols'     => 80,
                            'hint'     => __('The initial text or instruction provided to the language model before interacting with it in a conversational manner.', lineconnect::PLUGIN_NAME),
                        ),
                        'openai_function_call'     => array(
                            'type'     => 'select',
                            'label'    => __('Function Calling', lineconnect::PLUGIN_NAME),
                            'required' => true,
                            'list'     => array(
                                'off' => __('Disabled', lineconnect::PLUGIN_NAME),
                                'on'  => __('Enabled', lineconnect::PLUGIN_NAME),
                            ),
                            'default'  => 'off',
                            'hint'     => __('This setting determines whether Function Calling is used or not.', lineconnect::PLUGIN_NAME),
                        ),
                        'openai_enabled_functions' => array(
                            'type'     => 'multiselect',
                            'label'    => __('Functions to use', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'list'     => array(),
                            'default'  => array(),
                            'isMulti'  => true,
                            'hint'     => __('Function to be enabled by Function Calling.', lineconnect::PLUGIN_NAME),
                        ),
                        'openai_context'           => array(
                            'type'     => 'spinner',
                            'label'    => __('Number of context', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'default'  => 3,
                            'regex'    => '/^\d+$/',
                            'hint'     => __('This is a setting for how many conversation histories to use in order to have the AI understand the context and respond.', lineconnect::PLUGIN_NAME),
                        ),
                        'openai_max_tokens'        => array(
                            'type'     => 'spinner',
                            'label'    => __('Max tokens', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'default'  => -1,
                            'regex'    => '/^[+-]?\d+$/',
                            'hint'     => __('Maximum number of tokens to use. -1 is the upper limit of the model.', lineconnect::PLUGIN_NAME),
                        ),
                        'openai_temperature'       => array(
                            'type'     => 'range',
                            'label'    => __('Temperature', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'default'  => 1,
                            'min'      => 0,
                            'max'      => 1,
                            'step'     => 0.1,
                            'hint'     => __('This is the temperature parameter. The higher the value, the more diverse words are likely to be selected. Between 0 and 1.', lineconnect::PLUGIN_NAME),
                        ),
                        'openai_limit_normal'      => array(
                            'type'     => 'spinner',
                            'label'    => __('Limit for unlinked users', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'default'  => 3,
                            'regex'    => '/^[+-]?\d+$/',
                            'hint'     => __('Number of times an unlinked user can use it per day. -1 is unlimited.', lineconnect::PLUGIN_NAME),
                        ),
                        'openai_limit_linked'      => array(
                            'type'     => 'spinner',
                            'label'    => __('Limit for linked users', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'default'  => 5,
                            'regex'    => '/^[+-]?\d+$/',
                            'hint'     => __('Number of times an linked user can use it per day. -1 is unlimited.', lineconnect::PLUGIN_NAME),
                        ),
                        'openai_limit_message'     => array(
                            'type'     => 'textarea',
                            'label'    => __('Limit message', lineconnect::PLUGIN_NAME),
                            'required' => false,
                            'rows'     => 5,
                            'cols'     => 60,
                            'default'  => __('The number of times you can use it in a day (%limit% times) has been exceeded. Please try again after the date changes.', lineconnect::PLUGIN_NAME),
                            'hint'     => __('This message is displayed when the number of times the limit can be used in a day is exceeded. The %limit% is replaced by the limit number of times.', lineconnect::PLUGIN_NAME),
                        ),
                    ),
                ),
                'data'    => array(
                    'prefix' => '6',
                    'name'   => __('Data', lineconnect::PLUGIN_NAME),
                    'fields' => array(),
                ),
            )
        );
    }

    /**
     * Return channnel options
     * @return array channel options
     */
    public static function get_channel_options() {
        $channnel_option = apply_filters(
            lineconnect::FILTER_PREFIX . 'channnel_option',
            array(
                'name'                 => __('Channel name', lineconnect::PLUGIN_NAME),
                'channel-access-token' => __('Channel access token', lineconnect::PLUGIN_NAME),
                'channel-secret'       => __('Channel secret', lineconnect::PLUGIN_NAME),
                'role'                 => __('Default target role', lineconnect::PLUGIN_NAME),
                'linked-richmenu'      => __('Rich menu ID for linked users', lineconnect::PLUGIN_NAME),
                'unlinked-richmenu'    => __('Rich menu ID for unlinked users', lineconnect::PLUGIN_NAME),
            )
        );
        foreach (wp_roles()->roles as $role_name => $role) {
            $channnel_option[$role_name . '-richmenu'] = sprintf(__('Rich menu ID for %s.', lineconnect::PLUGIN_NAME), translate_user_role($role['name']));
        }
        return $channnel_option;
    }

    public static function get_management_command() {
        return array(
            'clear_richmenu_cache' => array(
                'type' => 'button',
                'label' => __('Clear the rich menu cache', lineconnect::PLUGIN_NAME),
                'description' => __('Clear the cache of the rich menu list.', lineconnect::PLUGIN_NAME),
                'class' => 'button-secondary',
                'confirm' => __('Are you sure you want to clear the rich menu cache?', lineconnect::PLUGIN_NAME),
            ),
            'delete_all_data'      => array(
                'type' => 'button',
                'label' => __('Delete all plugin data', lineconnect::PLUGIN_NAME),
                'description' => __('Delete all plugin data.', lineconnect::PLUGIN_NAME),
                'class' => 'button-danger',
                'confirm' => __('Are you sure you want to delete all plugin data?', lineconnect::PLUGIN_NAME),
            ),
        );
    }
}
