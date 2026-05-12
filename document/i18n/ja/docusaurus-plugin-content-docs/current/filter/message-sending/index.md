# メッセージ送信関連フィルター
このグループには、通知メッセージの引数と送信直前のメッセージオブジェクトを調整するフックが含まれています。

## 対象フック

- [slc_filter_notification_message_args](#slc_filter_notification_message_args): 通知メッセージテンプレートを作るための引数を変更します。
- [slc_filter_notification_message](#slc_filter_notification_message): 送信直前の通知メッセージオブジェクトを変更します。

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
