# Action Hook トリガー仕様書

## 概要

WordPressのアクションフックをトリガーとして使用し、特定のフックが発火した際にアクションを実行する機能。

## スキーマ構造（2フォームアーキテクチャ）

既存のトリガーと同様に2フォーム構成で保存する。

- **form[0]**: タイプ選択（`type: "action_hook"` を既存の `webhook`, `schedule` に追加）
- **form[1]**: action_hook 専用の設定スキーマ（`triggers.items` を差し替え、schedule と同様に `audience` を追加）

## 対象アクションフック

以下の10個の主要フックを事前定義。常に `add_action()` で登録する。

| フック名 | 説明 | 引数 |
|---------|------|------|
| `user_register` | ユーザー登録時 | `($user_id)` |
| `wp_login` | ログイン時 | `($user_login, $user)` |
| `wp_logout` | ログアウト時 | `()` |
| `profile_update` | プロフィール更新時 | `($user_id, $old_user_data)` |
| `delete_user` | ユーザー削除時 | `($user_id, $reassign)` |
| `save_post` | 投稿保存時 | `($post_ID, $post, $update)` |
| `comment_post` | コメント投稿時 | `($comment_ID, $comment_approved, $commentdata)` |
| `activated_plugin` | プラグイン有効化時 | `($plugin, $network_wide)` |
| `deactivated_plugin` | プラグイン無効化時 | `($plugin, $network_wide)` |
| `switch_theme` | テーマ切り替え時 | `($new_name, $new_theme, $old_theme)` |

## スキーマ定義

```json
{
  "type": "object",
  "properties": {
    "hook": {
      "type": "string",
      "title": "Event type",
      "description": "WordPress action hook to trigger on",
      "oneOf": [
        {"const": "user_register", "title": "User registration"},
        {"const": "wp_login", "title": "Login"},
        {"const": "wp_logout", "title": "Logout"},
        {"const": "profile_update", "title": "Profile update"},
        {"const": "delete_user", "title": "User deletion"},
        {"const": "save_post", "title": "Post saved"},
        {"const": "comment_post", "title": "Comment posted"},
        {"const": "activated_plugin", "title": "Plugin activated"},
        {"const": "deactivated_plugin", "title": "Plugin deactivated"},
        {"const": "switch_theme", "title": "Theme switched"}
      ]
    },
    "audience_mode": {
      "type": "string",
      "title": "Audience mode",
      "description": "Choose recipients for LINE messages",
      "oneOf": [
        {"const": "current_user", "title": "Use related user"},
        {"const": "standard", "title": "Specify audience"}
      ]
    },
    "current_user_channels": {
      "type": "array",
      "title": "Target channels",
      "description": "Channels where the related user exists",
      "uniqueItems": true,
      "items": {"type": "string", "oneOf": []}
    }
  },
  "dependencies": {
    "hook": {
      "oneOf": [
        {
          "properties": {
            "hook": {"const": "save_post"},
            "save_post": {
              "type": "object",
              "properties": {
                "post_type": {
                  "type": "array",
                  "title": "Post types",
                  "items": {"type": "string"},
                  "default": ["post", "page"]
                },
                "post_status": {
                  "type": "array",
                  "title": "Post status",
                  "items": {"type": "string"},
                  "default": ["publish", "draft", "pending"]
                }
              }
            }
          }
        },
        {
          "properties": {
            "hook": {"const": "comment_post"},
            "comment_post": {
              "type": "object",
              "properties": {
                "post_type": {
                  "type": "array",
                  "title": "Target post types",
                  "items": {"type": "string"}
                }
              }
            }
          }
        },
        {
          "properties": {
            "hook": {"const": "wp_login"},
            "wp_login": {
              "type": "object",
              "properties": {
                "role": {
                  "type": "array",
                  "title": "Target user roles",
                  "items": {"type": "string"}
                }
              }
            }
          }
        }
      ]
    },
    "audience_mode": {
      "oneOf": [
        {
          "properties": {
            "audience_mode": {"const": "current_user"},
            "current_user_channels": {"$ref": "#/definitions/secret_prefix"}
          },
          "required": ["current_user_channels"]
        },
        {
          "properties": {
            "audience_mode": {"const": "standard"},
            "audience": {
              "type": "object",
              "title": "Audience",
              "properties": {
                "condition": {"$ref": "#/definitions/audience_condition"}
              }
            }
          },
          "required": ["audience"]
        }
      ]
    }
  },
  "definitions": {
    "audience_condition": {"$ref": "src/PostType/Audience/Schema.php#/definitions/condition"},
    "secret_prefix": {
      "type": "array",
      "uniqueItems": true,
      "items": {"type": "string", "oneOf": []}
    }
  }
}
```

### トリガーデータ例
```
{
  "0": {
    "type": "action_hook"
  },
  "1": {
    "triggers": [
      {
        "wp_login": {
          "role": []
        },
        "hook": "wp_login"
      }
    ],
    "action": [
      {
        "action_name": "get_text_message",
        "response_return_value": true,
        "parameters": {
          "body": "新たなログインがありました。"
        }
      }
    ],
    "chain": [],
    "current_user_channels": [
      "04f7"
    ],
    "audience_mode": "current_user"
  }
}
```


