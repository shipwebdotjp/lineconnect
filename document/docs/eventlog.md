# Event log
View the received Webhook event log. You can see the event type, the user who sent it, and the content of the message.
| Item Name        | Content                                                                                     |
|------------------|--------------------------------------------------------------------------------------------|
| ID               | Event log serial number                                                                     |
| Event ID         | Received webhook event ID                                                                   |
| Event Type       | Received webhook event type                                                                 |
| Source Type      | Source of the received webhook event (user if received, bot if sent)                        |
| User ID          | Sender/Receiver user ID (display name if the user triggered a follow event)<br />Clicking the "Message" link that appears on hover will navigate to the screen to send a direct message to that LINE user. |
| BOT ID          | Channel that received the webhook event                                                     |
| Message Type     | Type of message if the event type is "message"                                              |
| Message          | Text of the message, file path, postback data                                               |
| DATE TIME   | Reception date and time of the webhook event                                                |