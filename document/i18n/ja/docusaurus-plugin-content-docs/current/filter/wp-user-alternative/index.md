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

## 制御用フィルター

### slc_filter_use_alternative_user_provider
`UserProvider` が代替ユーザープロバイダーのフックを使うかどうかを制御します。

#### 引数

- `$use_alternative`: (bool) 代替パスを使うかどうか。既定値は `false` です。

#### 補足

`true` を返すと、`UserProvider` は通常の WordPress ユーザー関数の代わりに `*_alternative` フックを呼び出します。

## 置き換え用フィルター

### slc_filter_get_userdata
通常モードで `UserProvider::get_userdata()` が返す `get_userdata()` の結果を変更します。

#### 引数

- `$userdata`: (WP_User|false) WordPress が返したユーザーオブジェクト。
- `$user_id`: (int) ユーザーID。

### slc_filter_get_userdata_alternative
代替モードでユーザーデータを返します。

#### 引数

- `$user_id`: (int) ユーザーID。

#### 補足

代替の保存先や形式に合わせたユーザーデータを返してください。

### slc_filter_get_user_meta
通常モードで `UserProvider::get_user_meta()` が返す `get_user_meta()` の結果を変更します。

#### 引数

- `$meta_value`: (mixed) WordPress が返したメタ値。
- `$user_id`: (int) ユーザーID。
- `$key`: (string) メタキー。
- `$single`: (bool) 単一値かどうか。

### slc_filter_get_user_meta_alternative
代替モードでユーザーメタを返します。

#### 引数

- `$user_id`: (int) ユーザーID。
- `$key`: (string) メタキー。
- `$single`: (bool) 単一値かどうか。

#### 補足

代替の保存先から取得したメタ値を返してください。

### slc_filter_update_user_meta
通常モードで `UserProvider::update_user_meta()` が返す `update_user_meta()` の結果を変更します。

#### 引数

- `$updated`: (bool|int) WordPress が返した結果。
- `$user_id`: (int) ユーザーID。
- `$meta_key`: (string) メタキー。
- `$meta_value`: (mixed) 新しいメタ値。
- `$prev_value`: (mixed) 以前のメタ値。

### slc_filter_update_user_meta_alternative
代替モードでユーザーメタ更新を実行します。

#### 引数

- `$user_id`: (int) ユーザーID。
- `$meta_key`: (string) メタキー。
- `$meta_value`: (mixed) 新しいメタ値。
- `$prev_value`: (mixed) 以前のメタ値。

#### 補足

代替実装の更新結果を返してください。

### slc_filter_delete_user_meta
通常モードで `UserProvider::delete_user_meta()` が返す `delete_user_meta()` の結果を変更します。

#### 引数

- `$deleted`: (bool) WordPress が返した結果。
- `$user_id`: (int) ユーザーID。
- `$meta_key`: (string) メタキー。
- `$meta_value`: (mixed) 削除対象のメタ値。

### slc_filter_delete_user_meta_alternative
代替モードでユーザーメタ削除を実行します。

#### 引数

- `$user_id`: (int) ユーザーID。
- `$meta_key`: (string) メタキー。
- `$meta_value`: (mixed) 削除対象のメタ値。

#### 補足

代替実装の削除結果を返してください。

### slc_filter_get_current_user_id
通常モードで `UserProvider::get_current_user_id()` が返す現在のユーザーIDを変更します。

#### 引数

- `$user_id`: (int) 現在のユーザーID。

### slc_filter_get_current_user_id_alternative
代替モードで現在のユーザーIDを返します。

#### 引数

- `$current_user_id`: (mixed) `UserProvider` から渡される現在のユーザーIDのプレースホルダー。

#### 補足

現在ユーザーとして扱うIDを返してください。

### slc_filter_is_user_id_valid
通常モードでユーザーIDの妥当性判定を変更します。

#### 引数

- `$is_valid`: (bool) ユーザーIDが有効かどうか。
- `$user_id`: (mixed) 判定対象のユーザーID。

### slc_filter_is_user_id_valid_alternative
代替モードでユーザーIDの妥当性を判定します。

#### 引数

- `$user_id`: (mixed) 判定対象のユーザーID。

### slc_filter_get_linked_userids_by_roles_alternative
代替モードで指定ロールに紐づくユーザーIDを返します。

#### 引数

- `$roles`: (array) 取得対象のロール配列。

#### 補足

指定ロールに対して LINE 連携済みのユーザーID配列を返してください。
