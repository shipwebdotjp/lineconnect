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