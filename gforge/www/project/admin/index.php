<?php
/**
  *
  * Project Admin Main Page
  *
  * This page contains administrative information for the project as well
  * as allows to manage it. This page should be accessible to all project
  * members, but only admins may perform most functions.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/project/admin/project_admin_utils.php');
require_once('common/include/account.php');

session_require(array('group'=>$group_id));

// get current information
$group =& group_get_object($group_id);
exit_assert_object($group,'Group');

$perm =& $group->getPermission( session_get_user() );
exit_assert_object($perm,'Permission');

// only site admin get access inactive projects
if (!$group->isActive() && !$perm->isSuperUser()) {
	exit_error('Permission denied', 'Group is inactive.');
}

$is_admin = $perm->isAdmin();

// Only admin can make modifications via this page
if ($is_admin && $func) {
	/*
		updating the database
	*/
	if ($func=='adduser') {
		/*
			add user to this project
		*/

		if (!$group->addUser($form_unix_name)) {
			$feedback .= $group->getErrorMessage();
		} else {
			$feedback = ' User Added Successfully ';
		}

	} else if ($func=='rmuser') {
		/*
			remove a user from this group
		*/
		if (!$group->removeUser($rm_id)) {
			$feedback .= $group->getErrorMessage();
		} else {
			$feedback = ' User Removed Successfully ';
		}
	}

}

$group->clearError();

project_admin_header(array('title'=>"Project Admin: ".$group->getPublicName(),'group'=>$group->getID(),'pagename'=>'project_admin','sectionvals'=>array($group->getPublicName())));

/*
	Show top box listing trove and other info
*/

?>

<TABLE width=100% cellpadding=2 cellspacing=2 border=0>
<TR valign=top><TD width=50%>

<?php $HTML->box1_top("Misc. Project Information");  ?>

&nbsp;
<BR>
Short Description: <?php echo $group->getDescription(); ?>
<P>
Homepage Link: <b><?php echo $group->getHomepage(); ?></b>
<p>
Group shell (SSH) server: <b><?php echo $group->getUnixName().'.'.$GLOBALS['sys_default_domain']; ?>
<p>
Group directory on shell server: <b><?php echo account_group_homedir($group->getUnixName()); ?>
<p>
Project WWW directory on shell server <a href="/docman/display_doc.php?docid=774&group_id=1">(how to upload)</a>:
<b><?php echo account_group_homedir($group->getUnixName()).'/htdocs'; ?>

<P align=center>
<A HREF="http://<?php echo $GLOBALS['sys_cvs_host']; ?>/cvstarballs/<?php echo $group->getUnixName(); ?>-cvsroot.tar.gz">[ Download Your Nightly CVS Tree Tarball ]</A>
<P>

