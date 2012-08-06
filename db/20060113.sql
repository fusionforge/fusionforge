alter table forum drop column bbcode_uid cascade;

CREATE VIEW forum_user_vw AS SELECT forum.msg_id, forum.group_forum_id, forum.posted_by, forum.subject, forum.body, forum.post_date, forum.is_followup_to, forum.thread_id, forum.has_followups, forum.most_recent_date, users.user_name, users.realname FROM forum, users WHERE (forum.posted_by = users.user_id);

alter table forum_pending_messages drop column bbcode_uid cascade;

CREATE VIEW forum_pending_user_vw AS
    SELECT forum_pending_messages.msg_id, forum_pending_messages.group_forum_id, forum_pending_messages.posted_by, forum_pending_messages.subject, forum_pending_messages.body, forum_pending_messages.post_date, forum_pending_messages.is_followup_to, forum_pending_messages.thread_id, forum_pending_messages.has_followups, forum_pending_messages.most_recent_date, users.user_name, users.realname FROM forum_pending_messages, users WHERE (forum_pending_messages.posted_by = users.user_id);
