# Message sending filters
This group contains hooks that adjust notification message template arguments and the final message object before it is sent.

## Included hooks

- [slc_filter_notification_message_args](#slc_filter_notification_message_args): Change the arguments used to build notification message templates.
- [slc_filter_notification_message](#slc_filter_notification_message): Change the final notification message object before sending.

## slc_filter_notification_message_args
This filter is used to modify the parameters for creating message templates. You can change the arguments passed to the template.

### Arguments

- `$args`: (array) An associative array of arguments passed to the message template.
- `$template`: (int) The ID of the template to use.

### Example

This example includes the featured image in the message template.  
Simply including `{{post_permalink}}` in the message can cause errors if there is no featured image, so this prevents that.

```php
function my_filter_notification_message_args($args, $template) {
    // If post_thumbnail is empty or does not start with https, set a placeholder image URL
    if (empty($args['post_thumbnail']) || substr($args['post_thumbnail'], 0, 5) != "https") {
        $args['post_thumbnail'] = 'https://placehold.jp/3d4070/ffffff/300x200.png?text=No%20Image';
    }
    return $args;
}
add_filter('slc_filter_notification_message_args', 'my_filter_notification_message_args', 10, 2);
```

## slc_filter_notification_message
This filter is used to modify the created notification message object before sending.

### Arguments

- `$buildMessage`: (LINE\LINEBot\MessageBuilder) The generated message object.
- `$args`: (array) An associative array of arguments passed to the message template.
- `$template`: (int) The ID of the template to use.

### Example

This example sets the sender name and icon for the update notification message (using SenderMessageBuilder).

```php
function my_filter_notification_message($buildMessage, $args, $template) {
    // Change the sender name and icon when sending an update notification
    $SenderMessageBuilder = new \LINE\LINEBot\SenderBuilder\SenderMessageBuilder("author_name", "https://placehold.jp/28c832/ffffff/200x200.png?text=icon");
    $buildMessage->setSender($SenderMessageBuilder);
    return $buildMessage;
}
add_filter('slc_filter_notification_message', 'my_filter_notification_message', 10, 3);
```
