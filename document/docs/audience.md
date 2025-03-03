# Audience
An **audience** refers to the target group for message delivery. You can configure which users to target by combining various conditions.

---

## Creating an Audience
You can create an audience from the **"Audience"** section in the management menu.

---

## Condition Types
You can filter the target audience using the following conditions:

---

### Channel (channel)
Filter users based on a specific LINE channel (official LINE account).

---

### Link Status (link)
Filter users based on the status of their connection with a LINE account.

- **All Friends (Broadcast):** Send messages to all friends (ignores all other conditions).
- **All Recognized Friends (Multicast):** Send to all recognized friends.
- **Linked:** Users linked to a WordPress account.
- **Not Linked:** Users not linked to a WordPress account.

**Note:** If **All Friends (Broadcast)** is selected, all other conditions are ignored, and the message is sent to all friends.

---

### Role (role)
Filter users based on their role in WordPress.

- **AND:** Users who have **all** of the specified roles.
- **OR:** Users who have **any** of the specified roles.
- **NOT:** Users who do **not** have any of the specified roles.

---

### LINE User ID (lineUserId)
Filter users by LINE User ID.
- When multiple IDs are entered, users who match **any** of the IDs will be targeted (**OR** condition).

---

### WordPress User ID (wpUserId)
Filter users by WordPress User ID.
- When multiple IDs are entered, users who match **any** of the IDs will be targeted (**OR** condition).

---

### Email Address (email)
Filter users by email address.
- When multiple addresses are entered, users who match **any** of the addresses will be targeted (**OR** condition).

---

### Username (userLogin)
Filter users by username.
- When multiple usernames are entered, users who match **any** of the usernames will be targeted (**OR** condition).

---

### Display Name (displayName)
Filter users by display name.
- When multiple display names are entered, users who match **any** of the display names will be targeted (**OR** condition).

---

### User Meta (usermeta)
Filter users based on user metadata.

- **Meta Key:** The field name of the user meta.
- **Comparison Method:** The operator used to search the user meta.
- **Value:** The value to search for.

#### Comparison Methods
The following comparison operators can be used:

- **=, !=, >, >=, <, <=**
  - `!=` targets users where the key exists and the value is different. If the key does not exist, it is **not** included.

- **Partial Match (LIKE), Not a Partial Match (NOT LIKE)**
  - Checks if a string is included.

- **Matches Any (IN), Matches None (NOT IN)**
  - Checks if any of the multiple values match.

- **Between (BETWEEN), Not Between (NOT BETWEEN)**
  - Checks if the value is between two values (including the boundary values).

- **Exists (EXISTS), Does Not Exist (NOT EXISTS)**
  - Checks if the meta key exists.

- **Regular Expression (REGEXP), Does Not Match Regex (NOT REGEXP)**
  - Checks using a regular expression.

**Note:** When multiple user meta conditions are added, users who match **any** of the conditions will be targeted (**OR** condition).

---

### Profile (profile)
Filter users based on user profile data.
- **Comparison operators** are the same as for **User Meta**.

**Note:** When multiple profile conditions are added, users who match **any** of the conditions will be targeted (**OR** condition).

---

### Audience Condition Group (group)
You can group and nest multiple conditions.
- Useful for setting up complex conditions.

---

## Combining Conditions
Specify how to combine multiple conditions:

- **Logical AND:** Targets users who meet **all** conditions (**default**).
- **Logical OR:** Targets users who meet **any** of the conditions.

**Exception:** When **Broadcast** is selected, all conditions **except for Channel** are ignored.

