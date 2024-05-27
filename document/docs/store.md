# Stored Formats
## LINE User ID
The LINE user ID, display name and profile image URL of the linked user are stored in the user meta with the key name `line`.
### Metadata Format
```
//Get user info for user ID 3.
$user_meta_line = get_user_meta(3, 'line', true);
var_dump($user_meta_line);

array(1) {
  ["the first four characters of the secret"]=>
  array(3) {
    ["id"]=>
    string(33) "LINE user ID"
    ["displayName"]=>
    string(12) "display name"
    ["pictureUrl"]=>
    string(135) "profile image URL"
  }
}
```
There is an array at the top level whose keys are the first 4 characters of the channel secret. It contains an array whose keys are `id`,`displayName`,`pictureUrl`.
