SET client_min_messages TO warning;

CREATE OR REPLACE FUNCTION forums_search(text, integer, text, boolean) RETURNS SETOF forums_results AS '
	DECLARE
	data forums_results;
	BEGIN
	IF $3 <> \'\' THEN
		IF $4 THEN
			FOR data IN SELECT forum.msg_id, ts_headline(forum.subject, q) AS subject, forum.post_date, users.realname, forum_group_list.forum_name
			FROM forum, users, forum_group_list, to_tsquery($1) AS q
			WHERE users.user_id = forum.posted_by
			AND forum_group_list.group_forum_id = forum.group_forum_id
			AND forum_group_list.is_public <> 9
			AND forum_group_list.group_forum_id IN (\'$3\')
			AND forum.msg_id IN (SELECT msg_id FROM forum_idx, to_tsquery($1) AS q
			WHERE group_id = $2 AND vectors @@ q ORDER BY rank(vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		ELSE
			FOR data IN SELECT forum.msg_id, ts_headline(forum.subject, q) AS subject, forum.post_date, users.realname, forum_group_list.forum_name
			FROM forum, users, forum_group_list, to_tsquery($1) AS q
			WHERE users.user_id = forum.posted_by
			AND forum_group_list.group_forum_id = forum.group_forum_id
			AND forum_group_list.is_public <> 9
			AND forum_group_list.group_forum_id IN (\'$3\')
			AND forum_group_list.is_public = 1
			AND forum.msg_id IN (SELECT msg_id FROM forum_idx, to_tsquery($1) AS q
			WHERE group_id = $2 AND vectors @@ q ORDER BY rank(vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		END IF;
	ELSE
		IF $4 THEN
			FOR data IN SELECT forum.msg_id, ts_headline(forum.subject, q) AS subject, forum.post_date, users.realname, forum_group_list.forum_name
			FROM forum, users, forum_group_list, to_tsquery($1) AS q
			WHERE users.user_id = forum.posted_by
			AND forum_group_list.group_forum_id = forum.group_forum_id
			AND forum_group_list.is_public <> 9
			AND forum.msg_id IN (SELECT msg_id FROM forum_idx, to_tsquery($1) AS q
			WHERE group_id = $2 AND vectors @@ q ORDER BY rank(vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		ELSE
			FOR data IN SELECT forum.msg_id, ts_headline(forum.subject, q) AS subject, forum.post_date, users.realname, forum_group_list.forum_name
			FROM forum, users, forum_group_list, to_tsquery($1) AS q
			WHERE users.user_id = forum.posted_by
			AND forum_group_list.group_forum_id = forum.group_forum_id
			AND forum_group_list.is_public <> 9
			AND forum_group_list.is_public = 1
			AND forum.msg_id IN (SELECT msg_id FROM forum_idx, to_tsquery($1) AS q
			WHERE group_id = $2 AND vectors @@ q ORDER BY rank(vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		END IF;
	END IF;
	RETURN;
	END;'
LANGUAGE 'plpgsql';
