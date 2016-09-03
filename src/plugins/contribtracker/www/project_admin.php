<?php
/**
 * ContribTracker plugin
 *
 * Copyright 2009, Roland Mas
 * Copyright 2016, Franck Villaume - TrivialDev
 * http://fusionforge.org/
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';

global $HTML;

$plugin = plugin_get_object('contribtracker');

$group_id = getIntFromRequest('group_id') ;
session_require_perm('project_admin', $group_id) ;
$group = group_get_object($group_id) ;

$params = array () ;
$params['toptab'] = 'contribtracker' ;
$params['group'] = $group_id ;
$params['title'] = sprintf (_('Contribution tracker for project %s'),
			    htmlspecialchars ($group->getPublicName()));
$params['pagename'] = 'contribtracker' ;
$params['sectionvals'] = array($group->getPublicName());

$date_format = _('%Y-%m-%d') ;

site_project_header ($params) ;

$action = getStringFromRequest ('action') ;
$action = util_ensure_value_in_set ($action, array ('display',
						    'add_contrib',
						    'post_add_contrib',
						    'edit_contrib',
						    'del_contrib',
						    'post_edit_contrib',
						    'del_part',
						    'add_part',
						    'move_part')) ;

function check_contrib_id ($c_id, $g_id) {
	$contrib = new ContribTrackerContribution ($c_id) ;
	if (!$contrib || !is_object ($contrib)
	    || $contrib->getGroup()->getId() != $g_id) {
		exit_permission_denied () ;
	}
}
function check_part_id ($p_id, $c_id) {
	$part = new ContribTrackerParticipation ($p_id) ;
	if (!$part || !is_object ($part)
	    || $part->getContribution()->getId() != $c_id) {
		exit_permission_denied () ;
	}
}
function check_date () {
	global $date_format ;
	$r_date = getStringFromRequest ('date') ;
	$tmp = strptime ($r_date, $date_format) ;
	if (!$tmp) {
		$date = time () ;
	} else {
		$date = mktime (0,0,0,$tmp['tm_mon']+1,$tmp['tm_mday'],$tmp['tm_year']+1900);
	}
	return $date ;
}
function check_updown () {
	$up = getStringFromRequest ('up') ;
	$down = getStringFromRequest ('down') ;
	if ($up != '') {
		return 1 ;
	} elseif ($down != '') {
		return -1 ;
	} else {
		return 0 ;
	}
}

// Get and validate parameters, error if tampered with
switch ($action) {
case 'display':
	break ;
case 'add_contrib':
	break ;
case 'post_add_contrib':
	$date = check_date () ;
	$name = getStringFromRequest ('contrib_name') ;
	$desc = getStringFromRequest ('contrib_desc') ;
	break ;
case 'edit_contrib':
case 'del_contrib':
	$contrib_id = getIntFromRequest ('contrib_id') ;
	check_contrib_id ($contrib_id, $group_id) ;
	break ;
case 'post_edit_contrib':
	$contrib_id = getIntFromRequest ('contrib_id') ;
	check_contrib_id ($contrib_id, $group_id) ;
	$date = check_date () ;
	$name = getStringFromRequest ('contrib_name') ;
	$desc = getStringFromRequest ('contrib_desc') ;
	break ;
case 'del_part':
	$contrib_id = getIntFromRequest ('contrib_id') ;
	check_contrib_id ($contrib_id, $group_id) ;
	$part_id = getIntFromRequest ('part_id') ;
	check_part_id ($part_id, $contrib_id) ;
	break ;
case 'move_part':
	$contrib_id = getIntFromRequest ('contrib_id') ;
	check_contrib_id ($contrib_id, $group_id) ;
	$part_id = getIntFromRequest ('part_id') ;
	check_part_id ($part_id, $contrib_id) ;
	$updown = check_updown () ;
	break ;
case 'add_part':
	$contrib_id = getIntFromRequest ('contrib_id') ;
	check_contrib_id ($contrib_id, $group_id) ;
	$actor_id = getIntFromRequest ('actor_id') ;
	$actor = new ContribTrackerActor ($actor_id) ;
	if (!$actor || !is_object ($actor)) {
		exit_permission_denied () ;
	}
	$role_id = getIntFromRequest ('role_id') ;
	$role = new ContribTrackerRole ($role_id) ;
	if (!$role || !is_object ($role)) {
		exit_permission_denied () ;
	}
	break ;
}

// Do the required action

switch ($action) {
case 'post_add_contrib':
	$contrib = new ContribTrackerContribution () ;
	if (!$contrib->create ($name, $date, $desc, $group)) {
		$error_msg = $contrib->getErrorMessage() ;
		$action = 'display';
	} else {
		$contrib_id = $contrib->getId() ;
		$action = 'edit_contrib' ;
	}
	break ;
case 'del_contrib':
	$contrib = new ContribTrackerContribution ($contrib_id) ;
	$contrib->delete () ;
	$action = 'display' ;
	break ;
case 'post_edit_contrib':
	$contrib = new ContribTrackerContribution ($contrib_id) ;
	$contrib->update ($name, $date, $desc, $group) ;
	$action = 'display' ;
	break ;
case 'del_part':
	$part = new ContribTrackerParticipation ($part_id) ;
	$part->delete () ;
	$action = 'edit_contrib' ;
	break ;
case 'move_part':
	$part = new ContribTrackerParticipation ($part_id) ;
	if ($updown > 0) {
		$part->moveUp() ;
	} elseif ($updown < 0) {
		$part->moveDown() ;
	}
	$action = 'edit_contrib' ;
	break ;
case 'add_part':
	$contrib = new ContribTrackerContribution ($contrib_id) ;
	$actor = new ContribTrackerActor ($actor_id) ;
	$role = new ContribTrackerRole ($role_id) ;
	$part = new ContribTrackerParticipation () ;
	$part->create ($contrib, $actor, $role) ;
	$action = 'edit_contrib' ;
	break ;
}

// Display appropriate forms

if(isset($error_msg) && !empty($error_msg)) {
	echo "<div class='error'>".$error_msg."</div>";
}
switch ($action) {
case 'add_contrib':
	print '<h1>'._('Register a new contribution').'</h1>';
	echo $HTML->openForm(array('action' => '/plugins/'.$plugin->name.'/project_admin.php', 'method' => 'post'));
?>
<input type="hidden" name="action" value="post_add_contrib" />
<input type="hidden" name="group_id" value="<?php echo $group_id ?>" />
<?php echo _('Contribution name')._(':') ?> <input type="text" name="contrib_name" size="20" /><br />
<?php echo _('Contribution date')._(':') ?> <input type="text" name="date" value="<?php echo strftime($date_format,time()) ?>"  /><br />
<?php echo _('Contribution description')._(':') ?><br />
<textarea name="contrib_desc" rows="20" cols="80"></textarea><br />
<input type="submit" name="submit" value="<?php echo _('Submit') ?>" />

<?php
	echo $HTML->closeForm();
	 break ;
case 'edit_contrib':
	print '<h1>'._('Edit a contribution').'</h1>' ;

	$contrib = new ContribTrackerContribution ($contrib_id) ;

	print '<h3>'._('Contribution details').'</h3>' ;
	echo $HTML->openForm(array('action' => '/plugins/'.$plugin->name.'/project_admin.php', 'method' => 'post'));
?>
<input type="hidden" name="action" value="post_edit_contrib" />
<input type="hidden" name="group_id" value="<?php echo $group_id ?>" />
<input type="hidden" name="contrib_id" value="<?php echo $contrib->getId() ?>" />
<?php echo _('Contribution name')._(':') ?> <input type="text" name="contrib_name" size="20" value="<?php echo htmlspecialchars ($contrib->getName()) ?>" /><br />
<?php echo _('Contribution date')._(':') ?> <input type="text" name="date" value="<?php echo strftime($date_format,time()) ?>" /><br />
<?php echo _('Contribution description')._(':') ?><br />
<textarea name="contrib_desc" rows="20" cols="80"><?php echo htmlspecialchars ($contrib->getDescription()) ?></textarea><br />
<input type="submit" name="submit" value="<?php echo _('Save') ?>" />
<?php
	echo $HTML->closeForm();
	print '<h3>'._('Current participants').'</h3>' ;

	$parts = $contrib->getParticipations () ;
	print '<strong>'.ngettext('Participant:',
				  'Participants:',
				  count ($parts)).'</strong> ' ;
	print '<br />' ;
	print '<ul>' ;
	foreach ($parts as $p) {
		print '<li>' ;
		printf (_('%s: %s (%s)'),
			htmlspecialchars ($p->getRole()->getName()),
			util_make_link ('/plugins/'.$plugin->name.'/?actor_id='.$p->getActor()->getId (),
					htmlspecialchars ($p->getActor()->getName())),
			htmlspecialchars ($p->getActor()->getLegalStructure()->getName())) ;
		echo $HTML->openForm(array('action' => '/plugins/'.$plugin->name.'/project_admin.php', 'method' => 'post'));
?>
<input type="hidden" name="action" value="del_part" />
<input type="hidden" name="group_id" value="<?php echo $group_id ?>" />
<input type="hidden" name="contrib_id" value="<?php echo $contrib->getId() ?>" />
<input type="hidden" name="part_id" value="<?php echo $p->getId() ?>" />
<input type="submit" name="submit" value="<?php echo _('Delete') ?>" />
<?php
		echo $HTML->closeForm();
		echo $HTML->openForm(array('action' => '/plugins/'.$plugin->name.'/project_admin.php', 'method' => 'post'));
?>
<input type="hidden" name="action" value="move_part" />
<input type="hidden" name="group_id" value="<?php echo $group_id ?>" />
<input type="hidden" name="contrib_id" value="<?php echo $contrib->getId() ?>" />
<input type="hidden" name="part_id" value="<?php echo $p->getId() ?>" />
<input type="submit" name="down" value="<?php echo _('Move participant down') ?>" />
<input type="submit" name="up" value="<?php echo _('Move participant up') ?>" />
<?php
		echo $HTML->closeForm();
		print '</li>' ;
	}
	print '</ul>' ;

	print '<h3>'._('Add a participant').'</h3>' ;
	echo $HTML->openForm(array('action' => '/plugins/'.$plugin->name.'/project_admin.php', 'method' => 'post'));
?>
<input type="hidden" name="action" value="add_part" />
<input type="hidden" name="group_id" value="<?php echo $group_id ?>" />
<input type="hidden" name="contrib_id" value="<?php echo $contrib->getId() ?>" />
<select name="actor_id">
<?php
	$actors = $plugin->getActors () ;
	foreach ($actors as $a) {
		print '<option value="'.$a->getId().'">'.htmlspecialchars($a->getName()).'</option>' ;
	}
?>
</select>
<select name="role_id">
<?php
	$roles = $plugin->getRoles () ;
	foreach ($roles as $r) {
		print '<option value="'.$r->getId().'">'.htmlspecialchars($r->getName()).'</option>' ;
	}
?>
</select>
<input type="submit" name="submit" value="<?php echo _('Add participant') ?>" />
<?php
	echo $HTML->closeForm();
	break ;
case 'display':
	$contribs = $plugin->getContributionsByGroup ($group) ;
	if (count ($contribs) != 0) {
		print '<h1>'._('Existing contributions').'</h1>' ;

		foreach ($contribs as $c) {
			print '<h3>'.$c->getName().'</h3>' ;
			print '<strong>'._('Date')._(':').'</strong> ' ;
			print strftime (_('%Y-%m-%d'), $c->getDate ()) ;
			print '<br />' ;

			print '<strong>'._('Description')._(':').'</strong> ' ;
			print htmlspecialchars ($c->getDescription ()) ;
			print '<br />' ;

			$parts = $c->getParticipations () ;
			print '<strong>'.ngettext('Participant:',
						  'Participants:',
						  count ($parts)).'</strong> ' ;
			print '<br />' ;
			print '<ul>' ;
			foreach ($parts as $p) {
				print '<li>' ;
				printf (_('%s: %s (%s)'),
					htmlspecialchars ($p->getRole()->getName()),
					util_make_link ('/plugins/'.$plugin->name.'/?actor_id='.$p->getActor()->getId (),
							htmlspecialchars ($p->getActor()->getName())),
					htmlspecialchars ($p->getActor()->getLegalStructure()->getName())) ;
				print '</li>' ;
			}
			print '</ul>' ;
			echo $HTML->openForm(array('action' => '/plugins/'.$plugin->name.'/project_admin.php', 'method' => 'post'));

?>
<input type="hidden" name="action" value="edit_contrib" />
<input type="hidden" name="group_id" value="<?php echo $group_id ?>" />
<input type="hidden" name="contrib_id" value="<?php echo $c->getId() ?>" />
<input type="submit" name="submit" value="<?php echo _('Edit') ?>" />
<?php
			echo $HTML->closeForm();
			echo $HTML->openForm(array('action' => '/plugins/'.$plugin->name.'/project_admin.php', 'method' => 'post'));
?>
<input type="hidden" name="action" value="del_contrib" />
<input type="hidden" name="group_id" value="<?php echo $group_id ?>" />
<input type="hidden" name="contrib_id" value="<?php echo $c->getId() ?>" />
<input type="submit" name="submit" value="<?php echo _('Delete'); ?>" />
<?php			echo $HTML->closeForm(); ?>
<hr />
<?php
		}
	} else {
		print '<h1>'._('No contributions for this project yet.').'</h1>' ;
	}
	echo $HTML->openForm(array('action' => '/plugins/'.$plugin->name.'/project_admin.php', 'method' => 'post'));

?>
<input type="hidden" name="action" value="add_contrib" />
<input type="hidden" name="group_id" value="<?php echo $group_id ?>" />
<input type="submit" name="submit" value="<?php echo _('Add new contribution') ?>" />
<?php
	echo $HTML->closeForm();
	break;
}

site_project_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
