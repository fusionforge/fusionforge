<?php
require_once('common/pm/ProjectTaskFactory.class');

function printr($var, $name='$var', $echo=true) {
//	return;
	//$str = highlight_string("<?php\n$name = ".var_export($var, 1).";\n? >\n", 1);
//	$str=var_export($var, 1);
	if ($echo) {
		if (is_array($var)) {
			$var =var_export($var, 1);
		}
//		echo $str;
//	} else {
		$fp=fopen('/tmp/msp.log','a');
		fwrite($fp,"\n-------".date('Y-m-d H:i:s')."-----".$name."-----\n".$var);
		fclose($fp);
	}
}

function printrcomplete() {
	exec("/bin/cat /tmp/msp.log | mail -s\"printr\" tim@perdue.net");
	exec("/bin/rm -f /tmp/msp.log");
}

function &pm_import_tasks($group_project_id,&$tasks) {
	printr($tasks,'MSPCheckin::in-array');

	$pg =& projectgroup_get_object($group_project_id);
	if (!$pg || !is_object($pg)) {
		$array['success']=false;
		$array['errormessage']='Could Not Get ProjectGroup';
	} elseif ($pg->isError()) {
		$array['success']=false;
		$array['errormessage']='Could Not Get ProjectGroup: '.$pg->getErrorMessage();
	} else {
		$count=count($tasks);
printr($count,'count - count of tasks');
		//
		//  Build hash list of technicians so we can get their ID for assigning tasks
		//
		$techs =& $pg->getTechnicianObjects();
		$tcount=count($techs);
		for ($i=0; $i<$tcount; $i++) {
			$tarr[strtolower($techs[$i]->getUnixName())]=$techs[$i]->getID();
			$tarr[strtolower($techs[$i]->getRealName())]=$techs[$i]->getID();
		}
		$invalid_names=array();
		//
		//  Create a linked list based on the msproj_id
		//
		for ($i=0; $i<$count; $i++) {
			$msprojid[$tasks[$i]['msproj_id']] =& $tasks[$i];
			$resrc = $tasks[$i]['resources'];
			for ($j=0; $j<count($resrc); $j++) {
				//validate user - see if they really exist as techs in this subproject
				if (!$tarr[strtolower($resrc[$j]['user_name'])]) {
					//create list of bogus names to send back
					if (array_search(strtolower($resrc[$j]['user_name']),$invalid_names) === false) {
						$invalid_names[]=$resrc[$j]['user_name'];
					}
				}
			}
		}

		//
		//  invalid assignees - send it back for remapping
		//
		if (count($invalid_names)) {
			$array['success']=false;
			$array['errormessage']='Invalid Resource Name';
			$array['resourcename']=$invalid_names;
			for ($i=0; $i<$tcount; $i++) {
				$array['usernames'][$techs[$i]->getID()]=$techs[$i]->getUnixName();
			}
			return $array;
		} else {
			//
			//  Begin inserting/updating the tasks
			//
			for ($i=0; $i<$count; $i++) {
				if ($was_error) {
					continue;
				}
				//no task_id so it must be new - create it
				if (!$tasks[$i]['id']) {
					if (!$tasks[$i]['notes']) {
						$tasks[$i]['notes']='None Provided';
					}
					//create the task
					$pt = new ProjectTask($pg);
					if (!$pt || !is_object($pt)) {
						$array['success']=false;
						$was_error=true;
						$array['errormessage']='Could Not Get ProjectTask';
					} elseif ($pt->isError()) {
						$array['success']=false;
						$was_error=true;
						$array['errormessage']='Could Not Get ProjectTask: '.$pt->getErrorMessage();
					} else {
						//remap priority names=>numbers
						$priority=$tasks[$i]['priority'];
						if (!$priority || $priority < 1 || $priority > 5) {
							printr($priority,'Invalid Priority On New Task');
							$priority=3;
						}
						//map users
						$assignees=array();
						$resrc = $tasks[$i]['resources'];
						for ($ucount=0; $ucount< count($resrc); $ucount++) {
							//get their user_id from the $tarr we created earlier
							$assignees[]=$tarr[strtolower($resrc[$ucount]['user_name'])];
						}
						//don't do anything with dependencies yet - we may only have
						//the MSprojid from dependent items
						$hours = $tasks[$i]['work'];
						if (!$hours) {
							$hours='0.0';
						}
						$percent_complete= intval($tasks[$i]['percent_complete']);
						if (!$percent_complete) {
							$percent_complete=0;
						} elseif ($percent_complete > 100) {
							$percent_complete=100;
						}
						if (!$pt->create(
							addslashes($tasks[$i]['name']),
							addslashes($tasks[$i]['notes']),
							$priority,
							$hours,
							strtotime($tasks[$i]['start_date']),
							strtotime($tasks[$i]['end_date']),
							100,
							$percent_complete,
							$assignees,
							$deps = array(),
							$tasks[$i]['duration'],
							$tasks[$i]['parent_id'])) {
							$array['success']=false;
							$was_error=true;
							$array['errormessage']='Error Creating ProjectTask: '.$pt->getErrorMessage();
							break 1;
//							continue;
						} else {
//successful
							$tasks[$i]['id']  = $pt->getID();
							$tasks[$i]['obj'] = $pt;
							$pt->setExternalID($tasks[$i]['msproj_id']);
							$pt = null;
						}
					}

				} else {
					//update existing task
					//create the task
					$pt = &projecttask_get_object($tasks[$i]['id']);
					if (!$pt || !is_object($pt)) {
						$array['success']=false;
						$was_error=true;
						$array['errormessage']='Could Not Get ProjectTask';
					} elseif ($pt->isError()) {
						$array['success']=false;
						$was_error=true;
						$array['errormessage']='Could Not Get ProjectTask: '.$pt->getErrorMessage();
					} else {
						//remap priority names=>numbers
						$priority=$tasks[$i]['priority'];
						if (!$priority || $priority < 1 || $priority > 5) {
							printr($priority,'Invalid Priority On Existing Task');
							$priority=3;
						}
						//map users
						$assignees=array();
						$resrc = $tasks[$i]['resources'];
						for ($ucount=0; $ucount<count($resrc); $ucount++) {
							//get their user_id from the $tarr we created earlier
							$assignees[]=$tarr[strtolower($resrc[$ucount]['user_name'])];
						}
						//don't do anything with dependencies yet - we may only have the
						//MSprojid from dependent items
						$hours = $tasks[$i]['work'];
						if (!$hours) {
							$hours='0.0';
						}
						$percent_complete= intval($tasks[$i]['percent_complete']);
						if (!$percent_complete) {
							$percent_complete=0;
						} elseif ($percent_complete > 100) {
							$percent_complete=100;
						}
						if (!$pt->update(
							addslashes($tasks[$i]['name']),
							addslashes($tasks[$i]['notes']),
							$priority,
							$hours,
							strtotime($tasks[$i]['start_date']),
							strtotime($tasks[$i]['end_date']),
							$pt->getStatusID(),
							$pt->getCategoryID(),
							$percent_complete,
							$assignees,
							$pt->getDependentOn(),
							$pg->getID(),
							$tasks[$i]['duration'],
							$tasks[$i]['parent_id'])) {
							$array['success']=false;
							$was_error=true;
							$array['errormessage']='Error Updating ProjectTask: '.$pt->getErrorMessage();
							break 1;
//							continue;

						} else {
//successful
							$tasks[$i]['id']  = $pt->getID();
							$tasks[$i]['obj'] = $pt;
							$pt->setExternalID($tasks[$i]['msproj_id']);
							$pt = null;

						}
					} //if task->iserror()
				} //if task_id
				//accumulate list of completed tasks
				//any task not in this list will be deleted
				$completed[$tasks[$i]['id']]=true;
			} //for i

			//
			//  Do task dependencies
			//
			if (!$was_error) {
//iterate the tasks
			for ($i=0; $i<$count; $i++) {
				$darr=$tasks[$i]['dependenton'];
				
				/*
				if (count($darr) == 0) {
					// if taks has no dependencies, make it depedent on task 100 (None).
					$darr[] = array('task_id'=>100, 'msproj_id'=>'', 'task_name'=>'', 'link_type'=>'SS');
				}
				*/
				
				$deps=array();
//iterate each dependency in a task
				for ($dcount=0; $dcount<count($darr); $dcount++) {
					//get the id of the task we're dependent on -
					// may have to get it from msprojid linked list
					$id=$darr[$dcount]['task_id'];
printr($id,'Task ID: '.$tasks[$i]['id'].' Getting Task ID that we are dependent on');
					if ($id < 1) {
printr($id,'No Task ID that we are dependent on - will reverse engineer it');
						$id=$msprojid[$darr[$dcount]['msproj_id']]['id'];
printr($id,'This is the task id that we reverse engineered');
					}
					$deps[$id]=$darr[$dcount]['link_type'];
printr($deps,'Dependencies');
				}
				if ($tasks[$i]['obj'] != '') {
					if (!$tasks[$i]['obj']->setDependentOn($deps)) {
						$was_error=true;
						$array['success']=false;
						printr($tasks[$i]['obj'],'FAILED TO SET DEPENDENCIES: '.$tasks[$i]['obj']->getErrorMessage());
					}
				} else {
					$was_error=true;
					$array['success']=false;
					printr($foo,'PROJECT TASK OBJECT DOES NOT EXIST IN OBJ ARRAY');
				}
				unset($deps);
			} //iterates tasks to do dependencies
			}

			//
			//	Delete unreferenced tasks
			//
			if (!$was_error) {
			$ptf =& new ProjectTaskFactory($pg);
			$pt_arr=& $ptf->getTasks();
			for ($i=0; $i<count($pt_arr); $i++) {
				if (!$completed[$pt_arr[$i]->getID()]) {
					if (!$pt_arr[$i]->delete(true)) {
						echo $pt_arr[$i]->getErrorMessage();
					} else {
						printr($foo,'Deleting Unreferenced Tasks');
					}
				}
			}
			}
		} //invalid names
	} //get projectGroup

	if (!$was_error) {
		$array['success']=true;
	}

	printr($array,'MSPCheckin::return-array');
	return $array;
}

?>
