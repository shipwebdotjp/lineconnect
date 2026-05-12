# WP user alternative filters
These hooks let LINE Connect replace WordPress user-related functions with alternative implementations.

## Included hooks

- [slc_filter_use_alternative_user_provider](#slc_filter_use_alternative_user_provider): Enable or disable the alternative user provider path.
- [slc_filter_get_userdata](#slc_filter_get_userdata): Filter the `get_userdata()` result in normal mode.
- [slc_filter_get_userdata_alternative](#slc_filter_get_userdata_alternative): Provide custom user data when alternative mode is enabled.
- [slc_filter_get_user_meta](#slc_filter_get_user_meta): Filter the `get_user_meta()` result in normal mode.
- [slc_filter_get_user_meta_alternative](#slc_filter_get_user_meta_alternative): Provide custom user meta when alternative mode is enabled.
- [slc_filter_update_user_meta](#slc_filter_update_user_meta): Filter the result of `update_user_meta()` in normal mode.
- [slc_filter_update_user_meta_alternative](#slc_filter_update_user_meta_alternative): Perform a custom meta update when alternative mode is enabled.
- [slc_filter_delete_user_meta](#slc_filter_delete_user_meta): Filter the result of `delete_user_meta()` in normal mode.
- [slc_filter_delete_user_meta_alternative](#slc_filter_delete_user_meta_alternative): Perform a custom meta delete when alternative mode is enabled.
- [slc_filter_get_current_user_id](#slc_filter_get_current_user_id): Filter the current user ID in normal mode.
- [slc_filter_get_current_user_id_alternative](#slc_filter_get_current_user_id_alternative): Provide a custom current user ID when alternative mode is enabled.
- [slc_filter_is_user_id_valid](#slc_filter_is_user_id_valid): Filter the user ID validity check in normal mode.
- [slc_filter_is_user_id_valid_alternative](#slc_filter_is_user_id_valid_alternative): Validate a user ID when alternative mode is enabled.
- [slc_filter_get_linked_userids_by_roles_alternative](#slc_filter_get_linked_userids_by_roles_alternative): Provide linked user IDs for the given roles when alternative mode is enabled.

## Control hook

### slc_filter_use_alternative_user_provider
`UserProvider` uses this filter hook to decide whether to use the alternative user provider hooks.

#### Arguments

- `$use_alternative`: (bool) Whether to use the alternative path. The default is `false`.

#### Notes

If you return `true`, `UserProvider` will call the `*_alternative` hooks instead of the normal WordPress user functions.

#### Example

```php
add_filter('slc_filter_use_alternative_user_provider', '__return_true');
```

## Replacement hooks

### slc_filter_get_userdata
`UserProvider::get_userdata()` filters the result of `get_userdata()` in normal mode.

#### Arguments

- `$userdata`: (WP_User|false) The user object returned by WordPress.
- `$user_id`: (int) The user ID.

#### Example

```php
add_filter('slc_filter_get_userdata', function($userdata, $user_id) {
    // Perform custom processing on the WP_User object
    return $userdata;
}, 10, 2);
```

### slc_filter_get_userdata_alternative
Returns user data in alternative mode.

#### Arguments

- `$user_id`: (int) The user ID.

#### Notes

Return user data that matches the alternative storage or format. It is recommended to return an object that mimics the `WP_User` class. See the [Custom User Object example](#custom-user-object-example) below.

#### Example

```php
add_filter('slc_filter_get_userdata_alternative', function($user_id) {
    // Return a custom user object from alternative storage
    return new MyCustomUser($user_id);
});
```

### slc_filter_get_user_meta
`UserProvider::get_user_meta()` filters the result of `get_user_meta()` in normal mode.

#### Arguments

- `$meta_value`: (mixed) The meta value returned by WordPress.
- `$user_id`: (int) The user ID.
- `$key`: (string) The meta key.
- `$single`: (bool) Whether a single value is requested.

#### Example

```php
add_filter('slc_filter_get_user_meta', function($meta_value, $user_id, $key, $single) {
    // Modify the meta value returned from WordPress
    return $meta_value;
}, 10, 4);
```

### slc_filter_get_user_meta_alternative
Returns user meta in alternative mode.

#### Arguments

- `$user_id`: (int) The user ID.
- `$key`: (string) The meta key.
- `$single`: (bool) Whether a single value is requested.

#### Notes

Return the meta value retrieved from the alternative storage.

#### Example

```php
add_filter('slc_filter_get_user_meta_alternative', function($user_id, $key, $single) {
    // Fetch meta from a custom table
    global $wpdb;
    $value = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM my_custom_table WHERE user_id = %d AND meta_key = %s", $user_id, $key));

    $value = maybe_unserialize($value);
    return $single ? $value : [$value];
}, 10, 3);
```

### slc_filter_update_user_meta
`UserProvider::update_user_meta()` filters the result of `update_user_meta()` in normal mode.

#### Arguments

- `$updated`: (bool|int) The result returned by WordPress.
- `$user_id`: (int) The user ID.
- `$meta_key`: (string) The meta key.
- `$meta_value`: (mixed) The new meta value.
- `$prev_value`: (mixed) The previous meta value.

#### Example

```php
add_filter('slc_filter_update_user_meta', function($updated, $user_id, $meta_key, $meta_value, $prev_value) {
    // Perform additional actions when meta is updated
    return $updated;
}, 10, 5);
```

### slc_filter_update_user_meta_alternative
Performs a user meta update in alternative mode.

#### Arguments

- `$user_id`: (int) The user ID.
- `$meta_key`: (string) The meta key.
- `$meta_value`: (mixed) The new meta value.
- `$prev_value`: (mixed) The previous meta value.

#### Notes

Return the result of the alternative implementation.

#### Example

```php
add_filter('slc_filter_update_user_meta_alternative', function($user_id, $meta_key, $meta_value, $prev_value) {
    // Update meta in a custom table
    global $wpdb;
    $result = $wpdb->update(
        'my_custom_table',
        ['meta_value' => maybe_serialize($meta_value)],
        ['user_id' => $user_id, 'meta_key' => $meta_key]
    );
    return $result !== false;
}, 10, 4);
```

### slc_filter_delete_user_meta
`UserProvider::delete_user_meta()` filters the result of `delete_user_meta()` in normal mode.

#### Arguments

- `$deleted`: (bool) The result returned by WordPress.
- `$user_id`: (int) The user ID.
- `$meta_key`: (string) The meta key.
- `$meta_value`: (mixed) The value to delete.

#### Example

```php
add_filter('slc_filter_delete_user_meta', function($deleted, $user_id, $meta_key, $meta_value) {
    // Perform additional actions when meta is deleted
    return $deleted;
}, 10, 4);
```

### slc_filter_delete_user_meta_alternative
Performs a user meta delete in alternative mode.

#### Arguments

- `$user_id`: (int) The user ID.
- `$meta_key`: (string) The meta key.
- `$meta_value`: (mixed) The value to delete.

#### Notes

Return the delete result from the alternative implementation.

#### Example

```php
add_filter('slc_filter_delete_user_meta_alternative', function($user_id, $meta_key, $meta_value) {
    // Delete meta from a custom table
    global $wpdb;
    $result = $wpdb->delete('my_custom_table', ['user_id' => $user_id, 'meta_key' => $meta_key]);
    return $result !== false;
}, 10, 3);
```

### slc_filter_get_current_user_id
`UserProvider::get_current_user_id()` filters the current user ID in normal mode.

#### Arguments

- `$user_id`: (int) The current user ID.

#### Example

```php
add_filter('slc_filter_get_current_user_id', function($user_id) {
    // Override the current user ID logic
    return $user_id;
});
```

### slc_filter_get_current_user_id_alternative
Returns the current user ID in alternative mode.

#### Arguments

- `$current_user_id`: (mixed) A placeholder current user ID passed from `UserProvider`.

#### Notes

Return the ID that should be treated as the current user.

#### Example

```php
add_filter('slc_filter_get_current_user_id_alternative', function($current_user_id) {
    // Return current user ID from a custom session or cookie
    return isset($_SESSION['my_custom_user_id']) ? $_SESSION['my_custom_user_id'] : 0;
});
```

### slc_filter_is_user_id_valid
Filters the user ID validity check in normal mode.

#### Arguments

- `$is_valid`: (bool) Whether the user ID is valid.
- `$user_id`: (mixed) The user ID being checked.

#### Example

```php
add_filter('slc_filter_is_user_id_valid', function($is_valid, $user_id) {
    // Perform custom validation on the user ID
    return $is_valid;
}, 10, 2);
```

### slc_filter_is_user_id_valid_alternative
Validates a user ID in alternative mode.

#### Arguments

- `$user_id`: (mixed) The user ID being checked.

#### Example

```php
add_filter('slc_filter_is_user_id_valid_alternative', function($user_id) {
    // Check if the user ID exists in the custom table
    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM my_custom_table WHERE user_id = %d", $user_id)) > 0;
});
```

### slc_filter_get_linked_userids_by_roles_alternative
Returns linked user IDs for the specified roles in alternative mode.

#### Arguments

- `$roles`: (array) The roles to look up.

#### Notes

Return the array of user IDs linked to LINE for the specified roles.

#### Example

```php
add_filter('slc_filter_get_linked_userids_by_roles_alternative', function($roles) {
    // Return custom user IDs that have been linked with LINE and have the specified roles
    global $wpdb;
    // Assuming roles are handled via status codes in a custom table
    return $wpdb->get_col("SELECT user_id FROM my_custom_table WHERE status IN (1, 2) AND has_line_link = 1");
});
```


### slc_wp_user_query_alternative
Returns a `WP_User_Query`-like result in alternative mode. Use this hook when user records live outside `wp_users`, or when a custom user management system needs to map query arguments to its own lookup logic.

#### Arguments

- `$args`: (array) Query arguments compatible with `WP_User_Query`.

#### Notes

Your callback can interpret `role__in`, `role__not_in`, `include`, `search`, `search_columns`, and `meta_query`, then return an array of user IDs.

#### Example

```php
add_filter('slc_wp_user_query_alternative', function($args) {
    // Use $args to run your own query and return the matching user ID array.
    $query_args = $args;
    $query_args['fields'] = 'ID';

    return get_users($query_args);
});
```

### slc_get_user_from_line_id
Returns a user resolved from a LINE ID. It is intended for adapters that need to map a LINE user ID to their own stored user data.

#### Arguments

- `$user`: (false|object) The initial result passed into the filter. It is typically `false`, and should be replaced with a matched user object when found.
- `$secret_prefix`: (string) The prefix that identifies the LINE channel.
- `$line_id`: (string) The LINE user ID.

#### Notes

The hook should search your storage using the LINE ID and any channel-specific identifier, then return the matching user object. If no match is found, return `false`.

#### Example

```php
add_filter('slc_get_user_from_line_id', function($user, $secret_prefix, $line_id) {
    // Search your storage by LINE ID and return the matched user object, or false.
    return /* matched user object or false */;
}, 10, 3);
```

### slc_roles
Adds custom statuses or pseudo roles to the role list.

#### Arguments

- `$roles`: (array) The existing role list.

#### Notes

Custom statuses do not need to map 1:1 to WordPress roles, but this hook lets you register pseudo roles such as `member_status_0` in the role list.

#### Example

```php
add_filter('slc_roles', function($roles) {
    // Add or adjust a custom status label
    $roles['member_status_99'] = [
        'name' => 'VIP Member',
    ];

    return $roles;
});
```

## Custom User Object example

When using alternative mode, `slc_filter_get_userdata_alternative` should return an object that mimics `WP_User`. At a minimum, it should have an `ID` property and a `roles` array.

```php
class MyCustomUser {
    public int $ID;
    public array $roles = [];
    public string $display_name;
    public string $user_email;

    public function __construct($user_id) {
        $this->ID = $user_id;
        // Load data from custom source
        $this->display_name = 'Custom User';
        $this->roles = ['custom_role'];
    }

    public function exists(): bool {
        return $this->ID > 0;
    }

    // Optional: Implement __get to handle other WP_User properties
    public function __get($name) {
        return $this->$name ?? null;
    }
}
```
