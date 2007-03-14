<?php

require_once('../../env.inc.php');
require_once('pre.php');

function forum_header($params) {
	global $HTML,$group_id,$forum_name,$forum_id,$sys_datefmt,$sys_news_group,$Language,$f,$sys_use_forum,$group_forum_id;

	if ($group_forum_id) {
		$forum_id=$group_forum_id;
	}
	if (!$sys_use_forum) {
		exit_disabled();
	}

	$params['group']=$group_id;
	$params['toptab']='webcalendar';

	/*
		bastardization for news
		Show icon bar unless it's a news forum
	*/
	if ($group_id == $sys_news_group) {
		//this is a news item, not a regular forum
		if ($forum_id) {
			// Show this news item at the top of the page
			$sql="SELECT submitted_by, post_date, group_id, forum_id, summary, details FROM news_bytes WHERE forum_id='$forum_id'";
			$result=db_query($sql);

			// checks which group the news item belongs to
			$params['group']=db_result($result,0,'group_id');
			$params['toptab']='news';
			$HTML->header($params);


			echo '<table><tr><td valign="top">';
			if (!$result || db_numrows($result) < 1) {
				echo '<h3>'._('Error - this news item was not found').'</h3>';
			} else {
				$user = user_get_object(db_result($result,0,'submitted_by'));
				$group =& group_get_object($params['group']);
				if (!$group || !is_object($group) || $group->isError()) {
					exit_no_group();
				}
				echo '
				<strong>'._('Posted by').':</strong> '.$user->getRealName().'<br />
				<strong>'._('Date').':</strong> '. date($sys_datefmt,db_result($result,0,'post_date')).'<br />
				<strong>'._('Summary').':</strong> <a href="/forum/forum.php?forum_id='.db_result($result,0,'forum_id').'&group_id='.$group_id.'">'. db_result($result,0,'summary').'</a><br/>
				<strong>'._('Project').':</strong> <a href="/projects/'.$group->getUnixName().'">'.$group->getPublicName().'</a> <br />
				<p>
				'. (util_make_links(nl2br(db_result($result,0,'details'))));

				echo '</p>';
			}
			echo '</td><td valign="top" width="35%">';
			echo $HTML->boxTop(_('Latest News'));
			echo news_show_latest($params['group'],5,false);
			echo $HTML->boxBottom();
			echo '</td></tr></table>';
		} else {
			site_project_header($params);
		}
	} else {
		site_project_header($params);
	}

	$menu_text=array();
	$menu_links=array();
	if ($f && $forum_id) {
		$menu_text[]=_('Discussion Forums:') .' '. $f->getName();
		$menu_links[]='"/forum/forum.php?forum_id='.$forum_id.'"';
	}
	if ($f && $f->userIsAdmin()) {
		$menu_text[]=_('Admin');
		$menu_links[]='/forum/admin/?group_id='.$group_id;
	}
	if (count($menu_text) > 0) {
		echo $HTML->subMenu(
			$menu_text,
			$menu_links
		);
	}

	if (session_loggedin() ) {
		if ($f) {
			if ($f->isMonitoring()) {
				echo '<a href="/forum/monitor.php?forum_id='.$forum_id.'&amp;group_id='.$group_id.'&amp;stop=1">' .
				html_image('ic/xmail16w.png','20','20',array()).' '._('Stop Monitoring').'</a> | ';
			} else {
				echo '<a href="/forum/monitor.php?forum_id='.$forum_id.'&amp;group_id='.$group_id.'&amp;start=1">' .
				html_image('ic/mail16w.png','20','20',array()).' '._('Monitor Forum').'</a> | ';
			}
			echo '<a href="/forum/save.php?forum_id='.$forum_id.'&amp;group_id='.$group_id.'">' .
			html_image('ic/save.png','24','24',array()) .' '._('Save Place').'</a> | ';
		}
	}

	if ($f && $forum_id) {
		echo '<a href="/forum/new.php?forum_id='.$forum_id.'&amp;group_id='.$group_id.'">' .
			html_image('ic/write16w.png','20','20',array('alt'=>_('Start New Thread'))) .' '.
			_('Start New Thread').'</a>';
	}
}

function forum_footer($params) {
	site_project_footer($params);
}

forum_header(array('title'=>'Webcalendar' ));

$group_id = getIntFromRequest('group_id');
if ($group_id > 5) { // add '> 5' if you won't a calendar for the admin groups
	$g =& group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) { 
		exit_no_group();
	} else {
	$user_id = user_getid() ;
	$belong =  user_belongs_to_group($user_id,$group_id);
	if($belong > 0){
	?>	
	<iframe src="/plugins/webcalendar/login.php?type=group&group_id=<?php print $group_id ?>" border=no scrolling="yes" width="100%" height="700"></iframe>	
	<?}
	else {
	print _('You are not allowed to see this calendar.');	
	}	

	}
	
	
} else {

	print _('No calendar for this group.');
	
	//exit_no_group(); 

}

echo site_user_footer(array());

function user_belongs_to_group($user_id,$group_id){
global $HTML,$Language;
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

?>
