<?php

require_once('../env.inc.php');
require_once('pre.php');
require_once($sys_path_to_jpgraph.'/jpgraph.php');
require_once($sys_path_to_jpgraph.'/jpgraph_gantt.php');
require_once('common/pm/ProjectTasksForUser.class');


if (!file_exists($sys_path_to_jpgraph.'/jpgraph.php')) {
	exit_error('Error', 'Package JPGraph not installed');
}

if (!session_loggedin()) {
	exit_error('Error', 'You are not logged in!');
}

// 
// The data for the graphs
//
$data = array();
$progress = array();

$User =& session_get_user();
$projectTasksForUser = new ProjectTasksForUser($User);
$userTasks =& $projectTasksForUser->getTasksByGroupProjectName();

$last_group="0";
$g_index = -1;
$pos = 0;
if (count($userTasks) > 0) {
	foreach ($userTasks as $task) {
		$projectGroup =& $task->getProjectGroup();
		$group =& $projectGroup->getGroup();

		if($projectGroup->getID() != $last_group) {
			$last_group = $projectGroup->getID();

			//???Ω???瑚????Ω?雀???Bar??
			//?????ヴ鞍械?蹄???????衛??飢?
			if ($g_index >= 0) {
				$data[$g_index][3]=date("Y-m-d",$group_begin);
				$data[$g_index][4]=date("Y-m-d",$group_end-86400);
			}

			//?雀Project?棲??ヌ?肱??
			$g_index = $pos;

			$group_begin = $task->getStartDate();
			$group_end = $task->getEndDate();;

			$data[$pos] = array($pos,ACTYPE_GROUP,
				"[".$group->getPublicName()."-".$projectGroup->getName()."]",
				date("Y-m-d",$group_begin),
				date("Y-m-d",$group_end-86400),
				'');
			$progress[$pos] = array($pos,$task->getPercentComplete()/100);
			$pos = $pos + 1;
		}

		$data[$pos] = array($pos,ACTYPE_NORMAL,
			"  - ".$task->getSummary(),
			date("Y-m-d",$task->getStartDate()),
			date("Y-m-d",$task->getEndDate()-86400),
			$task->getPercentComplete()."%");
		$progress[$pos] = array($pos,$task->getPercentComplete()/100);

		if($group_begin > $task->getStartDate()) 
			$group_begin = $task->getStartDate();
		if($group_end < $task->getEndDate()) 
			$group_end = $task->getEndDate();

		$pos = $pos + 1;	
	}
	if ($g_index > 0) {
		$data[$g_index][3]=date("Y-m-d",$group_begin);
		$data[$g_index][4]=date("Y-m-d",$group_end-86400);
	}
}


//$data = array(
//    array(0,ACTYPE_GROUP,    "Phase 1",        "2001-10-26","2001-11-23",''),
//    array(1,ACTYPE_NORMAL,   "  Label 2",      "2001-11-01","2001-11-20",''),
//    array(2,ACTYPE_NORMAL,   "  Label 3",      "2001-10-26","2001-11-03",''),
//    array(3,ACTYPE_MILESTONE,"  Phase 1 Done", "2001-11-23",'M2') );
// The constrains between the activities
//$constrains = array(array(2,1,CONSTRAIN_ENDSTART),
//		    array(1,3,CONSTRAIN_STARTSTART));

// progress
//$progress = array(array(1,0.4));

// Create the basic graph
$graph = new GanttGraph();
//$graph->title->SetFont(FF_BIG5,FS_NORMAL,10);
$graph->title->Set('Projects for '.$User->getRealName());


// Setup scale
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

// Add the specified activities
//$graph->SetSimpleFont(FF_BIG5,10);
$graph->CreateSimple($data,$constrains,$progress);

$todayline = new GanttVLine(date('Y-m-d',time()),"Today");
$todayline ->SetDayOffset (0.5);
$graph->Add( $todayline);

// .. and stroke the graph
$graph->Stroke();

?>
