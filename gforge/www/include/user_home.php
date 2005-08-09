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

$HTML->header(array('title'=>$Language->getText('user_home','title'),'pagename'=>'users'));

?>

<p>
<table width="100%" cellpadding="2" cellspacing="2" border="0"><tr valign="top">
<td width="50%">

<?php echo $HTML->boxTop($Language->getText('user_home','personal_information')); ?>
<tr>
	<td><?php echo $Language->getText('user_home','user_id') ?> </td>
	<td><strong><?php print $user_id; ?></strong> <?php if($GLOBALS['sys_use_people']) { ?>( <a href="/people/viewprofile.php?user_id=<?php print $user_id; ?>"><strong><?php echo $Language->getText('user_home','skills_profile') ?></strong></a> )<?php } ?></td>
</tr>

<tr valign="top">
	<td><?php echo $Language->getText('user_home','login_name') ?> </td>
	<td><strong><?php print $user->getUnixName(); ?></strong></td>
</tr>

<tr valign="top">
	<td><?php echo $Language->getText('user_home','real_name') ?> </td>
	<td><strong><?php print $user->getTitle() .' '. $user->getRealName(); ?></strong></td>
</tr>

<?php if(!isset($GLOBALS['sys_show_contact_info']) || $GLOBALS['sys_show_contact_info']) { ?>
<tr valign="top">
	<td><?php echo $Language->getText('user_home','email') ?>: </td>
	<td>
	<strong><a href="/sendmessage.php?touser=<?php print $user_id; 
		?>"><?php print str_replace('@',' @nospam@ ',$user->getEmail()); ?></a></strong>
	</td>
</tr>
<?php if ($user->getJabberAddress()) { ?>
<tr valign="top">
	<td><?php echo $Language->getText('user_home','jabber_address') ?></td>
	<td>
	<a href="jabber:<?php print $user->getJabberAddress(); ?>"><strong><?php print $user->getJabberAddress(); ?></strong></a>
	</td>
</tr>
<?php } ?>

<tr valign="top">
	<td><?php echo $Language->getText('account_options','address'); ?></td>
	<td><?php echo $user->getAddress(); ?></td>
</tr>

<tr valign="top">
	<td><?php echo $Language->getText('account_options','phone'); ?></td>
	<td><?php echo $user->getPhone(); ?></td>
</tr>

<tr valign="top">
	<td><?php echo $Language->getText('account_options','fax'); ?></td>
	<td><?php echo $user->getFax(); ?></td>
</tr>
<?php } ?>

<tr>
	<td>
	<?php echo $Language->getText('user_home','site_member_since') ?>
	</td>
	<td><strong><?php print date($sys_datefmt, $user->getAddDate()); ?></strong>
	<?php

	echo $HTML->boxMiddle($Language->getText('user_home','peer_rating'),false,false);

	if ($user->usesRatings()) {
		echo vote_show_user_rating($user_id);
	} else {
		echo $Language->getText('user_home','peer_rating_disabled');
	}

	echo $HTML->boxMiddle($Language->getText('user_home','diary_notes'));
 
	/*

		Get their diary information

	*/

	$res=db_query("SELECT count(*) from user_diary ".
		"WHERE user_id='". $user_id ."' AND is_public=1");
	echo $Language->getText('user_home','diary_notes_entries').' '.db_result($res,0,0).'
	<p>
	<a href="/developer/diary.php?diary_user='. $user_id .'">'.$Language->getText('user_home','diary_notes_view').'</a><?p>
	<p>
	<a href="/developer/monitor.php?diary_user='. $user_id .'">'. html_image("ic/check.png",'15','13',array(),0) .$Language->getText('user_home','diary_notes_monitor').'</a></p>';
	$hookparams['user_id'] = $user_id;
	plugin_hook("user_personal_links",$hookparams);
	
	?>
</td></tr>

<tr><td colspan="2">
	<h4><?php echo $Language->getText('user_home','project_info') ?></h4>
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
	<p><?php echo $Language->getText('user_home','no_projects') ?></p>
	<?php
} else { // endif no groups
	print "<p>".$Language->getText('user_home','member_of')."<br />&nbsp;";
	while ($row_cat = db_fetch_array($res_cat)) {
		print ("<br />" . "<a href=\"/projects/".$row_cat['unix_group_name']."/\">".$row_cat['group_name']."</a>\n");
	}
	print "</ul></p>";
} // end if groups

echo $HTML->boxBottom(); ?>

</td>


<td>

<?php 
$me = session_get_user(); 
if ($user->usesRatings() && (!$me || $me->usesRatings())) { 

echo $Language->getText('users','peerinfo1', $GLOBALS['sys_name']);
?>

	<div align="center">
        <?php echo vote_show_user_rate_box ($user_id, $me?$me->getID():0); ?>
	</div>

<?php echo $Language->getText('users','peerinfo2', $GLOBALS['sys_name']);

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

</td></tr>
</table></p>

<?php

$HTML->footer(array());

?>
