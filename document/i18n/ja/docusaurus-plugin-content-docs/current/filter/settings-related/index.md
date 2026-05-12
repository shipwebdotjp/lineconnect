# 設定関連フィルター

このグループには、プラグイン設定、チャネル設定、関連する管理コマンドを調整するフックが含まれています。

## 対象フック

- [slc_filter_settings_option](#slc_filter_settings_option): 設定オプションの定義を調整します。
- [slc_filter_channnel_option](#slc_filter_channnel_option): チャネルオプションの定義を調整します。
- [slc_filter_management_command](#slc_filter_management_command): 利用可能な管理コマンドを調整します。

## slc_filter_settings_option

プラグインの設定画面で使う定義を変更したい時に使用します。設定項目の追加、削除、表示名の変更などに便利です。

### 引数

- `$options`: (array) 現在の設定オプション定義。

### 例

既存の設定項目のラベルを変更する例です。

```php
function my_filter_settings_option( $options ) {
    if ( isset( $options['line_token'] ) ) {
        $options['line_token']['title'] = 'Custom LINE token';
    }

    return $options;
}
add_filter( 'slc_filter_settings_option', 'my_filter_settings_option' );
```

## slc_filter_channnel_option

チャネル設定画面で表示される定義を変更したい時に使用します。

### 引数

- `$options`: (array) 現在のチャネルオプション定義。

### 例

チャネル項目に説明文を追加する例です。

```php
function my_filter_channnel_option( $options ) {
    foreach ( $options as &$option ) {
        if ( isset( $option['id'] ) && $option['id'] === 'channel_name' ) {
            $option['description'] = 'LINE Connect で表示する名前として使います。';
        }
    }

    return $options;
}
add_filter( 'slc_filter_channnel_option', 'my_filter_channnel_option' );
```

## slc_filter_management_command

管理画面や CLI 連携で利用可能な管理コマンドを追加・削除・調整したい時に使用します。

### 引数

- `$commands`: (array) 現在の管理コマンド一覧。

### 例

カスタムコマンドのラベルを登録する例です。

```php
function my_filter_management_command( $commands ) {
    $commands['sync_users'] = array(
        'label' => 'Sync users',
        'description' => 'Synchronize LINE friends with WordPress users.',
    );

    return $commands;
}
add_filter( 'slc_filter_management_command', 'my_filter_management_command' );
```
