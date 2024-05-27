# クイックスタート
LINE Connectを使い始める
## プラグインのインストール
1. [GitHub](https://github.com/shipwebdotjp/lineconnect/releases)より最新版のZIPファイルをダウンロードします。
2. Wordpressの管理画面へログインし、「プラグイン」メニューから「新規追加」を選び、「プラグインをアップロード」をクリックします。
3. 「ファイルの選択」から、ダウンロードしておいたZIPファイルを選択し、「今すぐインストール」をクリックします。
4. インストールが完了したら、プラグイン一覧画面より「LINE Connect」を有効化します。

## 初期設定
### チャネルアクセストークンとチャネルシークレット
1. あらかじめ[LINE Developers](https://developers.line.biz/)にて、公式アカウントのMessaging APIチャネルを作成しておいてください。
2. チャネルシークレットをチャネル基本設定から取得します。
3. チャネルアクセストークン（長期）をMessaging API設定より取得します。

### Webhook URL
1. 該当の公式アカウントのLINE Messaging API設定のWebhook URLの欄に次のURLを入力します。
```
https://your-domain/wp-content/plugins/lineconnect/bot.php
```
※your-domainの部分をご自分のドメイン名に変更してください。もしWordpressのURLがドメイン直下でない場合はWordpressディレクトリのパスを追加してください。  
2. Webhookの利用をオンにします。 

	全ての友達へ投稿通知を行いたいだけの場合は、WebhookをONにする必要はありません。ただしその場合、ユーザー連携機能は使用できなくなります。

### 応答モード
2022年11月の仕様変更以降はBOT(Webhook)とチャットが併用できるようになりました。  
ユーザーからの問い合わせなど必要に応じてチャットを利用しつつ、ユーザー連携やChatGPTによる自動応答を利用できます。
1. [LINE Official Account Manager](https://manager.line.biz/)にログインします。 
2. [設定]-[応答設定]ページを開きます。 
3. 応答機能のチャットを必要に応じて「ON」に設定します。

### プラグインの初期設定
1. Wordpressの管理画面より、設定メニューから「LINE Connect」をクリックして設定画面を開きます。
2. チャネルタブで新規チャネル追加をクリックします。
2. チャネル名、チャネルアクセストークンとチャネルシークレットをそれぞれの欄に入力して保存します。

#### ログイン画面URLを変更している場合
デフォルトではログインURLとしてWordpressのデフォルトである「wp-login.php」を使用します。
もしもログイン画面URLを他のURLに変更している場合は設定画面の連携タブのログインページURLを変更してください。