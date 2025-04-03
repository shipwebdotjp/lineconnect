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