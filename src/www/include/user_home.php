<?php
/**
 * Developer Info Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Assumes $user object for displayed user is present
 */

require_once $gfwww.'include/vote_function.php';

$title = _('User Profile');
$HTML->header(array('title'=>$title));

echo $HTML->boxTop(_('Personal Information'), _('Personal Information')); ?>

<div about="" typeof="sioc:UserAccount">

<table width="100%" cellpadding="2" cellspacing="2" border="0">
<tr>
	<td width="150"><?php plugin_hook("user_logo", array('user_id' => $user_id, 'size' => 'l')); ?></td>
	<td>

<table class="my-layout-table" id="user-profile-personal-info">
<tr>
	<td>
		<?php echo _('User Id:') ?>
	</td>
	<td>
		<strong>
<?php
//print '<div property ="foaf:member" content="fusionforge:ForgeCommunity">';
//echo '</div>';
// description as a FusionForge Community member
//print '<div property ="dc:Identifier" content="'.$user_id.'">';

	if (session_loggedin() && user_ismember(1)) {
		echo util_make_link('/admin/useredit.php?user_id='.$user_id, $user_id);
	} else {
		echo $user_id;
//echo '</div>';
	}
?>
		</strong><?php if(forge_get_config('use_people')) { ?>( <?php echo util_make_link ('/people/viewprofile.php?user_id='.$user_id,'<strong>'._('Skills Profile').'</strong>'); ?> )<?php } ?>
	</td>
</tr>

<tr>
	<td><?php echo _('Login name:') ?></td>
	<td><strong><span property="sioc:name"><?php
		print $user->getUnixName();
		?></span></strong></td>
</tr>

<tr>
	<td><?php echo _('Real Name:') ?> </td>
	<td>
		<div rev="foaf:account">
			<div about="#me" typeof="foaf:Person">
				<strong><span property="foaf:name"><?php
				$user_title = $user->getTitle();
				print($user_title ? $user_title .' ' :'').$user->getRealName();
				?></span></strong>
			</div>
		</div>
	</td>
</tr>

<?php if(!isset($GLOBALS['sys_show_contact_info']) || $GLOBALS['sys_show_contact_info']) { ?>
<tr>
	<td><?php echo _('Email Address:') ?>: </td>
	<td>
	<strong><?php
		$user_mail=$user->getEmail();
		$user_mailsha1=$user->getSha1Email();
		// Removed for privacy reasons
		//print '<span property="sioc:email" content="'. $user_mail .'">';
		print '<span property="sioc:email_sha1" content="'. $user_mailsha1 .'">';
		echo util_make_link ('/sendmessage.php?touser='.$user_id, str_replace('@',' @nospam@ ',$user_mail));
		echo '</span>';
	?></strong>
	</td>
</tr>
<?php if ($user->getJabberAddress()) { ?>
<tr>
	<td><?php echo _('Jabber Address') ?></td>
	<td>
	<a href="jabber:<?php print $user->getJabberAddress(); ?>"><strong><?php print $user->getJabberAddress(); ?></strong></a>
	</td>
</tr>
<?php } ?>

<?php if ($user->getAddress() || $user->getAddress2()) { ?>
<tr>
	<td><?php echo _('Address:'); ?></td>
	<td><?php echo $user->getAddress().'<br/>'.$user->getAddress2(); ?></td>
</tr>
<?php } ?>

<?php if ($user->getPhone()) { ?>
<tr>
	<td><?php echo _('Phone:'); ?></td>
	<td><?php
//print '<div property="foaf:phone" content="'.$user->getPhone().'">';
echo $user->getPhone();
//echo '</div>';
?></td>
</tr>
<?php } ?>

<?php if ($user->getFax()) { ?>
<tr>
	<td><?php echo _('FAX:'); ?></td>
	<td><?php echo $user->getFax(); ?></td>
</tr>
<?php } ?>
<?php } ?>

<tr>
	<td>
	<?php echo _('Site Member Since:') ?>
	</td>
	<td><strong><?php print date(_('Y-m-d H:i'), $user->getAddDate()); ?></strong>
    </td>
</tr>
</table>
</td>
</tr>
</table>

	<?php
	if (forge_get_config('use_ratings')) {
		echo $HTML->boxMiddle(_('Peer Rating'), _('Peer Rating'));
        echo '<table class="my-layout-table" id="user-profile-rating">';
		if ($user->usesRatings()) {
			echo vote_show_user_rating($user_id);
		} else {
            echo '<tr><td colspan="2">';
			echo _('User chose not to participate in peer rating');
            echo '</td></tr>';
		}
        echo '</table><!-- id="user-profile-rating" -->';
	}

