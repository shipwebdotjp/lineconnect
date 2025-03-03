# Scenario
## Overview
A scenario is a configuration of a series of actions in sequence. It enables step-by-step delivery functionality where multiple messages can be sent sequentially at specified intervals.  
Beyond message delivery, scenarios can execute other actions and set conditions to determine whether specific steps should be executed.  
By combining with actions that move to specific steps, you can create not only linear scenarios but also branching scenarios.

## How to Create a Scenario
1. From the admin screen, click "Add New" in the "Scenarios" menu.

## How to Start a Scenario
You can start a scenario using the "Start LC Scenario" action.
### Scenario ID
Specify the ID of the scenario to start.

### Behavior when the scenario is already started
Specify the behavior when the scenario is already started.
- **Never restart**: Does nothing if the scenario is already started
- **Restart only completed**: Only restarts from the beginning if the scenario is completed.
- **Always restart**: Always restarts the scenario regardless of its status.

### LINE User ID
LINE User ID. The default value is the LINE user ID of the event source if not specified.

### Channel
First 4 characters of the channel secret. The default is the channel of the event source if not specified.

## Set Scenario Step
This action is used to move a specific user who is subscribing to a scenario to a specific step.

### Parameters
- **Scenario ID**: Scenario ID to execute
- **Step ID**: Step ID to set
- **Next execution date**: The next date to execute. You can set absolute date or relative time settings. Specify a string that can be interpreted by PHP's `strtotime` function.
- **LINE User ID**: LINE User ID of the target user
- **Channel**: Channel of the target user

## Change Scenario Status
This action changes the status of a scenario that a specific user is subscribing to.

### Parameters
- **Scenario ID**: Scenario ID to execute
- **Status**: Status to change to
    - **Active**: Sets the scenario to active status.
    - **Paused**: Sets the scenario to paused status.
    - **Error**: Sets the scenario to error status.
    - **Completed**: Sets the scenario to completed status.
- **LINE User ID**: LINE User ID of the target user
- **Channel**: Channel of the target user

## Components of a Scenario
### Steps
#### ID
An identifier to distinguish the step.  
It must be unique within the same scenario.  

#### Destination Conditions
Sets the conditions for executing this step. The following items can be set as conditions:
- Channel
- Destination (User ID, Group ID, Room ID)
- Connection status
- Role
- User meta value
- Profile item value

If no conditions are specified, the step will always be executed. If conditions are specified, the actions in this step will only be executed when the conditions are met.

#### Action
Set the action to be executed in this step.  
Commonly used actions include:  
- Get LINE Connect Message
- Get LINE Text Message

By checking "Send return value as LINE message", you can send the message obtained by the above actions to the user.

#### Action Chain
A mechanism to inject the return value of any action into the arguments of subsequent actions.  
For details, refer to the action chain in [Trigger](./trigger.md).  

#### Finish scenario execution
Check this box to end the scenario execution.

#### Next Step
Specify the next step ID.  
If not specified, the step will proceed to the next positioned step.  

## Schedule Configuration
You can set detailed timing for step execution.  
Steps will be automatically executed based on the configured schedule.  

### Setting Items
#### Types of Schedule Configuration
You can select from the following two types for the timing of the next step:
- Absolute date and time
- Relative interval value

#### Absolute Date Time
Select this when you want to execute a step at a specific date and time.
- **Absolute Date Time**: Specify the exact date and time to execute the next step.

#### Relative Interval Value
Specify the relative time interval until the execution of the next step.
- **Relative Interval Value**: Specify the interval value.
- **Interval Unit**: Select the unit of time interval.
    - Minutes
    - Hours
    - Days
    - Weeks
    - Months
    - Years
- **Execution Timing**: Specify when to execute.
    - **Exact Interval Execution**: Executes immediately after the set interval has elapsed.

### Detailed Settings by Unit
#### Minutes
When the interval is in minutes, you cannot specify seconds.

#### Hours
When the interval is in hours, you can choose to execute exactly after the interval or specify which minute to execute after adding the hour interval.
- **Align to Minute Marker**: Executes at the specified minute after the specified number of hours have elapsed.
- **Minute**: Specify the "minute" of the time (e.g., 5 for HH:05).

**Note**: Please note that this doesn't necessarily execute after exactly the specified time (e.g., 1 hour = 60 minutes if the relative interval value is 1). Instead, it executes at the minute of the time with the specified interval value added.
- Example 1) If a step is executed at 2025/02/09 20:45:00 and 50 is specified for minutes, it will be executed at 2025/02/09 21:50:00.
- Example 2) If a step is executed at 2025/02/09 23:45:00 and 10 is specified for minutes, it will be executed at 2025/02/10 00:10:00.

#### Days
When the interval is in days, you can choose to execute exactly after the interval or specify what time to execute on the day after adding the day interval.
- **Specified Date Time**: Executes at the set time on the day with the specified number of days added.
- **Time (HH:mm)**: Specify the time in HH:mm format (e.g., 12:10).

