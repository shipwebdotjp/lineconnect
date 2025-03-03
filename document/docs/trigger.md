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
To know more about the action, please refer to the [Action Flow](./actionflow.md) documentation.
