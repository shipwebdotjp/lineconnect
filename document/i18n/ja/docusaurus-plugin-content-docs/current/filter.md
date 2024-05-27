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


