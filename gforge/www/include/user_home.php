<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

/*

	Developer Info Page
	Written by dtype Oct 1999


	Assumes $user object for displayed user is present


*/

require ('vote_function.php');

$HTML->header(array('title'=>'Developer Profile'));

?>

<H3>Developer Profile</H3>
<P>
<TABLE width=100% cellpadding=2 cellspacing=2 border=0><TR valign=top>
<TD width=50%>

<?php echo $HTML->box1_top("Personal Information",true,false,false); ?>
<TR>
	<TD><?php echo $Language->USER_ID; ?>: </TD>
	<TD><B><?php print $user_id; ?></B> ( <A HREF="/people/viewprofile.php?user_id=<?php print $user_id; ?>"><B>Skills Profile</B></A> )</TD>
</TR>

<TR valign=top>
	<TD><?php echo $Language->LOGIN_NAME; ?>: </TD>
	<TD><B><?php print $user->getUnixName(); ?></B></TD>
</TR>

<TR valign=top>
	<TD><?php echo $Language->REALNAME; ?>: </TD>
	<TD><B><?php print $user->getRealName(); ?></B></TD>
</TR>

<TR valign=top>
	<TD><?php echo $Language->EMAILADDR; ?>: </TD>
	<TD>
	<B><A HREF="/sendmessage.php?touser=<?php print $user_id; 
		?>"><?php print $user->getUnixName(); ?> at users.<?php print $GLOBALS['sys_default_domain']; ?></A></B>
	</TD>
</TR>

<TR>
	<TD>
	<?php echo $Language->MEMBER_SINCE; ?>: 
	</TD>
	<TD><B><?php print date($sys_datefmt, $user->getAddDate()); ?></B>
	<?php

	echo $HTML->box1_middle('Peer Rating',false,false);

	echo vote_show_user_rating($user_id);

	echo $HTML->box1_middle($Language->DIARY);
 
	/*

		Get their diary information

	*/

	$res=db_query("SELECT count(*) from user_diary ".
		"WHERE user_id='". $user_id ."' AND is_public=1");
	echo 'Diary/Note Entries: '.db_result($res,0,0).'
	<P>
	<A HREF="/developer/diary.php?user_id='. $user_id .'">View Diary & Notes</A>
	<P>
	<A HREF="/developer/monitor.php?user_id='. $user_id .'">'. html_image("/images/ic/check.png",'15','13',array(),0) .'Monitor This Diary</A>';

	?>
</TD></TR>

<TR><TD COLSPAN=2>
	<H4>Project Info</H4>
	<P>
<?php
	// now get listing of groups for that user
	$res_cat = db_query("SELECT groups.group_name, "
	. "groups.unix_group_name, "
	. "groups.group_id, "
	. "user_group.admin_flags, "
	. "user_group.bug_flags FROM "
	. "groups,user_group WHERE user_group.user_id='$user_id' AND "
	// We don't need to block out foundries from displaying.
	//. "groups.group_id=user_group.group_id AND groups.is_public='1' AND groups.status='A' AND groups.type='1'");
	. "groups.group_id=user_group.group_id AND groups.is_public='1' AND groups.status='A'");

// see if there were any groups
if (db_numrows($res_cat) < 1) {
	?>
	<p>
	<?php echo $Language->MY_no_projects;
} else { // endif no groups
	print "<p>This developer is a member of the following groups:<BR>&nbsp;";
	while ($row_cat = db_fetch_array($res_cat)) {
		print ("<BR>" . "<A href=\"/projects/$row_cat[unix_group_name]/\">$row_cat[group_name]</A>\n");
	}
	print "</ul>";
} // end if groups

$HTML->box1_bottom(); ?>

</TD><TD>
<?php echo $Language->USERS_PEERINFO1; ?>
	<CENTER>
        <?php echo vote_show_user_rate_box ($user_id); ?>
	</CENTER>
<P>
<?php echo $Language->USERS_PEERINFO2; ?>
</ul>
</TD></TR>
</TABLE>

<TABLE width=100% cellpadding=2 cellspacing=2 border=0><TR valign=top>
<TR><TD COLSPAN="2">

<?php 

if (user_isloggedin()) {

	?>
	&nbsp;
	<P>
	<H3>Send a Message to <?php echo $user->getRealName(); ?></H3>
	<P>
	<FORM ACTION="/sendmessage.php" METHOD="POST">
	<INPUT TYPE="HIDDEN" NAME="touser" VALUE="<?php echo $user_id; ?>">

	<B>Your Email Address:</B><BR>
	<B><?php echo user_getname().'@users.'.$GLOBALS['sys_default_domain']; ?></B>
	<INPUT TYPE="HIDDEN" NAME="email" VALUE="<?php echo user_getname().'@users.'.$GLOBALS['sys_default_domain']; ?>">
	<P>
	<B>Your Name:</B><BR>
	<B><?php 

	$my_name=user_getrealname(user_getid());

	echo $my_name; ?></B>
	<INPUT TYPE="HIDDEN" NAME="name" VALUE="<?php echo $my_name; ?>">
	<P>
	<B>Subject:</B><BR>
	<INPUT TYPE="TEXT" NAME="subject" SIZE="30" MAXLENGTH="40" VALUE="">
	<P>
	<B>Message:</B><BR>
	<TEXTAREA NAME="body" ROWS="15" COLS="50" WRAP="HARD"></TEXTAREA>
	<P>
	<CENTER>
	<INPUT TYPE="SUBMIT" NAME="send_mail" VALUE="Send Message">
	</CENTER>
	</FORM>
	<?php

} else {

	echo '<H3>You Could Send a Message if you were logged in</H3>';

}

?>

</TD></TR>
</TABLE>

<?php

$HTML->footer(array());

?>
