=== LINE Connect ===
Contributors: shipweb
Tags: line, userid, connect, 連携
Requires at least: 5.0
Tested up to: 6.2
Stable tag: 4.1.7
License: GPL v3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 7.3

Connect wordpress user account with your LINE official account follower's LINE ID

== Description ==

**Update notification**
You can send a LINE notification to a WordPress user who is linked.	  
Also, you can send notifications to users who have specific roles.  
You can send notifications when feature post published or someone comment on your post too. 

**LINE Chat/Chat Bot**
Arbitrary LINE messages can be sent to individual users.  
Automatic response to messages using Chat GPT API.  

**Rich menu**
Different rich menus can be set up depending on the linking status.

[LINE Connect Official Site](https://blog.shipweb.jp/lineconnect/)  

== Installation ==

* Wordpress

1. Download ZIP file from [GitHub](https://github.com/shipwebdotjp/lineconnect/releases)
1. Save the .zip file to a location on your computer.
1. Open the WP admin panel, and click “Plugins” -> “Add new”.
1. Click “Upload Plugin”.. then browse to the .zip file downloaded from this page.
1. Click “Install Now”.. and then “Activate plugin”.
1. Select option menu “Line connect”.
1. Save your channel access token and channel secret.

* LINE Developers

1. Set Webhook URL like 'https://your-domain/wp-content/plugins/lineconnect/bot.php'
1. Turn on 'Use webhook'

== Technical Details == 

* This plugin set LINE ID to user meta with these meta key below:
- key: line
- value: array(
    'The first 4 characters of channel secret' => array(
        'id' => 'LINE User ID',
        'displayName' => 'LINE Display Name',
        'pictureUrl' => 'LINE Picture URL'
    )
)

You can call get_user_meta function from other plugin to get LINE ID.
For exsample: If you want to get LINE ID who user id is 1
 $user_id = 1;
 $line_metadata = get_user_meta( $user_id, 'line', true );
 $line_id = $line_metadata['xxxx']['id'];

== Screenshots == 

![Screenshots1](https://blog.shipweb.jp/wp-content/uploads/2021/07/lineconnect-ss-10.png) 
![Screenshots2](https://blog.shipweb.jp/wp-content/uploads/2021/07/lineconnect-ss-12.png) 
![Screenshots3](https://blog.shipweb.jp/wp-content/uploads/2021/07/lineconnect-ss-13.png) 
![Screenshots4](https://blog.shipweb.jp/wp-content/uploads/2021/07/lineconnect-ss-14.png) 
![Screenshots5](https://blog.shipweb.jp/wp-content/uploads/2021/07/lineconnect-ss-02.jpg) 

== Frequently Asked Questions ==

Q. How can I change Link starting keyword?
A. You can change keywords in the Admin Panel.

Q. How can I change login URL?
A. You can change login URL in the Admin Panel, too.

== Changelog ==
= 3.0.0 =
2024-05-14 Added the ability to create message templates and trigger actions.

= 2.7.0 =
2023-12-15 Add Filter to GPT Log page. You can now choose between button and text link styles. Fixed a bug that prevented searching from messages in the log.

= 2.6.2 =
2023-12-13 Added LINE Chat Log feature for admin. You can check LINE bot log and delete it. Add Setting whether or not show automatically linkage start message.

= 2.6.0 =
2023-08-08 Added Function Calling feature on AI chat response using OpenAI(Chat GPT) API. English is now available. 

= 2.5.1 =
2023-07-07 Added linking feature with WP LINE Login.  

= 2.5.1 =
2023-04-08 Fixed a bug that caused duplicate events to be recorded when events were resubmitted.

= 2.5.0 =
2023-04-07 Added auto-responder feature using Chat GPT.

= 2.4.0 =
2022-06-17 Add default setting for send checkbox. notify comment future.

= 2.3.0 =
2022-05-03 Notify when future post is published.

= 2.2.0 =
2022-01-12 You can send LINE message without post from LINE Chat.

= 2.1.1 =
2021-11-21 Show user has already linked or not in user columns. Remove unnecessary hooks in order to accelerate.

= 2.1.0 =
2021-11-08 You can choose role multiple now. Also you can log via Stream plugin.

= 2.0.1 =
2021-07-30 BUg Fix. You can send LINE When you post from Rest API.  

= 2.0.0 =
2021-07-17 Line connect has changed to multi futrure plugin for LINE.  

= 1.1.2 =
2021-05-21 fix readme miss types.  

= 1.1.1 =
2021-05-21 Use Flex Message. so that user who Use Windows/Mac LINE edition can account link/unlink now.  

= 1.0.1 =
2020-12-18 Move menu to sub menu of Setting

= 1.0.0 =
2020-12-17 First release
