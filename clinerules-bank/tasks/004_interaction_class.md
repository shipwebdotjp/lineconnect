## インタラクション機能 設計と進捗

**最終更新日**: 2025年8月21日

### 概要
ユーザーとの対話的なやり取り（インタラクション）を管理するための機能設計。

### namespace
`Shipweb\LineConnect\Interaction`

### folder
`src/Interaction`

### 動作の流れ（詳細版）

インタラクションの動作は、「新規に開始されるフロー」と「継続中の対話を処理するフロー」の2つに大別される。

#### フロー1：新規インタラクションの開始

このフローは、`bot.php`ではなく、外部システム（例: LCトリガーのアクション）によって起動されることを想定している。

1.  **[外部システム] 開始リクエスト**
    *   何らかの条件（例: 特定のキーワード、リッチメニューのタップ）に基づき、LCトリガーのアクション[src/Action/Definitions/StartInteraction.php->start_interaction]が実行される。
    *   そのアクションが、「インタラクションの開始」として **`InteractionManager`** の `startInteraction()` メソッドを直接呼び出す。
    *   引数として、開始したいインタラクションのID、ユーザーID、チャネル情報を渡す。

2.  **[`InteractionManager`] 新規セッションの作成**
    *   `startInteraction()`メソッドが実行される。
    *   引数のインタラクションIDを元に、**`InteractionDefinition`**（対話定義）を読み込む。
    *   新しい **`InteractionSession`** を生成し、最初のステップIDを設定する。
    *   生成したセッションを **`SessionRepository`** を使してデータベースに保存する。

3.  **[`InteractionManager` & `InteractionHandler`] 初回ステップの提示**
    *   `InteractionManager`は、作成したセッション情報と、ユーザーからの入力がないことを示す内部的なイベントオブジェクトを、**`InteractionHandler`** の `presentStep()` メソッドに渡す。
    *   `presentStep()` メソッドは、**現在の**ステップ（＝最初のステップ）の `beforeActions` があれば実行し、そのステップに定義されたメッセージを構築して `InteractionManager` に返す。
    *   `InteractionManager`は、受け取ったメッセージを呼び出し元（LCトリガー）に返し、ユーザーへの応答メッセージとして利用される。

---

#### フロー2：継続中インタラクションの処理

このフローは、`bot.php` がLINE Message APIからイベントを受け取った際の処理を記述している。

1.  **[bot.php] イベント受信と処理の移譲**
    *   LINE Message APIからWebhookイベントを受信します。
    *   `bot.php`は、まずアクティブなインタラクションが存在するかどうかを確認するため、**`InteractionManager`** の `handleEvent()` メソッドを呼び出す。

2.  **[`InteractionManager`] 継続セッションの特定**
    *   `handleEvent()` メソッドが実行される。
    *   `SessionRepository`を使い、ユーザーIDとチャネル情報を元にアクティブな（`status`が`active`の）**`InteractionSession`** が存在するかどうかを確認する。
    *   **【セッションが存在しない場合】**: `handleEvent`は何もせず、空の配列を返す。`bot.php`は後続の処理（通常のキーワード応答やAI応答など）を続ける。
    *   **【セッションが存在する場合】**: `InteractionManager`は、取得したセッションとイベント情報を **`InteractionHandler`** の `handle()` メソッドに渡し、以降の処理を委譲する。

3.  **[`InteractionHandler`] ユーザー入力の処理とステップの進行**
    *   `handle()` メソッドが実行される。
    *   **`InteractionSession`** から現在のステップIDを取得し、対応する **`StepDefinition`** を読み込む。
    *   イベントからユーザーの入力（テキスト、ポストバックデータ等）を抽出する。
    *   **入力の整形・検証**: **`InputNormalizer`** と **`Validator`** を使って入力を処理する。
        *   **【検証NGの場合】**: エラーメッセージを構築し、処理を中断して返却する。ユーザーのステップは移動しない。
        *   **【検証OKの場合】**: 検証済みの回答を **`InteractionSession`** に保存する。
    *   現在のステップの `afterActions` があれば実行する。

4.  **[`InteractionHandler`] 次のステップの決定と応答構築**
    *   （入力検証が成功した場合のみ）**`determine_next_step_id`** に基づき、次のステップIDを決定する。
    *   **【次のステップが存在する場合】**: 
        *   セッションの `current_step_id` を更新する。
        *   次のステップの `beforeActions` があれば実行する。
        *   **`MessageBuilder`** を使い、**次のステップ**のメッセージを構築する。
    *   **【次のステップが存在しない場合（完了）】**: 
        *   セッションのステータスを `completed` に更新する。
        *   完了ステップ（`special: complete`）が定義されていれば、そのメッセージを構築する。

