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
 * @author		Drew Streib <dtype@valinux.com>
 */

require_once $gfwww.'include/vote_function.php';

$HTML->header(array('title'=>_('Developer Profile')));

?>

<table width="100%" cellpadding="2" cellspacing="2" border="0">
<tr valign="top">
	<td width="50%">
		<?php echo $HTML->boxTop(_('Personal Information')); ?>
<tr>
	<td>
		<?php echo _('User Id') ?>
	</td>
	<td>
		<strong>
<?php
	if (session_loggedin() && user_ismember(1)) {
		echo util_make_link ('/admin/useredit.php?user_id='.$user_id,$user_id);
	} else {
		echo $user_id;
	}
?>
		</strong><?php if($GLOBALS['sys_use_people']) { ?>( <?php echo util_make_link ('/people/viewprofile.php?user_id='.$user_id,'<strong>'._('Skills Profile').'</strong>'); ?> )<?php } ?>
	</td>
</tr>

<tr valign="top">
	<td><?php echo _('Login name') ?></td>
	<td><strong><?php print $user->getUnixName(); ?></strong></td>
</tr>

<tr valign="top">
	<td><?php echo _('Real name') ?> </td>
	<td><strong><?php print $user->getTitle() .' '. $user->getRealName(); ?></strong></td>
</tr>

<?php if(!isset($GLOBALS['sys_show_contact_info']) || $GLOBALS['sys_show_contact_info']) { ?>
<tr valign="top">
	<td><?php echo _('Your Email Address') ?>: </td>
	<td>
	<strong><?php echo util_make_link ('/sendmessage.php?touser='.$user_id, str_replace('@',' @nospam@ ',$user->getEmail())); ?></strong>
	</td>
</tr>
<?php if ($user->getJabberAddress()) { ?>
<tr valign="top">
	<td><?php echo _('Jabber Address') ?></td>
	<td>
	<a href="jabber:<?php print $user->getJabberAddress(); ?>"><strong><?php print $user->getJabberAddress(); ?></strong></a>
	</td>
</tr>
<?php } ?>

<?php if ($user->getAddress() || $user->getAddress2()) { ?>
<tr valign="top">
	<td><?php echo _('Address:'); ?></td>
	<td><?php echo $user->getAddress().'<br/>'.$user->getAddress2(); ?></td>
</tr>
<?php } ?>

<?php if ($user->getPhone()) { ?>
<tr valign="top">
	<td><?php echo _('Phone:'); ?></td>
	<td><?php echo $user->getPhone(); ?></td>
</tr>
<?php } ?>

<?php if ($user->getFax()) { ?>
<tr valign="top">
	<td><?php echo _('FAX:'); ?></td>
	<td><?php echo $user->getFax(); ?></td>
</tr>
<?php } ?>
<?php } ?>

<tr>
	<td>
	<?php echo _('Site Member Since') ?>
	</td>
	<td><strong><?php print date(_('Y-m-d H:i'), $user->getAddDate()); ?></strong>
	<?php

	if ($sys_use_ratings) {
		echo $HTML->boxMiddle(_('Peer Rating'),false,false);
		if ($user->usesRatings()) {
			echo vote_show_user_rating($user_id);
		} else {
			echo _('User chose not to participate in peer rating');
		}
	}

	echo $HTML->boxMiddle(_('Diary and Notes'));
 
	/*

		Get their diary information

	*/

	$res=db_query("SELECT count(*) from user_diary ".
		"WHERE user_id='". $user_id ."' AND is_public=1");
	echo _('Diary/Note entries:').' '.db_result($res,0,0).'
	<p/>'.util_make_link ('/developer/diary.php?diary_user='.$user_id,_('View Diary & Notes')).'</p>
	<p/>';
	echo util_make_url ('/developer/monitor.php?diary_user='.$user_id,
			    html_image("ic/check.png",'15','13',array(),0) ._('Monitor this Diary')
		) ;
	echo '</p>';
	$hookparams['user_id'] = $user_id;
	plugin_hook("user_personal_links",$hookparams);
	
	?>
	</td>
</tr>

<tr>
	<td colspan="2">
	<h4><?php echo _('Project Info') ?></h4>
	<p/>
<?php
	// now get listing of groups for that user
	$res_cat = db_query("SELECT groups.group_name, 
	 groups.unix_group_name, 
	 groups.group_id, 
	 user_group.admin_flags,
         role.role_name
	 FROM 
	 groups,user_group,role WHERE user_group.user_id='$user_id' AND  user_group.role_id=role.role_id AND
	 groups.group_id=user_group.group_id AND groups.is_public='1' AND groups.status='A'");

// see if there were any groups
if (db_numrows($res_cat) < 1) {
	?>
	<p/><?php echo _('This developer is not a member of any projects.') ?><p/>
	<?php
} else { // endif no groups
	print "<p/>"._('This developer is a member of the following groups:')."<br />&nbsp;";
	while ($row_cat = db_fetch_array($res_cat)) {
			print ('<br />' . util_make_link_g ($row_cat['unix_group_name'],$row_cat['group_id'],htmlentities($row_cat['group_name'])).' ('.htmlentities($row_cat['role_name']).')');
	}
	print '</ul><p/>';
} // end if groups
?>
	</td>
</tr>

	<?php echo $HTML->boxBottom(); ?>

	</td>


	<td width="80%">

<?php 
$me = session_get_user(); 
if ($sys_use_ratings) {
if ($user->usesRatings() && (!$me || $me->usesRatings())) { 

printf(_('<p>If you are familiar with this user, please take a moment to rate him/her on the following criteria. Keep in mind, that your rating will be visible to the user and others.</p><p>The %1$s Peer Rating system is based on concepts from <a href="http://www.advogato.com/">Advogato.</a> The system has been re-implemented and expanded in a few ways.</p>'), $GLOBALS['sys_name']);
?>

	<div align="center">
        <?php echo vote_show_user_rate_box ($user_id, $me?$me->getID():0); ?>
	</div>

		  <?php printf(_('<p>The Peer rating box shows all rating averages (and response levels) for each individual criteria. Due to the math and processing required to do otherwise, these numbers incoporate responses from both "trusted" and "non-trusted" users.</p><ul><li>The "Sitewide Rank" field shows the user\'s rank compared to all ranked %1$s users.</li><li>The "Aggregate Score" shows an average, weighted overall score, based on trusted-responses only.</li><li>The "Personal Importance" field shows the weight that users ratings of other developers will be given (between 1 and 1.5) -- higher rated user\'s responses are given more weight.</li></ul><p><i>If you would like to opt-out from peer rating system (this will affect your ability to both rate and be rated), refer to <a href="%2$s">your account maintenance page</a>. If you choose not to participate, your ratings of other users will be permanently deleted and the \'Peer Rating\' box will disappear from your user page. </i></p>'),
			       $GLOBALS['sys_name'],
			       util_make_url ("/account/"));

} else if ($me && !$me->usesRatings()) { ?>
<p/>
<em>
		<?php printf (_('You opted-out from peer rating system, otherwise you would have a chance to rate the user. Refer to <a href="%1$s">your account maintenance page</a> for more information.'),
			      util_make_url ("/account")); ?>
</em>
<p/>
<?php }
      } ?>
	</td>
</tr>
</table>

<p/>

<?php

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
