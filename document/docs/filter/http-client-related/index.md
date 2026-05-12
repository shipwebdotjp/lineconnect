# HTTP client-related filters

These hooks let you replace the LINE Bot HTTP client implementation.

## Included hooks

- [slc_filter_httpclient](#slc_filter_httpclient): Replace the HTTP client used by LINE Bot API calls.

## slc_filter_httpclient

This filter lets you swap the HTTP client instance used when LINE Bot API requests are sent.
It is useful for tests, custom logging, or replacing the default cURL client with your own implementation.

### Arguments

- `$httpClient`: (`LINE\LINEBot\HTTPClient\HTTPClient`) The default HTTP client instance passed to LINE Bot API calls.

### Example

This example replaces the default client with a custom one.

```php
add_filter( 'slc_filter_httpclient', function ( $httpClient ) {
    return new My_Custom_HTTP_Client();
} );
```

This example is similar to the test setup used in this plugin and wraps the client with a dummy implementation.

```php
add_filter( 'slc_filter_httpclient', function ( $httpClient ) {
    $mock = function ( $testRunner, $httpMethod, $url, $data ) {
        return array( 'status' => 200 );
    };

    return new \LINE\Tests\LINEBot\Util\DummyHttpClient( $this, $mock );
} );
```
