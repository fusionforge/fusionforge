delete from forum where group_forum_id in 
(select group_forum_id  from forum_group_list where group_id in
	(select group_id from groups where status='D')
);

delete from forum_group_list where group_id in ( select group_id from groups where status='D');

delete from user_group where group_id in (select group_id from groups where status = 'D');

delete from artifact_message where artifact_id in (select artifact_id from artifact where group_artifact_id in (select group_artifact_id from artifact_group_list where group_id in (select group_id from groups where status = 'D')));
delete from artifact where group_artifact_id in (select group_artifact_id from artifact_group_list where group_id in (select group_id from groups where status = 'D'));
delete from artifact_perm where group_artifact_id in (select group_artifact_id from artifact_group_list where group_id in (select group_id from groups where status = 'D'));
delete from artifact_group where group_artifact_id in (select group_artifact_id from artifact_group_list where group_id in (select group_id from groups where status = 'D'));
delete from artifact_category where group_artifact_id in (select group_artifact_id from artifact_group_list where group_id in (select group_id from groups where status = 'D'));
delete from artifact_group_list where group_id in (select group_id from groups where status = 'D');
delete from frs_package where group_id in (select group_id from groups where status = 'D');

delete from groups where status = 'D' ;
