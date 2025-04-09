# FAQ
## What kind of plugin is LINE Connect?
LINE Connect is a WordPress plugin that integrates WordPress sites with LINE Official Accounts. Through this integration, you can send article update notifications via LINE, display different rich menus for connected users, non-connected users, and users with different roles, send bulk messages, and utilize various other features.

## How can WordPress users connect their accounts with LINE accounts using LINE Connect?
Users can start the connection process by adding the LINE Official Account as a friend, sending specific keywords in the chat, or using postback actions such as rich menu options. After that, users can complete the connection by tapping the connection link and logging into WordPress.

## How can I send article update notifications?
You can send LINE notifications to connected users or users with specific roles when a particular post is published or updated by selecting the notification recipients in the metabox on the post editing screen. You can also send notifications using customized templates.

## What customization options are available for article update notifications in LINE Connect?
For article update notifications, you can configure the post types to be notified, default sending settings (checkbox status, target roles, message templates), and link labels. You can also fine-tune message styles (image display style, aspect ratio, background color, text color, link style, etc.). Furthermore, when using LC messages (message templates), you can embed variables such as title, content, featured image URL, post permalink, etc., to create dynamic messages.

## What types of message delivery are possible with LINE Connect?
The following types of message delivery are mainly available:

Bulk delivery: Send any LINE message at once to users extracted based on various conditions.
Direct messages: Send individual text messages to users whose LINE user IDs are recorded in the event log, such as users who have added the official account as a friend or who have sent messages within the past 7 days (including non-connected users).
Scenario delivery (step delivery): Sequentially deliver multiple pre-configured messages to users at specified time intervals.

## What is the "Audience" feature in LINE Connect? How can it be used?
The Audience feature allows you to narrow down users who are the recipients of messages by combining various conditions (LINE channel, connection status, WordPress role, LINE user ID, WordPress user ID, email address, username, display name, user meta, profile information, etc.). Created audiences can be utilized for efficient message delivery to specific user groups when performing bulk delivery or executing scenarios.

## What is the "Action Flow" feature in LINE Connect?
Action Flow is a feature that executes multiple actions in sequence and includes an "action chain" mechanism that passes the return value of one action to the next action.  
Actions include sending messages, updating user meta, starting scenarios, etc., and you can also add custom actions through filter hooks.  

## What is the "Trigger" feature in LINE Connect?
Trigger is a feature that detects specific firing conditions (receipt of webhook events, specified date and time, day of the week, etc.) and executes configured actions (including action flows) when those conditions are met. For example, you can automatically send a reply message to a specific user when they send a message to the LINE Official Account.

## What specific capabilities does the "Scenario" feature in LINE Connect offer?
The Scenario feature allows you to:

Step delivery: Automatically deliver messages that start with a welcome message for new friends and gradually provide information or prompt specific actions.
Reminders: Send reminder messages at specified dates and times before events or campaigns.
Scenarios with conditional branching: Branch scenarios to execute different messages or actions based on specific user attributes or actions.

## What features are available for AI auto-response using Chat GPT API in LINE Connect?
In LINE Connect, by integrating the Chat GPT API, you can use a feature where AI automatically responds to messages sent by users to the LINE Official Account. Specifically, you can select which Chat GPT model to use, set system prompts that define the direction of AI responses, set context count for responses considering past conversation history, and adjust sampling temperature to control response diversity. Additionally, by enabling the Function Calling feature, the AI can utilize pre-configured functions (such as retrieving user information, getting current date and time, searching site articles, etc.) to provide more advanced and personalized responses. You can also set daily usage limits for both connected and non-connected users.

## Does it support creating and setting up rich menus?
You can create rich menus based on existing rich menus or templates. Also, you can display different rich menus for connected users, non-connected users, and users with different roles.
