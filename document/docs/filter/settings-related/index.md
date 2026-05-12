# Settings-related filters

These hooks let you adjust plugin settings, channel options, and related admin commands.

## Included hooks

- [slc_filter_settings_option](#slc_filter_settings_option): Adjust the settings option definitions.
- [slc_filter_channnel_option](#slc_filter_channnel_option): Adjust the channel option definitions.
- [slc_filter_management_command](#slc_filter_management_command): Adjust the available management commands.
- [slc_filter_roles](#slc_filter_roles): Adjust the available roles list.

## slc_filter_settings_option

Use this hook when you want to change the definitions used to build the plugin settings screen. It is useful for adding, removing, or redefining settings fields.

### Arguments

- `$options`: (array) The current settings option definitions.

### Example

Change the label of an existing settings field.

```php
function my_filter_settings_option( $options ) {
    if ( isset( $options['line_token'] ) ) {
        $options['line_token']['title'] = 'Custom LINE token';
    }

    return $options;
}
add_filter( 'slc_filter_settings_option', 'my_filter_settings_option' );
```

## slc_filter_channnel_option

Use this hook when you want to change the channel option definitions shown in the channel settings area.

### Arguments

- `$options`: (array) The current channel option definitions.

### Example

Add a custom description to a channel field.

```php
function my_filter_channnel_option( $options ) {
    foreach ( $options as &$option ) {
        if ( isset( $option['id'] ) && $option['id'] === 'channel_name' ) {
            $option['description'] = 'Used as the display name in LINE Connect.';
        }
    }

    return $options;
}
add_filter( 'slc_filter_channnel_option', 'my_filter_channnel_option' );
```

## slc_filter_management_command

Use this hook to add, remove, or adjust management commands that are available from the admin UI or CLI integration.

### Arguments

- `$commands`: (array) The current management command list.

### Example

Register a custom command label.

```php
function my_filter_management_command( $commands ) {
    $commands['sync_users'] = array(
        'label' => 'Sync users',
        'description' => 'Synchronize LINE friends with WordPress users.',
    );

    return $commands;
}
add_filter( 'slc_filter_management_command', 'my_filter_management_command' );
```

## slc_filter_roles

Use this hook to change the list of roles that can be selected in LINE Connect settings and related screens.

### Arguments

- `$roles`: (array) The current role list.

### Example

Add a custom role to the selectable list.

```php
function my_filter_roles( $roles ) {
    $roles['line_manager'] = 'LINE Manager';

    return $roles;
}
add_filter( 'slc_filter_roles', 'my_filter_roles' );
```
