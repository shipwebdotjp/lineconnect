# Post notification filters
This group contains hooks that customize LINE update notifications around post publishing.

## Included hooks

- [slc_filter_publish_postmeta_is_send_line](#slc_filter_publish_postmeta_is_send_line): Change the default LINE notification settings shown in the post editor.
- [slc_filter_send_notification_is_send_line](#slc_filter_send_notification_is_send_line): Change the notification settings submitted when a post is published.

## slc_filter_publish_postmeta_is_send_line
This filter is used to modify the initial values of the form elements in the post edit screen for update notifications: the checkbox for whether to send a notification, the list of target roles, and the message template dropdown. It filters the initial value of the post meta data `is-send-line`.

### Arguments

- `$is_send_line`: (mixed) The value of the LINE sending settings. Usually the value of the post meta data `is-send-line`.
    - `role`: (array) An array of target roles.
    - `template`: (int) The ID of the message template to use.
    - `isSend`: (string) The value of the "Send when scheduling post" checkbox ('ON' or '').
- `$post_ID`: (int) The post ID.

### Example

This example enables the "Send update notification" checkbox by default for specific post types.

```php
function my_filter_publish_postmeta_is_send_line($is_send_line, $post_ID) {
    $post_type = get_post_type($post_ID);
    if ($post_type === 'news') {
        // Settings per channel
        foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
            $is_send_line[$channel['prefix']] = array(
                'role' => array('slc_all'), // Send to all friends
                'template' => 113, // Set the template ID (LC message post ID) for the specific post type
                'isSend' => 'ON', // Check the "Send when scheduling post" checkbox by default
            );
        }
    }
    return $is_send_line;
}
add_filter('slc_filter_publish_postmeta_is_send_line', 'my_filter_publish_postmeta_is_send_line', 10, 2);
```

## slc_filter_send_notification_is_send_line
This filter is used to modify the values of the meta box (whether to send an update notification, target roles, message template) submitted via POST when a post is published.

### Arguments

- `$send_data`: (array) An associative array containing the sending data. It has the following keys:
    - `send_checkbox_value`: (string) The value of the send checkbox ('ON' or '').
    - `roles`: (array) An array of target roles.
    - `template`: (int) The ID of the template to use.
- `$post_ID`: (int) The post ID.
- `$post`: (WP_Post) The post object.

### Example

This example disables sending under certain conditions.

```php
function my_filter_send_notification_is_send_line($send_data, $post_ID, $post) {
    if (true) { // Some condition
        $send_data['send_checkbox_value'] = ''; // Forcefully prevent sending update notifications
    }
    return $send_data;
}
add_filter('slc_filter_send_notification_is_send_line', 'my_filter_send_notification_is_send_line', 10, 3);
```
