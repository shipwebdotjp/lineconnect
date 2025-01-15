# Rich Menu
You can set different rich menus for linked users, unlinked users, and users with different roles.

## Managing Rich Menus
The Rich Menu page displays a list of created rich menus.
On the Rich Menu list page, you can perform the following operations:

1. Create a new rich menu based on an existing one
2. Delete a rich menu

:::info
Once created, a rich menu cannot be edited. If changes are needed, create a new rich menu based on the existing one.
:::

## Creating a Rich Menu
To use rich menus, first create one.
※Rich menus created in [LINE Official Account Manager](https://manager.line.biz/) cannot be used.
Rich menus created using the Messaging API are required.
You can also use tools like [Rich Menu Editor](https://richmenu.app.e-chan.me/).

1. Open the "Rich Menu" page from the admin menu.
2. To create from an empty layout, click "Create New from Template".
3. Alternatively, click "Use as Template" from the Rich Menu list.
4. Set the rich menu properties and save.

## Setting Images for Rich Menus
5. Upload an image file.
If you used an existing rich menu as a template, its image will be initially set.

### Rich Menu Image Requirements
- Image format: JPEG or PNG
- Image width: 800px to 2500px
- Image height: Minimum 250px
- Aspect ratio (width ÷ height): 1.45 or higher
- Maximum file size: 1MB

:::info
Images set in rich menus cannot be replaced later. To update a rich menu image, create a new rich menu and set a new image.
:::

## Rich Menu Properties
### Name
The name of the rich menu. Not displayed to users.
We recommend using unique titles for easy identification.

### Size
The width and height of the rich menu displayed in the chat room. Width must be between 800px and 2500px, and height must be at least 250px.
However, the aspect ratio (width ÷ height) must be 1.45 or higher.

### Tap Areas
Coordinates and sizes of tap areas.
Maximum: 20

### Display Rich Menu by Default
Whether to display the rich menu by default.

### Chat Bar Text
Text displayed in the chat room menu.

## Displaying Rich Menus
1. Select the created rich menu in the Channel tab of LINE Connect settings.
2. Select rich menus for linked friends, unlinked friends, and each role from the dropdown.
3. Different rich menus will be displayed based on link status and role.

:::info
If newly added rich menus from APIs etc. do not appear in the dropdown list, use "Update Rich Menu List" in the Data tab.
:::

## Rich Menu List
Registered rich menus are displayed in a list.
- Rich Menu Name
- Image
- Use as Template
- Delete

## Aliases
Screen for creating and managing rich menu aliases.
By setting created aliases in rich menu switch actions, you can switch rich menus.
### Creating Aliases
1. Enter any ID in "Alias ID". Maximum 32 characters, using only alphanumeric characters, hyphens, and underscores.
2. Select the rich menu to associate with the alias.
3. Click "Create".

### Alias List
Registered aliases are displayed in a list.
- Alias ID
- Associated Rich Menu
### Updating Aliases
Select a rich menu from the dropdown and click "Update".
### Deleting Aliases
Click "Delete".
