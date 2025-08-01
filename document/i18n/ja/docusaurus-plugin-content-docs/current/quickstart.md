# クイックスタート
LINE Connectを使い始める
## プラグインのインストール
WordPressプラグインディレクトリでは公開しておらず、GitHubでのみ公開しています。
1. [GitHub](https://github.com/shipwebdotjp/lineconnect/releases)より最新版のZIPファイルをダウンロードします。
2. Wordpressの管理画面へログインし、「プラグイン」メニューから「新規追加」を選び、「プラグインをアップロード」をクリックします。
3. 「ファイルの選択」から、ダウンロードしておいたZIPファイルを選択し、「今すぐインストール」をクリックします。
4. インストールが完了したら、プラグイン一覧画面より「LINE Connect」を有効化します。

## LINE公式アカウントの用意
LINE公式アカウントが必要になりますので、お持ちでない場合は作成してください。作成は無料で行えます。

## LINE公式アカウントの初期設定
### チャネルアクセストークンとチャネルシークレット
1. あらかじめ[LINE Developers](https://developers.line.biz/)にて、公式アカウントのMessaging APIチャネルを作成しておいてください。
2. チャネルシークレットをチャネル基本設定から取得します。
3. チャネルアクセストークン（長期）をMessaging API設定より取得します。
:::note
チャネルシークレットと、チャネルアクセストークン（長期）はプラグインの設定画面で必要となります。
:::
### Webhook URL
1. WordPressのLINE Connectの設定画面、チャネル設定タブの下部Webhook設定にある「Webhook URL」をコピーしておきます。
2. 該当の公式アカウントのLINE Messaging API設定のWebhook URLの欄で「編集」を押し、コピーしておいたURLを貼り付けます。
3. この時点ではLINE Connectでチャネルを設定していないため「検証」は失敗します。
URLの例: 
```
https://{your-domain}/wp-content/plugins/lineconnect/bot.php
```
※your-domainの部分はご自分のドメイン名が入ります。  
もしWordpressのURLがドメイン直下でない場合(サブディレクトリにインストールしている場合など)はWordpressディレクトリのパスが追加されます。  
2. Webhookの利用をオンにします。 
:::note
	全ての友達へ投稿通知を行いたいだけの場合は、WebhookをONにする必要はありません。ただしその場合、ユーザー連携機能は使用できなくなります。
:::
### 応答モード
2022年11月の仕様変更以降はBOT(Webhook)とチャットが併用できるようになりました。  
ユーザーからの問い合わせなど必要に応じてチャットを利用しつつ、ユーザー連携やChatGPTによる自動応答を利用できます。
1. [LINE Official Account Manager](https://manager.line.biz/)にログインします。 
2. [設定]-[応答設定]ページを開きます。 
3. 応答機能のチャットを必要に応じて「ON」に設定します。

## プラグインの初期設定
### チャネルの設定
1. Wordpressの管理画面より、設定メニューから「LINE Connect」をクリックして設定画面を開きます。
2. チャネルタブで新規チャネル追加をクリックします。
3. チャネル名、チャネルアクセストークンとチャネルシークレットをそれぞれの欄に入力して保存します。

### チャネルの検証
正しく設定されているか確認するために、LINE DeveloppersのMessaging APIチャネルのページで先ほど設定したWebhook URLの「検証」ボタンをクリックし「成功」メッセージが表示されることを確認してください。
:::note
「検証」が失敗する場合、インストールが正常にできているか、.htaccessでwp-contentディレクトリへのアクセスを禁止していないかなどを確認してください。
:::

#### ログイン画面URLを変更している場合
デフォルトではログインURLとしてWordpressのデフォルトである「wp-login.php」を使用します。
もしもログイン画面URLを他のURLに変更している場合は設定画面の連携タブのログインページURLを変更してください。