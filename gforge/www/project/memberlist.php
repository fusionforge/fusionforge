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

site_project_header(array('title'=>"Project Member List",'group'=>$group_id,'toptab'=>'memberlist'));

print $Language->getText('project_memberlist', 'joining');

// list members
$query = "
	SELECT users.user_name AS user_name,users.user_id AS user_id,
		users.realname AS realname, users.add_date AS add_date, 
		user_group.admin_flags AS admin_flags, 
		people_job_category.name AS role 
	FROM users,user_group,people_job_category 
	WHERE users.user_id=user_group.user_id 
	AND user_group.group_id='$group_id' 
	AND user_group.member_role=people_job_category.category_id 
	AND users.status='A'
	ORDER BY users.user_name
";


$title_arr=array();
$title_arr[]='Developer';
$title_arr[]='Username';
$title_arr[]='Role/Position';
$title_arr[]='Email';
$title_arr[]='Skills';

echo html_build_list_table_top ($title_arr);

$res_memb = db_query($query);
while ( $row_memb=db_fetch_array($res_memb) ) {
	print "\t<tr>\n";
	print "\t\t";
	if ( trim($row_memb[admin_flags])=='A' ) {
		print "\t\t<td><b><A href=\"/users/$row_memb[user_name]/\">$row_memb[realname]</A></b></td>\n";
	} else {
		print "\t\t<td>$row_memb[realname]</td>\n";
	}
	print "\t\t<td align=\"middle\"><A href=\"/users/$row_memb[user_name]/\">$row_memb[user_name]</A></td>\n";
	print "\t\t<td align=\"middle\">$row_memb[role]</td>\n";
	print "\t\t<td align=\"middle\"><A href=\"/sendmessage.php?touser=".$row_memb['user_id'].
		"\">".$row_memb['user_name']." at users.".$GLOBALS['sys_users_host']."</td>\n";
	print "\t\t<td align=\"middle\"><A href=\"/people/viewprofile.php?user_id=".
		$row_memb['user_id']."\">View</a></td>\n";
	print "\t</tr>\n";
}
print "\t</table>";

site_project_footer(array());

?>
