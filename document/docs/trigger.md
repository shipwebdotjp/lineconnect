# Trigger
## Trigger type
### Webhook
Execute an action triggered by a webhook event that matches the conditions.
#### Event type
Types of webhook events such as message/postback/account link/add friend (follow)/block (unfollow).
For more information on event types, please see  [Receive messages (webhook) | LINE Developers](https://developers.line.biz/en/docs/messaging-api/receiving-messages/#webhook-event-types)
#### Keywords
When the event type is “message” and the message type is “text” or the event type is “postback”, the condition of the data received is specified.  
If a text message is received, the data is the message received; if a postback, the data is the contents of the data property.
##### Source: ######
- Keywords: treats data as a string
- Query string: treats data as a query string
##### Match type
If the source is "Keyword", select the matching method from Contains/Equals/Starts with/Ends with/Regular Expression.  
If the source is "query string", whether the specified parameter is included in the data when the data query string is parsed (it is considered a match even if other data is included), or whether it is equal(it is not considered a match if other data is included).
#### Source Conditions
These are condition settings to determine whether to trigger based on the source information of the received event.

##### Channel
Specifies the channel that received the Webhook event.

##### Source
Specifies the source as "User", "Group", or "Room".

##### User
Sets conditions to trigger based on the attributes of the user who generated (sent) the Webhook event.
- Link Status: Select from "Any", "Linked", or "Unlinked" as the condition based on the link status with the WordPress user.
- Role: Specify the user's role as a condition.
- LINE User ID: Specify particular LINE user IDs as a condition.

##### Group
Sets conditions based on the LINE group as the origin of the Webhook event.
- LINE Group ID: Specify the originating LINE group ID as a condition.

##### Room
Sets conditions for multi-person chat as the source of the Webhook event.
- LINE Room ID: Specify the multi-person chat room ID as a condition.

##### Keyword/Source condition group
Groups conditions. Multiple conditions can be set so that if all source conditions in a group match (AND) or if any of them match (OR), the entire group is considered a match.
##### Logical Negation
Checking the Not checkbox inverts the judgment of the condition. Specifically, it works as follows:

- If the condition matches, it is assumed to be unmatched.
- If the condition does not match, it is considered a match.
##### operator
Specifies how to handle multiple conditions when there are multiple conditions.
- And: All conditions must be true.
- Or: At least one condition must be true.

#### Schedule
This feature allows actions to be triggered based on specified dates, times, or days of the week.

#### Once
Sets a one-time schedule.
- Date and Time: Specifies the date and time to trigger the action.

#### Repeat
##### Every Hour
Triggers the action at the specified time each hour.

##### Every Day of the Week
Triggers the action on the specified day(s) of the week.
- Way of Calc: Specifies how to calculate the day of the week, either as the nth day of the month or the nth week of the month.
- Day of the week in the month: The meaning varies based on the "Way of Calc" selection.
  - If "Nth Day" is selected, choosing 1 would refer to the first specific weekday of the month.
  - If "Nth Week of the Month" is selected, choosing 1 would refer to the first week of the month on the specified weekday.
- Day: Specifies the day from Sunday to Saturday.
- First Day of the Week: Sets whether the week starts on Sunday or Monday when "Nth Week of the Month" is selected.

##### Every Day
Triggers the action on the specified day. Checking the box for the last day of the month will trigger the action on the last day of each month.

##### Every Week
Triggers the action on the specified week number of the year.

##### Every Month
Triggers the action in the specified month(s).

##### Every Year
Triggers the action in the specified year(s).

##### Start Date
The trigger will not activate if the current time is before the start date. The start date and time serve as the reference point for various schedule types.
Example: If the start date is "2024/05/15 21:38":
- If checked at 0 am for Every Hour, the trigger will execute daily at 00:38.
- If checked on the first Tuesday for Every Week, the trigger will execute at 21:38 on the first Tuesday of the month.
- If checked on the 1st for Every Day, the trigger will execute at 21:38 on the 1st of each month.
- If the week number is set to 2 for Every Week, the trigger will execute at 21:38 on the starting day of the 2nd week (e.g., Wednesday) of the year.
- If checked in August for Every Month, the trigger will execute at 21:38 on 15th August of that year.

##### End Date
The trigger will not activate if the current time is after the end date.

##### Beforehand notice (min)
- Minutes of Advance Notice: 
  Specifies the number of minutes before the target time to trigger the action.  
  For example, to trigger the action 24 hours before the last day of each month, check the last day of the month and input "1440" for the minutes of advance notice. This setup ensures the trigger will fire one day before the last day of the month, regardless of the exact last day.  

### Action
This is the action to be executed when the trigger is activated.  

The following types of actions are available. It is also possible to add custom actions using filter hooks.
- Actions that retrieve and return information
- Actions that search for information and return it
- Actions that perform operations such as sending messages

Actions are shared with the Function Calling feature used for AI responses.
#### Return Value of Actions
##### Send the return value as a response
If checked, the return value of the action will be sent as a response message to the originator of the Webhook event.

The type of message sent varies depending on the type of return value:
- String: Sent as a LINE text message
- Instance of LINE\LINEBot\MessageBuilder: Sent as a LINE message object
- Others: Sent as a dumped LINE text message

:::info
The return value is sent as a response message only when the trigger type is a Webhook event.  
If the trigger type is a schedule, checking this will not send the return value as a LINE message.  
In that case, you can use the action chain feature to set the return value as an argument for the "Send LINE Message" action to send a LINE message to the desired user.  
:::

#### Embedding Variables
If the argument of the action is a string, each variable can be embedded and used.  

##### Return Values of Actions
The return values of sequentially executed actions can be used in placeholders within the arguments of subsequent actions.  
`{{$.return.action_number}}`  
action_number is the action number in the action chain.  
If the return value of the target action is an array, you can extract a specific value by adding ".key" to the action number.  
Example: Set the "Get Current Date and Time" action to Action-1 (Action Number 1).  
Set the "Get LINE Text Message" action to Action-2 (Action Number 2).  
In the body of the parameters for Action-2, enter "The current time is `{{$.return.1.datetime}}`."  
This will embed the current time in the message and send it.  

##### Webhook Event Data
You can also use data included in received Webhook events.  
`{{$.webhook.eventObjectProperty}}`  
Example: To use the text message sent to the official account  
`{{$.webhook.message.text}}`  
Example) **When using postback data**
You can use the data directly with `{{$.webhook.postback.data}}`.  
When data is in query string format, the parsed data is stored in params.  

