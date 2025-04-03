# Bulk Message

You can send a bulk LINE message to users extracted based on various conditions.  
Users can be extracted by recalling an LC audience or creating a new set of extraction criteria.  
Messages can be used by creating a new LC message or recalling a saved message.  

## Settings
### Audience

The extraction criteria for target users are called "audiences."  
Note that these audiences are different from the ones in the official LINE account.  
To distinguish them, we refer to the extraction criteria created in LINE Connect as **LC audiences** and those in the official LINE account as **LINE audiences**.  

In the template selection box, you can choose to create a new audience or recall an existing one.  
If you want to create a new one, select **New Audience**.  
If you select an already created audience, it will be loaded, and you can modify it if necessary.  
For more details on audiences, please see [Audiences](./audience.md).  

### Message

In the template selection box, you can choose to create a new message or recall a saved message.  
If you select a saved message, it will be loaded, and you can modify it if necessary.  
For more information on LC messages, please see [LC Messages](./message.md).  

### Disable Notifications

If you want to send a message without notifying users, enable the **Disable Notifications** option.  

## Specifying Target Users from the Admin User Page  

### Selecting a Single User  

The display name in the LINE column acts as a link. Clicking this link will take you to the bulk message page with the corresponding LINE user ID pre-selected.  
<img src="https://blog.shipweb.jp/wp-content/uploads/2023/08/%E3%82%B9%E3%82%AF%E3%83%AA%E3%83%BC%E3%83%B3%E3%82%B7%E3%83%A7%E3%83%83%E3%83%88-2023-08-08-23.02.40.png" width="320" />  

### Selecting Multiple Users  

Check the checkboxes for the target users, select **Send LINE Message** from the bulk action dropdown, and click the **Apply** button.  
<img src="https://blog.shipweb.jp/wp-content/uploads/2022/01/lineconnect-ss-12.jpg" width="320" />  
The bulk message page will open with the selected WordPress user IDs pre-specified.  
<img src="https://blog.shipweb.jp/wp-content/uploads/2022/01/lineconnect-ss-13.jpg" width="320" />  