CREATE TABLE frs_dlstats_file(
ip_address text,
file_id int,
month int,
day int
);

DROP TABLE cache_store;

ALTER TABLE users ADD COLUMN jabber_address text;
ALTER TABLE users ADD COLUMN jabber_only int;

DROP TABLE top_group;
drop table stats_ftp_downloads;
drop table stats_http_downloads;

--
-- Perf enhancement
--
CREATE INDEX groupcvshistory_groupid ON group_cvs_history(group_id);

--
-- Forum Rewrite
--
DROP INDEX forum_forumid_isfollowupto;
CREATE VIEW forum_user_vw AS select forum.*,users.user_name,users.realname
	FROM forum,users WHERE forum.posted_by=users.user_id;
CREATE VIEW forum_group_list_vw AS SELECT forum_group_list.*, forum_agg_msg_count.count as total
    FROM forum_group_list LEFT JOIN forum_agg_msg_count USING (group_forum_id);
ALTER TABLE forum_group_list ADD CONSTRAINT forumgrouplist_groupid
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ON DELETE CASCADE;
ALTER TABLE forum ADD CONSTRAINT forum_groupforumid
	FOREIGN KEY (group_forum_id) REFERENCES forum_group_list(group_forum_id) MATCH FULL ON DELETE CASCADE;
ALTER TABLE forum ADD CONSTRAINT forum_userid
	FOREIGN KEY (posted_by) REFERENCES users(user_id) MATCH FULL;

--
-- Don't leave it empty
--
update users set realname='Nobody' where user_id=100;
