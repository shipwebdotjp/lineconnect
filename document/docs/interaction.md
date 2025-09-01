# Interaction
Interaction is a feature that allows for interactive communication with users of a LINE official account. It can be used for various purposes, such as conducting surveys or accepting reservations.

### Actions
You can reference the user's previous answers in the `{{session.stepname}}`. For example, if there is a step to obtain the user's name and the step ID is `name`, you can reference it in the action parameters after the input as follows.
```
You're name is {{session.name}}.
```