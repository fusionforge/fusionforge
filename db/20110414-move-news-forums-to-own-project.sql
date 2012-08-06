UPDATE forum_group_list SET group_id=(SELECT MIN(nb.group_id) FROM news_bytes nb WHERE forum_group_list.group_forum_id=nb.forum_id) WHERE group_forum_id IN (SELECT forum_id FROM news_bytes);
