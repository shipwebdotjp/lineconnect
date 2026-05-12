# HTTPクライアント関連フィルター

このグループには、LINE Bot のHTTPクライアント実装を差し替えるフックが含まれています。

## 対象フック

- [slc_filter_httpclient](#slc_filter_httpclient): LINE Bot API 呼び出しで使うHTTPクライアントを差し替えます。

## slc_filter_httpclient

このフィルターを使うと、LINE Bot API リクエスト送信時に使われるHTTPクライアントを差し替えられます。
テスト用のモック化、ログ取得の追加、独自HTTPクライアントへの置き換えなどに便利です。

### 引数

- `$httpClient`: (`LINE\LINEBot\HTTPClient\HTTPClient`) LINE Bot API 呼び出しに渡されるデフォルトのHTTPクライアントです。

### 使用例

以下は、デフォルトのクライアントを独自クライアントに差し替える例です。

```php
add_filter( 'slc_filter_httpclient', function ( $httpClient ) {
    return new My_Custom_HTTP_Client();
} );
```

以下は、このプラグインのテストでも近い形で使われている、DummyHttpClient を返す例です。

```php
add_filter( 'slc_filter_httpclient', function ( $httpClient ) {
    $mock = function ( $testRunner, $httpMethod, $url, $data ) {
        return array( 'status' => 200 );
    };

    return new \LINE\Tests\LINEBot\Util\DummyHttpClient( $this, $mock );
} );
```
