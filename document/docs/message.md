---
title: 'LC Message'
---
# LC Messages (Message Templates)
You can create various LINE messages in advance as templates and use them when executing LINE message retrieval or sending actions. Up to five messages can be sent in a single transmission.

## Message Types
For more details on message types, refer to [Message Types | LINE Developers](https://developers.line.biz/en/docs/messaging-api/message-types/).

The created messages can be used by calling the “Get LINE Connect Message” action.  
Select “Get LINE Connect Message” in the action and select the message you wish to use from the parameter slc_message_id.

### Insert
By embedding `{{key}}` in the message text, you can insert a value corresponding to the key, such as a name, when the action is executed.

For example, you can create a text message with the content “Thank you for adding `{{name}}` as a friend! and save it.  
Set the event type to “Follow” in the trigger, select “Get LINE Connect Message” in the action, and select the message you have created in the slc_message_id parameter.  
Add args (message replacement argument) and set name as the key and `{{$.user.data.display_name}}` as the value (name).  
This will cause `{{name}}` in the message text to be replaced by the `user's display name` and sent.  