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

<p>
<table width="100%" cellpadding="2" cellspacing="2" border="0"><tr valign="top">
<td width="50%">

<?php echo $HTML->boxTop("Personal Information"); ?>
<tr>
	<td>User ID: </td>
	<td><strong><?php print $user_id; ?></strong> ( <a href="/people/viewprofile.php?user_id=<?php print $user_id; ?>"><strong>Skills Profile</strong></a> )</td>
</tr>

<tr valign="top">
	<td>Login Name: </td>
	<td><strong><?php print $user->getUnixName(); ?></strong></td>
</tr>

<tr valign="top">
	<td>Real Name: </td>
	<td><strong><?php print $user->getRealName(); ?></strong></td>
</tr>

<tr valign="top">
	<td>Email Addr: </td>
	<td>
	<strong><a href="/sendmessage.php?touser=<?php print $user_id; 
		?>"><?php print $user->getUnixName(); ?> at users.<?php print $GLOBALS['sys_default_domain']; ?></a></strong>
	</td>
</tr>
<?php if ($user->getJabberAddress()) { ?>
<tr valign="top">
	<td>Jabber Addr: </td>
	<td>
	<a href="jabber:<?php print $user->getJabberAddress().'"><strong>'.$user->getJabberAddress().'</strong></a>'; ?>
	</td>
</tr>
<?php } ?>
<tr>
	<td>
	Site Member Since: 
	</td>
	<td><strong><?php print date($sys_datefmt, $user->getAddDate()); ?></strong>
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
	<p>
	<a href="/developer/diary.php?diary_user='. $user_id .'">View Diary & Notes</a><?p>
	<p>
	<a href="/developer/monitor.php?diary_user='. $user_id .'">'. html_image("ic/check.png",'15','13',array(),0) .'Monitor This Diary</a></p>';

	?>
</td></tr>

<tr><td colspan="2">
	<h4>Project Info</h4>
	<p>
<?php
	// now get listing of groups for that user
	$res_cat = db_query("SELECT groups.group_name, 
	 groups.unix_group_name, 
	 groups.group_id, 
	 user_group.admin_flags 
	 FROM 
	 groups,user_group WHERE user_group.user_id='$user_id' AND 
	 groups.group_id=user_group.group_id AND groups.is_public='1' AND groups.status='A'");

// see if there were any groups
if (db_numrows($res_cat) < 1) {
	?>
	<p>This developer is not a member of any projects.</p>
	<?php
} else { // endif no groups
	print "<p>This developer is a member of the following groups:<br />&nbsp;";
	while ($row_cat = db_fetch_array($res_cat)) {
		print ("<br />" . "<a href=\"/projects/$row_cat[unix_group_name]/\">$row_cat[group_name]</a>\n");
	}
	print "</ul></p>";
} // end if groups

echo $HTML->boxBottom(); ?>

</td>


<td>

<?php 
$me = session_get_user(); 
if ($user->usesRatings() && (!$me || $me->usesRatings())) { 

echo $Language->getText('users','peerinfo1', $GLOBALS[sys_name]);
?>

	<div align="center">
        <?php echo vote_show_user_rate_box ($user_id, $me?$me->getID():0); ?>
	</div>

<?php echo $Language->getText('users','peerinfo2', $GLOBALS[sys_name]);

} else if ($me && !$me->usesRatings()) { ?>
<p>
<em>
<?php echo $Language->getText('users','optout'); ?>
</em>
</p>
<?php } ?>
</td>


</tr>
</table>
</p>
<p>
<table width="100%" cellpadding="2" cellspacing="2" border="0"><tr valign="top">
<tr><td colspan="2">

<?php

if (session_loggedin()) {

	$u =& session_get_user();

	?>
	&nbsp;
	<p>&nbsp;</p>
	<h3>Send a Message to <?php echo $user->getRealName(); ?></h3>
	<p>
	<form action="/sendmessage.php" method="post">
	<input type="hidden" name="touser" value="<?php echo $user_id; ?>" />

	<strong>Your Email Address:</strong><br />
	<strong><?php echo $u->getUnixName().'@users.'.$GLOBALS['sys_default_domain']; ?></strong>
	<input type="hidden" name="email" value="<?php echo $u->getUnixName().'@users.'.$GLOBALS['sys_default_domain']; ?>" />
	<p>
	<strong>Your Name:</strong><br />
	<strong><?php echo $u->getRealName(); ?></strong>
	<input type="hidden" name="name" value="<?php echo $u->getRealName(); ?>" /></p>
	<p>
	<strong>Subject:</strong><br />
	<input type="TEXT" name="subject" size="30" maxlength="40" value="" /></p>
	<p>
	<strong>Message:</strong><br />
	<textarea name="body" rows="15" cols="50" wrap="hard"></textarea></p>
	<p>
	<div align="center">
	<input type="submit" name="send_mail" value="Send Message" />
	</div></p>
	</form></p>
	<?php

} else {

	echo '<h3>You Could Send a Message if you were logged in</h3>';

}

?>

</td></tr>
</table></p>

<?php

$HTML->footer(array());

?>
