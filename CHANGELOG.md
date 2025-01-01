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