Example) **Replying with a specified message ID using postback**
If the data is `action=message&slc_message_id=1354`, you can use `{{$.webhook.postback.params.slc_message_id}}` to get the value "1354".  
By utilizing this, you can create a single common trigger that fires on `action=message`, set the "Get LINE Connect message" action, and inject the desired message ID through action chain. This eliminates the need to create individual triggers for each message.  
[Configuration Example](/img/trigger/ex_postback_message.png)

Example) **Setting profile items using postback**
Create a postback with data `action=profile&key=value` and configure the trigger to fire on `action=profile`.  
Select "Update LINE User Profile" as the action and input `{{$.webhook.postback.params.key}}` for key and `{{$.webhook.postback.params.value}}` for value.
[設定例](/img/trigger/ex_postbak_update_profile.png)

##### User Data
You can use the data of the user who sent the Webhook event.  
`{{$.user.[WPUser object properties]}}`  
Example: Using the display name of a user  
 If the user is already linked to a WordPress user, you can get the display name of the WordPress user; if not, you can get the display name of the LINE user.  
`{{$.user.data.display_name}}`  
The profile of the LINE user (profile image URL, etc.) can be used in `{{$.user.profile.[profile property]}}`.  
The standard available values for profile properties are `displayName`, `pictureUrl`, `language`, and `statusMessage`.  
In addition, you can use your own item names set in the profile update action. 


#### action-chain
If the argument of an action is in the form of an object or a predetermined constant to be selected from, embedding variables is not possible.  
In such cases, a chain rule can be added to inject the return value of any action into the arguments of subsequent actions.  

##### Destination argument to
How to Specify the Target Action Argument for Injection: `ActionNumber.ArgumentName`  
- Action Number: The order of the actions starting from 1  
- Argument Name: The name corresponding to the required argument for each action  
Example: To specify the message of action-2 as the injected argument: `2.message`.  
(Note that it is not necessary to enclose in `{{ }}`)  

##### Data
Data to be injected can be strings or embedded variables.  