### conditions のデフォルト値

`save_post` の場合、条件を省略すると以下のデフォルトが適用される：

- `post_type`: `["post", "page"]` （`revision` を除外）
- `post_status`: `["publish", "draft", "pending"]` （autosaveを除外）

その他のフック（`comment_post`, `wp_login`）は条件省略時は全件対象。

## 引数マッピング定義

各フックの引数は以下のように `$injection_data['action_hook']` に格納される。
オブジェクト引数（`WP_User`, `WP_Post`, `WP_Theme`）はオブジェクトのまま格納し、`PlaceholderReplacer` でプロパティアクセス時に変換する。

```json
{
  "user_register": {
    "user_id": {"index": 0, "type": "integer"}
  },
  "wp_login": {
    "user_login": {"index": 0, "type": "string"},
    "user": {"index": 1, "type": "object", "class": "WP_User"}
  },
  "wp_logout": {},
  "profile_update": {
    "user_id": {"index": 0, "type": "integer"},
    "old_user_data": {"index": 1, "type": "object", "class": "WP_User"}
  },
  "delete_user": {
    "user_id": {"index": 0, "type": "integer"},
    "reassign": {"index": 1, "type": "integer"}
  },
  "save_post": {
    "post_id": {"index": 0, "type": "integer"},
    "post": {"index": 1, "type": "object", "class": "WP_Post"},
    "is_update": {"index": 2, "type": "boolean"}
  },
  "comment_post": {
    "comment_id": {"index": 0, "type": "integer"},
    "comment_approved": {"index": 1, "type": "boolean"},
    "comment_data": {"index": 2, "type": "array"}
  },
  "activated_plugin": {
    "plugin": {"index": 0, "type": "string"},
    "network_wide": {"index": 1, "type": "boolean"}
  },
  "deactivated_plugin": {
    "plugin": {"index": 0, "type": "string"},
    "network_wide": {"index": 1, "type": "boolean"}
  },
  "switch_theme": {
    "new_name": {"index": 0, "type": "string"},
    "new_theme": {"index": 1, "type": "object", "class": "WP_Theme"},
    "old_theme": {"index": 2, "type": "object", "class": "WP_Theme"}
  }
}
```

## 実行フロー

```
1. WordPressフック発火
   ↓
2. ActionHooks::handle_{$hook_name}() 呼び出し
   ↓
3. try-catch で囲み、例外時はログ記録して元のWP処理を継続
   ↓
4. アクティブなトリガー投稿を投稿ID順で取得
   ↓
5. ActionHook::check_condition() でフック特有の発火条件をチェック（conditions/hook）
   ↓
6. 対象オーディエンスの決定
   - audience_mode == "current_user" → 関連ユーザーIDを自動推論してオーディエンス条件を構築
   - audience_mode == "standard" → trigger['audience']['condition'] を使用
   ↓
7. audience の有無で分岐（schedule と同じパターン）
   - audience あり → Audience::get_audience_by_condition() → ActionFlow::execute_actionflow_by_audience()　（action_hook_args を第3引数で渡す）
   - audience なし → Action::do_action()（action_hook_args を第8引数で渡す）
   ↓
8. アクションチェーン実行（{{$.action_hook.*}} で引数データを参照可能）
```

### エラー処理

フックハンドラー内の例外は `try-catch` で捕捉し、`error_log()` で記録する。元のWordPress処理（ユーザー登録、投稿保存等）は継続される。

### 同一フックに対する複数トリガー

複数のトリガー投稿が同じフックをリッスンしている場合、投稿ID順で逐次実行する（webhook/schedule と同じ）。

## 関連ユーザーの自動推論

`audience_mode == "current_user"` の場合、フックに関連するユーザーIDを自動推論する。`get_current_user_id()` ではなく、フック引数から意味的に適切なユーザーIDを使用する。

| フック名 | 推論ユーザー | 推論方法 |
|---------|------------|---------|
| `user_register` | 登録されたユーザー | `$user_id`（第1引数） |
| `wp_login` | ログインしたユーザー | `$user->ID`（第2引数） |
| `wp_logout` | ログアウトしたユーザー | `get_current_user_id()`（フック発火時点ではまだ有効） |
| `profile_update` | 更新されたユーザー | `$user_id`（第1引数） |
| `delete_user` | 削除されるユーザー | `$user_id`（第1引数） |
| `save_post` | 投稿者 | `$post->post_author`（第2引数） |
| `comment_post` | コメント投稿者 | コメントからWPユーザーIDを取得（未登録ユーザーの場合は0） |
| `activated_plugin` | 現在の管理者 | `get_current_user_id()` |
| `deactivated_plugin` | 現在の管理者 | `get_current_user_id()` |
| `switch_theme` | 現在の管理者 | `get_current_user_id()` |

推論したユーザーIDが0（未ログイン・未登録）の場合、オーディエンスなしでアクションを実行する

