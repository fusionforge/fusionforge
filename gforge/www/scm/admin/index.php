<?php
/**
  *
  * SourceForge CVS Frontend
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');    
require_once('common/include/account.php');

//only projects can use cvs, and only if they have it turned on
$project =& group_get_object($group_id);

if (!$project->isProject()) {
 	exit_error('Error',$Language->getText('scm_index','error_only_projects_can_use_cvs'));
}
if (!$project->usesCVS()) {
	exit_error('Error',$Language->getText('scm_index','error_this_project_has_turned_off'));
}

site_project_header(array('title'=>$Language->getText('scm_index','cvs_repository'),'group'=>$group_id,'toptab'=>'scm_index','pagename'=>'scm_index','sectionvals'=>array($project->getPublicName())));

if ($submit) {
	$hook_params = array () ;
	$hook_params['group_id'] = $group_id ;
	$scmvars = array_keys ($_GET) ;
	foreach ($_GET as $key => $value) {
		foreach ($scm_list as $scm) {
			if ($key == strstr($key, $scm . "_")) {
				$hook_params[$key] = $value ;
			}
		}
	}
	$hook_params['scmradio'] = $scmradio ;
	plugin_hook ("scm_admin_update", $hook_params) ;
}

?>
<form action="/scm/admin/index.php?group_id=<?php echo $group_id; ?>">
<?php

	$hook_params = array () ;
	$hook_params['group_id'] = $group_id ;
	plugin_hook ("scm_admin_page", $hook_params) ;
?>
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
<input type="submit" name="submit" value="Update">
</form>
<?php

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
