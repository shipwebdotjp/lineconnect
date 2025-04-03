# Dashboard

This is the dashboard feature that displays statistics for each channel. You can check the overall monthly overview and daily details for specific channels.

## Monthly Channel Statistics

Displays a list of statistics for each channel for the specified year and month. By default, statistics for the current year and month are displayed.

**Features:**

* **Channel Search:** Filter the displayed channels by channel name.
* **Year/Month Selection:** Select the year and month for the statistics display from the dropdown menu. You can choose from the past 4 years plus the current year.
* **Previous/Next Month:** Navigate the displayed statistics month by month.
* **Refresh:** Update to the latest statistical information.

**Displayed Items:**

* **Channel Name:** The channel's icon and name. Clicking it navigates to the daily statistics screen for that channel.
* **Friends added:** The total number of friends added as of the end of the specified month (this number does not decrease even if they block the channel).
* **Target reach:** The total number of reachable friends (users who have added the friend and have not blocked it) as of the end of the specified month.
* **Blocks:** The total number of users who are blocking the channel as of the end of the specified month.
* **Recognized count:** The number of friends recognized by LINE Connect.
* **Linked:** The number of friends who have linked their LINE Connect account with their WordPress account.
* **Paid Messages:** The total number of billable messages sent in the specified month. This includes messages sent via LINE Official Account Manager (including targeted and step messages) and messages sent via the Messaging API's "Push API", "Multicast API", "Broadcast API", and "Narrowcast API".
* **Profile:** A link to the profile page of the channel's LINE Official Account.

## Daily Channel Statistics

Displays daily statistical information for a specific channel for the selected year and month. You can access this screen by clicking the **Channel Name** of the desired channel on the Monthly Channel Statistics list screen.

**Features:**

* **Year/Month Selection:** Select the year and month for the statistics display from the dropdown menu. You can choose from the past 4 years plus the current year.
* **Previous/Next Month:** Navigate the displayed statistics month by month.
* **Refresh:** Update to the latest statistical information.
* **Back to Overview:** Return to the Monthly Channel Statistics list screen.

**Displayed Items:**

* **Date:** The date of the statistical information.
* **Followers:** The total number of friends added up to the end of that day.
* **Target reach:** The number of target reachable friends at the end of that day.
* **Blocks:** The total number of blocks at the end of that day.
* **Linked:** The total number of linked users at the end of that day.
* **New Follows:** The number of new friends added on that day.
* **Unfollows:** The number of users who newly blocked the channel on that day.
* **New Links:** The number of users who newly linked their accounts on that day.
* **Unlinks:** The number of users who unlinked their accounts on that day.
* **Paid Messages:** The total number of billable messages sent on that day.