# Action hooks
Several action hooks are provided to call for sending LINE messages from other plug-ins. 
## send_message_to_wpuser($channel, $wp_user_id, $message)
Send a LINE message to the linked Wordpress users. 
### Usage
```
//From default channel（First channel）
//To User ID 2
do_action('send_message_to_wpuser', null, 2, 'Message from Wordpress');

//Get channel information by specifying the channel with the first 4 characters of the channel secret 
do_action('send_message_to_wpuser', lineconnect::get_channel('1fa8'), 3, 'Message from Wordpress');

//When passing the access token and secret of the channel to be sent as an array
$channel = array(
    'channel-access-token' => 'Channel access token',
    'channel-secret' => 'Channel Secret'
);
do_action('send_message_to_wpuser', $channel, 3, 'Message from Wordpress');

//Create ImageMessageBuilder using LINE BOT SDK and send images 
require_once(plugin_dir_path(__FILE__).'../lineconnect/vendor/autoload.php');
$originalContentUrl = "https://example.com/img.jpg";
$previewImageUrl = "https://example.com/img.jpg";
$imageMessageBuilder = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($originalContentUrl, $previewImageUrl);
do_action('send_message_to_wpuser', null, 3, $imageMessageBuilder);
```
### Parameters
**$channel (array|null)**  
Array of channel information with channel-access-token, channel-secret, or null (use default channel)
※The default channel is the registered channel if there is one, or the first channel if there are multiple registered channels.  
**$wp_user_id (int)**   
 Wordpress ID of the user sending the message (Note: this is not a LINE user ID)  
**$message (string|LINE\LINEBot\MessageBuilder)**  
Message to be sent. If a string is given, a text message is created and sent; you can also use the LINE BOT SDK to send messages created with MessageBuilder.

## send_message_to_role($channel, $role, $message)
Sends LINE messages to linked users by specifying a role. You can also send to all linked users by passing a reserved value to ``$role``.
### Usage
```
//Send a message to Administrator role 
do_action('send_message_to_role', null, array("administrator"), 'Message from Wordpress');

//Send message to all linked users 
do_action('send_message_to_role', null, array("slc_linked"), 'Message from Wordpress');

```
### Parameters
**$channel, $message**  
Same as send_message_to_wpuser  
**$role (array)**   
Array of role slugs to be sent.  
Example: `array("administrator")`  
If ``slc_linked`` is specified, send to all linked users. 

**Note: channel value**  
The default channel is changed by deleting a channel. For example, if there are multiple channels, deleting the first channel will make the second channel the default channel. To be sure, specify the channel information as an array or use the channel information obtained from the first four characters of the secret.
```
$channel = lineconnect::get_channel("the first four characters of the secret");
```