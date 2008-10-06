drop index downloads_idx;
create index frsdlstatsgroupagg_day_dls on  frs_dlstats_group_agg (day,downloads);
create index projectweeklymetric_ranking on project_weekly_metric(ranking);
create index users_status on users(status);
drop index news_date;
create index support_groupid_assignedto_status on support(group_id,assigned_to,support_status_id);
create index support_groupid_assignedto on support(group_id,assigned_to);
create index support_groupid_status on support(group_id,support_status_id);

create index patch_groupid_assignedto_status on patch(group_id,assigned_to,patch_status_id);
create index patch_groupid_assignedto on patch(group_id,assigned_to);
create index patch_groupid_status on patch(group_id,patch_status_id);

create index projecttask_projid_status on project_task(group_project_id,status_id);

create index forummonitoredforums_user on forum_monitored_forums(user_id);

create index filemodulemonitor_userid on filemodule_monitor(user_id);
create index support_status_assignedto on support(support_status_id,assigned_to);
create index bug_status_assignedto on bug(status_id,assigned_to);
