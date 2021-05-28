=== LINE Connect ===
Contributors: shipweb
Tags: line, userid, connect, 連携
Requires at least: 4.9.13
Tested up to: 5.5.3
Stable tag: 1.1.2
License: GPL v3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 5.3

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
- line_user_id
- line_displayname
- line_picture_url

You can call get_user_meta function from other plugin to get LINE ID.
For exsample: If you want to get LINE ID who user id is 1
 $user_id = 1;
 $line_id = get_user_meta( $user_id, 'line_user_id', true );

== Screenshots == 

![Screenshots](https://blog.shipweb.jp/wp-content/uploads/2020/12/Lineconnect-578x1024.png) 

== Frequently Asked Questions ==

Q. How can I change Link starting keyword?
A. You can change keywords in the bot.php file.

Q. How can I change login URL?
A. You can change login URL by $login_path vars in the config.php

== Customize ==
If you want to customize or make plugin using LINE ID, please contact me.
I can make plugin for you.
[Contact](https://blog.shipweb.jp/contact)

== More infomation == 
Please see my blog article.(Japanese)
[LINEと連携させるWordpressプラグイン LINE Connect](https://blog.shipweb.jp/archives/281)

== Changelog ==
= 1.1.2 =
2021-05-21 fix readme miss types.  

= 1.1.1 =
2021-05-21 Use Flex Message. so that user who Use Windows/Mac LINE edition can account link/unlink now.  

= 1.0.1 =
2020-12-18 Move menu to sub menu of Setting

= 1.0.0 =
2020-12-17 First release
