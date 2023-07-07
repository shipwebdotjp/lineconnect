# LINE Connect 
LINE ConnectはWordpressユーザーとLINEユーザーを連携させるプラグインです。  
Wordpressの投稿を連携済みのWordpressユーザーへ通知したり、連携済みかそうでないかに応じて異なるリッチメニューを登録することができます。  
Growniche社の[LINE AUTO POST](https://s-page.biz/line-auto-post/#home)を元に改変したものです。  
公式サイトはこちら→[LINEと連携させるWordPressプラグイン「LINE Connect」](https://blog.shipweb.jp/archives/281)  
## オリジナルとの違い  
### Wordpressユーザーとの連携機能
* LINE Messaging APIを利用して、LINE公式アカウントの友達とWordpressの登録ユーザーのアカウントを連携可能
* リッチメニューIDを連携済みのユーザーとそうでないユーザーとで分けて設定可能
### 投稿通知
* 連携済みのユーザーだけにLINE送信が可能
* 特定のロールのユーザーだけにLINE送信が可能
* 投稿画面での送信するかどうかのチェックボックスが右カラムに表示される
* 新規投稿の場合は自動的に送信チェックボックスにチェックが付く(設定で変更可能)
* 管理画面での設定メニューがトップメニューではなく設定メニュのサブメニューとして表示される
* 通知メッセージをFlexメッセージに変更し、アイキャッチ画像を含めたレイアウトで通知
* 予約投稿の公開時にLINE送信が可能
* 記事にコメントがあった際、投稿者へのLINE通知が可能
## 独自機能
### LINEチャット
* 任意のLINEメッセージを個別ユーザーに送信可能
### WP LINE Loginとの連携機能
* WP LINE Loginをインストールし、Messaging APIが設定されている場合、連携と同時にログイン連携も可能

## インストール方法
1. [GitHub](https://github.com/shipwebdotjp/lineconnect/releases)より最新版のZIPファイルをダウンロードします。
2. Wordpressの管理画面へログインし、「プラグイン」メニューから「新規追加」を選び、「プラグインをアップロード」をクリックします。
3. 「ファイルの選択」から、ダウンロードしておいたZIPファイルを選択し、「今すぐインストール」をクリックします。
4. インストールが完了したら、プラグイン一覧画面より「LINE Connect」を有効化します。

## 初期設定
### チャネルアクセストークンとチャネルシークレット
1. あらかじめ[LINE Developers](https://developers.line.biz/)にて、公式アカウントのMessaging APIチャネルを作成しておいてください。
2. チャネルシークレットをチャネル基本設定から、チャネルアクセストークン（長期）をMessaging API設定より取得します。

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

※全ての友達へ投稿通知を行いたいだけの場合は、応答モードをBOTにする必要はありません。その場合、ユーザー連携機能は使用できなくなります。

### プラグインの初期設定
1. Wordpressの管理画面より、設定メニューから「LINE Connect」をクリックして設定画面を開きます。
2. チャネルタブで新規チャネル追加をクリックします。
2. チャネル名、チャネルアクセストークンとチャネルシークレットをそれぞれの欄に入力して保存します。

### ログイン画面URLを変更している場合
デフォルトではログインはWordpressのデフォルトログインURL「wp-login.php」を使用します。
もしもログイン画面URLを他のURLに変更している場合は設定画面の連携タブのログインページURLを変更してください。

## 連携方法
### 連携開始
アカウント連携を開始するには3通りの方法があります。
1. 公式アカウントを友達登録 
2. 公式アカウントのトーク画面でアカウント連携・解除開始キーワードを入力して送信 
3. リッチメニューなどからポストバックアクション「action=link」を送る 

その後、連携開始リンクをタップし、Wordpressでログインすることで連係が行えます。

## LINE投稿方法
### 通知する投稿タイプの選択
1. 投稿通知タブの投稿タイプより、通知したい投稿タイプを選択して設定しておきます。カスタム投稿タイプにも対応しています。

### 投稿時
1. 投稿画面で、右カラムに「LINE Connect」ボックスが表示されるので、通知したい場合は「LINE送信する」へチェックを入れます。  
※チェックが入っていると新規投稿だけでなく更新する場合でも通知が行われます。  
※「予約投稿時にLINE送信する」にチェックを入れて保存すると、予約投稿が公開された時にLINE通知が行われます。  
2. 画像付きで通知させたい場合は、アイキャッチ画像を設定してください。
3. 「送信対象」リストからLINEで通知するユーザーを「すべての友達」「連携済みの友達」、連携済みの友達のうち各ロールに属するユーザーから選択してください。（複数選択可能）

## リッチメニュー設定方法
事前にリッチメニューをAPIを使用して作成し、リッチメニューIDを取得する必要があります。  
※[LINE Official Account Manager](https://manager.line.biz/)で作成したリッチメニューは使用できません。  
APIを利用したリッチメニューの作成は[リッチメニューエディタ](https://richmenu.app.e-chan.cf/)などを利用すると比較的簡単に行えます。 
1. 作成したリッチメニューのIDをLINE Connectの設定画面より設定します。
2. 連携済みの友達と未連携の友達用の2種類のリッチメニューを作成登録しておき、それぞれの欄にIDを設定することで、連携状態に応じて異なるリッチメニューを表示することができます。

## スクリーンショット
友達登録している人へこのように通知されます。  
<img src="https://blog.shipweb.jp/wp-content/uploads/2021/03/PNG-imageposttoline.png" width="320">  
リンクテキストや背景色、サムネのアスペクト比をカスタマイズすることも可能です。  
<img src="https://blog.shipweb.jp/wp-content/uploads/2021/03/PNG-imageposttolinecustom.png" width="320">  

## 管理画面の設定項目
管理画面から通知スタイルなどのカスタマイズが行えます。

### チャネル
複数のチャネルに対応しています。どのチャネルから通知するかをLINE通知メタボックスで投稿時に選択できます。
### 連携
ログインページURLや、連携開始・解除のキーワード、開始・解除時のメッセージなどを変更することができます。
### 投稿通知
#### 投稿タイプ
LINE送信メタボックスを表示させる投稿タイプを選択します。Wordpressデフォルトで存在する「投稿」「固定ページ」に加えて、Custom Post Type UIなどで追加したカスタム投稿タイプも選択できます。
#### 「LINE送信する」チェックボックスのデフォルト値
記事編集画面での「LINE送信する」チェックボックスのデフォルト値設定です。選択肢は「チェックあり」「チェックなし」「公開済みの場合はチェックなし」です。  
#### リンクラベル
投稿通知の下部に表示されるリンクの表示文字列です。「もっと読む」「Read more」などURLの代わりに表示されるラベルです。
#### コメントがあった時に投稿者へLINE通知を行う
記事にコメントがあった時にその記事の投稿者にLINE通知を行うかどうかの設定です。投稿者のWordPressアカウントがLINE連携している必要があります。
#### コメントリンクラベル
コメント通知の下部に表示されるリンクの表示文字列です。
### 通知スタイル
投稿があったことを通知するメッセージのスタイルを変更できます。
#### 画像表示スタイル
- 領域全体に表示・・・画像の領域に合わせて縦横比を維持したまま画像が拡大縮小されます。はみ出した画像の周囲は切り捨てられます。
- 画像全体を表示・・・画像全体が表示されるよう縦横比を維持したまま画像が拡大縮小されます。画像の周囲に余白が追加されます。
#### 画像領域アクペクト比
画像領域の縦横比を「幅:高さ」の半角数字の形で指定します。例）16:9、4:3、3:2  
高さを幅の3倍を超える設定にはできません。よく使うアイキャッチ画像のアスペクト比に合わせておくことで、余白の切り取りが行われなくなります。
#### 背景色
メッセージ全体の背景色です。画像、タイトル、本文、リンク領域を個別に指定することは現在のバージョンではできません。
#### タイトル文字色、本文文字色、リンク文字色
それぞれの文字色です。色以外のフォントサイズや書体、太字などは設定できません。
#### タイトル最大行数、本文最大行数
タイトルや本文の最大行数です。これを超える場合は省略記号(…）が付き省略されます。（Android/iOSのみに有効）

※最大行数以内であっても、500文字を超える部分は省略されて送信されます。

## 開発者向け他プラグインとの連携方法
他プラグインからLINEメッセージ送信を呼び出せるよういくつかアクションフックが用意されています。
### アクションフック
#### send_message_to_wpuser($channel, $wp_user_id, $message)
連携済みのWordpressユーザーにLINEメッセージを送信します。
##### 使い方
```
//デフォルト（登録されているチャネルが1つならそのチャネル、複数登録されている場合は最初のチャネル）のユーザーIDが2のユーザーへメッセージ送信
do_action('send_message_to_wpuser', null, 2, 'Wordpressからのメッセージ');

//チャンネルシークレットの先頭4文字でチャネルを指定してチャネル情報を取得
do_action('send_message_to_wpuser', lineconnect::get_channel('1fa8'), 3, 'Wordpressからのメッセージ');

//送信するチャネルのアクセストークンとシークレットを連想配列で渡す場合
$channel = array(
    'channel-access-token' => '実際のチャネルアクセストークン',
    'channel-secret' => '実際のチャネルシークレット'
);
do_action('send_message_to_wpuser', $channel, 3, 'Wordpressからのメッセージ');

//LINE BOT SDKを利用してImageMessageBuilderを作成し、画像を送信
require_once(plugin_dir_path(__FILE__).'../lineconnect/vendor/autoload.php');
$originalContentUrl = "https://example.com/img.jpg";
$previewImageUrl = "https://example.com/img.jpg";
$imageMessageBuilder = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($originalContentUrl, $previewImageUrl);
do_action('send_message_to_wpuser', null, 3, $imageMessageBuilder);
```
##### パラメータ
**$channel (array|null)**  
channel-access-token、channel-secretを持つチャネル情報の連想配列か、null（デフォルトチャネルを使用）  
※デフォルトチャネルは、登録されているチャネルが1つならそのチャネル、複数登録されている場合は最初のチャネルです。  
**$wp_user_id (int)**   
 メッセージを送信するユーザーのWordpress ID（LINEユーザーIDではないことに注意）  
**$message (string|LINE\LINEBot\MessageBuilder)**  
送信するメッセージ。文字列の場合はテキストメッセージを作成して送信します。LINE BOT SDKを利用してMessageBuilderで作成したメッセージを送ることもできます。

#### send_message_to_role($channel, $role, $message)
ロールを指定して連携済みユーザーへLINEメッセージを送信します。``$role``に予約されている値を渡すことで、連携済みユーザー全てに送信することもできます。
##### 使い方
```
//管理者ロールのユーザーへメッセージを送信
do_action('send_message_to_role', null, array("administrator"), 'Wordpressからのメッセージ');

//すべての連携済みユーザーへメッセージを送信
do_action('send_message_to_role', null, array("slc_linked"), 'Wordpressからのメッセージ');

```
##### パラメータ
**$channel, $message**  
send_message_to_wpuserのパラメータと同じです。  
**$role (array)**   
送信対象とするロールスラッグの配列。例）administrator  
``slc_linked``を指定すると、すべての連携済みユーザーへ送信します。  

**チャネル指定時の注意**  
デフォルトチャネルは、チャネルの削除によって変化します。例えば複数チャネルがある場合、1番目のチャネルを削除すると、２番目のチャネルがデフォルトチャネルになります。確実を期すためにはチャネル情報を配列で指定するか、チャネル情報をシークレットの先頭4文字で取得したものを使用してください。
```
$channel = lineconnect::get_channel("(シークレットの先頭4文字)");
```

### LINEユーザーIDの保存形式
連携済みユーザーのLINEユーザーID、表示名、プロフィール画像URLはユーザーメタに``line``というキー名で保存されています。
#### メタデータの形式  
```
//ユーザーID:3のLINEユーザーIDを取得
$user_meta_line = get_user_meta(3, 'line', true);
var_dump($user_meta_line);

array(1) {
  ["(シークレットの先頭4文字)"]=>
  array(3) {
    ["id"]=>
    string(33) "(LINEユーザーID)"
    ["displayName"]=>
    string(12) "(表示名)"
    ["pictureUrl"]=>
    string(135) "(プロフィール画像URL)"
  }
}
```
数チャネルに対応しているため、チャネルシークレットの先頭4文字をキーとする連想配列がトップレベルにあり、その中に``id``,``displayName``,``pictureUrl``をキーとする連想配列が含まれます。

### REST APIからのチャネル・ロール指定方法
REST APIから記事投稿する際にLINE ConnectにてLINE通知させたい場合は以下のキー＆値をJSONデータに加えてください。
```
  "lc_channels":{
      "(通知させたいチャネルのシークレットの先頭4文字)":"ロール名（複数ある場合は「,」で区切る）"
  }
```

### LINE通知ログの保存
STREAMプラグインがインストールされ有効な場合、LINE通知の種類（マルチキャスト、プッシュメッセージ、ブロードキャスト）、何通送信したかのログがStream Recordsに記録され後から閲覧できます。

### LINEチャット
記事の投稿時に記事内容をLINEで送信するのではなく、単なるテキストメッセージとしてお好きな内容でLINEメッセージを送信することができます。
#### すべての友達、連携済みの友達、ロール指定して送信
管理画面メニューより「LINEチャット」ページを開きます。  
<img src="https://blog.shipweb.jp/wp-content/uploads/2022/01/lineconnect-ss-10.jpg" width="320">  
**Channel**  
送信する対象のチャネル（LINE公式アカウント）を選択します。  
**Type**  
どのユーザーグループに送信するかを指定します。  
- 全て：友達登録しているユーザー全員に、連携済みかどうかにかかわらず送信します。
- 連携済み：友達登録しているユーザーのうち、Wordpressと連携しているユーザーに送信します。
- ロール指定：連携済みのユーザーのうち、特定のロールに属するユーザーに送信します。
- ユーザー指定：送信するユーザーを一人ずつ個別に指定して送信します。（指定方法は後述）

**Role**  
Typeをロール指定にした場合、どのロールに属するユーザーに送信するかを選択します。  
**Message**  
送信するLINEメッセージ内容を入力します。  

#### ユーザーを個別に指定して送信
LINEチャットページからは送信するユーザーを個別に指定できないため、ユーザー一覧ページから対象ユーザーを指定してください。
**一人だけ選択する場合**  
LINE連携カラムの「連携済」リンクからLINEチャットページへ移動すると、そのユーザーが指定された状態になります。  
<img src="https://blog.shipweb.jp/wp-content/uploads/2022/01/lineconnect-ss-11.jpg" width="320">  
**複数ユーザーの選択**
対象ユーザーのチェックボックスにチェックを入れ、一括操作で「LINEメッセージ送信」を選択し「適用」ボタンをクリックします。  
<img src="https://blog.shipweb.jp/wp-content/uploads/2022/01/lineconnect-ss-12.jpg" width="320">  
チェックしたユーザーが指定された状態でLINEチャット画面が開きます。  
<img src="https://blog.shipweb.jp/wp-content/uploads/2022/01/lineconnect-ss-13.jpg" width="320">  

### WP LINE Loginとの連携機能
下記の条件を満たす場合、LINE Connectでユーザー連携を行った際に、WP LINE Loginにおいても、該当ユーザーをLINEログイン連携状態にさせることが可能です。  
* WP LINE Loginがインストールされている
* LINE Loginの設定で「Messaging APIチャネルシークレット」が、LINE Coonectの「チャネルシークレット」と一致している

連携解除した場合、LINEログイン連携状態も解除されます。

## カスタマイズ・プラグイン作成
その他さまざまなカスタマイズを有償で承ります。[連絡先はこちら](https://blog.shipweb.jp/contact)

# 必要動作環境
* Wordpress  4.9.13以上

# 制作者
* ship [blog](https://blog.shipweb.jp/)

# 謝辞
* 素晴らしいプラグイン「LINE AUTO POST」を開発してくださったGrowniche社の方々

# ライセンス
GPLv3