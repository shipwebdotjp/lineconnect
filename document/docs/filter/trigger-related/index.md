# Trigger-related filters

These hooks let you extend trigger hook registration and preprocess action hook payloads.

## Included hooks

- [slc_filter_predefined_action_hooks](#slc_filter_predefined_action_hooks): Extend the list of predefined WordPress action hooks.
- [slc_filter_preprocess_action_hook](#slc_filter_preprocess_action_hook): Preprocess the action hook payload before it is dispatched.
- [slc_filter_trigger_schema](#slc_filter_trigger_schema): Adjust the trigger schema.
- [slc_filter_trigger_type_schema](#slc_filter_trigger_type_schema): Adjust the trigger type schema.
- [slc_filter_trigger_type_uischema](#slc_filter_trigger_type_uischema): Adjust the trigger type UI schema.
- [slc_filter_trigger_uischema](#slc_filter_trigger_uischema): Adjust the trigger UI schema.

## slc_filter_predefined_action_hooks

Use this filter when you want to extend the list of predefined WordPress action hooks. It receives the hook list used by `ActionHooks::init()`, so you can add your own custom hook names there.

### Arguments

- `$hooks`: (array) The list of hook names to register.

### Example

Add a custom action hook so it can be selected in trigger settings.

```php
function my_filter_predefined_action_hooks( $hooks ) {
	$hooks[] = 'my_custom_hook';

	return $hooks;
}
add_filter( 'slc_filter_predefined_action_hooks', 'my_filter_predefined_action_hooks' );
```

## slc_filter_preprocess_action_hook

Use this filter to adjust the hook name or payload right before an Action Hook is dispatched. It runs at the beginning of `ActionHook::process()`, so you can normalize values before trigger conditions are evaluated.

### Arguments

- `$action_hook_args`: (array) The Action Hook payload. It usually contains:
    - `hook`: (string) The hook name.
    - `args`: (array) The hook arguments.

### Example

Inject extra data into a specific custom hook payload.

```php
function my_filter_preprocess_action_hook( $action_hook_args ) {
	if ( empty( $action_hook_args['hook'] ) || 'my_custom_hook' !== $action_hook_args['hook'] ) {
		return $action_hook_args;
	}

	if ( empty( $action_hook_args['args'] ) || ! is_array( $action_hook_args['args'] ) ) {
		$action_hook_args['args'] = array();
	}

	$action_hook_args['args']['source'] = 'manual';

	return $action_hook_args;
}
add_filter( 'slc_filter_preprocess_action_hook', 'my_filter_preprocess_action_hook' );
```
