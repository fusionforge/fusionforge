<?php

//require_once('import_arrays.php');

/*
require_once $gfwww.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';
require_once $gfcommon.'include/Role.class.php';
require_once $gfcommon.'include/RoleObserver.class.php';
require_once $gfcommon.'include/rbac_texts.php';
*/

$equivs_text_value['projectadmin']['None']='0';
$equivs_text_value['projectadmin']['Admin']='A';
$equivs_text_value['frs']['Read']='0';
$equivs_text_value['frs']['Write']='1';
$equivs_text_value['scm']['No Access']='-1';
$equivs_text_value['scm']['Read']='0';
$equivs_text_value['scm']['Write']='1';
$equivs_text_value['docman']['Read/Post']='0';
$equivs_text_value['docman']['Admin']='1';
$equivs_text_value['forumadmin']['None']='0';
$equivs_text_value['forumadmin']['Admin']='2';
$equivs_text_value['forum']['No Access']='-1';
$equivs_text_value['forum']['Read']='0';
$equivs_text_value['forum']['Post']='1';
$equivs_text_value['forum']['Admin']='2';
$equivs_text_value['trackeradmin']['None']='0';
$equivs_text_value['trackeradmin']['Admin']='2';
$equivs_text_value['tracker']['No Access']='-1';
$equivs_text_value['tracker']['Read']='0';
$equivs_text_value['tracker']['Tech']='1';
$equivs_text_value['tracker']['Tech & Admin']='2';
$equivs_text_value['tracker']['Admin Only']='3';
$equivs_text_value['pmadmin']['None']='0';
$equivs_text_value['pmadmin']['Admin']='2';
$equivs_text_value['pm']['No Access']='-1';
$equivs_text_value['pm']['Read']='0';
$equivs_text_value['pm']['Tech']='1';
$equivs_text_value['pm']['Tech & Admin']='2';
$equivs_text_value['pm']['Admin Only']='3';
$equivs_text_value['webcal']['No access']='0';
$equivs_text_value['webcal']['Modify']='1';
$equivs_text_value['webcal']['See']='2';


$observer_equivs_text_value['projectpublic']['Private']=0;
$observer_equivs_text_value['projectpublic']['Public']=1;
$observer_equivs_text_value['scmpublic']['Private']=0;
$observer_equivs_text_value['scmpublic']['Public (PServer)']=1;
$observer_equivs_text_value['forumpublic']['Private']=0;
$observer_equivs_text_value['forumpublic']['Public']=1;
$observer_equivs_text_value['forumanon']['No Anonymous Posts']=0;
$observer_equivs_text_value['forumanon']['Allow Anonymous Posts']=1;
$observer_equivs_text_value['trackerpublic']['Private']=0;
$observer_equivs_text_value['trackerpublic']['Public']=1;
$observer_equivs_text_value['trackeranon']['No Anonymous Posts']=0;
$observer_equivs_text_value['trackeranon']['Allow Anonymous Posts']=1;
$observer_equivs_text_value['pmpublic']['Private']=0;
$observer_equivs_text_value['pmpublic']['Public']=1;
$observer_equivs_text_value['frspackage']['Private']=0;
$observer_equivs_text_value['frspackage']['Public']=1;


$equivs_name_value['Documentation Manager']='docman';
$equivs_name_value['File Release System']='frs';
$equivs_name_value['Forum Admin']='forumadmin';
$equivs_name_value['Forum:']='forum';
$equivs_name_value['Project Admin']='projectadmin';
$equivs_name_value['Tasks Admin']='pmadmin';
$equivs_name_value['Tasks:']='pm';
$equivs_name_value['Tracker Admin']='trackeradmin';
$equivs_name_value['Tracker:']='tracker';
$equivs_name_value['Webcal']='webcal';
$equivs_name_value['SCM']='scm';


