# Settings
You can configure various settings from the side menu under “LINE Connect” > “Settings”.

## Channel
Multiple channels are supported. You can select which channel you would like to be notified from when posting in the LINE Notifications meta box.
:::info
The channel referred to here is the Messaging API channel of the LINE Official Account.
:::
|item name|description|
|----:|----|
|Channel name|Channel name|
|Channel access token|channel access token (long-lived)|
|Channel Secret|Channel Secret|
|Default target role|Default selected role in the Send LINE meta box|
|The number of people|Number of people to be notified by the selected role or setting|
|Rich menu ID for linked users|Rich Menu for Linked Friends|
|Rich menu ID for unlinked users|Rich menu for unlinked friends|
|Rich menu ID for (role name)|Rich menu for Role name|

### Webhook settings
The webhook settings box shows the Webhook URL to use with your LINE Developers Messaging API channel.
The channel secret can be obtained from the Channel Basic Settings page, and the channel access token can be obtained from the Messaging API Settings page.
If you use Webhook, set the webhook URL to the displayed value.

## Link
You can change the login page URL, redirect behavior, keywords for starting or canceling the linking, and the messages shown when linking or unlinking starts or completes.
### Login page URL
The URL of the page to which you will be redirected when you tap the link to start linking. This is usually the login page URL.
### Redirect page method
Choose how the redirect page is handled after login.
- Direct file access
- Admin Post
- REST API
### Automatically initiate linkage
Automatically initiate linkage When user add an official account as a friend.
### Account link/unlink start keywords
Keywords used to start account linking or unlinking.
### Message title for account linkage initiation
The title shown in the message that starts account linking.
### Message body for account linkage initiation
The body shown in the message that starts account linking.
### Message button label to start account linkage
The button label shown in the message that starts account linking.
### Account Linkage Completion Message
The message shown when account linking has completed.
### Account Linkage Failure Messages
The message shown when account linking fails.
### Message title for account unlinking initiation
The title shown in the message that starts account unlinking.
### Message body for account unlinking initiation
The body shown in the message that starts account unlinking.
### Message button label to start account unlinking
The button label shown in the message that starts account unlinking.
### Account Unlinking Completion Message
The message shown when account unlinking has completed.
### Account Unlinking Failure Message
The message shown when account unlinking fails.
## Update Notification
### Post types
Select the post type for which you wish to display the Send Line meta box: in addition to the "Post" and "Page" that exist by default in Wordpress, you can also select a custom post type added by Custom Post Type UI or yourself.
### Default value of "Send update notification" checkbox
This is the default value setting for the "Send update notifications checkbox on the article edit screen. The options are "Checked", "Unchecked", and "Unchecked if published". 
### Default value of notification message template
This is the default value setting for the select box that determines which template to use for sending post notifications.
### "More" link label
The display string for the link that will appear at the bottom of the post notification. The label to be displayed in place of the URL, such as "Read more".
### Send notification to posters when comments are received
This setting determines whether or not notifications will be sent to the author of the article when there are comments on the article. The author's WordPress account must be linked to LINE.
### "Read comment" link label
The display string for the link that appears at the bottom of the comment notification. 
## Style
You can change the style of the default post notification template.
### Image fit mode
- cover: The replaced content is sized to maintain its aspect ratio while filling the image area. If the image's aspect ratio does not match the aspect ratio of its area, then the image will be clipped to fit. 
- contain: The replaced image is scaled to maintain its aspect ratio while fitting within the image area. The entire image is made to fill the box, while preserving its aspect ratio, so the image will be "letterboxed" if its aspect ratio does not match the aspect ratio of the area.
### Image area aspect ratio
The aspect ratio of the image area. For example, 16:9, 4:3, 3:2  
The height cannot be greater than three times the width. By matching the aspect ratio of frequently used futured images, the margins will not be cropped. 
### Background color of the message
The background color of the entire message. It is not possible in the current version to specify the image, title, body, and link areas individually.
### Link style
- Button: button style.
- Link: HTML link style
### Title text color, Body text color, Link text color and Link button background color
The color of each area. Font size, typeface, bold type, etc. cannot be changed!.
### Max title lines and Max body lines
The maximum number of lines for the title and body text. If this number is exceeded, it will be omitted with an ellipsis (...). (Android/iOS only)
Even if the maximum number of lines is not exceeded, the part exceeding 500 characters will be omitted and sent.
## AI Chat
Using the Chat GPT API, you can set up AI to automatically respond to messages sent to LINE official accounts. 
### Auto response by AI
Whether or not to use AI auto-response. Enable to use.
### OpenAI API Endpoint
The URL of the OpenAI or OpenAI-compatible API. The default is the OpenAI API URL.
### OpenAI API Key
Enter your OpenAI or OpenAI-compatible API key to use the Chat GPT API, which can be obtained [Open AI website](https://platform.openai.com/). 
### Model
Which model to use.
### System prompt
The initial text or instruction provided to the language model before interacting with it in a conversational manner.  
You can use Twig placeholder.
### Function Calling
whether Function Calling is used or not. When enabled, predefined functions can be used to return site-specific information, etc. 
### Functions to use
Function to be enabled by Function Calling. Only a select function is used.
#### Available functions
- Get my user information
	Information about the user who sent the message is retrieved and used in the response. 
- Get the current date and time
	Current date and time are used in the response.
- Search posts
	Function to respond based on the content of posts on the site. Searches for posts and retrieves content in your site.
### Number of context
Chow many conversation histories to use in order to have the AI understand the context and respond. A larger number increases the number of tokens used. 
### Max tokens
Maximum number of tokens to use. -1 is the upper limit of the model.
### Temperature
The temperature parameter. The higher the value, the more diverse words are likely to be selected. Between 0 and 1.
### Limit for unlinked users
Number of times an unlinked user can use it per day. -1 is unlimited.
### Limit for linked users
Number of times an linked user can use it per day. -1 is unlimited.
### Limit message
This message is displayed when the number of times the limit can be used in a day is exceeded. The `%limit%` is replaced by the limit number of times.
## Data
### Clear the rich menu cache
This button clears the rich menu cache held by LINE Connect so updates made through the API are reflected.
### Delete all plugin data
Delete all plugin data stored by LINE Connect.
