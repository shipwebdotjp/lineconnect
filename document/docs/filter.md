# Filter hooks
Filter hooks are provided to add functionality or make changes to settings in functions.php of other plugins or themes.

## slc_filter_actions
This filter hook is used to register your own new actions in addition to the pre-defined actions.  
By using this hook, developers can define their own actions and use them in LINE Connect.  

### Arguments
- `$actions`: (array) an associative array whose keys are the action function names and whose values are the action details array. New actions are registered by returning them after they have been added to the array.

### Example
Here is an example of adding a `get_the_weather` action that returns the weather forecast.
```php
function my_filter_actions($actions) {
    $actions['get_the_weather'] = array(
        'title' => 'Get Weather Forecast',
        'description' => 'Returns a weather forecast for a given location',
        'parameters' => array(
            array(
                'type' => 'string',
                'name' => 'location',
                'description' => 'Location',
                'required' => true,
            ),
        ),
        'namespace' => 'LineConnectDemo',
        'role' => 'any',
    );
    return $actions;
}
add_filter('slc_filter_actions', 'my_filter_actions');
```
### Structure of the action array
#### Keys
The key is the name of the function that executes the action.

#### Value
The data about the action in an associative array format.

- `title`: (string) Title of the action. User-friendly name.
- `description`: (string) Description of the action. Describes what the action does.
- `parameters`: (array) An array of parameters. The elements of the array, in order, are mapped as arguments to the function. (The first element of the array is the first argument of the function, the second element is the second argument, and so on). Each parameter has the following structure.
  - `type`: (string) The data type of the parameter (e.g., 'integer', 'string', 'object', 'array', 'slc_message', 'slc_channel').  
  'slc_message' displays a drop-down to select the LC message, the value is the post ID.  
  'slc_channel' will display a drop-down to select the channel, the value is the secret first 4-character.
  - `name`: (string) The name of the parameter.
  - `description`: (string) The parameter description.
  - `required`: (boolean) Whether the parameter is required or not.
- `namespace`: (string) The namespace to which the action belongs. For example, a class name.
- `role`: (string) The user role that can execute the action, applicable when called from a Chat GPT with Function Calling. Ignored if called from a trigger. 'any' means all users.

### Example Usage
The following is an example code that adds an action to get the weather forecast using the `slc_filter_actions` filter hook.

```php title="lcdemo.php"
class LineConnectDemo {
    static function instance() {
        return new self();
    }
    function __construct() {
        add_filter('slc_filter_actions', array($this, 'slc_filter_actions'));
    }
    function slc_filter_actions($actions) {
    $actions['get_the_weather'] = array(
        'title' => 'Get Weather Forecast',
        'description' => 'Returns a weather forecast for a given location',
        'parameters' => array(
            array(
                'type' => 'string',
                'name' => 'location',
                'description' => 'Location',
                'required' => true,
            ),
        ),
        'namespace' => 'LineConnectDemo',
        'role' => 'any',
    );
        return $actions;
    }
    function get_the_weather($location){
        $weather = wp_remote_get('https://api.openweathermap.org/data/2.5/weather?q='.$location.'&appid=YOUR_API_KEY');
        return json_decode($weather['body'], true);
    }
}

$GLOBALS['LineConnectDemo'] = new LineConnectDemo();
```
#### When using with AI response
Please enable “Auto Answer by AI” and “Function Calling” in the settings, and check “Get Weather Forecast” in “Function to use”.  
Then try sending “Tell me the weather forecast for London".

#### When using with a trigger
1. specify an appropriate trigger
2. set “Get weather forecast” in action-1, and enter London or something similar in location. 
3. In action-2, set “Get LINE text message” and check “Send return value as LINE message”.
4. enter `{{$.return.1.weather.0.description}}` in the body of parameters.


# Filter Hooks for Post Notifications

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
