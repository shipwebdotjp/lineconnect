# Update notification
This is a way to send LINE users notifications of updates to articles posted on WordPress.

## How to send update notification
### Select the post type you want to be notified about
1. select post type you want to be notified about from the Post Types in the Update Notification tab. Custom post types are also supported.
### When posting
1. on the post edit screen, the "LINE Connect" box will appear in the right column, and if you want to be notified, check the "Send update notification" checkbox.  
*If you have checked, notifications will be sent not only of new posts published, but also posts updated.  
*If you check the "Send when a future post is published" checkbox and save it, notifications will be sent to LINE when the future posts are published.
2. If a post has an futured image, notifications will be sent with the image.
3. From the "Send target:" list, select the users to be notified by LINE from "All Friends", "Linked Friends", and each of the roles. You can select multiple targets.
4. From the “Message Template” list, select the template you wish to use. For the default template, you can adjust the general design and colors in the settings screen. For other messages, you can customize to the smallest detail.

### Variables that can be used when using LC messages as message templates
|variable name|content|
|----:|----|
|{{formatted_title}}|Post title|
|{{formatted_content}}|500 characters at the beginning of the text without tags, line breaks, etc.|
|{{post_thumbnail}}|Thumbnail image URL|
|{{post_permalink}}|post permalink|
|{{link_label}}|“Read more” label|
|{{alttext}}|Title, body, and link combined first 400 characters|

### Properties of the WP_Post object
Each property of the [WP_Post](https://developer.wordpress.org/reference/classes/wp_post/) object can also be used as a variable.  
Example:
|variable name|content|
|----:|----|
|{{post_date}}|Post date and time|
|{{post_excerpt}}|excerpt of the post|
|{{post_category.0}}|first category ID|
|{{post_category.1}}|second category ID|
|{{tags_input.0}}|first tag|

#### Custom Field Values
Custom field values for posts are also available in `{{post_meta.custom_field_name}}`.

### How to Specify Channels and Roles from the REST API
If you would like to have LINE Connect send LINE notifications when posting articles from the REST API, please add the following keys and values to the JSON data.
```
  "lc_channels":{
      "the first four characters of the secret":{
        "roles": ["target role name1", "target role name2"],
        "template": Post ID of LC message or 0(Defult template)
      }
  }
```

For versions 3.2.1 and earlier, the format is as follows 
```
  "lc_channels":{
      "the first four characters of the secret":"Role name (if there are multiple roles, separate them with ",")"
  }
```

## Screen shots
LINE messages are displayed like this:  
<img src="https://blog.shipweb.jp/wp-content/uploads/2021/03/PNG-imageposttoline.png" width="320"></img>  
You can also customize link text, background color, and thumbnail aspect ratio.  
<img src="https://blog.shipweb.jp/wp-content/uploads/2021/03/PNG-imageposttolinecustom.png" width="320"></img>    