if (forge_get_config('use_diary')) {
		echo $HTML->boxMiddle(_('Diary and Notes'), _('Diary and Notes'));

		/*
			Get their diary information
		*/

		$res = db_query_params('SELECT count(*) from user_diary WHERE user_id=$1 AND is_public=1',
					array($user_id));
		echo _('Diary/Note entries:').' '.db_result($res, 0, 0).'
		<p>';
		//.'<span rel="foaf:weblog">'
		echo util_make_link('/developer/diary.php?diary_user='.$user_id,htmlentities(_('View Diary & Notes')));
		//.'</span>'.
		echo '</p>
		<p>';
		echo util_make_link('/developer/monitor.php?diary_user='.$user_id,
						html_image("ic/check.png",'15','13',array(),0) ._('Monitor this Diary')
			) ;
		echo '</p>';
		$hookparams['user_id'] = $user_id;
		plugin_hook("user_personal_links", $hookparams);
	}
	?>


<?php

    echo $HTML->boxMiddle(_('Project Info'), _('Project Info'));

$projects = $user->getGroups () ;
sortProjectList ($projects) ;
$roles = RBACEngine::getInstance()->getAvailableRolesForUser ($user) ;
sortRoleList ($roles) ;

// see if there were any groups
echo '<div>'."\n";
if (count ($projects) < 1) {
	?>
	<p><?php echo _('This developer is not a member of any projects.') ?></p>
	<?php
} else { // endif no groups
	print "<p>"._('This developer is a member of the following projects:')."</p>\n";

	foreach ($projects as $p) {
		$project_link = util_make_link_g ($p->getUnixName(),$p->getID(),$p->getPublicName());
		$project_uri = util_make_url_g ($p->getUnixName(),$p->getID());
		// sioc:UserGroups for all members of a project are named after /projects/A_PROJECT/members/
		$usergroup_uri = $project_uri .'members/';


		print '<div rel="sioc:member_of">'."\n"
			.'<div about="'. $usergroup_uri .'" typeof="sioc:UserGroup">'."\n"
			.'<div rel="sioc:usergroup_of">'."\n"
			.'<div about="'. $project_uri .'" typeof="sioc:Space">';
		$role_names = array () ;
		$sioc_has_function_close = "";
		foreach ($roles as $r) {
			if ($r instanceof RoleExplicit
			    && $r->getHomeProject() != NULL
			    && $r->getHomeProject()->getID() == $p->getID()) {
				$role_names[] = $r->getName() ;
				print '<div property="sioc:has_function" content= "'.$r->getName().'">';
				$sioc_has_function_close .= "</div>";
			}
		}

		print ('<br />' . $project_link .' ('.htmlspecialchars (implode (', ', $role_names)).')');
		print "\n";

		if (forge_check_perm_for_user ($user, 'project_admin', $p->getID())) {
			print '<span rev="doap:maintainer" resource="#me"></span>';
		}
		else {
			print '<span rev="doap:developer" resource="#me"></span>';
		}

		echo $sioc_has_function_close."\n";  // sioc:has_function
		echo "</div>\n";  // sioc:Space .../projects/A_PROJECT/
		echo "</div>\n"; // sioc:usergroup_of
		echo "</div>\n";  // sioc:UserGroup .../projects/A_PROJECT/members
		echo "</div>\n"; // sioc:member_of
	}
} // end if groups
echo "</div>\n"; // prefixes

echo "</div>\n"; // end of about=""

$me = session_get_user();
if (forge_get_config('use_ratings')) {
if ($user->usesRatings() && (!$me || $me->usesRatings())) {

print "<p>";
print _('If you are familiar with this user, please take a moment to rate him/her on the following criteria. Keep in mind, that your rating will be visible to the user and others.');
print "</p>";

print "<p>";
printf(_('The %s Peer Rating system is based on concepts from <a href="http://www.advogato.com/">Advogato.</a> The system has been re-implemented and expanded in a few ways.'), forge_get_config ('forge_name'));
print "</p>";
?>

	<div class="align-center">
        <?php echo vote_show_user_rate_box ($user_id, $me?$me->getID():0); ?>
	</div>

		  <?php printf(_('<p>The Peer rating box shows all rating averages (and response levels) for each individual criteria. Due to the math and processing required to do otherwise, these numbers incoporate responses from both "trusted" and "non-trusted" users.</p><ul><li>The "Sitewide Rank" field shows the user\'s rank compared to all ranked %1$s users.</li><li>The "Aggregate Score" shows an average, weighted overall score, based on trusted-responses only.</li><li>The "Personal Importance" field shows the weight that users ratings of other developers will be given (between 1 and 1.5) -- higher rated user\'s responses are given more weight.</li></ul><p>If you would like to opt-out from peer rating system (this will affect your ability to both rate and be rated), refer to <a href="%2$s">your account maintenance page</a>. If you choose not to participate, your ratings of other users will be permanently deleted and the \'Peer Rating\' box will disappear from your user page.</p>'),
			       forge_get_config ('forge_name'),
			       util_make_url ("/account/"));

} else if ($me && !$me->usesRatings()) { ?>
<p>
<em>
		<?php printf (_('You opted-out from peer rating system, otherwise you would have a chance to rate the user. Refer to <a href="%1$s">your account maintenance page</a> for more information.'),
			      util_make_url ("/account")); ?>
</em>
</p>
<?php }
      }

echo $HTML->boxBottom();

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
