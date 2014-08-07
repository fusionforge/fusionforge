--
--	Making system oracle-safe
--
ALTER TABLE forum RENAME date TO post_date;

DROP VIEW forum_group_list_vw;
CREATE VIEW forum_group_list_vw AS
SELECT forum_group_list.*, forum_agg_msg_count.count as total,
    (SELECT max(post_date) AS recent FROM forum WHERE group_forum_id=forum_group_list.group_forum_id) AS recent,
    (SELECT count(*) FROM
        (SELECT thread_id
            FROM forum
            WHERE group_forum_id=forum_group_list.group_forum_id GROUP BY thread_id) as tmp) AS threads
    FROM forum_group_list LEFT JOIN forum_agg_msg_count USING (group_forum_id);


DROP VIEW forum_user_vw;
CREATE VIEW forum_user_vw AS select forum.*,users.user_name,users.realname
        FROM forum,users WHERE forum.posted_by=users.user_id;

ALTER TABLE session RENAME TO user_session;

ALTER TABLE news_bytes RENAME date TO post_date;

ALTER TABLE people_job RENAME date TO post_date;

ALTER TABLE snippet_package_version RENAME date TO post_date;

ALTER TABLE snippet_version RENAME date TO post_date;

ALTER TABLE survey_rating_response RENAME date TO post_date;

ALTER TABLE survey_responses RENAME date TO post_date;

ALTER TABLE group_history RENAME date TO adddate;
