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

## Replacement hooks

### slc_filter_get_userdata
`UserProvider::get_userdata()` filters the result of `get_userdata()` in normal mode.

#### Arguments

- `$userdata`: (WP_User|false) The user object returned by WordPress.
- `$user_id`: (int) The user ID.

### slc_filter_get_userdata_alternative
Returns user data in alternative mode.

#### Arguments

- `$user_id`: (int) The user ID.

#### Notes

Return user data that matches the alternative storage or format.

### slc_filter_get_user_meta
`UserProvider::get_user_meta()` filters the result of `get_user_meta()` in normal mode.

#### Arguments

- `$meta_value`: (mixed) The meta value returned by WordPress.
- `$user_id`: (int) The user ID.
- `$key`: (string) The meta key.
- `$single`: (bool) Whether a single value is requested.

### slc_filter_get_user_meta_alternative
Returns user meta in alternative mode.

#### Arguments

- `$user_id`: (int) The user ID.
- `$key`: (string) The meta key.
- `$single`: (bool) Whether a single value is requested.

#### Notes

Return the meta value retrieved from the alternative storage.

### slc_filter_update_user_meta
`UserProvider::update_user_meta()` filters the result of `update_user_meta()` in normal mode.

#### Arguments

- `$updated`: (bool|int) The result returned by WordPress.
- `$user_id`: (int) The user ID.
- `$meta_key`: (string) The meta key.
- `$meta_value`: (mixed) The new meta value.
- `$prev_value`: (mixed) The previous meta value.

### slc_filter_update_user_meta_alternative
Performs a user meta update in alternative mode.

#### Arguments

- `$user_id`: (int) The user ID.
- `$meta_key`: (string) The meta key.
- `$meta_value`: (mixed) The new meta value.
- `$prev_value`: (mixed) The previous meta value.

#### Notes

Return the result of the alternative implementation.

### slc_filter_delete_user_meta
`UserProvider::delete_user_meta()` filters the result of `delete_user_meta()` in normal mode.

#### Arguments

- `$deleted`: (bool) The result returned by WordPress.
- `$user_id`: (int) The user ID.
- `$meta_key`: (string) The meta key.
- `$meta_value`: (mixed) The value to delete.

### slc_filter_delete_user_meta_alternative
Performs a user meta delete in alternative mode.

#### Arguments

- `$user_id`: (int) The user ID.
- `$meta_key`: (string) The meta key.
- `$meta_value`: (mixed) The value to delete.

#### Notes

Return the delete result from the alternative implementation.

### slc_filter_get_current_user_id
`UserProvider::get_current_user_id()` filters the current user ID in normal mode.

#### Arguments

- `$user_id`: (int) The current user ID.

### slc_filter_get_current_user_id_alternative
Returns the current user ID in alternative mode.

#### Arguments

- `$current_user_id`: (mixed) A placeholder current user ID passed from `UserProvider`.

#### Notes

Return the ID that should be treated as the current user.

### slc_filter_is_user_id_valid
Filters the user ID validity check in normal mode.

#### Arguments

- `$is_valid`: (bool) Whether the user ID is valid.
- `$user_id`: (mixed) The user ID being checked.

### slc_filter_is_user_id_valid_alternative
Validates a user ID in alternative mode.

#### Arguments

- `$user_id`: (mixed) The user ID being checked.

### slc_filter_get_linked_userids_by_roles_alternative
Returns linked user IDs for the specified roles in alternative mode.

#### Arguments

- `$roles`: (array) The roles to look up.

#### Notes

Return the array of user IDs linked to LINE for the specified roles.