$observer_equivs_name_value['Project']='projectpublic';
$observer_equivs_name_value['SCM']='scmpublic';
$observer_equivs_name_value['Forum:']='forumpublic';
$observer_equivs_name_value['Forum:AnonPost:']='forumanon';
$observer_equivs_name_value['Tracker:']='trackerpublic';
$observer_equivs_name_value['Project']='projectpublic';
$observer_equivs_name_value['Tracker:AnonPost:']='trackeranon';
$observer_equivs_name_value['Tasks:']='pmpublic';
$observer_equivs_name_value['Files']='frspackage';

global $cache_forums;
global $cache_tasks;
global $cache_trackers;
global $cache_frs;	
	
//$cache_forums=array();
//$cache_tasks=array();
//$cache_trackers=array();
//$cache_frs=array();	


function get_role_by_name($role,$group_id){
	$role_id = FALSE;
  $res = db_query("SELECT role_id
					FROM role
					WHERE group_id='$group_id' AND role_name='$role'");
  //TODO:Cleanup, there must be a function to catch only one result
  while ($row=db_fetch_array($res)){
    $role_id=$row['role_id'];
  }
  return $role_id;
}
function check_roles(&$roles, $group_id){
  $rolestodelete = array();
  $res = db_query("SELECT role_id,role_name
					FROM role
					WHERE group_id='$group_id'");
		
  while ($row_roles=db_fetch_array($res)){
    $res_roles[]=array($row_roles['role_name'],$row_roles['role_id']);
  }	
  foreach($res_roles as $nameid){
    if(isset($roles[$nameid[0]])){
      $roles[$nameid[0]]["role_id"]=$nameid[1];
    }
    else{
      $rolestodelete[]=$nameid[1];
    }
  }
  return $rolestodelete;
}
	

function get_forum_id($forumname,$group_id,$i){
  $forum_id=-1;
		
  if(array_key_exists($forumname,$cache_forums)){
    $cache_forums[$forumname][1]=1;
    $forum_id = $cache_forums[$forumname];
  }
  else {
    $res = db_query("SELECT group_forum_id,forum_name 
					FROM forum_group_list WHERE group_id='$group_id'");
    while ($row=db_fetch_array($res)){
      if ($row['forum_name']==$forumname){
	$cache_forums[$row['forum_name']]=array($row['group_forum_id'],1);
	$forum_id = $row['group_forum_id'];
      }
      else{
	$cache_forums[$row['forum_name']]=array($row['group_forum_id'],0);
      }
    }
  }
  if($forum_id==-1){
    //TODO:Create forum
  }
  return $forum_id;
}
	
function get_tasks_id($taskname,$group_id,$i){
  $task_id=-1;
		
  if(array_key_exists($taskname,$cache_tasks)){
    $cache_tasks[$taskname][1]=1;
    $task_id = $cache_tasks[$taskname][0];
  }
  else {
    $res = db_query("SELECT group_project_id,project_name 
					FROM project_group_list WHERE group_id='$group_id'");
    while ($row=db_fetch_array($res)){
      if ($row['project_name']==$taskname){
	$cache_tasks[$row['project_name']]=array($row['group_project_id'],1);
	$task_id = $row['group_project_id'];
      }
      else{
	$cache_forums[$row['project_name']]=array($row['group_project_id'],0);
      }
    }
  }
  if ($task_id==-1){
    //TODO:Create Task tracker
  }
  return $task_id;
}
	
function get_tracker_id($trackername,$group_id,$i){
  $tracker_id=-1;
		
  if(array_key_exists($trackername,$cache_trackers)){
    $cache_trackers[$trackername][1]=1;
    $tracker_id = $cache_trackers[$trackername][0];
  }
  else {
    $res = db_query("SELECT group_artifact_id,name 
				FROM artifact_group_list WHERE group_id='$group_id'");
    while ($row=db_fetch_array($res)){
      if ($row['name']==$trackername){
	$cache_trackers[$row['name']]=array($row['group_artifact_id'],1);
	$tracker_id = $row['group_artifact_id'];
      }
      else{
	$cache_trackers[$row['name']]=array($row['group_artifact_id'],0);
      }
    }
  }
	
  if ($tracker_id==-1){
    //TODO:Create Tracker
  }
  return $tracker_id;
}
	
	
function get_frs_id($frsname, $group_id){
  $frs_id=-1;
  if(array_key_exists($trackername,$cache_frs)){
    $cache_frs[$frsname][1]=1;
    $frs_id = $cache_frs[$frsname][0];
  }
  else {
    $res = db_query("SELECT package_id,name 
				FROM frs_package WHERE group_id='$group_id'");
    while ($row=db_fetch_array($res)){
      if ($row['name']==$frsname){
	$cache_frs[$row['name']]=array($row['package_id'],1);
	$frs_id = $row['package_id'];
      }
      else{
	$cache_frs[$row['name']]=array($row['package_id'],0);
      }
    }
  }
		
  return $frs_id;
}
	
function role_update($group_id, $rolename, $role_id, $data){

  if ($role_id=='observer') {
			
    $role = new RoleObserver(group_get_object($group_id));
    if (!$role || !is_object($role)) {
      exit_error('Error','Could Not Get RoleObserver');
    } elseif ($role->isError()) {
      exit_error('Error',$role->getErrorMessage());
    }
			
    if (!$role->update($data)) {
      $feedback = $role->getErrorMessage();
    } else {
      $feedback = _('Successfully Updated Role');
    }
			
			
  }
  else{
    echo "update de : ".$role_id." ".$rolename."<br>";
    $role = new Role(group_get_object($group_id),$role_id);
    if (!$role || !is_object($role)) {
      exit_error('Error',_('Could Not Get Role'));
    } elseif ($role->isError()) {
      exit_error('Error',$role->getErrorMessage());
    }
			
    if (!$role->update($rolename,$data)) {
      $feedback = $role->getErrorMessage();
    } else {
      $feedback = _('Successfully Updated Role');
    }
    plugin_hook('change_cal_permission_auto',$group_id);	
  }
}
	
function role_create($group_id, $rolename, $data){
  $role = new Role(group_get_object($group_id),false);
  if (!$role || !is_object($role)) {
    exit_error('Error',_('Could Not Get Role'));
  } elseif ($role->isError()) {
    exit_error('Error',$role->getErrorMessage());
  }
  echo "<br>Role added:".$rolename;/*
				     var_dump($rolename);	
				     echo "<br>";
				     echo "groupidid:<br>";
				     var_dump($group_id);
				     echo "<br>";
				     echo "data:<br>";
				     var_dump($data);
				     echo "<br>";*/
  $role_id=$role->create($rolename,$data);
  if (!$role_id) {
    $feedback = $role->getErrorMessage();
  } else {
    $feedback = _('Successfully Created New Role');
  }
  plugin_hook('change_cal_permission_auto',$group_id);	
}
	
function role_fill($roles,$group_id, $equivs_text_value,$equivs_name_value, $observer_equivs_text_value, $observer_equivs_name_value ){
  //	$debugdata=array();
  foreach($roles as $rolename => $rights){
			
    $data = array(array());
			
    $i=0;
	
    if($rolename=='Observer'){
      $j=0;
      foreach($rights as $rightname => $right){
	if(substr($rightname, 0, 6)=='Forum:'){
	  if(substr($rightname,6, 9)=='AnonPost:'){
	    $forum_id = get_forum_id(substr($rightname, 15),$group_id,$j);
	    $data['forumanon'][$forum_id]=$observer_equivs_text_value['forumanon'][$right];
	  }
	  else{
	    $forum_id = get_forum_id(substr($rightname, 6),$group_id,$j);
	    $data['forumpublic'][$forum_id]=$observer_equivs_text_value['forumpublic'][$right];
	  }
	}
	elseif(substr($rightname, 0, 8)=='Tracker:'){
	  if(substr($rightname,8, 9)=='AnonPost:'){
	    $tracker_id = get_tracker_id(substr($rightname, 17),$group_id, $j);
	    $data['trackeranon'][$tracker_id]=$observer_equivs_text_value['trackeranon'][$right];
	  }
	  else{
	    $tracker_id = get_tracker_id(substr($rightname, 6), $group_id, $j);
	    $data['trackerpublic'][$tracker_id]=$observer_equivs_text_value['trackerpublic'][$right];
	  }
	}
	elseif(substr($rightname, 0, 6)=='Tasks:'){
	  $tasks_id = get_tasks_id(substr($rightname, 6),$group_id,$j);
	  $data['pmpublic'][$tasks_id]=$observer_equivs_text_value['pmpublic'][$right];
	}
	elseif(substr($rightname, 0, 6)=='Files:'){
	  $frs_id = get_frs_id(substr($rightname, 6),$group_id);
	  $data['frspackage'][$frs_id]=$observer_equivs_text_value['frspackage'][$right];
	}
	elseif($rightname!='role_id'){
	  $data[$observer_equivs_name_value[$rightname]][0]=$observer_equivs_text_value[$observer_equivs_name_value[$rightname]][$right];
	}
	$j++;	
      }
      if(array_key_exists("role_id", $rights)){
	role_update($group_id, $rolename, $rights['role_id'], $data);
      }
      else{
	role_create($group_id, $rolename, $data);
      }
      //			$debugdata[]=array($rolename,$data);
    }
    else{
	
      foreach($rights as $rightname => $right){
	if(substr($rightname, 0, 6)=='Forum:'){
	  $forum_id = get_forum_id(substr($rightname, 6),$group_id,$i);
	  $data['forum'][(int) $forum_id]=$equivs_text_value['forum'][(string)$right];
	}
	elseif(substr($rightname, 0, 8)=='Tracker:'){
	  $tracker_id = get_tracker_id(substr($rightname, 8),$group_id,$i);
	  $data['tracker'][$tracker_id]=$equivs_text_value['tracker'][$right];
	}
	elseif(substr($rightname, 0, 6)=='Tasks:'){
	  $tasks_id = get_tasks_id(substr($rightname, 6),$group_id,$i);
	  $data['pm'][$tasks_id]=$equivs_text_value['pm'][$right];
	}
	elseif($rightname!='role_id'){
	  $data[$equivs_name_value[$rightname]][0]=$equivs_text_value[$equivs_name_value[$rightname]][$right];
	}
	$i++;
      }
      if(array_key_exists("role_id", $rights)){
	role_update($group_id, $rolename, $rights['role_id'], $data);
      }
      else{
	role_create($group_id,$rolename, $data);
      }
      //			$debugdata[]=array($rolename,$data);
    }
  }
  //	var_dump($debugdata);
}
	

/**
 * Insert users into the group
 * @param unknown_type $users 'user_name' => {'role': 'role_name' }
                  ... }
 * @param unknown_type $group_id group to insert users into
 * @param unknown_type $check
 */
function user_fill($users, $group_id, $check=False){

	$group =& group_get_object($group_id);
	if (!$group || !is_object($group)) {
		exit_error('Error','Could Not Get Group');
	} elseif ($group->isError()) {
		exit_error('Error',$group->getErrorMessage());
	}

	foreach ($users as $user => $role){
		global $feedback;
		global $message;
		$user_object = &user_get_object_by_name($user);
		if (!$user_object) {
			$feedback .= sprintf(_('Failed to find user %s'), $user);
		} else {
			$user_id = $user_object->getID();
			$role_id = get_role_by_name($role['role'],$group_id);
			if(!$check) {
				if (!$group->addUser($user,$role_id)) {
					$feedback = $group->getErrorMessage();
				} else {
					echo 'User added:'.$user.'<br>';
					$feedback = _('User Added Successfully');

					//plugin webcal
					//change assistant for webcal
					$params[0] = $user_id;
					$params[1] = $group_id;
					plugin_hook('change_cal_permission',$params);
				}
			}
			else {
				$message .= 'Need to add user: '.$user.' to group with role '. $role_id. "<br />\n";
			}
		}
	}
}
