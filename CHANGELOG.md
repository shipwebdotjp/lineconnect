# Change Log

## [Unreleased]
### Added
- 
### Changed
- 
### Deprecated
- 
### Removed
- 
### Fixed

### Security
- 

## [4.3.4] - 2025-08-13
### Change
- 配布用パッケージのスリム化

## [4.3.3] - 2025-07-30
### Fix
- チャット: ユーザーを切り替えた際にメッセージ履歴が正しく読み込まれない不具合を修正
- イベントログで、Flexメッセージの内容が表示されない問題を修正

## [4.3.2] - 2025-07-28
### Fix
- CREATE TABLE文に不備がありテーブル作成に失敗する不具合を修正

## [4.3.1] - 2025-07-28
### Fix
- プラグインアクティベーションに失敗する不具合を修正

## [4.3.0] - 2025-07-28
### Feature
<img src="/document/static/img/chat/chat_screen.png" width="50%">
- ダイレクトメッセージ画面を全面改良し、各ユーザーとのチャット画面を作成しました。
    - チャネルごとにユーザーを選択し、過去のメッセージの履歴を表示したり、あらたにメッセージを送信できます。
    - 右側のユーザー情報ペインでは、ユーザーのプロフィールやタグ、シナリオ購読情報を一覧でき、編集も行えます。
- チャネル設定画面で、Webhook URLを表示するようにしました。コピーしてLINE Developersの設定画面にコピペできます。

### Change
- データベースバージョンを1.6に更新しました。(設定画面で保存することで、データベースのバージョンアップが行えます)
- イベントログの日時を日本時間からUTCへ変更しました。

## [4.2.2] - 2025-07-15
### Fixed
- プラグインがアクティベーションできなかった問題を修正しました。

## [4.2.1] - 2025-07-14
### Features
- プラグインデータ削除機能を追加しました。
### Fixed
- 投稿通知をロールを指定して送信する際に不要なファイルを読み込もうとして送信できない問題を修正しました。

## [4.2.0] - 2025-06-29
### Changed
- 内部のリファクタリングを行いました。
### Fixed
- デバッグログの出力を抑制

## [4.1.7] - 2025-05-15
### Changed
- プレースホルダーの置換処理をTwigを使用するように変更。これによりTwigに用意されているタグやフィルタを使用できるようになりました。(PHP8.1以上が必要)
- OpenAI APIエンドポイントを設定可能に変更(これにより、OpenAI APIと互換性のあるAPIを使用することが可能になりました。)
- OpenAI API endpoint can now be set. (This allows you to use APIs compatible with OpenAI API.)
- AI応答の使用モデルをリストから自由入力に変更(これにより、任意のモデルが使用可能になりました。)
- AI応答のシステムプロンプトにTwigを使ったプレースホルダーを使用できるようになりました。
- 画像を送信した後に、テキストを送信することで、その画像をAIチャットに入力して画像について質問などができるようになりました。
- ポストバックトリガーでリッチメニュー切り替えの際のIDやステータスを条件に含めることが可能になりました。

### Fixed
- アクションチェインで返り値が数値等の場合、置換できなかった問題を修正
- AI応答の最大トークン数のパラメータをDeprecatedだったmax_tokensからmax_completion_tokensに変更
- メッセージをトリガーにしている場合、画像や位置情報でも反応してしまうバグを修正

## [4.1.6] - 2025-04-11
### Changed
- メッセージ、トリガー、オーディエンス、シナリオなどカスタム投稿タイプで管理しているデータをJSON形式でエクスポート・インポートできるようになりました。
- 上記カスタム投稿タイプのフォームでバリデーションを行うようになりました。

### Fixed
- 一部の条件で正しくフォームが表示されない不具合を修正しました。

## [4.1.5] - 2025-04-06
### Changed
- シナリオ開始、ステータス変更、ステップ実行の各アクションでシナリオを指定する引数名をscenarioからslc_scenario_idに変更
    - アクションチェインでscenarioを指定して注入している場合、修正が必要です。

## [4.1.4] - 2025-04-03
### Added
- LINE IDリストからダイレクトメッセージ画面へ遷移できるように
- LINE IDリストからIDをクリップボードにコピーする機能を追加

### Changed
- メニューの順序を変更

### Fixed
- イベントログからダイレクトメッセージ画面に遷移した時、画面が正しく表示されない不具合を修正

## [4.1.3] - 2025-04-03
### Fixed
- generate-installable-zip.yamlを修正

## [4.1.2] - 2025-04-03
### Fixed
- インストール用ZIPの除外ファイルを修正
- Fixed the exclusion files in the installation ZIP.

## [4.1.1] - 2025-04-03
### Changed
- インストール用ZIPから不要なフォルダを削除し、サイズを減らしました。
- Removed unnecessary folders from the installation ZIP to reduce size.

### Fixed
- ダッシュボードが表示されないバグを修正しました。
- Fixed a bug that caused the dashboard to not display.

## [4.1.0] - 2025-04-03
### Added
- チャネルごとの月次および日次統計を表示するダッシュボード機能を追加しました。
- オーディエンスの対象となるユーザーのLINE ID一覧をダウンロードする機能を追加しました。
- 認識済みのLINE IDを一覧表示するページを追加しました。
- 統計用データベーステーブルの追加 **注意:** このバージョンへのアップデート後、データベースの更新が必要です。

## [4.0.0] - 2025-03-03
### Added
- アクション即時実行機能を追加しました。
- アクションフローを保存しておき、トリガーや即時実行で呼び出せるようになりました。
- シナリオ配信機能を追加しました。
- 送信対象をオーディエンスとして保存しておき、メッセージ送信やシナリオで使用できるようになりました。

## [3.4.0] - 2025-01-15
### Added
- リッチメニューとリッチメニューエイリアスの作成、管理機能を追加
- 個別ユーザーとリッチメニューとのリンクが行えるアクションを追加
- Added ability to create and manage rich menus and rich menu aliases
- Added an action to link individual users to the rich menu

## [3.3.0] - 2025-01-01
### Changed
- ユーザーロールごとに個別のリッチメニューを設定できるようになりました。
- リッチメニューをIDを入力する代わりに、リストから選択できるようになりました。
- Rich menus can now be configured according to user roles.
- Rich menus can now be selected from a list instead of entering an ID.

## [3.2.0] - 2024-10-26
### Added
- Messages saved in the Bulk message screen can now be loaded and used.

## [3.1.0] - 2024-09-13
### Fixed
- Fixed a bug that users with role added with add_role were not found in search

## [3.0.0] - 2024-05-20
### Added
- Save and view event logs
- Bulk distribution of any text
- Send direct messages to LINE users
- Triggered action execution
- Message template creation

### Fixed
- Remove unnecessary comma for substr in setting.php Thnaks for EDYNMD! 

## [2.8.0]
### Added
- Collect LINE user id, language, displayname and profile picture even they hasn't been linked with WordPress user.
### Changed
- Database version. 1.1 to 1.2. Add lineconnect_line_id tabled.

## [2.7.0] - 2023-12-15
### Feat
Feature: Add Filter to GPT Log page.
Feature: You can now choose between button and text link styles.

### Fix
Fix: Fixed a bug that prevented searching from messages in the log.

### Notice
Notice: The database needs to be updated.