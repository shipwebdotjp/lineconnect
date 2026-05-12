# Event log
View received Webhook event logs. You can inspect the event type, source, user, message content, and reception time.

Use the controls above the list to filter by event type, source type, channel, and message type. The search box searches message content.

| Item Name | Content |
|---|---|
| Selection checkbox | Select multiple rows for bulk deletion. |
| ID | Event log serial number. The row action menu lets you delete the item. |
| Event ID | Received webhook event ID |
| Event Type | Received webhook event type |
| Source Type | Source of the received webhook event (user if received, bot if sent) |
| User ID | Sender/receiver user ID (display name if the user triggered a follow event)<br />Clicking the "Message" link that appears on hover will navigate to the screen to send a direct message to that LINE user. |
| BOT ID | Channel that received the webhook event. If the channel is registered, its channel name is shown instead of the raw BOT ID. |
| Message Type | Type of message if the event type is "message" |
| Message | Message text, file path, postback data, or alt text for sticker/template messages |
| DATE TIME | Reception date and time of the webhook event |
