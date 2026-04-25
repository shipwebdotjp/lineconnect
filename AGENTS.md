# AGENTS: lineconnect リポジトリ用エージェント設定ドキュメント

このドキュメントはリポジトリ内の開発規約・作業方針・テスト手順などを要約した開発者向けエージェント設定です。CI / 自動化エージェントやドキュメント生成、レビューワークフローなどで参照されることを想定しています。

## 概要
- リポジトリ: `lineconnect` (WordPress プラグイン)
- GitHub: [lineconnect](https://github.com/shipwebdotjp/lineconnect)
- 目的: LINE公式アカウントのユーザー と WordPressのユーザー を接続するプラグインの開発・テスト・保守
- 主な技術スタック: PHP (WordPress), JavaScript (React, Docusaurus), PHPUnit

## エージェントの役割（想定）
- 開発ガイドエージェント: コーディング規約や作業中のタスクを参照して、PR コメントやコードスタイル提案を行う
- テストランナーエージェント: 指定のテストコマンドでユニットテスト / 結合テストを実行・結果を報告する
- ドキュメントエージェント: `document/` 配下のドキュメント整備、翻訳やサイドバー更新を補助する
- モック / 検証エージェント: LINEBot の HTTP クライアント差し替え手順に従ってテスト用のモックをセットアップする手順を提示する

## コーディング規約
- できるだけ WordPress コーディング規約に従うこと
  - PHP: WordPress のコーディング規約に準拠する（インデント、命名、ドキュメンテーション等）
  - 翻訳関数: `__()`, `_e()`, `_x()` 等を適切に使用する
  - 既存ファイルヘッダ・スタイルに整合させる
- フロントエンドは既存の `document/` や `frontend/` の慣例に従う

## テスト方針
- テスト配置:
  - `tests/` 配下にカテゴリ別ディレクトリを作成しテストファイルを配置する
  - テストファイル名とクラス名は原則として `*Test.php` / `*Test` の形式に統一する
    - 例: `ActionHookBuildAudienceConditionTest.php` / `ActionHookBuildAudienceConditionTest`
    - 既存の `TestFooBar.php` のような命名がある場合は、追加・更新時に順次統一する
- テスト実行環境:
  - テストはテスト用 WordPress 上で実行される
  - テストデータは `tests/testdata/` に置く
    - チャネルデータ: `channels.json`
    - WP ユーザーデータ: `wp_users.json`
    - LINE ユーザーデータ: `line_users.json`
- テストクラス:
  - Ajax テスト: `WP_Ajax_UnitTestCase` を継承したクラスを作成する
  - その他のテスト: `WP_UnitTestCase` を継承したクラスを作成する
  - いずれの場合も `wpSetUpBeforeClass` 内で `lineconnectTest::init();` を呼ぶ

## LINEBot のモック方法
- 使用クラス:
  - `LINE\Tests\LINEBot\Util\DummyHttpClient` を使用して HTTP リクエストをモックする
- フィルター:
  - `LineConnect::FILTER_PREFIX . 'httpclient'` フィルターを使って HTTP クライアントを差し替える
- 例（テスト内 `setUp` の例）:
```
public function setUp(): void {
    parent::setUp();
    add_filter(LineConnect::FILTER_PREFIX . 'httpclient', function ($httpClient) {
        $mock = function ($testRunner, $httpMethod, $url, $data) {
            return ['status' => 200];
        };
        $dummyHttpClient = new LINE\Tests\LINEBot\Util\DummyHttpClient($this, $mock);
        return $dummyHttpClient;
    });
    lineconnectTest::init();
}
```

## テスト実行コマンド
- Docker
  - `.jules/setup-jules-environment.sh`
- ローカル / CI:
  - `composer test -- --filter [TargetTestFile]`
  - `composer test -- --filter SampleTest`
- PHPUnit 設定はプロジェクトルートの `phpunit.xml` を参照

## ドキュメント・翻訳
- ドキュメントソースは `document/`（Docusaurus）にある
- 日本語ドキュメントは `document/i18n/ja/` 配下
- ドキュメント修正時はサイドバーや翻訳ファイルの更新を行う

## 開発ワークフロー／チェックリスト（エージェント向け）
- [ ] 変更箇所の影響範囲を特定する
- [ ] 単体テストを追加 / 更新する（`tests/`）
- [ ] 外部通信はモック化してテストを実行する
- [ ] ドキュメント (`document/`) を更新する
- [ ] PR 作成時に CI（テスト）が通ることを確認する

## 追加メモ
- テストデータ例や雛形は `tests/testdata/` を参照する
- `lineconnectTest::init();` がテストユーティリティの初期化エントリとなるため、テストセットアップで必ず呼び出す