**Note**: Please note that this doesn't necessarily execute after exactly the specified number of days (e.g., 1 day = 24 hours if the relative interval value is 1). For 1 day, it executes at the specified time the next day.
- Example 1) If a step is executed at 2025/02/09 20:45:00, with 1 specified for days and 10:30 specified for time, it will be executed at 2025/02/10 10:30:00.
- Example 2) If a step is executed at 2025/02/09 20:45:00, with 1 specified for days and 22:00 specified for time, it will be executed at 2025/02/10 22:00:00.

#### Weeks
When the interval is in weeks, you can choose to execute exactly after the interval or specify which day of the week and what time to execute after adding the week interval.
- **Exact Time Execution**: Executes at the specified time on the exact date after the set number of weeks has elapsed.
- **Align to Weekday and Time**: Executes at the specified time on the specified day of the week.
  - **Day of the week**: Specify the day of the week (e.g., Saturday).
  - **Time (HH:mm)**: Specify the time in HH:mm format (e.g., 12:10).

**Note**: It executes on the first occurrence of the specified day of the week after the specified number of weeks (1 week if the relative interval value is 1) has elapsed. If the relative interval value is 0, it will be the next occurrence of the day of the week. However, if it's the same day of the week, it will be the current day.
- Example with relative interval value 1) If a step is executed at 2025/02/09 (Sun) 20:45:00, with Wednesday specified for the day of the week and 15:30 for time, it will be executed at 2025/02/19 (Wed) 15:30:00. (The first Wednesday after counting from the 16th, which is 1 week later)
- Example with relative interval value 1) If a step is executed at 2025/02/09 (Sun) 20:45:00, with Sunday specified for the day of the week and 10:00 for time, it will be executed at 2025/02/16 (Sun) 10:00:00. (The same day of the week after 1 week)
- Example with relative interval value 0) If a step is executed at 2025/02/09 (Sun) 20:45:00, with Sunday specified for the day of the week and 10:00 for time, it would be 2025/02/09 (Sun) 10:00:00, but since this is earlier than the current time, it will be executed immediately.
- Example with relative interval value 0) If a step is executed at 2025/02/09 (Sun) 20:45:00, with Wednesday specified for the day of the week and 15:30 for time, it will be executed at 2025/02/12 (Wed) 15:30:00. (The next Wednesday)

#### Months
When the interval is in months, you can choose to execute exactly after the interval or specify which day and what time to execute in the month after adding the month interval.
- **Exact Time Execution**: Executes at the specified time on the exact date after the set number of months has elapsed.
- **Align to Day and Time**: Executes at the specified date and time in the month after the specified number of months has elapsed.
  - **Day of the month**: Specify the date (e.g., 15).
  - **Time (HH:mm)**: Specify the time in HH:mm format (e.g., 12:10).

**Note**: Please note that this doesn't necessarily execute after exactly the specified number of months. For 1 month, it executes on the specified date and time in the following month.
**Important**: If the calculation results in a date that doesn't exist in that month (e.g., 29th, 30th, 31st), it will be carried over to the next month according to PHP specifications.
- Example 1) If a step is executed at 2025/01/31 20:45:00 and 1 is specified for months, since February 31 doesn't exist, it carries over to the next month and executes at 2025/03/03 20:45:00. If you want to execute at the end of the month, select "Last day of the month".
- Example 2) If a step is executed at 2025/01/31 20:45:00, with 1 specified for months and 31 specified for date, it will be executed at 2025/03/03 20:45:00.
- If "Last day of the month" is selected for the date setting, it will be the last day of that month.
- Example 3) If a step is executed at 2025/01/31 20:45:00, with 1 specified for months and "Last day of the month" selected for date, it will be executed at 2025/02/28 20:45:00.

#### Years
When the interval is in years, you can choose to execute exactly after the interval or specify which month, day, and time to execute in the year after adding the year interval.
- **Exact Time Execution**: Executes at the specified time on the exact date after the set number of years has elapsed.
- **Align to Month, Day, and Time**: Executes at the specified time on the specified month and date (e.g., June 15th at 12:10).
  - **Month of the year**: Specify the month (1-12).
  - **Day of the month**: Specify the date (e.g., 15).
  - **Time (HH:mm)**: Specify the time in HH:mm format (e.g., 12:10).

**Note**: Please note that this doesn't necessarily execute after exactly the specified number of years. It executes on the specified month and day in the next year.
- Example 1) If a step is executed at 2025/12/31 12:10:00, with an interval value of 1, month 1, and date 1 specified, it will actually be executed the next day at 2026/01/01 12:10:00.
- If a non-existent date is specified, it will be carried over to the next month.
- Example 2) If February 31 is specified, it will be executed on March 3.

### Execution Time Delay
Since a pseudo-Cron system is used (where actions are processed when there is access), execution time may be delayed if access is low or almost nonexistent.