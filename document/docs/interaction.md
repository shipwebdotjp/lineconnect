---
id: interaction
title: Interaction
---

Interaction is a feature that allows for interactive communication with users of a LINE official account. It can be used for various purposes, such as conducting surveys, accepting reservations, or guiding users through a process.

## Creating an Interaction

To create a new Interaction, navigate to the "Interaction" menu in the WordPress dashboard and click "Add New". You will be presented with a form to define the behavior and flow of your interaction.

## Interaction Settings

At the top level of the Interaction form, you can configure the overall settings for the interaction.

| Field | Description |
| :--- | :--- |
| **Timeout (minutes)** | The number of minutes of inactivity before the interaction session expires. Set to 0 for no timeout. |
| **Send timeout reminder** | The number of minutes before the timeout to send a reminder message. Set to 0 for no reminder. The content of the reminder message is defined in the special "Timeout Remind" step. |
| **On Timeout** | Defines what happens when a session times out. <br/> - **Delete Session**: The session data is completely removed. <br/> - **Mark as Timeout**: The session is marked as timed out but the data is retained. |
| **Run Policy** | Defines how to handle multiple concurrent interaction sessions for the same user. <br/> - **Don't allow**: Prevents the user from starting a new interaction if they already have one active. <br/> - **Allow (keep latest only)**: Allows a new interaction to start, terminating the previous one. <br/> - **Allow (keep history)**: Allows multiple interactions to run concurrently. |
| **Override Policy** | Defines how to handle a new interaction request when another interaction is already in progress. <br/> - **Reject**: The new interaction request is rejected. <br/> - **Restart only same**: If the new interaction is the same as the current one, the current one is restarted. <br/> - **Restart only different**: If the new interaction is different from the current one, the current one is terminated and the new one starts. <br/> - **Always restart**: Any new interaction request will terminate the current one and start the new one. <br/> - **Stack**: The current interaction is paused, and the new one starts. Once the new interaction is complete, the previous one resumes. |
| **Version** | The version of the interaction form. Increment this if you make structural changes to an existing interaction that is in use. |
| **Storage** | Defines where the collected data is stored. <br/> - **Bind to Profile**: The data is saved to the user's profile meta. <br/> - **Interactions**: The data is saved within the interaction session data. |
| **Exclude Steps** | A list of step IDs to exclude from data storage. This is useful for steps that don't collect user data, like confirmation screens. |
| **Cancel Words** | A list of words or phrases that, when received from the user, will immediately terminate the interaction. You can define matching conditions (Equals, Contains, Regex). |

## Building Steps

An interaction is composed of one or more steps. Each step defines a part of the conversation.

### Step Configuration

| Field | Description |
| :--- | :--- |
| **ID** | A unique identifier for the step. This is used for branching and referencing data. It's recommended to use English alphanumeric characters and hyphens (e.g., `ask-name`, `confirm-email`). |
| **Title** | A display title for the step in the admin editor. |
| **Description** | A description for the step in the admin editor. |
| **Next Step ID** | The ID of the step to proceed to after this one completes, if no other branching logic applies. |
| **Stop** | If enabled, the interaction will end after this step. |

### Sending Messages

In each step, you can send one or more messages to the user. You can choose from various message types:

- **Text**: A simple text message.
- **Sticker**: A LINE sticker.
- **Image, Video, Audio**: Media messages.
- **Location**: A location pin on a map.
- **Flex**: A message with a customizable layout using JSON created with the [Flex Message Simulator](https://developers.line.biz/flex-simulator/).
- **Raw**: A raw JSON object for a LINE message.
- **Template Button**: A message with a title and a set of buttons. Each button can have a label, a value, and a `nextStepId` to branch the conversation.
- **Confirm Template**: A predefined template with "Apply" and "Edit" buttons for confirmation flows.
- **Edit Picker Template**: A template that allows users to select a previous step to edit.
- **Cancel Confirm Template**: A predefined template to confirm if the user wants to cancel the interaction.

### Handling User Input

You can process and validate the user's input in each step.

#### Normalize

Normalization rules clean up the user's input before validation and storage.

- **Trim**: Removes whitespace from the beginning and end of the input.
- **Omit...**: Removes characters like commas, hyphens, or spaces.
- **Character Conversion**: Converts between different Japanese character sets (Hiragana, Katakana) or between half-width and full-width alphanumeric characters.

#### Validation

Validation rules ensure the user's input is in the correct format. If validation fails, the user will be prompted again.

- **Required**: The user must provide an input.
- **Number**: The input must be a number (with optional min/max values).
- **Length**: The input must have a specific character length (with optional min/max).
- **Email, Phone, URL**: The input must be a valid email, phone number, or URL.
- **Date, Time, Datetime**: The input must match a date/time format.
- **Enum**: The input must be one of a predefined list of values.
- **Regex**: The input must match a regular expression.
- **Japanese**: The input must be either Hiragana or Katakana.
- **Forbidden Content**: The input cannot contain certain words or match specific patterns.

### Branching Logic

You can control the flow of the conversation using the **Branches** section. A branch defines a condition based on the user's input and a `nextStepId` to jump to if the condition is met.

- **Condition Type**: `Equals`, `Contains`, or `Regex`.
- **Value to Match**: The string or pattern to check against the user's input.
- **Next Step ID**: The target step ID if the condition is true.

Branches are evaluated in order. If no branch condition is met, the default `Next Step ID` for the step is used.

### Special Steps

Special steps have predefined behaviors for common scenarios in an interaction.

| Special Step | Description |
| :--- | :--- |
| **Confirm** | A step that summarizes the user's input and asks for confirmation. |
| **Edit Picker** | A step that dynamically generates buttons for the user to choose which previous step they want to go back to and edit. |
| **Complete** | The final step of the interaction. After this step, the interaction is marked as complete. |
| **Cancel Confirm** | A step that asks the user to confirm if they want to cancel the interaction. |
| **Canceled** | A step that is executed after the user confirms cancellation. |
| **Timeout Remind** | The message to send when a timeout reminder is triggered. |
| **Timeout Notice** | The message to send when the interaction officially times out. |

### Actions

You can execute actions at two points in a step's lifecycle:
- **Before Input Actions**: Executed when the step begins, before the user provides input.
- **After Input Actions**: Executed after the user has provided input and it has been validated.

This allows you to fetch data, update user profiles, or trigger other processes as part of the interaction.

## Using Interaction Data

You can reference data collected in previous steps within your messages or action parameters. Use the format `{{session.step_id}}`, where `step_id` is the ID of the step whose data you want to access.

For example, if you have a step with the ID `ask-name` where the user enters their name, you can use `{{session.ask-name}}` in a subsequent step to address them personally:

```
Hello, {{session.ask-name}}! Welcome to our service.
```
