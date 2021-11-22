=== LINE Connect ===
Contributors: shipweb
Tags: line, userid, connect, 連携
Requires at least: 4.9.13
Tested up to: 5.8
Stable tag: 2.1.0
License: GPL v3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 7.3

== Description ==

* Connect wordpress user account with your LINE official account follower's LINE ID

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

== Customize ==
If you want to customize or make plugin using LINE ID, please contact me.
I can make plugin for you.
[Contact](https://blog.shipweb.jp/contact)

== More infomation == 
Please see my blog article.(Japanese)
[LINEと連携させるWordpressプラグイン LINE Connect](https://blog.shipweb.jp/archives/281)

== Changelog ==
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
