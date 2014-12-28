taskboard
=========

TaskBoard plugin for FusionForge 5.x . Supports Scrum and Kanban methodologies.


FusionForge project can use one ore more artifact trackers.

TaskBoard plugin is a tool for having a consolidated 'board' like view for artifacts, managed with trackers.
For changing artifact resolution you should just move it from one column to another.

Task board allows to manage tasks in agile style (Scrum or Kanban).

Trackers, using extra field with 'resolution' alias, can be used as tasks trackers. So
related artifacts are shown as draggable stickers on the task board.

'resolution' extra field should have a 'select' type.

You can configure a separate colors for different tasks trackers (e.g., light green for 
feature requests, light red for bug reports). In this case configured colors will be used as background colors
for titles of 'resolution' stickers.

One of tracker can be used as user stories tracker. User stories tracker is optional, but
if it's used - you will see all tasks grouped by related user stories, and first column is used for
user stories stickers.

Any tracker could be used either like a tasks tracker or like user stories tracker.

In user stories tracker you can define an extra field, that will be used for user stories sorting.
It's a 'text' type extra field that usually keeps a unique number - relative priority of the user story.

Naturally, in tasks trackers you should define an extra field for keeping a referetce to related user story artifact
(identifier of user story artifact).

User story extra field should have 'text' or 'relation' type.

There are two ways to manage sprints/releases:
- by tasks
- by user stories

In the first case we use tasks tracker extra field, dedicated to release. So, every task can be linked 
to release directly. And it's possible to have tasks that are not linked to any user story.

In the second case case we use user stories tracker extra field, dedicated to release. In this case all planned tasks
should be linked to particular user stories, and user stories are linked to releases.

In both cases release extra field should have a 'select' type.

Users having 'manager' permissions in tracker can modify sticker's title and body. It's activated with double click on the related text.
Changes are submitted with ENTER, canceled with ESC.

Other technicians can only move stickers from column to column (so they can change resolution of the related artifact). 

Moved task will be automatically assigned to the current person if "autoassign" is enabled for the target column.
