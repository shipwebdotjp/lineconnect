---
sidebar_position: 2
---

# Quick Start
## How to install plugin
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

