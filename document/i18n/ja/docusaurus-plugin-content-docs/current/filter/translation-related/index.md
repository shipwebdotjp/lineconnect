# 翻訳関連フィルター

このグループには、React JSON Schema Form のUIで使う翻訳文字列をカスタマイズするフックが含まれています。

## 対象フック

- [slc_filter_rjsf_translate_string](#slc_filter_rjsf_translate_string): React JSON Schema Form の各種文字列をカスタマイズします。

## slc_filter_rjsf_translate_string

このフィルターを使うと、React JSON Schema Form のUIで使われる翻訳文字列を上書きできます。
管理画面で表示されるラベル、ボタン文言、エラーメッセージなどを調整したいときに便利です。

### 引数

- `$translateString`: (array) React JSON Schema Form で使われる翻訳文字列の連想配列です。

### 使用例

以下は、フォームUIのいくつかの文言を変更する例です。

```php
add_filter( 'slc_filter_rjsf_translate_string', function ( $translateString ) {
    $translateString['Add'] = '作成';
    $translateString['Close'] = '閉じる';
    $translateString['Errors'] = '入力エラー';

    return $translateString;
} );
```
