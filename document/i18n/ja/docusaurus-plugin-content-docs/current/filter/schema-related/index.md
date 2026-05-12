# スキーマ関連フィルター

このグループには、管理画面やフォーム、ドキュメントで使うスキーマとUIスキーマを調整するフックが含まれています。

## 対象フック

- [slc_filter_audience_schema](#slc_filter_audience_schema): オーディエンススキーマを調整します。
- [slc_filter_audience_uischema](#slc_filter_audience_uischema): オーディエンスUIスキーマを調整します。
- [slc_filter_message_schema](#slc_filter_message_schema): メッセージスキーマを調整します。
- [slc_filter_message_uischema](#slc_filter_message_uischema): メッセージUIスキーマを調整します。
- [slc_filter_message_type_schema](#slc_filter_message_type_schema): メッセージ種別スキーマを調整します。
- [slc_filter_message_type_uischema](#slc_filter_message_type_uischema): メッセージ種別UIスキーマを調整します。
- [slc_filter_trigger_schema](#slc_filter_trigger_schema): トリガースキーマを調整します。
- [slc_filter_trigger_uischema](#slc_filter_trigger_uischema): トリガーUIスキーマを調整します。
- [slc_filter_trigger_type_schema](#slc_filter_trigger_type_schema): トリガー種別スキーマを調整します。
- [slc_filter_trigger_type_uischema](#slc_filter_trigger_type_uischema): トリガー種別UIスキーマを調整します。
- [slc_filter_interaction_schema](#slc_filter_interaction_schema): インタラクションスキーマを調整します。
- [slc_filter_interaction_uischema](#slc_filter_interaction_uischema): インタラクションUIスキーマを調整します。
- [slc_filter_richmenu_schema](#slc_filter_richmenu_schema): リッチメニュースキーマを調整します。
- [slc_filter_richmenu_uischema](#slc_filter_richmenu_uischema): リッチメニューUIスキーマを調整します。

## slc_filter_*_schema

各種スキーマを変更したい時に使用します。

### 引数

- `$schema`: (array) 現在のスキーマ。

### 例

スキーマに独自プロパティを追加する例です。

```php
function my_filter_schema( $schema ) {
    $schema['properties']['custom_note'] = array(
        'type' => 'string',
        'title' => 'Custom note',
    );

    return $schema;
}
add_filter( 'slc_filter_audience_schema', 'my_filter_schema' );
```

## slc_filter_*_uischema

各種UI スキーマを変更したい時に使用します。

### 引数

- `$uischema`: (array) 現在の UI スキーマ。

### 例

フィールドをテキストエリアとして表示する例です。

```php
function my_filter_uischema( $uischema ) {
    $uischema['custom_note'] = array(
        'ui:widget' => 'textarea',
    );

    return $uischema;
}
add_filter( 'slc_filter_audience_uischema', 'my_filter_uischema' );
```
