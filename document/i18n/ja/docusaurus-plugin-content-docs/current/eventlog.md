# イベントログ
受信したWebhookイベントログを表示します。イベントタイプや送信したユーザー、メッセージの内容などを確認できます。
| 項目名           | 内容                                                                                       |
|------------------|--------------------------------------------------------------------------------------------|
| ID               | イベントログの連番                                                                          |
| イベントID       | 受信したwebhookイベントID                                                                  |
| イベントタイプ   | 受信したwebhookイベントタイプ                                                              |
| ソースタイプ     | 受信したwebhookイベントソース（受信した場合はuser、送信した場合はbot）                    |
| ユーザーID       | 受信元/送信先ユーザーID（フォローイベントが発生したユーザーの場合は表示名）<br />ホバー時に出る「メッセージ」リンクをクリックすると、そのLINEユーザーへダイレクトメッセージを送信する画面へ遷移します。 |
| チャネル         | 受信したチャネル                                                                           |
| メッセージタイプ | イベントタイプが「message」の場合のメッセージのタイプ                                      |
| メッセージ       | メッセージのテキスト、ファイルパス、ポストバックデータ                                     |
| 受信日時         | webhookイベントの受信日時                                                                  |