5.  **[`InteractionHandler` & `InteractionManager`] セッション保存と応答**
    *   `InteractionHandler`は、更新されたセッションの状態を **`SessionRepository`** を使ってデータベースに保存する。
    *   構築したメッセージオブジェクトを `InteractionManager` に返し、`InteractionManager`はそれをさらに`bot.php`に返す。
    *   `bot.php`は、受け取ったメッセージをユーザーに送信する。

### 現在までの進捗
- **完了**
  - 基本設計（クラス構成、DBスキーマ定義）
  - データモデルと永続化クラスの実装 (`InteractionDefinition`, `StepDefinition`, `InteractionSession`, `SessionRepository`)
  - 入力処理クラスの実装 (`InputNormalizer`, `Validator`, `ValidationResult`)
  - アクション実行クラス (`ActionRunner`)
  - メッセージ関連クラス (`MessageBuilder`)
  - 中核処理・統括クラス (`InteractionHandler`, `InteractionManager`)
  - 上記クラス群に対する単体テストの実装とパス確認

---

### クラス設計

インタラクション機能は、以下のクラス群によって構成される。

#### データ定義・実体クラス
- `InteractionDefinition`: インタラクション全体の定義を保持する。
- `StepDefinition`: 個々のステップの定義を保持する。
- `InteractionSession`: ユーザー毎のインタラクションの実行状態を管理するオブジェクト。`complete()`メソッドでセッションを完了状態にできる。

#### 永続化クラス
- `SessionRepository`: `InteractionSession`オブジェクトをデータベースに永続化する。

#### 実行・処理クラス
- `InteractionManager`: インタラクション全体のフローを管理する司令塔。外部からの`startInteraction()`呼び出しによる対話の新規開始と、`bot.php`からの`handleEvent()`呼び出しによる継続中の対話処理という、2つのエントリーポイントを持つ。
- `InteractionHandler`: 個別のインタラクションステップの処理を実行する。責務が以下の2つのメソッドに分割されている。
    - `presentStep()`: 現在のステップのメッセージを表示する（対話開始時に利用）。
    - `handle()`: ユーザーからの入力を処理し、対話を次のステップに進める。
- `InputNormalizer`: ユーザーからの入力を整形する。
- `Validator`: 整形後の入力値を検証する。
- `ActionRunner`: ステップの前後で定義されたアクションを実行する。`run()`メソッドはアクション定義オブジェクト、セッション、イベントを引数に取る。

#### メッセージ関連クラス
- `MessageBuilder`: `StepDefinition`からLINEへ送信するメッセージオブジェクトを構築する。

---

### データベース設計

（変更なし）

---

### 主要な変更点（今回の実装反映）
以下は、実装作業で追加・修正した点を元ドキュメントに追記した内容です（必要に応じて部分修正を行っています）。

- 変更日: 2025年8月21日
- 反映された実装・ファイル:
  - `src/Core/Cron.php`:
    - 追加: `process_interaction_timeouts` 関数を実装しました。この関数は、有効期限 (`expires_at`) が切れたアクティブなインタラクションセッションを処理します。
    - 変更: `run_schedule` 関数から `process_interaction_timeouts` を呼び出すように変更しました。
    - 機能:
        - タイムアウトしたセッションに対して、まず通知メッセージを送信します。
        - メッセージは、インタラクション定義の特別ステップ `timeoutNotice` が設定されていればそれを使用し、なければデフォルトの「セッションがタイムアウトしました」というメッセージを送信します。
        - メッセージ送信後、インタラクション定義の `get_on_timeout()` の設定 (`delete_session` または `mark_timeout`) に基づいて、セッションの削除またはステータスの更新 (`timeout`へ) を行います。

- 変更日: 2025年8月20日
- 反映された実装・ファイル:
  - `src/Interaction/InputNormalizer.php`
    - 変更: `mb_convert_kana` のフラグが誤っていた問題を修正し、正しい文字変換が行われるようにしました。
  - `tests/Interaction/InputNormalizerTest.php`
    - 追加: `InputNormalizer` のためのテストファイルを作成し、全ての正規化ルール（trim, omit, 各種かな変換）に対するテストケースを追加しました。
  - `src/Interaction/InteractionSession.php`
    - 追加: `from_db_row` メソッドに `interaction_session_load` フィルターフックを追加しました。
  - `src/Interaction/SessionRepository.php`
    - 追加: `save` メソッドに `interaction_session_save` フィルターフックを追加しました。

