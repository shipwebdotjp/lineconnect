# インタラクション関連フィルター

このグループには、インタラクション定義、セッション、応答メッセージをカスタマイズするフックが含まれています。

## 対象フック

- [slc_filter_interaction_definition](#slc_filter_interaction_definition): インタラクション定義の生データを調整します。
- [slc_filter_interaction_session_load](#slc_filter_interaction_session_load): 読み込み後のインタラクションセッションを調整します。
- [slc_filter_interaction_session_save](#slc_filter_interaction_session_save): 保存前のインタラクションセッションのDBデータを調整します。
- [slc_filter_interaction_message](#slc_filter_interaction_message): インタラクションハンドラが返す応答メッセージを調整します。
- [slc_filter_interaction_message_cancel_confirm](#slc_filter_interaction_message_cancel_confirm): キャンセル確認メッセージを調整します。
- [slc_filter_interaction_message_cancel_request](#slc_filter_interaction_message_cancel_request): キャンセル要求メッセージを調整します。
- [slc_filter_interaction_normalize](#slc_filter_interaction_normalize): 正規化済みのユーザー入力を調整します。
- [slc_filter_interaction_validate](#slc_filter_interaction_validate): バリデーション結果を調整します。

## slc_filter_interaction_definition

インタラクション定義の JSON データを読み込んだ直後に、その内容を調整したいときに使用します。`InteractionDefinition::from_post()` の中で、`InteractionDefinition` オブジェクトを生成する前に実行されます。

### 引数

- `$data`: (array) インタラクション定義データ。`steps`、`timeoutMinutes`、`runPolicy` などを含む配列です。
- `$post_id`: (int) インタラクション投稿 ID。
- `$form_version`: (int|string|null) 読み込まれたフォームのバージョン。

### 例

特定バージョンのインタラクションに対して、タイムアウト設定を上書きする例です。

```php
function my_filter_interaction_definition($data, $post_id, $form_version) {
    if ((int) $form_version === 2) {
        $data['timeoutMinutes'] = 30;
        $data['timeoutRemind'] = 5;
    }

    return $data;
}
add_filter('slc_filter_interaction_definition', 'my_filter_interaction_definition', 10, 3);
```

## slc_filter_interaction_session_load

DB から読み込んだインタラクションセッションを調整したいときに使用します。`InteractionSession::from_db_row()` の戻り値に対して適用されます。

### 引数

- `$session`: (InteractionSession) 読み込まれたセッションオブジェクト。

### 例

読み込み直後に、特定条件のセッションへ独自の状態を付与する例です。

```php
function my_filter_interaction_session_load($session) {
    if ($session->get_status() === 'active') {
        // 必要に応じてセッションに独自処理を追加します。
    }

    return $session;
}
add_filter('slc_filter_interaction_session_load', 'my_filter_interaction_session_load');
```

## slc_filter_interaction_session_save

セッションを DB に保存する前に、保存内容を調整したいときに使用します。`SessionRepository::save()` の中で、`INSERT` / `UPDATE` 前に適用されます。

### 引数

- `$data`: (array) 保存用の連想配列。`channel_prefix`、`line_user_id`、`status`、`answers` などを含みます。
- `$session`: (InteractionSession) 保存対象のセッションオブジェクト。

### 例

保存時に `answers` の一部を書き換える例です。

```php
function my_filter_interaction_session_save($data, $session) {
    if (isset($data['answers'])) {
        $answers = json_decode($data['answers'], true);
        if (is_array($answers)) {
            $answers['source'] = 'interaction';
            $data['answers'] = wp_json_encode($answers);
        }
    }

    return $data;
}
add_filter('slc_filter_interaction_session_save', 'my_filter_interaction_session_save', 10, 2);
```

## slc_filter_interaction_message

インタラクションの各ステップで返されるメッセージ配列を調整したいときに使用します。`presentStep()` と `handle()` の最後で、返却メッセージに対して適用されます。

### 引数

- `$messages`: (array) `MessageBuilder` が生成したメッセージの配列。
- `$session`: (InteractionSession) 現在のセッション。
- `$event`: (object) LINE のイベントオブジェクト。

### 例

送信前にメッセージの末尾へ注意書きを追加する例です。

```php
function my_filter_interaction_message($messages, $session, $event) {
    $messages[] = array(
        'type' => 'text',
        'text' => 'このメッセージはカスタマイズされています。',
    );

    return $messages;
}
add_filter('slc_filter_interaction_message', 'my_filter_interaction_message', 10, 3);
```

## slc_filter_interaction_message_cancel_confirm

キャンセル確認を求めるメッセージを調整したいときに使用します。キャンセルワードが検出されたときなどに、`cancelConfirm` ステップのメッセージをカスタマイズできます。

### 引数

- `$messages`: (array) キャンセル確認用メッセージの配列。
- `$session`: (InteractionSession) 現在のセッション。
- `$event`: (object) LINE のイベントオブジェクト。

### 例

キャンセル確認メッセージに補足案内を追加する例です。

```php
function my_filter_interaction_message_cancel_confirm($messages, $session, $event) {
    $messages[] = array(
        'type' => 'text',
        'text' => '入力を中止しますか？',
    );

    return $messages;
}
add_filter('slc_filter_interaction_message_cancel_confirm', 'my_filter_interaction_message_cancel_confirm', 10, 3);
```

## slc_filter_interaction_message_cancel_request

キャンセル実行時に返すメッセージを調整したいときに使用します。キャンセルが確定し、セッションを削除する直前に適用されます。

### 引数

- `$messages`: (array) キャンセル実行用メッセージの配列。
- `$session`: (InteractionSession) 現在のセッション。
- `$event`: (object) LINE のイベントオブジェクト。

### 例

キャンセル完了時の文言を置き換える例です。

```php
function my_filter_interaction_message_cancel_request($messages, $session, $event) {
    $messages = array(
        array(
            'type' => 'text',
            'text' => 'キャンセルしました。',
        ),
    );

    return $messages;
}
add_filter('slc_filter_interaction_message_cancel_request', 'my_filter_interaction_message_cancel_request', 10, 3);
```

## slc_filter_interaction_normalize

ユーザー入力の正規化結果を調整したいときに使用します。入力値がバリデーションされる前に適用されます。

### 引数

- `$normalized_input`: (string) `InputNormalizer::normalize()` が返した正規化済み文字列。
- `$step`: (StepDefinition) 現在のステップ定義。
- `$session`: (InteractionSession) 現在のセッション。
- `$event`: (object) LINE のイベントオブジェクト。

### 例

正規化後に全角スペースを除去する例です。

```php
function my_filter_interaction_normalize($normalized_input, $step, $session, $event) {
    return trim(str_replace('　', ' ', $normalized_input));
}
add_filter('slc_filter_interaction_normalize', 'my_filter_interaction_normalize', 10, 4);
```

## slc_filter_interaction_validate

ユーザー入力のバリデーション結果を調整したいときに使用します。`Validator::validate()` の戻り値をカスタマイズできます。

### 引数

- `$validation_result`: (ValidationResult) バリデーション結果オブジェクト。
- `$step`: (StepDefinition) 現在のステップ定義。
- `$session`: (InteractionSession) 現在のセッション。
- `$event`: (object) LINE のイベントオブジェクト。

### 例

特定ステップでは、バリデーションエラーを無効化する例です。

```php
function my_filter_interaction_validate($validation_result, $step, $session, $event) {
    if ($step->get_id() === 'optional_step') {
        return new \Shipweb\LineConnect\Interaction\ValidationResult(true, array());
    }

    return $validation_result;
}
add_filter('slc_filter_interaction_validate', 'my_filter_interaction_validate', 10, 4);
```
