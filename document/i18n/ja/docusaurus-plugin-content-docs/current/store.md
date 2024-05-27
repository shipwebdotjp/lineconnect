# 保存形式
## LINEユーザーID
連携済みユーザーのLINEユーザーID、表示名、プロフィール画像URLはユーザーメタに`line`というキー名で保存されています。
### メタデータの形式  
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
複数チャネルに対応しているため、チャネルシークレットの先頭4文字をキーとする連想配列がトップレベルにあり、その中に`id`,`displayName`,`pictureUrl`をキーとする連想配列が含まれます。