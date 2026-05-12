# Translation-related filters

These hooks let you customize translation strings used by the React JSON Schema Form UI.

## Included hooks

- [slc_filter_rjsf_translate_string](#slc_filter_rjsf_translate_string): Customize strings shown by the React JSON Schema Form components.

## slc_filter_rjsf_translate_string

This filter lets you override the translation strings used by the React JSON Schema Form UI.
It is useful when you want to adjust labels, button text, or error messages shown in admin screens.

### Arguments

- `$translateString`: (array) An associative array of translation strings used by React JSON Schema Form.

### Example

This example changes a few labels shown in the form UI.

```php
add_filter( 'slc_filter_rjsf_translate_string', function ( $translateString ) {
    $translateString['Add'] = 'Create';
    $translateString['Close'] = 'Dismiss';
    $translateString['Errors'] = 'Validation errors';

    return $translateString;
} );
```
