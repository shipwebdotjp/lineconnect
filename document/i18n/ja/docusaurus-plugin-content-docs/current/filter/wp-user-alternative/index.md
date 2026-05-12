# WPユーザー代替フィルター
LINE Connect が WordPress のユーザー関連処理を代替実装へ切り替えるためのフィルター群です。

## 対象フック

- [slc_filter_use_alternative_user_provider](#slc_filter_use_alternative_user_provider): 代替ユーザープロバイダーを有効化または無効化します。
- [slc_filter_get_userdata](#slc_filter_get_userdata): 通常モードで `get_userdata()` の結果を変更します。
- [slc_filter_get_userdata_alternative](#slc_filter_get_userdata_alternative): 代替モードでユーザーデータを返します。
- [slc_filter_get_user_meta](#slc_filter_get_user_meta): 通常モードで `get_user_meta()` の結果を変更します。
- [slc_filter_get_user_meta_alternative](#slc_filter_get_user_meta_alternative): 代替モードでユーザーメタを返します。
- [slc_filter_update_user_meta](#slc_filter_update_user_meta): 通常モードで `update_user_meta()` の結果を変更します。
- [slc_filter_update_user_meta_alternative](#slc_filter_update_user_meta_alternative): 代替モードでユーザーメタ更新を実行します。
- [slc_filter_delete_user_meta](#slc_filter_delete_user_meta): 通常モードで `delete_user_meta()` の結果を変更します。
- [slc_filter_delete_user_meta_alternative](#slc_filter_delete_user_meta_alternative): 代替モードでユーザーメタ削除を実行します。
- [slc_filter_get_current_user_id](#slc_filter_get_current_user_id): 通常モードで現在のユーザーIDを変更します。
- [slc_filter_get_current_user_id_alternative](#slc_filter_get_current_user_id_alternative): 代替モードで現在のユーザーIDを返します。
- [slc_filter_is_user_id_valid](#slc_filter_is_user_id_valid): 通常モードでユーザーIDの妥当性判定を変更します。
- [slc_filter_is_user_id_valid_alternative](#slc_filter_is_user_id_valid_alternative): 代替モードでユーザーIDの妥当性を判定します。
- [slc_filter_get_linked_userids_by_roles_alternative](#slc_filter_get_linked_userids_by_roles_alternative): 代替モードで指定ロールに紐づくユーザーIDを返します。
- [slc_wp_user_query_alternative](#slc_wp_user_query_alternative): 代替モードで `WP_User_Query` 相当のユーザー検索結果を返します。
- [slc_get_user_from_line_id](#slc_get_user_from_line_id): LINE ID からユーザーデータを取得します。
- [slc_roles](#slc_roles): ロール一覧に独自のステータスや疑似ロールを追加します。

## 制御用フィルター

### slc_filter_use_alternative_user_provider
`UserProvider` が代替ユーザープロバイダーのフックを使うかどうかを制御します。

#### 引数

- `$use_alternative`: (bool) 代替パスを使うかどうか。既定値は `false` です。

#### 補足

`true` を返すと、`UserProvider` は通常の WordPress ユーザー関数の代わりに `*_alternative` フックを呼び出します。

#### 使用例

```php
add_filter('slc_filter_use_alternative_user_provider', '__return_true');
```

## 置き換え用フィルター

### slc_filter_get_userdata
通常モードで `UserProvider::get_userdata()` が返す `get_userdata()` の結果を変更します。

#### 引数

- `$userdata`: (WP_User|false) WordPress が返したユーザーオブジェクト。
- `$user_id`: (int) ユーザーID。

#### 使用例

```php
add_filter('slc_filter_get_userdata', function($userdata, $user_id) {
    // WP_User オブジェクトに対して独自の処理を行う
    return $userdata;
}, 10, 2);
```

### slc_filter_get_userdata_alternative
代替モードでユーザーデータを返します。

#### 引数

- `$user_id`: (int) ユーザーID。

#### 補足

代替の保存先や形式に合わせたユーザーデータを返してください。 `WP_User` クラスを模倣したオブジェクトを返すことを推奨します。詳細は後述の [カスタムユーザーオブジェクトの例](#カスタムユーザーオブジェクトの例) を参照してください。

#### 使用例

```php
add_filter('slc_filter_get_userdata_alternative', function($user_id) {
    // 代替ストレージから独自のユーザーオブジェクトを返す
    return new MyCustomUser($user_id);
});
```

### slc_filter_get_user_meta
通常モードで `UserProvider::get_user_meta()` が返す `get_user_meta()` の結果を変更します。

#### 引数

- `$meta_value`: (mixed) WordPress が返したメタ値。
- `$user_id`: (int) ユーザーID。
- `$key`: (string) メタキー。
- `$single`: (bool) 単一値かどうか。

#### 使用例

```php
add_filter('slc_filter_get_user_meta', function($meta_value, $user_id, $key, $single) {
    // WordPress から返されたメタ値を変更する
    return $meta_value;
}, 10, 4);
```

### slc_filter_get_user_meta_alternative
代替モードでユーザーメタを返します。

#### 引数

- `$user_id`: (int) ユーザーID。
- `$key`: (string) メタキー。
- `$single`: (bool) 単一値かどうか。

#### 補足

代替の保存先から取得したメタ値を返してください。

#### 使用例

```php
add_filter('slc_filter_get_user_meta_alternative', function($user_id, $key, $single) {
    // 独自のテーブルからメタ情報を取得する
    global $wpdb;
    $value = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM my_custom_table WHERE user_id = %d AND meta_key = %s", $user_id, $key));

    $value = maybe_unserialize($value);
    return $single ? $value : [$value];
}, 10, 3);
```

### slc_filter_update_user_meta
通常モードで `UserProvider::update_user_meta()` が返す `update_user_meta()` の結果を変更します。

#### 引数

- `$updated`: (bool|int) WordPress が返した結果。
- `$user_id`: (int) ユーザーID。
- `$meta_key`: (string) メタキー。
- `$meta_value`: (mixed) 新しいメタ値。
- `$prev_value`: (mixed) 以前のメタ値。

#### 使用例

```php
add_filter('slc_filter_update_user_meta', function($updated, $user_id, $meta_key, $meta_value, $prev_value) {
    // メタ情報が更新された際に追加のアクションを実行する
    return $updated;
}, 10, 5);
```

### slc_filter_update_user_meta_alternative
代替モードでユーザーメタ更新を実行します。

#### 引数

- `$user_id`: (int) ユーザーID。
- `$meta_key`: (string) メタキー。
- `$meta_value`: (mixed) 新しいメタ値。
- `$prev_value`: (mixed) 以前のメタ値。

#### 補足

代替実装の更新結果を返してください。

#### 使用例

```php
add_filter('slc_filter_update_user_meta_alternative', function($user_id, $meta_key, $meta_value, $prev_value) {
    // 独自のテーブルのメタ情報を更新する
    global $wpdb;
    $result = $wpdb->update(
        'my_custom_table',
        ['meta_value' => maybe_serialize($meta_value)],
        ['user_id' => $user_id, 'meta_key' => $meta_key]
    );
    return $result !== false;
}, 10, 4);
```

### slc_filter_delete_user_meta
通常モードで `UserProvider::delete_user_meta()` が返す `delete_user_meta()` の結果を変更します。

#### 引数

- `$deleted`: (bool) WordPress が返した結果。
- `$user_id`: (int) ユーザーID。
- `$meta_key`: (string) メタキー。
- `$meta_value`: (mixed) 削除対象のメタ値。

#### 使用例

```php
add_filter('slc_filter_delete_user_meta', function($deleted, $user_id, $meta_key, $meta_value) {
    // メタ情報が削除された際に追加のアクションを実行する
    return $deleted;
}, 10, 4);
```

### slc_filter_delete_user_meta_alternative
代替モードでユーザーメタ削除を実行します。

#### 引数

- `$user_id`: (int) ユーザーID。
- `$meta_key`: (string) メタキー。
- `$meta_value`: (mixed) 削除対象のメタ値。

#### 補足

代替実装の削除結果を返してください。

#### 使用例

```php
add_filter('slc_filter_delete_user_meta_alternative', function($user_id, $meta_key, $meta_value) {
    // 独自のテーブルからメタ情報を削除する
    global $wpdb;
    $result = $wpdb->delete('my_custom_table', ['user_id' => $user_id, 'meta_key' => $meta_key]);
    return $result !== false;
}, 10, 3);
```

### slc_filter_get_current_user_id
通常モードで `UserProvider::get_current_user_id()` が返す現在のユーザーIDを変更します。

#### 引数

- `$user_id`: (int) 現在のユーザーID。

#### 使用例

```php
add_filter('slc_filter_get_current_user_id', function($user_id) {
    // 現在のユーザーIDの取得ロジックをオーバーライドする
    return $user_id;
});
```

### slc_filter_get_current_user_id_alternative
代替モードで現在のユーザーIDを返します。

#### 引数

- `$current_user_id`: (mixed) `UserProvider` から渡される現在のユーザーIDのプレースホルダー。

#### 補足

現在ユーザーとして扱うIDを返してください。

#### 使用例

```php
add_filter('slc_filter_get_current_user_id_alternative', function($current_user_id) {
    // 独自のセッションやクッキーから現在のユーザーIDを返す
    return isset($_SESSION['my_custom_user_id']) ? $_SESSION['my_custom_user_id'] : 0;
});
```

### slc_filter_is_user_id_valid
通常モードでユーザーIDの妥当性判定を変更します。

#### 引数

- `$is_valid`: (bool) ユーザーIDが有効かどうか。
- `$user_id`: (mixed) 判定対象のユーザーID。

#### 使用例

```php
add_filter('slc_filter_is_user_id_valid', function($is_valid, $user_id) {
    // ユーザーIDの妥当性チェックを独自に行う
    return $is_valid;
}, 10, 2);
```

### slc_filter_is_user_id_valid_alternative
代替モードでユーザーIDの妥当性を判定します。

#### 引数

- `$user_id`: (mixed) 判定対象のユーザーID。

#### 使用例

```php
add_filter('slc_filter_is_user_id_valid_alternative', function($user_id) {
    // 独自のテーブルにユーザーIDが存在するか確認する
    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM my_custom_table WHERE user_id = %d", $user_id)) > 0;
});
```

### slc_filter_get_linked_userids_by_roles_alternative
代替モードで指定ロールに紐づくユーザーIDを返します。

#### 引数

- `$roles`: (array) 取得対象のロール配列。

#### 補足

指定ロールに対して LINE 連携済みのユーザーID配列を返してください。

#### 使用例

```php
add_filter('slc_filter_get_linked_userids_by_roles_alternative', function($roles) {
    // LINE連携済みかつ指定されたロールを持つ、独自のユーザーID一覧を返す
    global $wpdb;
    // ロールが独自のテーブルでステータスコードとして管理されていると仮定
    return $wpdb->get_col("SELECT user_id FROM my_custom_table WHERE status IN (1, 2) AND has_line_link = 1");
});
```


### slc_wp_user_query_alternative
代替モードで `WP_User_Query` 相当の検索結果を返します。WordPress の `wp_users` とは別の保存先や、独自のユーザー管理システムに合わせて検索条件を解釈できます。

#### 引数

- `$args`: (array) `WP_User_Query` と同様の検索条件。

#### 補足

コールバック側で `role__in`、`role__not_in`、`include`、`search`、`search_columns`、`meta_query` を解釈し、ユーザー ID の配列を返してください。

#### 使用例

```php
add_filter('slc_wp_user_query_alternative', function($args) {
    // $args を使って独自に検索し、該当するユーザーID配列を返す
    $query_args = $args;
    $query_args['fields'] = 'ID';

    return get_users($query_args);
});
```

### slc_get_user_from_line_id
LINE ID をキーに保存先を検索し、該当するユーザーを返します。

#### 引数

- `$user`: (false|object) フィルターに渡される初期値です。通常は `false` が渡されるため、見つかったユーザーオブジェクトで置き換えてください。
- `$secret_prefix`: (string) LINE チャンネルを識別するプレフィックス。
- `$line_id`: (string) LINE ユーザー ID。

#### 補足

LINE ID とチャネル固有の識別子を使って保存先を検索し、見つかったユーザーを返してください。該当がなければ `false` を返します。

#### 使用例

```php
add_filter('slc_get_user_from_line_id', function($user, $secret_prefix, $line_id) {
    // LINE ID で保存先を検索し、見つかったユーザーオブジェクト、または false を返す
    return /* 見つかったユーザーオブジェクト、または false */;
}, 10, 3);
```

### slc_roles
ロール一覧に独自のステータスや疑似ロールを追加します。

#### 引数

- `$roles`: (array) 既存のロール一覧。

#### 補足

独自のステータスは WordPress のロールと 1 対 1 で対応しなくても構いませんが、このフックにより `member_status_0` のような疑似ロールをロール一覧へ追加できます。

#### 使用例

```php
add_filter('slc_roles', function($roles) {
    // 独自のステータス表示名を追加・調整する
    $roles['member_status_99'] = [
        'name' => 'VIP メンバー',
    ];

    return $roles;
});
```

## カスタムユーザーオブジェクトの例

代替モードを使用する場合、`slc_filter_get_userdata_alternative` は `WP_User` を模倣したオブジェクトを返す必要があります。最低限、`ID` プロパティと `roles` 配列を持っている必要があります。

```php
class MyCustomUser {
    public int $ID;
    public array $roles = [];
    public string $display_name;
    public string $user_email;

    public function __construct($user_id) {
        $this->ID = $user_id;
        // 独自ソースからデータを読み込む
        $this->display_name = 'Custom User';
        $this->roles = ['custom_role'];
    }

    public function exists(): bool {
        return $this->ID > 0;
    }

    // オプション: 他の WP_User プロパティを扱うために __get を実装
    public function __get($name) {
        return $this->$name ?? null;
    }
}
```
