# Schema-related filters

These hooks let you adjust schema and UI schema definitions used across admin forms and the Docusaurus docs.

## Included hooks

- [slc_filter_audience_schema](#slc_filter_audience_schema): Adjust the audience schema.
- [slc_filter_audience_uischema](#slc_filter_audience_uischema): Adjust the audience UI schema.
- [slc_filter_message_schema](#slc_filter_message_schema): Adjust the message schema.
- [slc_filter_message_uischema](#slc_filter_message_uischema): Adjust the message UI schema.
- [slc_filter_message_type_schema](#slc_filter_message_type_schema): Adjust the message type schema.
- [slc_filter_message_type_uischema](#slc_filter_message_type_uischema): Adjust the message type UI schema.
- [slc_filter_trigger_schema](#slc_filter_trigger_schema): Adjust the trigger schema.
- [slc_filter_trigger_uischema](#slc_filter_trigger_uischema): Adjust the trigger UI schema.
- [slc_filter_trigger_type_schema](#slc_filter_trigger_type_schema): Adjust the trigger type schema.
- [slc_filter_trigger_type_uischema](#slc_filter_trigger_type_uischema): Adjust the trigger type UI schema.
- [slc_filter_interaction_schema](#slc_filter_interaction_schema): Adjust the interaction schema.
- [slc_filter_interaction_uischema](#slc_filter_interaction_uischema): Adjust the interaction UI schema.
- [slc_filter_richmenu_schema](#slc_filter_richmenu_schema): Adjust the rich menu schema.
- [slc_filter_richmenu_uischema](#slc_filter_richmenu_uischema): Adjust the rich menu UI schema.

## slc_filter_*_schema

Use this hook to modify the various schema definitions.

### Arguments

- `$schema`: (array) The current schema.

### Example

Add a custom property to the schema.

```php
function my_filter_schema( $schema ) {
    $schema['properties']['custom_note'] = array(
        'type' => 'string',
        'title' => 'Custom note',
    );

    return $schema;
}
add_filter( 'slc_filter_audience_schema', 'my_filter_schema' );
```

## slc_filter_*_uischema

Use this hook to modify the various UI schema definitions.

### Arguments

- `$uischema`: (array) The current UI schema.

### Example

Display a field as a textarea.

```php
function my_filter_uischema( $uischema ) {
    $uischema['custom_note'] = array(
        'ui:widget' => 'textarea',
    );

    return $uischema;
}
add_filter( 'slc_filter_audience_uischema', 'my_filter_uischema' );
```
