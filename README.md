# LINE Connect 
LINE Connect is a WordPress plugin with features to help you utilize LINE official accounts with WordPress.  
You can perform various actions such as linking WordPress users with LINE users, notifications of article posts, rich menu tapping, and sending messages triggered by message reception.  

## Features and How to use
See the [LINE Connect Document](https://lc.shipweb.jp/en/)

## Settings


## Bulk message
Instead of sending the article content on LINE when submitting an article, you can send a LINE message with the content of your choice as a simple text message.
### Usage
Open the "Bulk message" page from the LINE Connect menu.  
<img src="https://blog.shipweb.jp/wp-content/uploads/2022/01/lineconnect-ss-10.jpg" width="320">  

#### Channel
Select target channel  

#### Type
Specify which user groups to send to.  
- All: Send to all users who are add as friends, regardless of whether they are linked or not.
- Linked: Send to users who are linked to Wordpress among users who are add as friends.
- Roles: Sends to users who belong to a specific role among the users who have linked with Wordpress.
- Users: Send to each user individually. (How to specify users is described below.)

#### Role  
Select which roles to send to users belonging to.   

#### Message
Enter the contents of the LINE message.

### Specify individual users and send
Since you cannot specify individual users to be sent from the Bulk message page, please specify the target users from the User List page.  

#### To only one user
If the "check box" link in the LINE column takes you to the Bulk message page, the user will be selected.   
<img src="https://blog.shipweb.jp/wp-content/uploads/2023/08/%E3%82%B9%E3%82%AF%E3%83%AA%E3%83%BC%E3%83%B3%E3%82%B7%E3%83%A7%E3%83%83%E3%83%88-2023-08-08-23.02.40.png" width="320">  

#### To multiple users
Check the checkboxes for the target users, select "Send LINE Message" for the batch operation, and click the "Apply" button. 
<img src="https://blog.shipweb.jp/wp-content/uploads/2023/08/lineconnect-ss-bulk-send.png" width="320">  
The LINE chat screen will open with the checked user selected.  
<img src="https://blog.shipweb.jp/wp-content/uploads/2022/01/lineconnect-ss-13.jpg" width="320">  

## Direct message
If a LINE user ID is recorded in the event log, such as a user who has added the official account as a friend or sent a message to the official LINE account within 7 days, you can send a LINE text message to that LINE user.  
By clicking on the “Message” link that appears on mouse-over in the User ID column of the Event Log, you can specify the user to send the message to.

## Trigger
### Trigger type
#### Webhook
Execute an action triggered by a webhook event that matches the conditions.
##### Event type
Types of webhook events such as message/postback/account link/add friend (follow)/block (unfollow).
For more information on event types, please see  <a href='https://developers.line.biz/en/docs/messaging-api/receiving-messages/#webhook-event-types'>Receive messages (webhook) | LINE Developers</a>
##### Keywords
When the event type is “message” and the message type is “text” or the event type is “postback”, the condition of the data received is specified.  
If a text message is received, the data is the message received; if a postback, the data is the contents of the data property.
###### Source: ######
- Keywords: treats data as a string
- Query string: treats data as a query string
###### Match type
If the source is "Keyword", select the matching method from Contains/Equals/Starts with/Ends with/Regular Expression.
If the source is "query string", whether the specified parameter is included in the data when the data query string is parsed (it is considered a match even if other data is included), or whether it is equal(it is not considered a match if other data is included).
##### Source Conditions
These are condition settings to determine whether to trigger based on the source information of the received event.

###### Channel
Specifies the channel that received the Webhook event.

###### Source
Specifies the source as "User", "Group", or "Room".

###### User
Sets conditions to trigger based on the attributes of the user who generated (sent) the Webhook event.
- Link Status: Select from "Any", "Linked", or "Unlinked" as the condition based on the link status with the WordPress user.
- Role: Specify the user's role as a condition.
- LINE User ID: Specify particular LINE user IDs as a condition.

###### Group
Sets conditions based on the LINE group as the origin of the Webhook event.
- LINE Group ID: Specify the originating LINE group ID as a condition.

###### Room
Sets conditions for multi-person chat as the source of the Webhook event.
- LINE Room ID: Specify the multi-person chat room ID as a condition.

###### Keyword/Source condition group
Groups conditions. Multiple conditions can be set so that if all source conditions in a group match (AND) or if any of them match (OR), the entire group is considered a match.
###### Logical Negation
Checking the Not checkbox inverts the judgment of the condition. Specifically, it works as follows:

- If the condition matches, it is assumed to be unmatched.
- If the condition does not match, it is considered a match.
###### operator
Specifies how to handle multiple conditions when there are multiple conditions.
- And: All conditions must be true.
- Or: At least one condition must be true.

##### Schedule
This feature allows actions to be triggered based on specified dates, times, or days of the week.

##### Once
Sets a one-time schedule.
- Date and Time: Specifies the date and time to trigger the action.

##### Repeat
###### Every Hour
Triggers the action at the specified time each hour.

###### Every Day of the Week
Triggers the action on the specified day(s) of the week.
- Way of Calc: Specifies how to calculate the day of the week, either as the nth day of the month or the nth week of the month.
- Day of the week in the month: The meaning varies based on the "Way of Calc" selection.
  - If "Nth Day" is selected, choosing 1 would refer to the first specific weekday of the month.
  - If "Nth Week of the Month" is selected, choosing 1 would refer to the first week of the month on the specified weekday.
- Day: Specifies the day from Sunday to Saturday.
- First Day of the Week: Sets whether the week starts on Sunday or Monday when "Nth Week of the Month" is selected.

###### Every Day
Triggers the action on the specified day. Checking the box for the last day of the month will trigger the action on the last day of each month.

###### Every Week
Triggers the action on the specified week number of the year.

###### Every Month
Triggers the action in the specified month(s).

###### Every Year
Triggers the action in the specified year(s).

###### Start Date
The trigger will not activate if the current time is before the start date. The start date and time serve as the reference point for various schedule types.
Example: If the start date is "2024/05/15 21:38":
- If checked at 0 am for Every Hour, the trigger will execute daily at 00:38.
- If checked on the first Tuesday for Every Week, the trigger will execute at 21:38 on the first Tuesday of the month.
- If checked on the 1st for Every Day, the trigger will execute at 21:38 on the 1st of each month.
- If the week number is set to 2 for Every Week, the trigger will execute at 21:38 on the starting day of the 2nd week (e.g., Wednesday) of the year.
- If checked in August for Every Month, the trigger will execute at 21:38 on 15th August of that year.

###### End Date
The trigger will not activate if the current time is after the end date.

###### Beforehand notice (min)
- Minutes of Advance Notice: 
  Specifies the number of minutes before the target time to trigger the action.
  For example, to trigger the action 24 hours before the last day of each month, check the last day of the month and input "1440" for the minutes of advance notice. This setup ensures the trigger will fire one day before the last day of the month, regardless of the exact last day.

#### Action
This is the action to be executed when the trigger is activated.

The following types of actions are available. It is also possible to add custom actions using filter hooks.
- Actions that retrieve and return information
- Actions that search for information and return it
- Actions that perform operations such as sending messages

Actions are shared with the Function Calling feature used for AI responses.
##### Return Value of Actions
###### Send the return value as a response
If checked, the return value of the action will be sent as a response message to the originator of the Webhook event.

The type of message sent varies depending on the type of return value:
- String: Sent as a LINE text message
- Instance of LINE\LINEBot\MessageBuilder: Sent as a LINE message object
- Others: Sent as a dumped LINE text message

The return value is sent as a response message only when the trigger type is a Webhook event.
If the trigger type is a schedule, checking this will not send the return value as a LINE message.
In that case, you can use the action chain feature to set the return value as an argument for the "Send LINE Message" action to send a LINE message to the desired user.

##### Embedding Variables
If the argument of the action is a string, each variable can be embedded and used.

###### Return Values of Actions
The return values of sequentially executed actions can be used in placeholders within the arguments of subsequent actions.
`{{$.return.action_number}}`
action_number is the action number in the action chain.
If the return value of the target action is an array, you can extract a specific value by adding ".key" to the action number.
Example: Set the "Get Current Date and Time" action to Action-1 (Action Number 1).
Set the "Get LINE Text Message" action to Action-2 (Action Number 2).
In the body of the parameters for Action-2, enter "The current time is {{$.return.1.datetime}}."
This will embed the current time in the message and send it.

###### Webhook Event Data
You can also use data included in received Webhook events.
{{$.webhook.eventObjectProperty}}
Example: To use the text message sent to the official account
{{$.webhook.message.text}}

###### User Data
You can use the data of the user who sent the Webhook event.
`{{$.user.[WPUser object properties]}}`
Example: Using the display name of a user
 If the user is already linked to a WordPress user, you can get the display name of the WordPress user; if not, you can get the display name of the LINE user.
`{{$.user.data.display_name}}`
The profile of the LINE user (profile image URL, etc.) can be used in `{{$.user.profile.[profile property]}}`.
The values that can be used as profile properties are `displayName`, `pictureUrl`, `language` and `statusMessage`.


##### action-chain
If the argument of an action is in the form of an object or a predetermined constant to be selected from, embedding variables is not possible.
In such cases, a chain rule can be added to inject the return value of any action into the arguments of subsequent actions.

###### Destination argument to
How to Specify the Target Action Argument for Injection: `ActionNumber.ArgumentName`
- Action Number: The order of the actions starting from 1
- Argument Name: The name corresponding to the required argument for each action
Example: To specify the message of action-2 as the injected argument: `2.message`.
(Note that it is not necessary to enclose in {{ }})

###### Data
Data to be injected can be strings or embedded variables.

## LC Messages (Message Templates)
You can create various LINE messages in advance as templates and use them when executing LINE message retrieval or sending actions. Up to five messages can be sent in a single transmission.
### Message Types
For more details on message types, refer to [Message Types | LINE Developers](https://developers.line.biz/en/docs/messaging-api/message-types/).

The created messages can be used by calling the “Get LINE Connect Message” action.
Select “Get LINE Connect Message” in the action and select the message you wish to use from the parameter slc_message_id.

By embedding `{{key}}` in the message text, you can insert a value corresponding to the key, such as a name, when the action is executed.

For example, you can create a text message with the content “Thank you for adding {{name}} as a friend! and save it.
Set the event type to “Follow” in the trigger, select “Get LINE Connect Message” in the action, and select the message you have created in the slc_message_id parameter.
Add `args (message replacement argument) and set `name` as the key and `{{$.user.data.display_name}}` as the value (name).
This will cause `{{name}}` in the message text to be replaced by the `user's display name` and sent.

## For Developers
Several action hooks are provided to call for sending LINE messages from other plug-ins. 
### Action hooks
#### send_message_to_wpuser($channel, $wp_user_id, $message)
Send a LINE message to the linked Wordpress users. 
##### Usage
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
##### Parameters
**$channel (array|null)**  
Array of channel information with channel-access-token, channel-secret, or null (use default channel)
※The default channel is the registered channel if there is one, or the first channel if there are multiple registered channels.  
**$wp_user_id (int)**   
 Wordpress ID of the user sending the message (Note: this is not a LINE user ID)  
**$message (string|LINE\LINEBot\MessageBuilder)**  
Message to be sent. If a string is given, a text message is created and sent; you can also use the LINE BOT SDK to send messages created with MessageBuilder.

#### send_message_to_role($channel, $role, $message)
Sends LINE messages to linked users by specifying a role. You can also send to all linked users by passing a reserved value to ``$role``.
##### Usage
```
//Send a message to Administrator role 
do_action('send_message_to_role', null, array("administrator"), 'Message from Wordpress');

//Send message to all linked users 
do_action('send_message_to_role', null, array("slc_linked"), 'Message from Wordpress');

```
##### Parameters
**$channel, $message**  
Same as send_message_to_wpuser  
**$role (array)**   
Array of role slugs to be sent. Example: array("administrator")
If ``slc_linked`` is specified, send to all linked users. 

**Note: channel value**  
The default channel is changed by deleting a channel. For example, if there are multiple channels, deleting the first channel will make the second channel the default channel. To be sure, specify the channel information as an array or use the channel information obtained from the first four characters of the secret.
```
$channel = lineconnect::get_channel("the first four characters of the secret");
```
### Stored Formats for LINE User IDs
The LINE user ID, display name and profile image URL of the linked user are stored in the user meta with the key name ``line``.
#### Metadata Format
```
//Get user info for user ID 3.
$user_meta_line = get_user_meta(3, 'line', true);
var_dump($user_meta_line);

array(1) {
  ["the first four characters of the secret"]=>
  array(3) {
    ["id"]=>
    string(33) "LINE user ID"
    ["displayName"]=>
    string(12) "display name"
    ["pictureUrl"]=>
    string(135) "profile image URL"
  }
}
```
There is an array at the top level whose keys are the first 4 characters of the channel secret. It contains an array whose keys are ``id``,``displayName``,``pictureUrl``.

### How to Specify Channels and Roles from the REST API
If you would like to have LINE Connect send LINE notifications when posting articles from the REST API, please add the following keys and values to the JSON data.
```
  "lc_channels":{
      "the first four characters of the secret":"Role name (if there are multiple roles, separate them with ",")"
  }
```

### Saving the LINE Notification Log
If the STREAM plug-in is installed and enabled, a log of the type of LINE notification (multicast, push message, broadcast) and how many messages were sent will be recorded in Stream Records for later viewing.
### Event Log
Displays the Webhook event logs received. You can check the event type, the user who sent it, and the content of the message.

### Connect with WP LINE Login
If the following conditions are met, when a user is linked via LINE Connect, the corresponding user will also be linked to LINE Login for [WP LINE Login](https://blog.shipweb.jp/wplinelogin/).
* WP LINE Login is installed.
* The "Messaging API Channel Secret" in the LINE Login settings matches the "Channel Secret" of LINE Coonect.

If user unlinked, the LINE Login linking status will also be unlink.

## Customization
Various other customizations are available for a fee. [Contact us](https://blog.shipweb.jp/contact)

# Requires at least
* Wordpress 5.0 or upper

# Author
* ship [blog](https://blog.shipweb.jp/)

# Thanks
* I would like to thank Growniche for developing the wonderful "LINE AUTO POST" plugin.

# License
GPLv3