# アクションフック
他プラグインからLINEメッセージ送信を呼び出せるよういくつかアクションフックが用意されています。
## send_message_to_wpuser($channel, $wp_user_id, $message)
連携済みのWordpressユーザーにLINEメッセージを送信します。
### 使い方
```
//デフォルト（登録されているチャネルが1つならそのチャネル、複数登録されている場合は最初のチャネル）のユーザーIDが2のユーザーへメッセージ送信
do_action('send_message_to_wpuser', null, 2, 'Wordpressからのメッセージ');

//チャンネルシークレットの先頭4文字でチャネルを指定してチャネル情報を取得
do_action('send_message_to_wpuser', lineconnect::get_channel('1fa8'), 3, 'Wordpressからのメッセージ');

//送信するチャネルのアクセストークンとシークレットを連想配列で渡す場合
$channel = array(
    'channel-access-token' => '実際のチャネルアクセストークン',
    'channel-secret' => '実際のチャネルシークレット'
);
do_action('send_message_to_wpuser', $channel, 3, 'Wordpressからのメッセージ');

//LINE BOT SDKを利用してImageMessageBuilderを作成し、画像を送信
require_once(plugin_dir_path(__FILE__).'../lineconnect/vendor/autoload.php');
$originalContentUrl = "https://example.com/img.jpg";
$previewImageUrl = "https://example.com/img.jpg";
$imageMessageBuilder = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($originalContentUrl, $previewImageUrl);
do_action('send_message_to_wpuser', null, 3, $imageMessageBuilder);
```
### パラメータ
**$channel (array|null)**  
channel-access-token、channel-secretを持つチャネル情報の連想配列か、null（デフォルトチャネルを使用）  
※デフォルトチャネルは、登録されているチャネルが1つならそのチャネル、複数登録されている場合は最初のチャネルです。  
**$wp_user_id (int)**   
 メッセージを送信するユーザーのWordpress ID（LINEユーザーIDではないことに注意）  
**$message (string|LINE\LINEBot\MessageBuilder)**  
送信するメッセージ。文字列の場合はテキストメッセージを作成して送信します。LINE BOT SDKを利用してMessageBuilderで作成したメッセージを送ることもできます。

## send_message_to_role($channel, $role, $message)
ロールを指定して連携済みユーザーへLINEメッセージを送信します。``$role``に予約されている値を渡すことで、連携済みユーザー全てに送信することもできます。
### 使い方
```
//管理者ロールのユーザーへメッセージを送信
do_action('send_message_to_role', null, array("administrator"), 'Wordpressからのメッセージ');

//すべての連携済みユーザーへメッセージを送信
do_action('send_message_to_role', null, array("slc_linked"), 'Wordpressからのメッセージ');

```
### パラメータ
**$channel, $message**  
send_message_to_wpuserのパラメータと同じです。  
**$role (array)**   
送信対象とするロールスラッグの配列。  
例）`array("administrator")`  
`slc_linked`を指定すると、すべての連携済みユーザーへ送信します。  

**チャネル指定時の注意**  
デフォルトチャネルは、チャネルの削除によって変化します。例えば複数チャネルがある場合、1番目のチャネルを削除すると、２番目のチャネルがデフォルトチャネルになります。確実を期すためにはチャネル情報を配列で指定するか、チャネル情報をシークレットの先頭4文字で取得したものを使用してください。
```
$channel = lineconnect::get_channel("(シークレットの先頭4文字)");
```
