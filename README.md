# LINE Connect 
LINE Connect is a WordPress plugin that can connect Wordpress users with LINE users.    
It can notify Wordpress post's update to the linked Wordpress users.  
It can register different rich menus depending on whether they are linked or not.    
Forked from [LINE AUTO POST](https://s-page.biz/line-auto-post/#home) by Growniche.  
The official site is here -> [LINE Connect, a WordPress plugin to connect with LINE](https://blog.shipweb.jp/lineconnect/) (Japanese)    
Demo site here -> [SHIP LAB](https://gpt.shipweb.jp/) (Japanese)  

## Features 
### Linking function with Wordpress users
* Using LINE Messaging API to link friends of LINE official accounts with accounts of Wordpress users
* Rich menu IDs can be set separately for users who have already linked and those who have not
### Update Notification
* Send article update notifications via LINE to linked users and users in specific roles.
* Notification messages are sent in a card-style layout including an futured image.
* When posts published, updated or future post is published can send notifications.
* LINE notifications can be sent to posters when someone comment on thier articles.
### Bulk message
* Arbitrary LINE text messages can be sent to individual users
* Save LINE messages and use them for messages sent by actions
### Direct Messages
* Text messages can be sent to LINE users who have an event log, even if they are not connected to LINE.
### Connectivity with Official LINE account
* Automatic response to messages using Chat GPT API (You can use your LINE official account as a AI chatbot)
* Can execute specified actions triggered by messages or postback actions
* Can execute specified actions such as sending LINE messages at scheduled times and dates, days
### Connectivity with WP LINE Login
* If WP LINE Login is installed and Messaging API is configured, login integration is possible at the same time as integration

## How to install
1. download the latest version of the ZIP file from [GitHub](https://github.com/shipwebdotjp/lineconnect/releases).
2. login to the Wordpress administration page, select "Add New" from the "Plugins" menu, and click "Upload Plugin". 
3. From "Select File", select the ZIP file you have downloaded and click "Install Now".  
4. After installation is complete, activate "LINE Connect" from the Plug-in List screen.

## Initial Setup
### Channel Access Token and Channel Secret
1. create a Messaging API channel for your official account at [LINE Developers](https://developers.line.biz/) in advance.
2. obtain the Channel Secret from the Basic Settings. 
3. Obtain a Channel Access Token (long-lived) from the Messaging API Settings.

### Webhook URL
1. Set the Webhook URL in Webhook settings as follows
```
https://your-domain/wp-content/plugins/lineconnect/bot.php
```
※Change the your-domain part to your domain name. If your Wordpress URL is not directly under your domain, add the path to your Wordpress directory.
2. Turn on "Use webhook" toggle.

Note: If you only want to send post notifications to all your friends, you do not need to turn on "Use webhook". In that case, the user linking feature will not be available.

### Bot and Chat
BOT and chat can be used together. User inquiries, etc. can be responded to using chat, while user linking and automatic responses via ChatGPT can be used.
1. Login to [LINE Official Account Manager](https://manager.line.biz/).
2. Go to [settings]-[Response settings]. 
3. Turn on "Chat" if manual chat response is required.

### Initial Settings for the Plug-in
1. from the Wordpress admin page, click "LINE Connect" from the settings menu to open the settings page.
2. On the Channels tab, click "Add New Channel".
2. Enter the Channel Name, Channel Access Token and Channel Secret and save.

### If you are changing the login page URL.
By default, this plugin uses Wordpress's default "wp-login.php" as the login URL.
If you have changed the login page URL to another URL, please change the login page URL in the "Link" tab of the settings page.

## How to linking
There are three ways to start linking accounts. 
1. Add the official account as a friend.
2. Enter the keyword to start linking or unlinking accounts on the official account's talk screen and send it to the account. 
3. Send the postback action "action=link" from the rich menu.

Then, tap the link to start the linking, and log in to Wordpress to linking.

## How to send update notification
### Select the post type you want to be notified about
1. select post type you want to be notified about from the Post Types in the Update Notification tab. Custom post types are also supported.
### When posting
1. on the post edit screen, the "LINE Connect" box will appear in the right column, and if you want to be notified, check the "Send update notification" checkbox.  
*If you have checked, notifications will be sent not only of new posts published, but also posts updated.  
*If you check the "Send when a future post is published" checkbox and save it, notifications will be sent to LINE when the future posts are published.
2. If a post has an futured image, notifications will be sent with the image.
3. From the "Send target:" list, select the users to be notified by LINE from "All Friends", "Linked Friends", and each of the roles. You can select multiple targets.

## How to set the rich menu
A rich menu must be created in advance using the API and a rich menu ID must be obtained.  
※Rich menus created in [LINE Official Account Manager](https://manager.line.biz/) cannot be used.  
Creating rich menus using the API is relatively easy with the [Rich Menu Editor](https://richmenu.app.e-chan.cf/).
1. set the rich menu IDs on the LINE Connect settings page.
2. Create and register two types of rich menus, one for friends who are already linked and one for friends who are not linked, and set the IDs in the respective fields to display different rich menus depending on the status of the linking.

## Screen shots
LINE messages are displayed like this:  
<img src="https://blog.shipweb.jp/wp-content/uploads/2021/03/PNG-imageposttoline.png" width="320">  
You can also customize link text, background color, and thumbnail aspect ratio.  
<img src="https://blog.shipweb.jp/wp-content/uploads/2021/03/PNG-imageposttolinecustom.png" width="320">  

## Settings
You can configure various settings from the side menu under “LINE Connect” > “Settings”.

### Channel
Multiple channels are supported. You can select which channel you would like to be notified from when posting in the LINE Notifications meta box.

|item name|description|
|----:|----|
|Channel name|Channel name|
|Channel access token|channel access token (long-lived)||
|Channel Secret|Channel Secret|
|Default target role|Default selected role in the Send LINE meta box|
|The number of people|Number of people to be notified by the selected role or setting|
|Rich menu ID for linked users|Rich Menu ID for Linked Friends|
|Rich menu ID for unlinked users|Rich menu ID for unlinked friends|

### Link
You can change the login page URL, keywords for starting or canceling the linking, and messages when starting or canceling the linking.
#### Automatically initiate linkage
Automatically initiate linkage When user add an official account as a friend.
### Update Notification
#### Post types
Select the post type for which you wish to display the Send Line meta box: in addition to the "Post" and "Page" that exist by default in Wordpress, you can also select a custom post type added by Custom Post Type UI or yourself.
#### Default value of "Send update notification" checkbox
This is the default value setting for the "Send update notifications checkbox on the article edit screen. The options are "Checked", "Unchecked", and "Unchecked if published". 
#### "More" link label
The display string for the link that will appear at the bottom of the post notification. The label to be displayed in place of the URL, such as "Read more".
#### Send notification to posters when comments are received
This setting determines whether or not notifications will be sent to the author of the article when there are comments on the article. The author's WordPress account must be linked to LINE.
#### "Read comment" link label
The display string for the link that appears at the bottom of the comment notification. 
### Style
You can change the styles of the LINE message notifications.
#### Image fit mode
- cover: The replaced content is sized to maintain its aspect ratio while filling the image area. If the image's aspect ratio does not match the aspect ratio of its area, then the image will be clipped to fit. 
- contain: The replaced image is scaled to maintain its aspect ratio while fitting within the image area. The entire image is made to fill the box, while preserving its aspect ratio, so the image will be "letterboxed" if its aspect ratio does not match the aspect ratio of the area.
#### Image area aspect ratio
The aspect ratio of the image area. For example, 16:9, 4:3, 3:2  
The height cannot be greater than three times the width. By matching the aspect ratio of frequently used futured images, the margins will not be cropped. 
#### Background color of the message
The background color of the entire message. It is not possible in the current version to specify the image, title, body, and link areas individually.
#### Link style
- Button: button style.
- Link: HTML link style
#### Title text color, Body text color, Link text color and Link button background color
The color of each area. Font size, typeface, bold type, etc. cannot be changed!.
#### Max title lines and Max body lines
The maximum number of lines for the title and body text. If this number is exceeded, it will be omitted with an ellipsis (...). (Android/iOS only)
Even if the maximum number of lines is not exceeded, the part exceeding 500 characters will be omitted and sent.
### AI Chat
Using the Chat GPT API, you can set up AI to automatically respond to messages sent to LINE official accounts. 
#### Auto response by AI
Whether or not to use AI auto-response. Enable to use.
#### OpenAI API Key
Enter your OpenAI API key to use the Chat GPT API, which can be obtained [Open AI website](https://platform.openai.com/). 
#### Model
Which model to use.
#### System prompt
The initial text or instruction provided to the language model before interacting with it in a conversational manner.
#### Function Calling
whether Function Calling is used or not. When enabled, predefined functions can be used to return site-specific information, etc. 
#### Functions to use
Function to be enabled by Function Calling. Only a select function is used.
##### Available functions
- Get my user information
	Information about the user who sent the message is retrieved and used in the response. 
- Get the current date and time
	Current date and time are used in the response.
- Search posts
	Function to respond based on the content of posts on the site. Searches for posts and retrieves content in your site.
#### Number of context
Chow many conversation histories to use in order to have the AI understand the context and respond. A larger number increases the number of tokens used. 
#### Max tokens
Maximum number of tokens to use. -1 is the upper limit of the model.
#### Temperature
The temperature parameter. The higher the value, the more diverse words are likely to be selected. Between 0 and 1.
#### Limit for unlinked users
Number of times an unlinked user can use it per day. -1 is unlimited.
#### Limit for linked users
Number of times an linked user can use it per day. -1 is unlimited.
#### Limit message
This message is displayed when the number of times the limit can be used in a day is exceeded. The `%limit%` is replaced by the limit number of times.

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