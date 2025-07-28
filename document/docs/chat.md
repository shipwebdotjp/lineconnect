# Chat
Chat with users who have added your LINE Official Account as a friend or sent messages within the last 7 days.

![Chat Screen Layout](/img/chat/chat_screen.png)

## How to Open the Chat Interface
- From admin sidebar: Select "Chat" > Choose channel > Select user
- Click "Message" link in the floating menu next to user ID in Event Log
- Click "Message" link in LINE ID List
- Click LINE display name link for linked users in user list

## Chat Interface Structure
### 1. Channel/User Selection Area (Left Panel)
- Switch between multiple channels (for multi-channel management)
- Display user list for selected channel

### 2. Message Area (Center Panel)
#### Message Display
- Supported formats: Text, Images, Videos, Location, Flex Messages
- Unsupported formats: Stickers, Template Messages
- Message bubbles (Left: Received, Right: Sent)

#### Action Buttons
- **Load Older Messages**: Load historical messages
- **Open Message Input Form**: Show message composition modal

### 3. User Information Area (Right Panel)
#### Profile Information
- LINE display name/Profile image/Friend status
- Created Date: Date when user was first identified (e.g., first message received after LINE Connect setup)
- Last Updated: Date of last data update/message exchange

#### Management Menu
- **Edit Profile**: Display user profile edit form
- **Edit Tags**: Display tag management form (add/edit/delete)
- **Scenarios**: Display scenario configuration form linked to user

## Message Sending Procedure
1. Click "Open Message Input Form" button at bottom of message area
2. Message composition modal will appear
3. Select existing message from template dropdown or create new
4. Enter message text (supports line breaks)
5. Check "Disable notifications" if needed
6. Click **Send** button

![Message Input Modal](/img/chat/message_modal.png)

### Disable Notifications
When checked: Push notifications will not appear on the user's smartphone.

## Common Operations
### Updating User Profile
1. Click "Edit" icon in User Information Area
2. Update necessary information in edit form
3. Click **Save** to apply changes

## Current Limitations
- Unsupported message formats: Stickers, Template Messages
- No message editing/deletion functionality
- No user/message search functionality
- No auto message refresh functionality

:::note
Sent messages count against your LINE Official Account's paid message quota (push notifications).
:::
