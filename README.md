# LINE Connect 
LINE ConnectはWordpressユーザーとLINEユーザーを連携させるプラグインです。  
Wordpressの投稿を連携済みのWordpressユーザーへ通知したり、連携済みかそうでないかに応じて異なるリッチメニューを登録することができます。  
Growniche社の[LINE AUTO POST](https://s-page.biz/line-auto-post/#home)を元に改変したものです。  
## オリジナルとの違い  
### Wordpressユーザーとの連携機能
* LINE Messaging APIを利用した、LINE公式アカウントの友達とWordpressの登録ユーザーのアカウントを連携可能
* リッチメニューIDを連携済みのユーザーとそうでないユーザーとで分けて設定可能
### 投稿通知
* 連携済みのユーザーだけにLINE送信が可能
* 特定のロールのユーザーだけにLINE送信が可能
* 投稿画面での送信するかどうかのチェックボックスが右カラムに表示される
* 新規投稿の場合は自動的に送信チェックボックスにチェックが付く
* 管理画面での設定メニューがトップメニューではなく設定メニュのサブメニューとして表示される
* 通知メッセージをFlexメッセージに変更し、アイキャッチ画像を含めたレイアウトで通知

## インストール方法
1. [GitHub](https://github.com/shipwebdotjp/lineconnect/releases)より最新版のZIPファイルをダウンロードします。
2. Wordpressの管理画面へログインし、「プラグイン」メニューから「新規追加」を選び、「プラグインをアップロード」をクリックします。
3. 「ファイルの選択」から、ダウンロードしておいたZIPファイルを選択し、「今すぐインストール」をクリックします。
4. インストールが完了したら、プラグイン一覧画面より「LINE Connect」を有効化します。

## 初期設定
### チャネルアクセストークンとチャネルシークレット
1. あらかじめ[LINE Developers](https://developers.line.biz/)にて、公式アカウントのMessaging APIチャネルを作成しておいてください。
2. チャネルシークレットをチャネル基本設定から、チャネルアクセストークン（長期）をMessaging API設定より取得します。
3. Wordpressの管理画面より、設定メニューから「LINE Connect」をクリックしてPreferences画面を開きます。
4. チャネルアクセストークンとチャネルシークレットをそれぞれの欄にコピペして保存します。
### Webhook URL
1. 該当の公式アカウントのLINE Messaging API設定のWebhook URLの欄に次のURLを入力します。
```
https://your-domain/wp-content/plugins/lineconnect/bot.php
```
※your-domainの部分をご自分のドメイン名に変更してください。もしWordpressのURLがドメイン直下でない場合はWordpressディレクトリのパスを追加してください。  
2. Webhookの利用をオンにします。 

### 応答モード
1. [LINE Official Account Manager](https://manager.line.biz/)にログインします。 
2. [設定]-[応答設定]ページを開きます。 
3. 応答モードを「BOT」に設定します。

## 連携方法
### 連携開始
アカウント連携を開始するには3通りの方法があります。
1. 公式アカウントを友達登録 
2. 公式アカウントのトーク画面で「アカウント連携」と入力して送信 
3. リッチメニューなどからポストバックアクション「action=link」を送る 

その後、連携開始リンクをタップし、Wordpressでログインすることで連係が行えます。

## LINE投稿方法
1. 投稿画面で、右カラムに「LINE Connect」ボックスが表示されるので、通知したい場合は「LINE送信する」へチェックを入れて投稿を行います。  
※チェックが入っていると新規投稿だけでなく更新する場合でも通知されますので注意してください。
2. 画像付きで通知させたい場合は、アイキャッチ画像を設定してください。
3. 「対象」リストからLINEで通知するユーザーを「全ての友達」「連携済みの友達」、連携済みの友達のうち各ロールに属するユーザーから選択してください。

## リッチメニュー設定方法
事前にリッチメニューをAPIを使用して作成し、リッチメニューIDを取得する必要があります。  
※[LINE Official Account Manager](https://manager.line.biz/)で作成したリッチメニューは使用できません。  
APIを利用したリッチメニューの作成は[リッチメニューエディタ](https://richmenu.app.e-chan.cf/)などを利用すると比較的簡単に行えます。 
1. 作成したリッチメニューのIDをLINE Connectの設定画面より設定します。
2. 連携済みの友達と未連携の友達用の２種類のリッチメニューを作成登録しておき、それぞれの欄にIDを設定することで、連携状態に応じて異なるリッチメニューを表示することができます。

## スクリーンショット
友達登録している人へこのように通知されます。  
<img src="https://blog.shipweb.jp/wp-content/uploads/2021/03/PNG-imageposttoline.png" width="320">  
リンクテキストや背景色、サムネのアスペクト比をカスタマイズすることも可能です。  
<img src="https://blog.shipweb.jp/wp-content/uploads/2021/03/PNG-imageposttolinecustom.png" width="320">  

## カスタマイズ
config.phpを編集することでいくつかのカスタマイズが行えます。
* 通知メッセージ中のリンクラベルを変えたい場合は下記の'Read more'の部分を変更  
```
    const PARAMETER__READ_MORE_LABEL = 'Read more';
```
* 通知メッセージ中の画像領域のアスペクト比を変えたい場合は下記の'2:1'の部分を変更  
```
    const PARAMETER__IMAGE_ASPECTRATE = '2:1';s
```

* 通知メッセージ中の背景色を変えたい場合は下記の'#FFFFFF'の部分を変更  
```
    const PARAMETER__TILE_BACKGROUND_COLOR = "#FFFFFF";
```

その他さまざまなカスタマイズを有償で承ります。[連絡先はこちら](https://blog.shipweb.jp/contact)

# 必要動作環境
* Wordpress  4.9.13以上

# 制作者
* ship [blog](https://blog.shipweb.jp/)

# 謝辞
* 素晴らしいプラグイン「LINE AUTO POST」を開発してくださったGrowniche社の方々

# ライセンス
GPLv3