<HR NOSHADE>
<P>
<H4>Trove Categorization:
<A href="/project/admin/group_trove.php?group_id=<?php echo $group->getID(); ?>">
[Edit]</A></H4>
<P>
<HR NOSHADE>
<P>
<H4>Showing The SourceForge Logo:</H4>
<p>
<font size=-1>
If you use SourceForge services, we ask you to display our logo
on project homepage, as explained
<a href="http://sourceforge.net/docman/display_doc.php?docid=790&group_id=1">here
</a>
</font>
</p>
<P>
<?php
echo htmlspecialchars('<A href="http://'.$GLOBALS['sys_default_domain'].'"> 
<IMG src="http://'.$GLOBALS['sys_default_domain'].'/sflogo.php?group_id='. $group_id .'" width="88" height="31"
border="0" alt="SourceForge Logo"></A>');

echo '<P>'.html_image('images/sflogo-88-1.png','88','31',array(),0);

$HTML->box1_bottom(); 

echo '
</TD><TD>&nbsp;</TD><TD width=50%>';


$HTML->box1_top("Group Members");

/*

	Show the members of this project

*/

$res_memb = db_query("SELECT users.realname,users.user_id,users.user_name,user_group.admin_flags ".
		"FROM users,user_group ".
		"WHERE users.user_id=user_group.user_id ".
		"AND user_group.group_id='$group_id'");

print '<TABLE WIDTH="100%" BORDER="0">';

while ($row_memb=db_fetch_array($res_memb)) {

	if (stristr($row_memb['admin_flags'], 'A')) {
		$img="trash-x.png";
	} else {
		$img="trash.png";
	}
	if ($is_admin) {
		$button='<INPUT TYPE="IMAGE" NAME="DELETE" SRC="/images/ic/'.$img.'" HEIGHT="16" WIDTH="16" BORDER="0">';
	} else {
		$button='&nbsp;';
	}
	print '
		<FORM ACTION="rmuser.php" METHOD="POST"><INPUT TYPE="HIDDEN" NAME="func" VALUE="rmuser">'.
		'<INPUT TYPE="HIDDEN" NAME="return_to" VALUE="'.$REQUEST_URI.'">'.
		'<INPUT TYPE="HIDDEN" NAME="rm_id" VALUE="'.$row_memb['user_id'].'">'.
		'<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="'. $group_id .'">'.
		'<TR><TD ALIGN="MIDDLE">'.$button.'</TD></FORM>'.
		'<TD><A href="/users/'.$row_memb['user_name'].'/">'.$row_memb['realname'].'</A></TD></TR>';
}
print '</TABLE>';

/*
	Add member form
*/

if ($is_admin) {

	// After adding user, we go to the permission page for one
?>
	<HR NoShade SIZE="1">
	<FORM ACTION="userpermedit.php?group_id=<?php echo $group->getID(); ?>" METHOD="POST">
	<INPUT TYPE="hidden" NAME="func" VALUE="adduser">
	<TABLE WIDTH="100%" BORDER="0">
	<TR><TD><B>Unix Name:</B></TD><TD><INPUT TYPE="TEXT" NAME="form_unix_name" SIZE=10 VALUE=""></TD></TR>
	<TR><TD COLSPAN="2" ALIGN="CENTER"><INPUT TYPE="SUBMIT" NAME="submit" VALUE="Add User"></TD></TR></FORM>
	</TABLE>

	<HR NoShade SIZE="1">
	<div align="center">
	<A href="/project/admin/userperms.php?group_id=<?php echo $group->getID(); ?>">[Edit Member Permissions]</A>
	</div>
	</TD></TR>

<?php
}
?>
 
<?php $HTML->box1_bottom();?>


</TD></TR>

<TR valign=top><TD width=50%>

<?php

/*
	Tool admin pages
*/

$HTML->box1_top('Tool Admin');

?>

<BR>
<A HREF="/tracker/admin/?group_id=<?php echo $group->getID(); ?>">Tracker Admin</A><BR>
<A HREF="/docman/admin/?group_id=<?php echo $group->getID(); ?>">DocManager Admin</A><BR>
<A HREF="/mail/admin/?group_id=<?php echo $group->getID(); ?>">Mail Admin</A><BR>
<A HREF="/news/admin/?group_id=<?php echo $group->getID(); ?>">News Admin</A><BR>
<A HREF="/pm/admin/?group_id=<?php echo $group->getID(); ?>">Task Manager Admin</A><BR>
<A HREF="/forum/admin/?group_id=<?php echo $group->getID(); ?>">Forum Admin</A><BR>

<?php $HTML->box1_bottom(); ?>




</TD>

<TD>&nbsp;</TD>

<TD width=50%>

<?php
/*
	Show filerelease info
*/
?>

<?php $HTML->box1_top("File Releases"); ?>
	&nbsp;<BR>
	<CENTER>
	<A href="editpackages.php?group_id=<?php print $group_id; ?>"><B>[Edit/Add File Releases]</B></A>
	</CENTER>

	<HR>
	<B>Packages:</B> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A href="/docman/display_doc.php?docid=780&group_id=1"><i>What is this?</i></A> (Very Important!)

	<P>

	<?php

	$res_module = db_query("SELECT * FROM frs_package WHERE group_id='$group_id'");
	while ($row_module = db_fetch_array($res_module)) {
		print "$row_module[name]<BR>";
	}

	echo $HTML->box1_bottom();
	?>
</TD>
</TR>
</TABLE>

<?php

project_admin_footer(array());

?>