```php
// 例: save_post の場合、current_user_channels = ["dev_"]
$condition = [
    'conditions' => [
        [
            'type' => 'wpUserId',
            'wpUserId' => [$post->post_author]
        ],
        [
            'type' => 'channel',
            'secret_prefix' => ['dev_']
        ]
    ],
    'operator' => 'and'
];
$recepient = Audience::get_audience_by_condition($condition);
```

## 実装ファイル構成

### 新規作成

| ファイル | 説明 |
|---------|------|
| `src/Trigger/ActionHook.php` | トリガー条件評価・オーディエンス決定・アクション実行 |
| `src/Core/ActionHooks.php` | WordPressフックへの `add_action()` 登録・ハンドラー定義 |
| `docs/features/trigger-action-hook.md` | このドキュメント |

### 編集

| ファイル | 編集内容 |
|---------|---------|
| `src/PostType/Trigger/Trigger.php` | `get_types()` に action_hook を追加、スキーマ定義追加 |
| `src/Action/Action.php` | `do_action()` に `$action_hook_args` 引数を追加、`$injection_data['action_hook']` を設定 |

### 責務分割

**`src/Core/ActionHooks.php`**（WordPressフック管理層）:
- `init()`: 全10フックの `add_action()` 登録
- 各フックのハンドラーメソッド（`handle_save_post()` 等）：フック引数の受け取りと正規化
- `lineconnect_custom_hook` の `add_action()` 登録
- `lineconnect_predefined_action_hooks` フィルターの適用

**`src/Trigger/ActionHook.php`**（トリガー評価層）:
- `process()`: 共通ハンドラー。トリガー投稿の検索・条件評価・アクション実行
- `check_condition()`: フック固有の発火条件チェック
- `resolve_related_user()`: 関連ユーザーIDの自動推論
- `build_audience_condition()`: audience_mode に基づくオーディエンス条件構築

## UI Schema（RJSF）

```json
{
  "ui:schema": {
    "hook": {
      "ui:widget": "select"
    },
    "audience_mode": {
      "ui:widget": "radio"
    },
    "current_user_channels": {
      "ui:widget": "select",
      "ui:options": {
        "multiple": true
      }
    },
    "audience": {
      "condition": {
        "ui:widget": "audienceSelector"
      }
    }
  }
}
```

`current_user_channels` は既存の `#/definitions/secret_prefix` 定義を流用し、既存のチャネルセレクタUIと同じウィジェットを使用する。

## API 変更点

### Action::do_action()

```php
// Before
static function do_action($actions, $chains, $event = null, $secret_prefix = null, $scenario_id = null, ?InteractionSession $session = null)

// After
static function do_action($actions, $chains, $event = null, $secret_prefix = null, $scenario_id = null, ?InteractionSession $session = null, $action_hook_args = null)
```

`$action_hook_args` は `$injection_data['action_hook']` として格納され、アクションチェーン内で `{{$.action_hook.*}}` で参照可能になる。

### ActionHooks ハンドラーメソッドシグネチャ

全てのハンドラーメソッドは実際のフック引数を受け取り、それを正規化して共通ハンドラー `ActionHook::process()` に渡す。

```php
// src/Core/ActionHooks.php
public static function handle_save_post($post_ID, $post = null, $update = null) {
    $action_hook_args = [
        'hook_name' => 'save_post',
        'args' => [
          'post_id' => post_ID,
          'post' => $post,
          'update' => $update
        ]
    ];
    ActionHook::process($action_hook_args);
}
```

### 汎用カスタムフック受け口

事前定義フックもカスタムフックも、共通ハンドラー `ActionHook::process()` を通じて処理する。

```php
// src/Core/ActionHooks.php
add_action('lineconnect_custom_hook', [ActionHook::class, 'handle_custom_hook'], 10, 2);

public static function handle_custom_hook(string $hook_name, array $args = []): void {
    $action_hook_args = [
        'hook_name' => $hook_name,
        'args' => $args
    ];
    ActionHook::process($action_hook_args);
}
```

外部からの呼び出し例：
```php
do_action('lineconnect_custom_hook', 'my_custom_hook', [
    'arg1' => 'a',
    'arg2' => 1,
]);
```

## フック引数の使用方法

アクションチェーンやメッセージテンプレート内で以下のように参照可能：

```
{{$.action_hook.post_id}}           ← save_postの場合
{{$.action_hook.user_login}}        ← wp_loginの場合
{{$.action_hook.comment_id}}        ← comment_postの場合
```

## 今後の拡張

カスタムフックの追加は `lineconnect_predefined_action_hooks` フィルターを通して可能。
`handler` の指定は不要（全て共通ハンドラーで処理）。

```php
add_filter('lineconnect_predefined_action_hooks', function($hooks) {
    $hooks['my_custom_hook'] = [
        'title' => 'カスタムフック',
        'args' => ['arg1', 'arg2'],
    ];
    return $hooks;
});
```

定義されたカスタムフックは `ActionHooks::init()` で `add_action()` に自動登録される。
外部からは `do_action('lineconnect_custom_hook', 'my_custom_hook', [...])` でも呼び出し可能。