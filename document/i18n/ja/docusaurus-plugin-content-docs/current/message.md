---
title: 'LCメッセージ'
---
# LCメッセージ(メッセージテンプレート)
各種LINEメッセージをあらかじめテンプレートとして作成しておき、LINEメッセージ取得や送信アクション実行時に使用することができます。
一回の送信で最大5つのメッセージを送信できます。
### メッセージタイプ
メッセージタイプについては[メッセージタイプ | LINE Developers](https://developers.line.biz/ja/docs/messaging-api/message-types/)を参照してください。  
作成したメッセージは「LINE Connectメッセージ取得」アクションで呼び出して使用することができます。  
アクションで「LINE Connectメッセージ取得」を選択し、パラメーターのslc_message_idから使用したいメッセージを選択します。  

### 差し込み
`{{キー}}`をメッセージ文中に埋め込むことで、アクション実行時に、名前などキーに対応する値を差し込むことができます。  

例えば、「`{{name}}`さん友だち追加ありがとうございます！」という内容でテキストメッセージを作成し保存しておきます。  
トリガーでイベントタイプをフォローに設定し、アクションで「LINE Connectメッセージ取得」を選択し、parametersのslc_message_idでは作成しておいたメッセージを選択します。  
args（メッセージ置換用引数）を追加し、キーに`name`、値(name)に`{{$.user.data.display_name}}`を設定します。  
これにより、メッセージ文中の`{{name}}`がユーザーの表示名で置き換えられて送信されます。  