delete from forum where group_forum_id in
(select group_forum_id  from forum_group_list where group_id in
	(select group_id from groups where status='D')
);

delete from forum_group_list where group_id in ( select group_id from groups where status='D');

delete from user_group where group_id in (select group_id from groups where status = 'D');

delete from artifact_message where artifact_id in (select artifact_id from artifact where group_artifact_id in (select group_artifact_id from artifact_group_list where group_id in (select group_id from groups where status = 'D')));
delete from artifact where group_artifact_id in (select group_artifact_id from artifact_group_list where group_id in (select group_id from groups where status = 'D'));
delete from artifact_group where group_artifact_id in (select group_artifact_id from artifact_group_list where group_id in (select group_id from groups where status = 'D'));
delete from artifact_category where group_artifact_id in (select group_artifact_id from artifact_group_list where group_id in (select group_id from groups where status = 'D'));
delete from artifact_group_list where group_id in (select group_id from groups where status = 'D');


delete from frs_file where release_id in ( select release_id from frs_release where package_id in ( select package_id from frs_package where group_id in (select group_id from groups where status = 'D')));


delete from frs_release where package_id in ( select package_id from frs_package where group_id in (select group_id from groups where status = 'D'));

delete from frs_package where group_id in (select group_id from groups where status = 'D');

delete from project_task where group_project_id in (select group_project_id from project_group_list where group_id in (select group_id from groups where status = 'D'));

delete from group_plugin where group_id in (select group_id from groups where status = 'D');

delete from project_group_list where group_id in (select group_id from groups where status = 'D');

delete from groups where status = 'D' ;
