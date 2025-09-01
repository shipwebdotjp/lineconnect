# Update notification
This is a way to send LINE users notifications of updates to articles posted on WordPress.

## How to send update notification
### Select the post type you want to be notified about
1. Select the post type you want to be notified about from the Post Types in the Update Notification tab. Custom post types are also supported.

### When posting (post editor)
1. On the post edit screen, the "LINE Connect" box will appear in the right column. If you want to send a notification, check the "Send update notification" checkbox.  
   * If checked, notifications will be sent not only for new posts but also when posts are updated.  
   * If you check the "Send when a future post is published" checkbox and save, notifications will be sent to LINE when scheduled posts are published.
2. To include an image in the notification, set a featured image for the post.
3. Choose the recipients from the "Send target:" list.
   - For regular channels, you can choose "All Friends", "Linked Friends", or users in specific WordPress roles (multiple selection supported).
   - At the end of the channel list you will see "Audience". When you select Audience, the options shown are posts saved under the custom post type "Audience". Selecting multiple audiences will send the message to each specified audience separately (if the same user belongs to multiple audiences they may receive duplicate messages).
4. From the "Message Template" list, select the template to use. For the default template you can adjust general design and colors in the settings screen. For other LC message templates, you can fully customize the message content.

### Variables available when using LC messages as templates
|variable name|content|
|----:|----|
|`{{formatted_title}}`|Post title|
|`{{formatted_content}}`|Beginning 500 characters of the content with tags and line breaks removed|
|`{{post_thumbnail}}`|Featured image URL|
|`{{post_permalink}}`|Post permalink|
|`{{link_label}}`|"Read more" label|
|`{{alttext}}`|Title, content, and link concatenated (first 400 characters)|

#### WP_Post object properties
Each property of the [WP_Post](https://developer.wordpress.org/reference/classes/wp_post/) object can also be used as a template variable. Example:
|variable name|content|
|----:|----|
|`{{post_date}}`|Post date and time|
|`{{post_excerpt}}`|Post excerpt|
|`{{post_category.0}}`|First category ID|
|`{{post_category.1}}`|Second category ID|
|`{{tags_input.0}}`|First tag|

#### Custom field (post_meta) values
Custom field values for the post are available using `{{post_meta.custom_field_name}}`. Nested or array meta values can be referenced using dot notation (for example `{{post_meta.some_key.sub_key}}`).

Note: Templates now correctly expand WP_Post properties and post_meta values when using custom LC message templates.

---

### How to specify channels, roles, and audiences from the REST API
When posting (or updating) via the REST API and you want LINE Connect to send notifications, include the following in your JSON payload.

- Channel (traditional format)
```
"lc_channels": {
  "(the first four characters of the channel secret)": {
    "roles": ["role_name1", "role_name2"],
    "template": LC message post ID (0 for default template)
  }
}
```

- Audience specification (new format)
  - Use the `audience` key and provide Audience post IDs in the `roles` array.
```
"lc_channels": {
  "audience": {
    "roles": [123, 456],       // array of Audience post IDs
    "template": LC message post ID (0 for default template)
  }
}
```
This will send the message to each Audience identified by the given post IDs. If the same user belongs to multiple audiences, they may receive duplicate messages.

#### Backwards compatibility (versions 3.2.1 and earlier)
The older string format is still supported:
```
"lc_channels": {
  "(the first four characters of the channel secret)": "role_name (if multiple, separate with \",\")"
}
```

## Screenshots
LINE messages appear like this for subscribed users:  
<img src="https://blog.shipweb.jp/wp-content/uploads/2021/03/PNG-imageposttoline.png" width="320"></img>  
You can also customize link text, background color, and thumbnail aspect ratio.  
<img src="https://blog.shipweb.jp/wp-content/uploads/2021/03/PNG-imageposttolinecustom.png" width="320"></img>
