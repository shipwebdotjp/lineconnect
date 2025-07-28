# Quick Start
## How to install plugin
This plugin is unavailable in the Wordpress plugin directory. To install it, please download it from GitHub and install it manually.
1. download the latest version of the ZIP file from [GitHub](https://github.com/shipwebdotjp/lineconnect/releases).
2. login to the Wordpress administration page, select "Add New" from the "Plugins" menu, and click "Upload Plugin". 
3. From "Select File", select the ZIP file you have downloaded and click "Install Now".  
4. After installation is complete, activate "LINE Connect" from the Plug-in List screen.

## LINE Official Account
If you do not have a LINE official account, please create one. It is free to create an account.
## Initial Settings for LINE Official Account
### Channel Access Token and Channel Secret
1. create a Messaging API channel for your official account at [LINE Developers](https://developers.line.biz/) in advance.
2. obtain the Channel Secret from the Basic Settings. 
3. Obtain a Channel Access Token (long-lived) from the Messaging API Settings.
:::note
The Channel Secret and Channel Access Token (long-lived) will be required in the plugin settings screen.
:::
### Webhook URL
1. Copy the Webhook URL from the "Webhook Settings" section at the bottom of the Channel Settings tab in LINE Connect's WordPress admin panel
2. In the LINE Official Account's Messaging API settings:
   - Click "Edit" next to the Webhook URL field
   - Paste the copied URL
3. Verification will fail at this stage as the channel is not yet configured in LINE Connect

Example URL: 
```
https://{your-domain}/wp-content/plugins/lineconnect/bot.php
```
※ Replace `{your-domain}` with your actual domain name.  
※ If WordPress is installed in a subdirectory (not at root), append the WordPress directory path after the domain.

2. Turn on "Use webhook" toggle.
:::note
If you only want to send post notifications to all your friends, you do not need to turn on "Use webhook". In that case, the user linking feature will not be available.
:::
### Bot and Chat
BOT and chat can be used together. User inquiries, etc. can be responded to using chat, while user linking and automatic responses via ChatGPT can be used.
1. Login to [LINE Official Account Manager](https://manager.line.biz/).
2. Go to [settings]-[Response settings]. 
3. Turn on "Chat" if manual chat response is required.

## Initial Settings for the Plug-in
### Channel Settings
1. from the Wordpress admin page, click "LINE Connect" from the settings menu to open the settings page.
2. On the Channels tab, click "Add New Channel".
3. Enter the Channel Name, Channel Access Token and Channel Secret and save.

### Channel Verification
To verify that the settings are correct, click the "Verify" button for the Webhook URL you set in the LINE Developers Messaging API channel page and check that the "Success" message is displayed.
:::note
If "Verification" fails, check that the installation was successful and that you are not blocking access to the wp-content directory in .htaccess.
:::

### If you are changing the login page URL.
By default, this plugin uses Wordpress's default "wp-login.php" as the login URL.
If you have changed the login page URL to another URL, please change the login page URL in the "Link" tab of the settings page.

