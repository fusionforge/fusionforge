detete from forum where group_forum_id in 
(select group_forum_id  from forum_group_list where group_id in
	(select group_id from groups where status='D')
);

delete from forum_group_list where group_id in ( select group_id from groups where status='D');

delete from user_group where group_id in (select group_id from groups where status = 'D');

delete from groups where status = 'D' ;
