# FAQ
## LINE Connectとはどのようなプラグインですか？
LINE Connectは、WordPressサイトとLINE公式アカウントを連携させるためのWordPressプラグインです。連携により、記事の更新通知をLINEで送信したり、連携済み・未連携のユーザー、各ロールごとに異なるリッチメニューを表示したり、一括メッセージ配信など、様々な機能を利用できるようになります。

## LINE ConnectでWordPressユーザーとLINEアカウントを連携させるにはどのような方法がありますか？
ユーザーは、LINE公式アカウントを友だち登録する、トーク画面で特定キーワードを送信する、またはリッチメニューなどのポストバックアクションを利用することで、WordPressアカウントとの連携を開始できます。その後、連携開始リンクをタップし、WordPress上でログインすることで連携が完了します。

## 記事更新の通知はどのように行えますか？
投稿画面のメタボックスで通知先を選ぶことで特定の投稿が公開または更新された際にLINEで連携済みのユーザーや特定のロールのユーザーにLINEで通知できます。カスタマイズされたテンプレートでの通知も可能です。

## LINE Connectで記事更新通知を送信する際に、どのようなカスタマイズが可能ですか？
記事更新通知では、通知する投稿タイプ、デフォルトの送信設定（チェックボックスの状態、送信対象ロール、メッセージテンプレート）、リンクラベルなどを設定できます。また、メッセージのスタイル（画像の表示スタイル、アスペクト比、背景色、文字色、リンクスタイルなど）を細かく調整することも可能です。さらに、LCメッセージ（メッセージテンプレート）を使用する際には、タイトル、本文、アイキャッチ画像URL、投稿パーマリンクなどの変数を埋め込んで、動的なメッセージを作成できます。

## LINE Connectでどのような種類のメッセージ配信ができますか？
主に以下のメッセージ配信が可能です。

一括配信: 様々な条件で抽出したユーザーに対して、任意のLINEメッセージを一度に送信できます。
ダイレクトメッセージ: 公式アカウントを友だちに追加しているか、過去7日以内にメッセージを送ったユーザーなど、イベントログにLINEユーザーIDが記録されているユーザーに対して、個別にテキストメッセージを送信できます（未連携ユーザーも対象）。
シナリオ配信（ステップ配信）: 事前に設定した複数のメッセージを、指定した時間間隔で順次ユーザーに配信できます。

## LINE Connectの「オーディエンス」機能とは何ですか？ どのように活用できますか？
オーディエンス機能は、メッセージの送信対象となるユーザーを、様々な条件（LINEチャネル、連携状態、WordPressロール、LINEユーザーID、WordPressユーザーID、メールアドレス、ユーザー名、表示名、ユーザーメタ、プロフィール情報など）を組み合わせて絞り込むことができる機能です。作成したオーディエンスは、一括配信やシナリオ実行時などに呼び出して、特定のユーザーグループに対して効率的にメッセージを送信するために活用できます。

## LINE Connectの「アクションフロー」とは何ですか？
アクションフローは、複数のアクションを連続して実行する機能であり、アクションの戻り値を次のアクションに渡す「アクションチェイン」の仕組みも備えています。  
アクションには、メッセージ送信やユーザーメタの更新、シナリオ開始などがあり、フィルターフックで独自のアクションを追加することも可能です。  

## LINE Connectの「トリガー」機能とは何ですか？
トリガーは、特定の発火条件（Webhookイベントの受信、指定日時、曜日など）を検知し、その条件が満たされた際に設定されたアクション（アクションフローを含む）を実行する機能です。例えば、特定のユーザーがLINE公式アカウントにメッセージを送信した際に、そのユーザーに対して自動的に返信メッセージを送信するなどの処理を実現できます。

## LINE Connectの「シナリオ」機能を利用すると、具体的にどのようなことができますか？
シナリオ機能を利用すると、以下のようなことが可能です。

ステップ配信: 新規友だち追加時などに、ウェルカムメッセージから始まり、段階的に情報提供や特定のアクションを促すメッセージを自動的に配信できます。
リマインダー: イベントやキャンペーンの前に、指定した日時にリマインダーメッセージを送信できます。
条件分岐のあるシナリオ: 特定のユーザー属性やアクションに応じて、異なるメッセージやアクションを実行するようにシナリオを分岐させることができます。

## LINE ConnectでChat GPT APIを利用したAI自動応答を設定できますが、どのような機能がありますか？
LINE Connectでは、Chat GPT APIを連携させることで、LINE公式アカウントに送信されたユーザーからのメッセージに対してAIが自動で応答する機能を利用できます。具体的には、使用するChat GPTモデルの選択、AIの応答の方向性を定めるシステムプロンプトの設定、過去の会話履歴を考慮した応答のための文脈数設定、応答の多様性を調整するサンプリング温度の設定などが可能です。さらに、Function Calling機能を有効にすることで、事前に設定した関数（例えば、ユーザー情報の取得、現在日時の取得、サイト内記事の検索など）をAIが利用し、より高度でパーソナライズされた応答を提供できます。また、未連携・連携済みのユーザーごとに1日の利用回数制限を設定することも可能です。

## リッチメニューの作成や設定に対応していますか？
既存リッチメニューやテンプレートを元にリッチメニューの作成が可能です。また、連携済みユーザーと未連携ユーザー、各ロールごとに異なるリッチメニューを出し分けして表示できます。

