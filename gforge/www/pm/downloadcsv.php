<?php
/*
Record description for .csv file format

project_task_id - this is the ID in gforge database
external_task_id - the equivalent of project_task_id but determined by 
		external application, such as MS Project
parent_id - the project_task_id of the parent task, if any
external_parent_id - the equivalent of parent project_task_id but 
		determined by external application, such as MS Project
title - name                ********may contain , characters???
duration - days
work - hours
start_date
end_date
percent_complete
priority - low, medium, high
notes - details             ********may contain , characters???
resource1_unixname
resource2_unixname
resource3_unixname
resource4_unixname
resource5_unixname
dependenton1_project_task_id
dependenton1_external_task_id
dependenton1_linktype - SS SF FS FF
dependenton2_project_task_id
dependenton2_external_task_id
dependenton2_linktype
dependenton3_project_task_id
dependenton3_external_task_id
dependenton3_linktype
dependenton4_project_task_id
dependenton4_external_task_id
dependenton4_linktype
dependenton5_project_task_id
dependenton5_external_task_id
dependenton5_linktype
*/

require_once('common/include/User.class.php');
require_once('common/pm/ProjectTaskFactory.class.php');

$ptf = new ProjectTaskFactory($pg);
if (!$ptf || !is_object($ptf)) {
    exit_error('Error','Could Not Get ProjectTaskFactory');
} elseif ($ptf->isError()) {
    exit_error('Error',$ptf->getErrorMessage());
}
$ptf->order='external_id';
$pt_arr =& $ptf->getTasks();
if ($ptf->isError()) {
    exit_error('Error',$ptf->getErrorMessage());
}

//
//	Iterate the array of tasks and dump them out to a comma-separated file
//

$arrRemove = array("\r\n", "\n", ',');

for ($i=0; $i<count($pt_arr); $i++) {

	echo $pt_arr[$i]->getID().','.
		$pt_arr[$i]->getExternalID().','.
		$pt_arr[$i]->getParentID().','.
		','.
		str_replace($arrRemove, ' ', $pt_arr[$i]->getSummary()).','.
		$pt_arr[$i]->getDuration().','.
		$pt_arr[$i]->getHours().','.
		date('Y-m-d H:i:s',$pt_arr[$i]->getStartDate()).','.
		date('Y-m-d H:i:s',$pt_arr[$i]->getEndDate()).','.
		$pt_arr[$i]->getPercentComplete().','.
		$pt_arr[$i]->getPriority().','.
		str_replace($arrRemove, ' ', $pt_arr[$i]->getDetails()).',';

		$users =& user_get_objects($pt_arr[$i]->getAssignedTo());
		for ($j=0; $j<5; $j++) {
			if ($j < count($users)) {
				if ($users[$j]->getUnixName() != 'none') {
					echo $users[$j]->getUnixName();
				}
			}
			echo ',';
		}

		$dependentOn =& $pt_arr[$i]->getDependentOn();
		$keys=array_keys($dependentOn);
		for ($j=0; $j<5; $j++) {
			if ($j < count($keys)) {
				echo $keys[$j].',,'.$dependentOn[$keys[$j]];
			} else {
				echo ',,';
			}
			if ($j<4) {
				echo ',';
			}
		}
	echo "\n";
}
?>
