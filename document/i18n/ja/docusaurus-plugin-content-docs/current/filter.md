# フィルターフック
他のプラグインやテーマのfunctions.phpで機能を追加したり、設定に変更を加えるためのフィルターフックが用意されています。

## slc_filter_actions
あらかじめ用意されているアクションに加えて、独自の新しいアクションを登録するためのフィルターフックです。  
このフックを利用することで、開発者は独自のアクションを定義し、LINE Connectで使用することができます。  

### 引数
- `$actions`: (array) アクション関数名をキーとし、アクション詳細配列を値とする連想配列。新たなアクションを配列に追加した後で返すことで、新しいアクションを登録します。

### 例
天気予報を返す`get_the_weather`アクションを追加する例です。
```php
function my_filter_actions($actions) {
    $actions['get_the_weather'] = array(
        'title' => '天気予報を取得',
        'description' => '指定された地域の天気予報を返す',
        'parameters' => array(
            array(
                'type' => 'string',
                'name' => 'location',
                'description' => '地域名',
                'required' => true,
            ),
        ),
        'namespace' => 'LineConnectDemo',
        'role' => 'any',
    );
    return $actions;
}
add_filter('slc_filter_actions', 'my_filter_actions');
```

### アクション配列の構造
#### キー
キーはアクションを実行する関数名です。

#### 値
アクションに関するデータを連想配列形式で指定します。

- `title`: (string) アクションのタイトル。ユーザーフレンドリーな名前。
- `description`: (string) アクションの説明。アクションが何をするのかを説明。
- `parameters`: (array) パラメータの配列。配列の要素の順に、関数の引数としてマッピングされます。（配列の1番目の要素は関数の第1引数に、2番目の要素は第2引数に、と言う具合です）。各パラメータは以下の構造を持ちます。
  - `type`: (string) パラメータのデータ型（例: 'integer', 'string', 'object', 'array', 'slc_message', 'slc_channel'）。  
  'slc_message'はLCメッセージを選択するドロップダウンが表示され、値は、投稿IDです。  
  'slc_channel'はチャネルを選択するドロップダウンが表示され、値はシークレットの先頭4文字です。
  - `name`: (string) パラメータの名前。
  - `description`: (string) パラメータの説明。
  - `required`: (boolean) パラメータが必須かどうか。
- `namespace`: (string) アクションが属する名前空間。クラス名など。
- `role`: (string) アクションを実行できるユーザーロール。Function CallingでChat GPTから呼び出される場合に適用されます。トリガーから呼び出される場合は無視されます。'any' はすべてのユーザーを意味します。

### 使用例
以下は、実際に `slc_filter_actions` フィルターフックを使って天気予報を取得するアクションを追加するコード例です。

```php title="lcdemo.php"
class LineConnectDemo {
    static function instance() {
        return new self();
    }
    function __construct() {
        add_filter('slc_filter_actions', array($this, 'slc_filter_actions'));
    }
    function slc_filter_actions($actions) {
        $actions['get_the_weather'] = array(
            'title' => '天気予報を取得',
            'description' => '指定された地域の天気予報を返す',
            'parameters' => array(
                array(
                    'type' => 'string',
                    'name' => 'location',
                    'description' => '地域名',
                    'required' => true,
                ),
            ),
            'namespace' => 'LineConnectDemo',
            'role' => 'any',
        );
        return $actions;
    }
    function get_the_weather($location){
        $weather = wp_remote_get('https://api.openweathermap.org/data/2.5/weather?q='.$location.'&appid=YOUR_API_KEY&lang=ja');
        return json_decode($weather['body'], true);
    }
}

$GLOBALS['LineConnectDemo'] = new LineConnectDemo();
```
#### AI応答で使う場合
設定で「AIによる自動応答」と「Function Calling」を有効にした上で、「使用するFunction」で「天気予報を取得」にチェックを入れてください。  
その上で、「東京の天気予報を教えて」と送信してみてください。

#### トリガーで使う場合
1. 適切なトリガーを指定します。
2. アクション-1に、「天気予報を取得」をセットし、locationに「東京」などと入力します。
3. アクション-2で、「LINEテキストメッセージ取得」とし、「戻り値をLINEメッセージで送信」にチェックを入れます。
4. parametersのbodyに`{{$.return.1.weather.0.description}}`と入力します。

# 投稿通知関連のフィルターフック

## slc_filter_publish_postmeta_is_send_line

投稿編集画面で更新通知を送信するかどうかのチェックボックス、送信対象ロールのリスト、メッセージテンプレートのプルダウンのフォーム初期値を変更したい時に使用します。投稿メタデータ `is-send-line` の初期値をフィルターします。

