# LINE Connectとは？

LINE ConnectはWordpressユーザーとLINE公式アカウントのLINEユーザーを連携させるプラグインです。  
Wordpressの投稿を連携済みのWordpressユーザーへ通知したり、連携済みかそうでないかに応じて異なるリッチメニューを登録することができます。  
Growniche社の[LINE AUTO POST](https://s-page.biz/line-auto-post/#home)を元に改変したものです。  
公式ブログ記事→[LINEと連携させるWordPressプラグイン「LINE Connect」](https://blog.shipweb.jp/lineconnect/)  
デモサイト→[SHIP LAB](https://gpt.shipweb.jp/)  

## 機能 
### Wordpressユーザーとの連携機能
* LINE Messaging APIを利用して、LINE公式アカウントの友達とWordpressの登録ユーザーのアカウントを連携可能
* リッチメニューIDを連携済みのユーザーとそうでないユーザーとで分けて設定可能
### 更新通知
* 連携済みのユーザー、特定のロールのユーザーにLINEで記事の更新通知を送信
* 通知メッセージは、アイキャッチ画像を含めたカード型レイアウトで通知
* 通常の公開時、更新時に加えて、予約投稿の公開時にも通知可能
* 記事にコメントがあった際、投稿者へのLINE通知が可能
### 一括配信
* 任意のLINEテキストメッセージを個別ユーザーに送信可能
* LINEメッセージを保存しておき、アクションで送信するメッセージに利用可能
### ダイレクトメッセージ
* 未連携ユーザーでも、イベントログの残っているLINEユーザーへテキストメッセージを送信可能
### チャット連携
* Chat GPT APIを利用したメッセージへの自動応答(AIチャットボット機能)
* メッセージやポストバックアクションをトリガーとして、指定したアクションを実行可能
* スケジュールした時刻/日付/曜日に、LINEメッセージ送信などの指定したアクションを実行可能
### WP LINE Loginとの連携機能
* WP LINE Loginをインストールし、Messaging APIが設定されている場合、連携と同時にログイン連携も可能
