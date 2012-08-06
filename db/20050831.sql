ALTER TABLE forum ALTER bbcode_uid SET DEFAULT -1;

ALTER TABLE forum_group_list ADD COLUMN moderation_level integer;

ALTER TABLE forum_group_list ALTER moderation_level SET DEFAULT 0;

DROP VIEW forum_group_list_vw;

CREATE VIEW forum_group_list_vw AS
    SELECT forum_group_list.group_forum_id, forum_group_list.group_id, forum_group_list.forum_name, forum_group_list.is_public, forum_group_list.description, forum_group_list.allow_anonymous, forum_group_list.send_all_posts_to, forum_group_list.moderation_level, forum_agg_msg_count.count AS total, (SELECT max(forum.post_date) AS recent FROM forum WHERE (forum.group_forum_id = forum_group_list.group_forum_id)) AS recent, (SELECT count(*) AS count FROM (SELECT forum.thread_id FROM forum WHERE (forum.group_forum_id = forum_group_list.group_forum_id) GROUP BY forum.thread_id) tmp) AS threads FROM (forum_group_list LEFT JOIN forum_agg_msg_count USING (group_forum_id));

DROP VIEW forum_user_vw;

CREATE VIEW forum_user_vw AS
    SELECT forum.msg_id, forum.group_forum_id, forum.posted_by, forum.subject, forum.body, forum.post_date, forum.is_followup_to, forum.thread_id, forum.has_followups, forum.most_recent_date, forum.bbcode_uid, users.user_name, users.realname FROM forum, users WHERE (forum.posted_by = users.user_id);

CREATE TABLE forum_pending_messages (
    msg_id serial NOT NULL,
    group_forum_id integer DEFAULT 0 NOT NULL,
    posted_by integer DEFAULT 0 NOT NULL,
    subject text DEFAULT ''::text NOT NULL,
    body text DEFAULT ''::text NOT NULL,
    post_date integer DEFAULT 0 NOT NULL,
    is_followup_to integer DEFAULT 0 NOT NULL,
    thread_id integer DEFAULT 0 NOT NULL,
    has_followups integer DEFAULT 0,
    most_recent_date integer DEFAULT 0 NOT NULL,
    bbcode_uid character varying(15) DEFAULT -1
);

ALTER TABLE ONLY forum_pending_messages ADD CONSTRAINT forum_pending_messages_pkey PRIMARY KEY (msg_id);

ALTER TABLE ONLY forum_pending_messages
    ADD CONSTRAINT forum_pending_messages_group_forum_id_fkey FOREIGN KEY (group_forum_id) REFERENCES forum_group_list(group_forum_id) ON DELETE CASCADE;

CREATE TABLE forum_pending_attachment (
    attachmentid serial NOT NULL,
    userid integer DEFAULT 100 NOT NULL,
    dateline integer DEFAULT 0 NOT NULL,
    filename character varying(100) DEFAULT ''::character varying NOT NULL,
    filedata text NOT NULL,
    visible smallint DEFAULT (0)::smallint NOT NULL,
    counter smallint DEFAULT (0)::smallint NOT NULL,
    filesize integer DEFAULT 0 NOT NULL,
    msg_id integer DEFAULT 0 NOT NULL,
    filehash character varying(32) DEFAULT ''::character varying NOT NULL
);

ALTER TABLE ONLY forum_pending_attachment
    ADD CONSTRAINT forum_pending_attachment_pkey PRIMARY KEY (attachmentid);


ALTER TABLE ONLY forum_pending_attachment
    ADD CONSTRAINT forum_pending_attachment_msg_id_fkey FOREIGN KEY (msg_id) REFERENCES forum_pending_messages(msg_id) ON DELETE CASCADE;

ALTER TABLE ONLY forum_pending_attachment
    ADD CONSTRAINT forum_pending_attachment_userid_fkey FOREIGN KEY (userid) REFERENCES users(user_id) ON DELETE SET DEFAULT;

CREATE VIEW forum_pending_user_vw AS
    SELECT forum_pending_messages.msg_id, forum_pending_messages.group_forum_id, forum_pending_messages.posted_by, forum_pending_messages.subject,
    forum_pending_messages.body, forum_pending_messages.post_date, forum_pending_messages.is_followup_to, forum_pending_messages.thread_id, forum_pending_messages.has_followups,
    forum_pending_messages.most_recent_date, forum_pending_messages.bbcode_uid, users.user_name, users.realname FROM forum_pending_messages, users WHERE (forum_pending_messages.posted_by = users.user_id);