### 引数

- `$is_send_line`: (mixed) LINE送信設定の値。通常は投稿メタデータ`is-send-line`の値。
    - `role`: (array) 送信対象ロールの配列
    - `template` (int) 使用するメッセージテンプレートID
    - `isSend`: (string) 「予約投稿時に送信する」チェックボックスの値('ON' or '')。
- `$post_ID`: (int) 投稿ID。

### 例

特定の投稿タイプの場合は、デフォルトで更新通知を送信するチェックボックスを有効にする例です。

```php
function my_filter_publish_postmeta_is_send_line($is_send_line, $post_ID) {
    $post_type = get_post_type($post_ID);
    if ($post_type === 'news') {
        // チャンネルごとに設定
        foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
            $is_send_line[$channel['prefix']] = array(
                'role' => array('slc_all'), // すべての友達に送信
                'template' => 113, // 得敵の投稿タイプ用のテンプレートID(LCメッセージの投稿ID)を設定
                'isSend' => 'ON', // デフォルトで予約投稿時に送信するのチェックボックスをON
            );
        }
    }
    return $is_send_line;
}
add_filter('slc_filter_publish_postmeta_is_send_line', 'my_filter_publish_postmeta_is_send_line', 10, 2);
```

## slc_filter_send_notification_is_send_line

投稿時にPOST送信されたメタボックスの値（更新通知を送信するかどうか、送信対象ロール、メッセージテンプレート）を変更したい時に使用します。

### 引数

- `$send_data`: (array) 送信データを含む連想配列。以下のキーを持ちます。
    - `send_checkbox_value`: (string) 送信チェックボックスの値('ON' or '')。
    - `roles`: (array) 送信対象ロールの配列。
    - `template`: (int) 使用するテンプレートID。
- `$post_ID`: (int) 投稿ID。
- `$post`: (WP_Post) 投稿オブジェクト。

### 例

特定の条件で送信を無効にする例です。

```php
function my_filter_send_notification_is_send_line($send_data, $post_ID, $post) {
    if (true) { //何らかの条件
        $send_data['send_checkbox_value'] = ''; // 強制的に更新通知を送信しないようにする
    }
    return $send_data;
}
add_filter('slc_filter_send_notification_is_send_line', 'my_filter_send_notification_is_send_line', 10, 3);
```

## slc_filter_notification_message_args

メッセージテンプレートを作成するためのパラメータに手を加える場合に使用します。テンプレートに渡される引数を変更できます。

### 引数

- `$args`: (array) メッセージテンプレートに渡される引数の連想配列。
- `$template`: (int) 使用するテンプレートID。

### 例

メッセージテンプレートに、アイキャッチ画像を含める例です。  
単純に`{{post_permalink}}`をメッセージ内に含めるだけだと、アイキャッチが無い場合にメッセージとして成立せずエラーとなるのを防ぎます。

```php
function my_filter_notification_message_args($args, $template) {
    // post_thumbnail が空だったり、httpsでない場合は代替画像URLをセット
	if (empty($args['post_thumbnail']) || substr($args['post_thumbnail'], 0, 5) != "https") {
		$args['post_thumbnail'] = 'https://placehold.jp/3d4070/ffffff/300x200.png?text=No%20Image';
	}
    return $args;
}
add_filter('slc_filter_notification_message_args', 'my_filter_notification_message_args', 10, 2);
```

## slc_filter_notification_message

作成された通知メッセージオブジェクトに変更を加えて送信する場合に使用します。

### 引数

- `$buildMessage`: (LINE\LINEBot\MessageBuilder) 生成されたメッセージオブジェクト。
- `$args`: (array) メッセージテンプレートに渡された引数の連想配列。
- `$template`: (int) 使用するテンプレートID。

### 例

更新通知メッセージの送信者名と送信者アイコンを設定する例です。(SenderMessageBuilderを使用)

```php
function my_filter_notification_message($buildMessage, $args, $template) {
	// 更新通知を送信する際の送信者名とアイコンを変更する
	if($buildMessage instanceof \LINE\LINEBot\MessageBuilder\FlexMessageBuilder) {
		$SenderMessageBuilder = new \LINE\LINEBot\SenderBuilder\SenderMessageBuilder("author_name", "https://placehold.jp/28c832/ffffff/200x200.png?text=author");
		$buildMessage->setSender($SenderMessageBuilder);
	}
    return $buildMessage;
}
add_filter('slc_filter_notification_message', 'my_filter_notification_message', 10, 3);
```