- 変更日: 2025年8月19日
- 反映された実装・ファイル:
  - `src/Interaction/MessageBuilder.php`
    - 変更: `build` メソッドを拡張し、`text` と `template_button` に加えて、以下のメッセージタイプに対応しました。
      - `sticker`, `image`, `video`, `audio`, `location`, `flex`, `raw`
    - 各メッセージタイプに対応する `LineMessageBuilder` のメソッドを呼び出すように実装しました。
    - 追加: `buildTemplateButtonMessage` メソッドを実装し、`template_button` タイプからFlex Messageを構築するロジックを追加しました。
  - `tests/Interaction/MessageBuilderTest.php`
    - `MessageBuilder` のためのテストファイルを作成・更新しました。
    - `template_button` を含む、すべてのサポート対象メッセージタイプ（`text`を除く）について、メッセージが正しく構築されることを検証するテストケースを追加しました。

- 変更日: 2025年8月18日
- 反映された実装・ファイル:
  - `src/Interaction/InteractionSession.php`
    - 追加: `clear_answers()`、`reset_to_step(?string $step_id)`（既存セッションをリセットして再利用するためのヘルパー）
  - `src/Interaction/SessionRepository.php`
    - 追加: `find_paused(string $channel_prefix, string $line_user_id)`、`find_paused_by_interaction(string $channel_prefix, string $line_user_id, int $interaction_id)`（スタック / paused セッションの検索用）
  - `src/Interaction/InteractionManager.php`
    - 変更: `startInteraction` のシグネチャを以下に揃え、overridePolicy に基づく開始制御を実装
      ```
      public function startInteraction(int $interaction_id, string $line_user_id, string $channel_prefix, ?string $overridePolicy = null)
      ```
    -実装したポリシー:
      - `reject`
      - `restart_same`
      - `restart_diff`
      - `restart_always`
      - `stack`
    - デフォルトのポリシーは `InteractionDefinition::get_override_policy()` を参照、未設定時は `\`'stack\'`
    - 「同一フォーム判定」は `interaction_id` のみで行う（バージョンは含めていない）
    - スタック時は既存セッションを `status=\'paused\'` にして保存する
  - `tests/Interaction/InteractionManagerTest.php`
    - 各ポリシーの挙動を検証するテストケースを追加（reject, restart_same, restart_diff, restart_always, stack）

---

### テスト結果
- テスト実行コマンド:
  - `composer test -- --filter MessageBuilderTest`
- 現在の実行結果:
  - 8 tests, 47 assertions — OK

---

### 互換性・注意点（追記）
- `find_active()` は現状 `status IN (\'active\',\'editing\')` を返す実装です。`startInteraction` ではこの返り値を「アクティブ相当」として扱っています。将来的に `editing` を除外する場合は `find_active()` の修正が必要です。
- 「同一フォーム判定」に version を含める必要がある場合は仕様変更を連絡してください（現在は `interaction_id` のみ）。
- `paused` ステータスの導入により、既存の処理や管理画面で `status` を前提にしたロジックがある場合は影響が出る可能性があります。必要なら関連箇所の修正を行います。
- スタック（paused）からの復帰（resume）ロジックは今回未実装です。必要な場合は別タスクで対応します。

---

### 今後の計画（次回作業）
- [x] paused セッションの resume（復帰）ロジック実装
- [x] textタイプ以外のメッセージを組み立てる処理の実装(`MessageBuilder.php`)
- [x] ノーマライズの追加テスト（失敗ケース含む）
- [x] get_next_step_id の分岐ロジック拡張（入力に応じた分岐）
- [x] フィルターフックの実装 apply_filters(lineconnect::FILTER_PREFIX . \'name\', target, args...);
  - [x] `interaction_definition` フィルターを追加し、インタラクション定義をフックできるようにする。
  - [x] `interaction_session_load` フィルターを追加し、セッションデータのロード時にフックできるようにする。
  - [x] `interaction_session_save` フィルターを追加し、セッションデータの保存時にフックできるようにする。
  - [x] `interaction_message` フィルターを追加し、表示するメッセージデータをフックできるようにする。
  - [x] `interaction_normalize` フィルターを追加し、入力データの正規化処理をフックできるようにする。
  - [x] `interaction_validate` フィルターを追加し、入力データの検証処理をフックできるようにする。
-[ ] storageがprofileの場合の処理実装
  - complete時の処理として、LINEIDのユーザーのprofileをマッピングして更新する。マッピングテーブルをスキーマにまず追加する必要があるかも。
-[x] runPolicyの処理(single_forbid, single_latest_only, multi_keep_history)
-[x] timeoutの処理を実装
