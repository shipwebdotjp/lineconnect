# 投稿通知関連フィルター
このグループには、投稿公開まわりのLINE通知設定を変更するフックが含まれています。

## 対象フック

- [slc_filter_publish_postmeta_is_send_line](#slc_filter_publish_postmeta_is_send_line): 投稿編集画面に表示されるLINE通知設定の初期値を変更します。
- [slc_filter_send_notification_is_send_line](#slc_filter_send_notification_is_send_line): 投稿公開時にPOST送信される通知設定を変更します。

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
                'template' => 113, // 特定の投稿タイプ用のテンプレートID(LCメッセージの投稿ID)を設定
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
