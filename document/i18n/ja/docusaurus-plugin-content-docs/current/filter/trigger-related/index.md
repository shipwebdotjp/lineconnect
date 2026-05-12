# トリガー関連フィルター

このグループには、トリガーフックの登録やアクションフック引数の前処理を拡張するフックが含まれています。

## 対象フック

- [slc_filter_predefined_action_hooks](#slc_filter_predefined_action_hooks): 事前定義済みのWordPressアクションフック一覧を拡張します。
- [slc_filter_preprocess_action_hook](#slc_filter_preprocess_action_hook): ディスパッチ前のアクションフック引数を前処理します。

## slc_filter_predefined_action_hooks

事前定義済みの WordPress アクションフック一覧を拡張したいときに使用します。`ActionHooks::init()` で登録対象のフック一覧を受け取り、そこに独自フックを追加できます。

### 引数

- `$hooks`: (array) 登録対象のフック名一覧。

### 例

独自のアクションフックを追加して、トリガーで選択できるようにする例です。

```php
function my_filter_predefined_action_hooks( $hooks ) {
	$hooks[] = 'my_custom_hook';

	return $hooks;
}
add_filter( 'slc_filter_predefined_action_hooks', 'my_filter_predefined_action_hooks' );
```

## slc_filter_preprocess_action_hook

Action Hook がディスパッチされる直前に、フック名や引数の内容を調整したいときに使用します。`ActionHook::process()` の最初で呼ばれるため、トリガー条件の判定やアクション実行の前に値を整形できます。

### 引数

- `$action_hook_args`: (array) アクションフックのペイロード。通常は次のキーを持ちます。
    - `hook`: (string) フック名。
    - `args`: (array) フック引数。

### 例

特定のカスタムフックに対して、引数へ追加情報を差し込む例です。

```php
function my_filter_preprocess_action_hook( $action_hook_args ) {
	if ( empty( $action_hook_args['hook'] ) || 'my_custom_hook' !== $action_hook_args['hook'] ) {
		return $action_hook_args;
	}

	if ( empty( $action_hook_args['args'] ) || ! is_array( $action_hook_args['args'] ) ) {
		$action_hook_args['args'] = array();
	}

	$action_hook_args['args']['source'] = 'manual';

	return $action_hook_args;
}
add_filter( 'slc_filter_preprocess_action_hook', 'my_filter_preprocess_action_hook' );
```