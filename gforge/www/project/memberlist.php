<?php
/**
  *
  * Project Members Information
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');

if (!$group_id && $form_grp) {
	$group_id = $form_grp;
}

site_project_header(array('title'=>$Language->getText('project_memberlist','title'),'group'=>$group_id,'toptab'=>'memberlist'));

print $Language->getText('project_memberlist', 'joining');

// list members
$query = "SELECT users.*,user_group.admin_flags,people_job_category.name AS role
	FROM users,user_group 
	LEFT JOIN people_job_category ON user_group.member_role=people_job_category.category_id
	WHERE users.user_id=user_group.user_id 
	AND user_group.group_id='$group_id' 
	AND users.status='A'
	ORDER BY users.user_name ";


$title_arr=array();
$title_arr[]=$Language->getText('project_memberlist', 'developer');
$title_arr[]=$Language->getText('project_memberlist', 'username');
$title_arr[]=$Language->getText('project_memberlist', 'role');
$title_arr[]=$Language->getText('project_memberlist', 'skills');

echo $GLOBALS['HTML']->listTableTop ($title_arr);

$res_memb = db_query($query);
while ( $row_memb=db_fetch_array($res_memb) ) {
	print "<tr ".$HTML->boxGetAltRowStyle($i++).">";
	if ( trim($row_memb['admin_flags'])=='A' ) {
		print "\t\t<td><strong>$row_memb[realname]</strong></td>\n";
	} else {
		print "\t\t<td>$row_memb[realname]</td>\n";
	}
	print "
		<td align=\"center\"><a href=\"/users/$row_memb[user_name]/\">$row_memb[user_name]</a></td>
		<td align=\"center\">$row_memb[role]</td>
		<td align=\"center\"><a href=\"/people/viewprofile.php?user_id=$row_memb[user_id]\">".$Language->getText('project_memberlist','view')."</a></td>
	</tr>";
}

echo $GLOBALS['HTML']->listTableBottom();

site_project_footer(array());

?>
