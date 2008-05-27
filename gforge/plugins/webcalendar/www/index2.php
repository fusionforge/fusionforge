<?php

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';

$group_id = getIntFromRequest('group_id');
site_project_header(array('title'=>'Webcalendar','group'=>$group_id,'toptab'=>'webcalendar' ));

if ($group_id > 5) { // add '> 5' if you won't a calendar for the admin groups
	$g =& group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) { 
		exit_no_group();
	} else {
	$user_id = user_getid() ;
	$belong =  user_belongs_to_group($user_id,$group_id);
		if($belong > 0){
	?>	
	<iframe src="<?php echo util_make_url('/plugins/webcalendar/login.php?type=group&group_id='.$group_id); ?>" border=no scrolling="yes" width="100%" height="700"></iframe>	
	<?
		} else {
			print _('You are not allowed to see this calendar.');	
		}	

	}
} else {
	print _('No calendar for this group.');
	//exit_no_group(); 
}
echo site_project_footer(array());

function user_belongs_to_group($user_id,$group_id){
global $HTML;
$sql = "SELECT value,admin_flags FROM user_group,role_setting WHERE role_setting.role_id = user_group.role_id AND user_group.user_id = '".$user_id."' AND user_group.group_id = '".$group_id."' AND role_setting.section_name = 'webcal'";
		
//$sql = "SELECT COUNT(*) FROM user_group WHERE user_id = '".$user_id."' AND group_id = '".$group_id."'";	
$res = db_query($sql);
$row = db_fetch_array($res);
if($row[0] < 1 ){
//verif si admin 
	$sql_admin = "SELECT COUNT(*) FROM  user_group WHERE user_id = '".$user_id."' AND  group_id = '".$group_id."' AND admin_flags = 'A'" ;	
	$res_admin = db_query($sql_admin);
	$row_admin = db_fetch_array($res_admin);
	$row[0] = $row_admin[0];
} 
if( $row[0] < 1) {
	//verif si admin 
	$sql_admin = "SELECT COUNT(*) FROM  webcal_user,users WHERE users.user_name = webcal_user.cal_login AND users.user_id = '".$user_id."' AND  cal_is_admin = 'Y'" ;	
	$res_admin = db_query($sql_admin);
	$row_admin = db_fetch_array($res_admin);
	$row[0] = $row_admin[0];
}


return $row[0];	
	
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
