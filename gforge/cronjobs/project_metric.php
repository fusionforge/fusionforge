<?php

require ('squal_pre.php');

if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
        exit_permission_denied();
}

$last_week=(time()-604800);
$this_week=time();

#print "\nlast_week: ". $last_week;
#print "\n\nthis_week: ". $this_week;

$sql="DROP TABLE IF EXISTS project_counts_tmp";
$rel = db_query($sql);



$sql="DROP TABLE IF EXISTS project_metric_tmp";
$rel = db_query($sql);



$sql="DROP TABLE IF EXISTS project_metric_tmp1";
$rel = db_query($sql);



#create a table to put the aggregates in
$sql="CREATE TABLE project_counts_tmp (group_id int,type text,count float(8,5))";
$rel = db_query($sql);


#forum messages
$sql="INSERT INTO project_counts_tmp 
SELECT forum_group_list.group_id,'forum',log(3*count(forum.msg_id)) AS count 
FROM forum,forum_group_list 
WHERE forum.group_forum_id=forum_group_list.group_forum_id 
GROUP BY group_id";

#print "\n\n".$sql;

$rel = db_query($sql);



#project manager tasks
$sql="INSERT INTO project_counts_tmp 
SELECT project_group_list.group_id,'tasks',log(4*count(project_task.project_task_id)) AS count 
FROM project_task,project_group_list 
WHERE project_task.group_project_id=project_group_list.group_project_id 
GROUP BY group_id";

#print "\n\n".$sql;

$rel = db_query($sql);



#bugs
$sql="INSERT INTO project_counts_tmp 
SELECT group_id,'bugs',log(3*count(*)) AS count 
FROM bug 
GROUP BY group_id";

#print "\n\n".$sql;

$rel = db_query($sql);



#patches
$sql="INSERT INTO project_counts_tmp 
SELECT group_id,'patches',log(10*count(*)) AS count 
FROM patch 
GROUP BY group_id";

#print "\n\n".$sql;

$rel = db_query($sql);



#support
$sql="INSERT INTO project_counts_tmp 
SELECT group_id,'support',log(5*count(*)) AS count 
FROM support 
GROUP BY group_id";

#print "\n\n".$sql;

$rel = db_query($sql);



#cvs commits
$sql="INSERT INTO project_counts_tmp 
SELECT group_id,'cvs',log(sum(cvs_commits)) AS count 
FROM group_cvs_history 
GROUP BY group_id";

#print "\n\n".$sql;

$rel = db_query($sql);



#developers
$sql="INSERT INTO project_counts_tmp 
SELECT group_id,'developers',log(5*count(*)) AS count FROM user_group GROUP BY group_id";
$rel = db_query($sql);


/*
#file releases
$sql="INSERT INTO project_counts_tmp 
select group_id,'filereleases',log(5*count(*)) 
FROM filerelease 
GROUP BY group_id";

#print "\n\n".$sql;

$rel = db_query($sql);
*/


#file downloads
$sql="INSERT INTO project_counts_tmp 
SELECT group_id,'downloads',log(.3*sum(downloads)) 
FROM filerelease
GROUP BY group_id";

#print "\n\n".$sql;

$rel = db_query($sql);



#create a new table to insert the final records into
$sql="CREATE TABLE project_metric_tmp1 (ranking int not null primary key auto_increment,
group_id int not null,
value float (8,5))";
$rel = db_query($sql);



#insert the rows into the table in order, adding a sequential rank #
$sql="INSERT INTO project_metric_tmp1 (group_id,value) 
SELECT project_counts_tmp.group_id,(survey_rating_aggregate.response * sum(project_counts_tmp.count)) AS value 
FROM project_counts_tmp,survey_rating_aggregate 
WHERE survey_rating_aggregate.id=project_counts_tmp.group_id 
AND survey_rating_aggregate.type=1 
AND survey_rating_aggregate.response > 0
AND project_counts_tmp.count > 0
GROUP BY group_id ORDER BY value DESC";
$rel = db_query($sql);



#numrows in the set
$sql="SELECT count(*) FROM project_metric_tmp1";
$rel = db_query($sql);

$counts = db_result($rel,0,0);
#print "\n\nCounts: ".$counts;

#create a new table to insert the final records into
$sql="CREATE TABLE project_metric_tmp (ranking int not null primary key auto_increment,
percentile float(8,2), group_id int not null)";
$rel = db_query($sql);


$sql="INSERT INTO project_metric_tmp (ranking,percentile,group_id)
SELECT ranking,(100-(100*((ranking-1)/$counts))),group_id 
FROM project_metric_tmp1 ORDER BY ranking ASC";
$rel = db_query($sql);



#create an index
$sql="create index idx_project_metric_group on project_metric_tmp(group_id)";
$rel = db_query($sql);



#drop the old metrics table
$sql="DROP TABLE IF EXISTS project_metric";
$rel = db_query($sql);



#move the new ratings to the correct table name
$sql="alter table project_metric_tmp rename as project_metric";
$rel = db_query($sql);


?>
