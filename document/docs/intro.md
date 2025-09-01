# What's LINE Connect
LINE Connect is a WordPress plugin that can connect Wordpress users with LINE users of Official LINE account.    
It can notify Wordpress post's update to the linked Wordpress users.  
It can register different rich menus depending on whether they are linked or not.    
Forked from [LINE AUTO POST](https://s-page.biz/line-auto-post/#home) by Growniche.  
The official blog article is here -> [LINE Connect, a WordPress plugin to connect with LINE](https://blog.shipweb.jp/lineconnect/) (Japanese)    
Demo site here -> [SHIP LAB](https://gpt.shipweb.jp/) (Japanese)  

## Features 
### Linking function with Wordpress users
* Using LINE Messaging API to link friends of LINE official accounts with accounts of Wordpress users
### Update Notification
* Send article update notifications via LINE to linked users, users in specific roles and audiences.
* Notification messages are sent in a card-style layout including an futured image.
* Notification message layout can be customized to original layout with Flex message.
* When posts published, updated or future post is published can send notifications.
* LINE notifications can be sent to posters when someone comment on thier articles.
### Bulk message
* Arbitrary LINE text messages can be sent to individual users
* Save LINE messages and use them for messages sent by actions
* Insert user's name etc. into the message and send it
### Chat
* Display messages history and sent to LINE users who have an event log, even if they are not connected to LINE.
* Display LINE user profile and tag information.
### Audience
* Create audiences that narrow down users based on user attributes and call them when sending messages or executing actions
### Scenario
* Execute actions such as sending messages at specified time intervals
### Action Flow
* Execute actions in sequence
* Action chain that uses the return value of the action in the next action
### Trigger
* Execute specified actions as triggers for specified dates and times and various Webhook events
### Rich Menu
* Create a new rich menu based on existing rich menus and templates
* Rich menu ID can be set for linked users, unlinked users, and roles
### Chat Integration
* Automatic response to messages using Chat GPT API (You can use your LINE official account as a AI chatbot)
### Connectivity with WP LINE Login
* If WP LINE Login is installed and Messaging API is configured, login integration is possible at the same time as integration

