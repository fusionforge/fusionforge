<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require ('pre.php');    
require ($DOCUMENT_ROOT.'/project/admin/project_admin_utils.php');
require ('account.php');

session_require(array('group'=>$group_id));

// get current information
$project=&group_get_object($group_id);
$res_grp = $project->getData();
$is_admin=$project->userIsAdmin();

// Only admin can make modifications via this page
if ($is_admin && $func) {
	/*
		updating the database
	*/
	if ($func=='adduser') {
		/*
			add user to this project
		*/

		if (!$project->addUser($form_unix_name)) {
			$feedback .= $project->getErrorMessage();
		} else {
			$feedback = ' User Added Successfully ';
		}

	} else if ($func=='rmuser') {
		/*
			remove a user from this portal
		*/
		if (!$project->removeUser($rm_id)) {
			$feedback .= $project->getErrorMessage();
		} else {
			$feedback = ' User Removed Successfully ';
		}
	}

}

$project->clearError();

project_admin_header(array('title'=>"Project Admin: ".group_getname($group_id),'group'=>$group_id));

/*
	Show top box listing trove and other info
*/

echo '<TABLE width=100% cellpadding=2 cellspacing=2 border=0>
<TR valign=top><TD width=50%>';

$HTML->box1_top("Misc. Project Information"); 

print '&nbsp;
<BR>
Short Description: '. db_result($res_grp,0,'short_description') .'
<P>
Homepage Link: <B>'. db_result($res_grp,0,'homepage') .'</B>
<P align=center>
<A HREF="http://'.$GLOBALS['sys_cvs_host'].'/cvstarballs/'. db_result($res_grp,0,'unix_group_name') .'-cvsroot.tar.gz">[ Download Your Nightly CVS Tree Tarball ]</A>
<P>

<HR NOSHADE>
<P>
<H4>Trove Categorization:</H4>
<P>
<A href="/project/admin/group_trove.php?group_id='.$group_id.'">'
.'<B>[Edit Trove Categorization]</B></A>
<P>
<HR NOSHADE>
<P>
<H4>Showing The SourceForge Logo:</H4>
<P>
'.
htmlspecialchars('<A href="http://sourceforge.net"> 
<IMG src="http://sourceforge.net/sflogo.php?group_id='. $group_id .'" width="88" height="31"
border="0" alt="SourceForge Logo"></A>');

echo '<P>'.html_image('/images/sflogo-88-1.png','88','31',array(),0);

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
		"AND user_group.group_id=$group_id");

	print '<TABLE WIDTH="100%" BORDER="0">
';
	while ($row_memb=db_fetch_array($res_memb)) {
		if ($row_memb['admin_flags']=='A') $img="trash-x.png";
		else $img="trash.png";
                if ($is_admin) $button='<INPUT TYPE="IMAGE" NAME="DELETE" SRC="/images/ic/'.$img.'" HEIGHT="16" WIDTH="16" BORDER="0">';
                else $button='&nbsp;';
		print '
		<FORM ACTION="'. $PHP_SELF .'" METHOD="POST"><INPUT TYPE="HIDDEN" NAME="func" VALUE="rmuser">'.
		'<INPUT TYPE="HIDDEN" NAME="rm_id" VALUE="'.$row_memb['user_id'].'">'.
		'<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="'. $group_id .'">'.
		'<TR><TD ALIGN="MIDDLE">'.$button.'</TD></FORM>'.
		'<TD><A href="/users/'.$row_memb['user_name'].'/">'.$row_memb['realname'].'</A></TD></TR>';
	}
	print '</TABLE>
';

/*
	Add member form
*/

if ($is_admin)
echo '
	<HR NoShade SIZE="1">
	<FORM ACTION="'. $PHP_SELF .'" METHOD="POST">
	<INPUT TYPE="hidden" NAME="func" VALUE="adduser">
	<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="'. $group_id .'">
	<TABLE WIDTH="100%" BORDER="0">
	<TR><TD><B>Unix Name:</B></TD><TD><INPUT TYPE="TEXT" NAME="form_unix_name" SIZE=10 VALUE=""></TD></TR>
	<TR><TD COLSPAN="2" ALIGN="CENTER"><INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="Add User"></TD></TR></FORM>
	</TABLE>

	<HR NoShade SIZE="1">
	<div align="center">
	<A href="/project/admin/userperms.php?group_id='. $group_id.'">[Edit Member Permissions]</A>
	</div>
	</TD></TR>
';
 
$HTML->box1_bottom();


echo '</TD></TR>

<TR valign=top><TD width=50%>';

/*
	Tool admin pages
*/

$HTML->box1_top('Tool Admin');

echo '
<BR>
<A HREF="/docman/admin/?group_id='.$group_id.'">DocManager Admin</A><BR>
<A HREF="/bugs/admin/?group_id='.$group_id.'">Bug Admin</A><BR>
<A HREF="/patch/admin/?group_id='.$group_id.'">Patch Admin</A><BR>
<A HREF="/mail/admin/?group_id='.$group_id.'">Mail Admin</A><BR>
<A HREF="/news/admin/?group_id='.$group_id.'">News Admin</A><BR>
<A HREF="/pm/admin/?group_id='.$group_id.'">Task Manager Admin</A><BR>
<A HREF="/support/admin/?group_id='.$group_id.'">Support Manager Admin</A><BR>
<A HREF="/forum/admin/?group_id='.$group_id.'">Forum Admin</A><BR>
';

$HTML->box1_bottom(); 




echo '</TD>

<TD>&nbsp;</TD>

<TD width=50%>';

/*
	Show filerelease info
*/

$HTML->box1_top("File Releases"); ?>
	&nbsp;<BR>
	<CENTER>
	<A href="editpackages.php?group_id=<?php print $group_id; ?>"><B>[Edit/Add File Releases]</B></A>
	</CENTER>

	<HR>
	<B>Packages:</B> <A href="/docman/display_doc.php?docid=780&group_id=1">Documentation</A> (Very Important!)

	<P><?php

	$res_module = db_query("SELECT * FROM frs_package WHERE group_id=$group_id");
	while ($row_module = db_fetch_array($res_module)) {
		print "$row_module[name]<BR>";
	}

	echo $HTML->box1_bottom(); ?>
</TD>
</TR>
</TABLE>

<?php
project_admin_footer(array());

?>
