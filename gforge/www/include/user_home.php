<?php
/**
 * user_home.php
 * Developer Info Page
 * Assumes $user object for displayed user is present
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: 
 * @author		Drew Streib <dtype@valinux.com>
 */

require_once('vote_function.php');

$HTML->header(array('title'=>'Developer Profile','pagename'=>'users'));

?>

<P>
<TABLE width=100% cellpadding=2 cellspacing=2 border=0><TR valign=top>
<TD width=50%>

<?php echo $HTML->box1_top("Personal Information"); ?>
<TR>
	<TD>User ID: </TD>
	<TD><B><?php print $user_id; ?></B> ( <A HREF="/people/viewprofile.php?user_id=<?php print $user_id; ?>"><B>Skills Profile</B></A> )</TD>
</TR>

<TR valign=top>
	<TD>Login Name: </TD>
	<TD><B><?php print $user->getUnixName(); ?></B></TD>
</TR>

<TR valign=top>
	<TD>Real Name: </TD>
	<TD><B><?php print $user->getRealName(); ?></B></TD>
</TR>

<TR valign=top>
	<TD>Email Addr: </TD>
	<TD>
	<B><A HREF="/sendmessage.php?touser=<?php print $user_id; 
		?>"><?php print $user->getUnixName(); ?> at users.<?php print $GLOBALS['sys_default_domain']; ?></A></B>
	</TD>
</TR>

<TR>
	<TD>
	Site Member Since: 
	</TD>
	<TD><B><?php print date($sys_datefmt, $user->getAddDate()); ?></B>
	<?php

	echo $HTML->boxMiddle('Peer Rating',false,false);

	if ($user->usesRatings()) {
		echo vote_show_user_rating($user_id);
	} else {
		echo 'User chose not to participate in peer rating';
	}

	echo $HTML->boxMiddle('Diary And Notes');
 
	/*

		Get their diary information

	*/

	$res=db_query("SELECT count(*) from user_diary ".
		"WHERE user_id='". $user_id ."' AND is_public=1");
	echo 'Diary/Note Entries: '.db_result($res,0,0).'
	<P>
	<A HREF="/developer/diary.php?diary_user='. $user_id .'">View Diary & Notes</A>
	<P>
	<A HREF="/developer/monitor.php?diary_user='. $user_id .'">'. html_image("images/ic/check.png",'15','13',array(),0) .'Monitor This Diary</A>';

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
	<p>This developer is not a member of any projects.
	<?php
} else { // endif no groups
	print "<p>This developer is a member of the following groups:<BR>&nbsp;";
	while ($row_cat = db_fetch_array($res_cat)) {
		print ("<BR>" . "<A href=\"/projects/$row_cat[unix_group_name]/\">$row_cat[group_name]</A>\n");
	}
	print "</ul>";
} // end if groups

echo $HTML->boxBottom(); ?>

</TD>


<TD>

<?php 
$me = session_get_user(); 
if ($user->usesRatings() && (!$me || $me->usesRatings())) { 

echo $Language->getText('users','peerinfo1', $GLOBALS[sys_name]);
?>

	<CENTER>
        <?php echo vote_show_user_rate_box ($user_id, $me?$me->getID():0); ?>
	</CENTER>

<?php echo $Language->getText('users','peerinfo2', $GLOBALS[sys_name]);

} else if ($me && !$me->usesRatings()) { ?>
<p>
<i>
<?php echo $Language->getText('users','optout'); ?>
</i>
</p>
<?php } ?>
</TD>


</TR>
</TABLE>

<TABLE width=100% cellpadding=2 cellspacing=2 border=0><TR valign=top>
<TR><TD COLSPAN="2">

<?php 

if (session_loggedin()) {

	$u =& session_get_user();

	?>
	&nbsp;
	<P>
	<H3>Send a Message to <?php echo $user->getRealName(); ?></H3>
	<P>
	<FORM ACTION="/sendmessage.php" METHOD="POST">
	<INPUT TYPE="HIDDEN" NAME="touser" VALUE="<?php echo $user_id; ?>">

	<B>Your Email Address:</B><BR>
	<B><?php echo $u->getUnixName().'@users.'.$GLOBALS['sys_default_domain']; ?></B>
	<INPUT TYPE="HIDDEN" NAME="email" VALUE="<?php echo $u->getUnixName().'@users.'.$GLOBALS['sys_default_domain']; ?>">
	<P>
	<B>Your Name:</B><BR>
	<B><?php echo $u->getRealName(); ?></B>
	<INPUT TYPE="HIDDEN" NAME="name" VALUE="<?php echo $u->getRealName(); ?>">
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
