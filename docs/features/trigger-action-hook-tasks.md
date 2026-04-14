# Action Hook トリガー 実装タスクリスト

このファイルは docs/features/trigger-action-hook.md の実装計画を、小さなレビュー可能なタスクに分割したものです。
各タスクは原則として「1タスク = 1つの目的、1～3ファイル、30～90分でレビュー可能」を目安にしています。

注意: ここでは実装は行わず、タスク分割のみを行います。

------------------------------------------------------------
タスク一覧 (優先順)

1) トリガー種別とスキーマ登録を追加
   - 目的: 既存のトリガータイプ一覧に `action_hook` を追加し、スキーマ参照を登録する。
   - 変更ファイル (想定):
     - src/PostType/Trigger/Trigger.php (get_type_schema() に type を追加、スキーマ設定追加)
   - 見積り: 30-60分
   - 完了条件:
     - get_type_schema() の戻り値に `action_hook` が追加されている
   - テスト/検証:
     - 管理画面のトリガー作成フォームで `action_hook` が選べること（手動確認）
   - ロールバック: 変更を元の get_types() に戻す

2) スキーマ定義ファイルの追加/更新（form[1] 用）
   - 目的: docs/features/trigger-action-hook.md に記載の JSON スキーマをリポジトリのスキーマ定義に追加する。
   - 変更ファイル (想定):
     - src/PostType/Trigger/Trigger.php (get_types() に `action_hook` を追加、スキーマ設定追加)
   - 見積り: 30-90分
   - 完了条件:
     - スキーマファイルに action_hook のプロパティ (hook, audience_mode, current_user_channels, conditions, audience 等) が存在する
     - default 値と dependencies の記述がドキュメントと一致している
   - テスト/検証:
     - スキーマバリデータで構文チェックが通る（ローカルで json lint）
   - ロールバック: ファイルを元に戻す

3) 新規ファイル: src/Core/ActionHooks.php の追加 (骨格)
   - 目的: WordPress のフック登録と各ハンドラーを集約するクラスの骨格を追加する。
   - 変更ファイル:
     - src/Core/ActionHooks.php (新規)
   - 見積り: 30-60分
   - 完了条件:
     - クラス ActionHooks と静的メソッド init() を追加
     - プリセット10フック（user_register, wp_login, ...）の add_action() 登録コード（ハンドラーはスタブ）を追加
     - フィルター lineconnect_predefined_action_hooks を適用してカスタムフックを取得する箇所を用意
   - テスト/検証:
     - PHP の構文チェックが通る
   - ロールバック: ファイルを削除

4) ActionHooks: 単純ハンドラ実装 (グループ A)
   - 目的: いくつかのフックハンドラーを実装して ActionHook::process() に正規化した引数を渡す。
   - 対象ハンドラ (例): user_register, wp_login, wp_logout
   - 変更ファイル:
     - src/Core/ActionHooks.php
     - src/Trigger/ActionHook.php (process() を呼べる最低限の public メソッドが必要なため、ない場合は次タスクで作成)
   - 見積り: 30-90分
   - 完了条件:
     - 各ハンドラーが hook_name と args 配列を作成し ActionHook::process() を呼べる
     - 例外は try-catch で捕捉し、 error_log() を呼ぶ構造になっている
   - テスト/検証:
     - 単体でハンドラー関数を呼び、process() が呼ばれることをモック/ログで確認
   - ロールバック: 追加したハンドラーをスタブに差し戻す

5) ActionHooks: 残りハンドラ実装 (グループ B)
   - 目的: 残りのプリセットフック（profile_update, delete_user, save_post, comment_post, activated_plugin, deactivated_plugin, switch_theme）を実装
   - 変更ファイル:
     - src/Core/ActionHooks.php
   - 見積り: 60-120分（複数ハンドラのためやや長め）
   - 完了条件:
     - すべてのハンドラーが正規化した args を生成して ActionHook::process() を呼べる
     - save_post や comment_post のように第2引数オブジェクトが渡されるケースで引数形がドキュメントに合う
   - テスト/検証:
     - 各ハンドラを単体テストで呼び、ActionHook::process が受け取るデータが期待どおりであることを確認
   - ロールバック: 変更を元に戻す

6) カスタムフック受け口の実装
   - 目的: lineconnect_custom_hook の add_action 登録と handle_custom_hook 実装
   - 変更ファイル:
     - src/Core/ActionHooks.php
   - 見積り: 30-60分
   - 完了条件:
     - add_action('lineconnect_custom_hook', [ActionHooks::class, 'handle_custom_hook'], 10, 2) が登録される
     - handle_custom_hook は (string $hook_name, array $args) を受け取り、ActionHook::process() に渡す
   - テスト/検証:
     - do_action('lineconnect_custom_hook', 'my_custom_hook', ['arg'=>1]) で process が呼ばれる（ユニット/統合テスト）
   - ロールバック: 追加コードの削除

7) トリガー評価層: src/Trigger/ActionHook.php の骨格作成
   - 目的: ActionHook::process(), check_condition(), resolve_related_user(), build_audience_condition() の基本的なクラスを追加
   - 変更ファイル:
     - src/Trigger/ActionHook.php (新規)
   - 見積り: 30-90分
   - 完了条件:
     - public static function process(array $action_hook_args) が存在し、参照可能である
     - 空のまたは最小限の実装の check_condition(), resolve_related_user(), build_audience_condition() が存在する（後続タスクで拡張）
   - テスト/検証:
     - process() を呼べること（モックで確認）
   - ロールバック: ファイルを削除

