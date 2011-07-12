<?php

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';

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
	<?php
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
$res = db_query_params ('SELECT value::integer,admin_flags FROM user_group,role_setting WHERE role_setting.role_id = user_group.role_id AND user_group.user_id = $1 AND user_group.group_id = $2 AND role_setting.section_name = $3',
			array ($user_id,
				$group_id,
				'webcal'));
$row = db_fetch_array($res);
if($row[0] < 1 ){
//verif si admin
	$res_admin = db_query_params ('SELECT COUNT(*) FROM  user_group WHERE user_id = $1 AND  group_id = $2 AND admin_flags = $3',
			array ($user_id,
				$group_id,
				'A'));
	$row_admin = db_fetch_array($res_admin);
	$row[0] = $row_admin[0];
}
if( $row[0] < 1) {
	//verif si admin
	$res_admin = db_query_params ('SELECT COUNT(*) FROM  webcal_user,users WHERE users.user_name = webcal_user.cal_login AND users.user_id = $1 AND  cal_is_admin = $2',
			array ($user_id,
				'Y'));
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