8) ActionHook::check_condition() の実装（フック固有ロジック：save_post, comment_post, wp_login）
   - 目的: ドキュメントにある conditions のデフォルトやフック固有チェックを実装する
   - 変更ファイル:
     - src/Trigger/ActionHook.php
   - 見積り: 60-120分
   - 完了条件:
     - save_post の post_type/post_status のデフォルト適用ロジックが実装されている
     - comment_post と wp_login の role チェックができる
     - フック引数を元に真偽を返却する API がある
   - テスト/検証:
     - save_post 用の引数を与えて、条件省略時に true/false を返すユニットテスト
   - ロールバック: 実装を以前のスタブに戻す

9) 関連ユーザー自動推論の実装（resolve_related_user）
   - 目的: ドキュメントの推論表に従って関連ユーザーIDを決定するロジックを実装
   - 変更ファイル:
     - src/Trigger/ActionHook.php
   - 見積り: 45-90分
   - 完了条件:
     - 各フックに対して適切なユーザーIDを返す実装がある
     - 推論結果が 0 の場合はそのトリガーをスキップする判定ロジックがある
   - テスト/検証:
     - 各フックごとの推論結果を返すユニットテストを追加
   - ロールバック: 実装を以前のスタブに戻す

10) audience 条件構築と分岐ロジックの実装
    - 目的: audience_mode に応じて audience 条件を組み立て、Audience::get_audience_by_condition() / ActionFlow::execute_actionflow_by_audience()／Action::do_action() に渡す
    - 変更ファイル:
      - src/Trigger/ActionHook.php
      - 参照: src/PostType/Audience/（既存ロジック）
    - 見積り: 60-120分
    - 完了条件:
      - current_user モードで current_user_channels を使って audience 条件が生成される
      - standard モードで trigger['audience']['condition'] をそのまま使う分岐がある
      - audience が空のときは Action::do_action() を呼ぶ分岐がある
    - テスト/検証:
      - current_user モードで構築された条件により Audience::get_audience_by_condition() が呼ばれるモックテスト
    - ロールバック: 実装を以前のスタブに戻す

11) Action::do_action() のシグネチャ変更と injection_data 設定
    - 目的: Action::do_action() に $action_hook_args 引数を追加し、チェーン実行時に $injection_data['action_hook'] をセットする
    - 変更ファイル:
      - src/Action/Action.php
    - 見積り: 30-60分
    - 完了条件:
      - 関数シグネチャに新しい引数が追加されている
      - $injection_data['action_hook'] が action_hook_args の形で設定され、テンプレートで参照可能
    - テスト/検証:
      - 既存のアクションチェーン実行テストが修正され、テンプレート内で {{$.action_hook.*}} が参照可能であることを確認
    - ロールバック: シグネチャを以前に戻す

12) フィルターフックとカスタムフックのドキュメント追記
    - 目的: lineconnect_predefined_action_hooks フィルターと lineconnect_custom_hook の使い方をドキュメントに記載
    - 変更ファイル:
      - docs/features/trigger-action-hook.md (追記)
    - 見積り: 15-30分
    - 完了条件:
      - フィルターとカスタムフック呼び出し例がドキュメントに追加されている
    - ロールバック: ドキュメントの変更を取り消す

13) ユニット/統合テスト追加（小分け）
    - 目的: 各コンポーネントの動作を個別にテストするテストコードを追加する。タスクを分割して少しずつ追加すること。
    - 変更ファイル:
      - tests/trigger/TestActionHooksInit.php (init 登録のテスト)
      - tests/trigger/TestActionHookHandlers.php (個別ハンドラの入力変換テスト)
      - tests/trigger/TestActionHookProcess.php (process の条件評価 + audience 分岐テスト)
    - 見積り: 各テストファイル 30-90分、合計分割して実施
    - 完了条件:
      - 各テストがローカルの PHPUnit で実行できる
      - HTTP 外部通信は DummyHttpClient 等でモックされている
    - テスト/検証:
      - composer test -- --filter [TargetTestFile] で実行
    - ロールバック: 追加したテストファイルを削除

14) 例外/エラー処理とログの整備
    - 目的: process 呼び出しでの try-catch、error_log() によるログ出力の共通パターンを確認・実装
    - 変更ファイル:
      - src/Core/ActionHooks.php
      - src/Trigger/ActionHook.php
    - 見積り: 30-60分
    - 完了条件:
      - 全ハンドラーが try-catch し、例外発生時に error_log() を呼ぶ
      - 元の WordPress の処理は中断されない（例外を swallow する実装）
    - テスト/検証:
      - 故意に例外を投げるテストケースで error_log が呼ばれることを確認
    - ロールバック: 例外処理を元に戻す

15) ドキュメント最終確認と UI スキーマの追記
    - 目的: RJSF 用 UI スキーマや管理画面の説明を docs に追加
    - 変更ファイル:
      - docs/features/trigger-action-hook.md
      - frontend/rjsf/（必要なら）
    - 見積り: 30-60分
    - 完了条件:
      - UI スキーマ (ui:schema) の説明がドキュメントに含まれている
      - 管理画面のスクリーンショットや補足があれば追加
    - ロールバック: ドキュメントの編集を元に戻す

------------------------------------------------------------
タスク分割に関する補足
- 各タスクは "小さく独立して検証可能" になるよう意識してあります。実装者は順に進め、各タスク完了後にコミット & CI（unit tests）を回してください。
- テストは可能な限り外部通信（LINE API 等）をモック化してください。既存の DummyHttpClient を利用すること。
- 実装中に仕様変更が必要になった場合、このタスクリストを更新し、変更点を分かりやすくコメントしてください。

------------------------------------------------------------
タスクの割り当て・見積り等で質問があれば教えてください。
