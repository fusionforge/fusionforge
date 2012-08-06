<?php

/**
 * ContribTracker plugin
 *
 * Copyright 2009, Roland Mas
 * Copyright 2010 (c) Franck Villaume
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';
$plugin = plugin_get_object ('contribtracker') ;

$max_logo_size = 50 ;		// In kibibytes

session_require_global_perm ('forge_admin') ;


$action = getStringFromRequest ('action') ;
$action = util_ensure_value_in_set ($action, array ('display',
						    'add_role',
						    'add_actor',
						    'add_structure',
						    'post_add_role',
						    'post_add_actor',
						    'post_add_structure',
						    'edit_role',
						    'edit_actor',
						    'edit_structure',
						    'post_edit_role',
						    'post_edit_actor',
						    'post_edit_structure',
						    'del_role',
						    'del_actor',
						    'del_structure')) ;

function check_role_id ($r_id) {
	$role = new ContribTrackerRole ($r_id) ;
	if (!$role || !is_object ($role)) {
		exit_permission_denied ('','home') ;
	}
}
function check_actor_id ($a_id) {
	$actor = new ContribTrackerActor ($a_id) ;
	if (!$actor || !is_object ($actor)) {
		exit_permission_denied ('','home') ;
	}
}
function check_structure_id ($s_id) {
	$structure = new ContribTrackerLegalStructure ($s_id) ;
	if (!$structure || !is_object ($structure)) {
		exit_permission_denied ('','home') ;
	}
}
function check_logo ($arr, $a_id=false) {
	global $max_logo_size ;

	if ($a_id) {
		$actor = new ContribTrackerActor ($a_id) ;
		$default = $actor->getLogo() ;
	} else {
		$default = '' ;
	}
	if ($arr['tmp_name'] == '') {
		$logo = $default ;
	} else {
		if ($arr['size'] > 1024 * $max_logo_size) {
			$logo = $default ;
		} else {
			$logo = file_get_contents ($arr['tmp_name'], 0, NULL, -1, 1024 * $max_logo_size) ;
		}
		unlink ($arr['tmp_name']) ;
	}
	return $logo ;
}

// Get and validate parameters, error if tampered with
switch ($action) {
case 'display':
	break ;

case 'add_role':
	break ;
case 'post_add_role':
	$name = getStringFromRequest ('role_name') ;
	$desc = getStringFromRequest ('role_desc') ;
	break ;
case 'edit_role':
case 'del_role':
	$role_id = getIntFromRequest ('role_id') ;
	check_role_id ($role_id) ;
	break ;
case 'post_edit_role':
	$role_id = getIntFromRequest ('role_id') ;
	check_role_id ($role_id) ;
	$name = getStringFromRequest ('role_name') ;
	$desc = getStringFromRequest ('role_desc') ;
	break ;

case 'add_structure':
	break ;
case 'post_add_structure':
	$name = getStringFromRequest ('structure_name') ;
	$desc = getStringFromRequest ('structure_desc') ;
	break ;
case 'edit_structure':
case 'del_structure':
	$structure_id = getIntFromRequest ('structure_id') ;
	check_structure_id ($structure_id) ;
	break ;
case 'post_edit_structure':
	$structure_id = getIntFromRequest ('structure_id') ;
	check_structure_id ($structure_id) ;
	$name = getStringFromRequest ('structure_name') ;
	$desc = getStringFromRequest ('structure_desc') ;
	break ;

case 'add_actor':
	break ;
case 'post_add_actor':
	$name = getStringFromRequest ('actor_name') ;
	$url = getStringFromRequest ('actor_url') ;
	$email = getStringFromRequest ('actor_email') ;
	$desc = getStringFromRequest ('actor_desc') ;
	$logoarr = getUploadedFile ('actor_logo') ;
	$logo = check_logo ($logoarr) ;
	$structure_id = getIntFromRequest ('structure_id') ;
	check_structure_id ($structure_id) ;
	break ;
case 'edit_actor':
case 'del_actor':
	$actor_id = getIntFromRequest ('actor_id') ;
	check_actor_id ($actor_id) ;
	break ;
case 'post_edit_actor':
	$actor_id = getIntFromRequest ('actor_id') ;
	check_actor_id ($actor_id) ;
	$name = getStringFromRequest ('actor_name') ;
	$url = getStringFromRequest ('actor_url') ;
	$email = getStringFromRequest ('actor_email') ;
	$desc = getStringFromRequest ('actor_desc') ;
	$logoarr = getUploadedFile ('actor_logo') ;
	$logo = check_logo ($logoarr, $actor_id) ;
	$structure_id = getIntFromRequest ('structure_id') ;
	check_structure_id ($structure_id) ;
	break ;

}

// Do the required action

switch ($action) {
case 'post_add_role':
	$role = new ContribTrackerRole () ;
	if (!$role->create ($name, $desc)) {
		exit_error ($role->getErrorMessage(),'contribtracker') ;
	}
	$role_id = $role->getId() ;
	$action = 'display' ;
	break ;
case 'del_role':
	$role = new ContribTrackerRole ($role_id) ;
	$role->delete () ;
	$action = 'display' ;
	break ;
case 'post_edit_role':
	$role = new ContribTrackerRole ($role_id) ;
	$role->update ($name, $desc) ;
	$action = 'display' ;
	break ;

case 'post_add_structure':
	$structure = new ContribTrackerLegalStructure () ;
	if (!$structure->create ($name, $desc)) {
		exit_error ($structure->getErrorMessage(),'contribtracker') ;
	}
	$structure_id = $structure->getId() ;
	$action = 'display' ;
	break ;
case 'del_structure':
	$structure = new ContribTrackerLegalStructure ($structure_id) ;
	$structure->delete () ;
	$action = 'display' ;
	break ;
case 'post_edit_structure':
	$structure = new ContribTrackerLegalStructure ($structure_id) ;
	$structure->update ($name, $desc) ;
	$action = 'display' ;
	break ;

case 'post_add_actor':
	$actor = new ContribTrackerActor () ;
	$structure = new ContribTrackerLegalStructure ($structure_id) ;
	if (!$actor->create ($name, $url, $email, $desc, $logo, $structure)) {
		exit_error ($actor->getErrorMessage(),'contribtracker') ;
	}
	$actor_id = $actor->getId() ;
	$action = 'display' ;
	break ;
case 'del_actor':
	$actor = new ContribTrackerActor ($actor_id) ;
	$actor->delete () ;
	$action = 'display' ;
	break ;
case 'post_edit_actor':
	$actor = new ContribTrackerActor ($actor_id) ;
	$structure = new ContribTrackerLegalStructure ($structure_id) ;
	$actor->update ($name, $url, $email, $desc, $logo, $structure) ;
	$action = 'display' ;
	break ;
}

// Display appropriate forms
site_admin_header (array ('title' => _('Contribution tracker administration'))) ;

switch ($action) {
case 'display':
	print '<h1>'._('Existing actors').'</h1>' ;
	$actors = $plugin->getActors () ;
	if (count ($actors)) {
		print '<table><thead><tr>' ;
		print '<td><strong>'._('Logo').'</strong></td>' ;
		print '<td><strong>'._('Short name').'</strong></td>' ;
		print '<td><strong>'._('URL').'</strong></td>' ;
		print '<td><strong>'._('Email').'</strong></td>' ;
		print '<td><strong>'._('Description').'</strong></td>' ;
		print '<td><strong>'._('Legal structure').'</strong></td>' ;
		print '<td><strong>'._('Actions').'</strong></td>' ;
		print '</tr></thead><tbody>' ;
		foreach ($actors as $a) {
			print '<tr>';
			print '<td>' ;
			if ($a->getLogo() != '') {
				print '<img type="image/png" src="'.util_make_url ('/plugins/'.$plugin->name.'/actor_logo.php?actor_id='.$a->getId ()).'" />' ;
			}
			print '</td>' ;
			print '<td>'.htmlspecialchars($a->getName()).'</td>' ;
			print '<td>' ;
			if ($a->getUrl() != '') {
				print '<a href="'.htmlspecialchars($a->getUrl()).'">'.htmlspecialchars($a->getUrl()).'</a>';
			}
			print '</td>' ;
			print '<td>'.htmlspecialchars($a->getEmail()).'</td>' ;
			print '<td>'.htmlspecialchars($a->getDescription()).'</td>' ;
			print '<td>'.htmlspecialchars($a->getLegalStructure()->getName()).'</td>' ;
			?>
				<td>
					 <form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post">
					 <input type="hidden" name="action" value="edit_actor" />
					 <input type="hidden" name="actor_id" value="<?php echo $a->getId () ?>" />
					 <input type="submit" name="submit" value="<?php echo _('Edit') ?>" />
					 </form>
					 <form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post">
					 <input type="hidden" name="action" value="del_actor" />
					 <input type="hidden" name="actor_id" value="<?php echo $a->getId () ?>" />
					 <input type="submit" name="submit" value="<?php echo _('Delete') ?>" />
					 </form>
					 </td>
					 <?php
					 print '</tr>';
		}
		print '</tbody></table>' ;
	} else {
		print _('No legal structures currently defined.') ;
	}
	$structs = $plugin->getLegalStructures () ;
	if (count ($structs)) {
		?>
		<form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post">
			<input type="hidden" name="action" value="add_actor" />
			<input type="submit" name="submit" value="<?php echo _('Register new actor') ?>" />
			</form>

			<?php
			} else {
		print _("No legal structures yet, can't define actors without them.") ;
	}

	print '<h1>'._('Existing legal structures').'</h1>' ;
	$structs = $plugin->getLegalStructures () ;
	if (count ($structs)) {
		print '<table><thead><tr>' ;
		print '<td><strong>'._('Short name').'</strong></td>' ;
		print '<td><strong>'._('Actions').'</strong></td>' ;
		print '</tr></thead><tbody>' ;
		foreach ($structs as $s) {
			print '<tr>';
			print '<td>'.htmlspecialchars($s->getName()).'</td>' ;
			?>
				<td>
					 <form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post">
					 <input type="hidden" name="action" value="edit_structure" />
					 <input type="hidden" name="structure_id" value="<?php echo $s->getId () ?>" />
					 <input type="submit" name="submit" value="<?php echo _('Edit') ?>" />
					 </form>
					 <form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post">
					 <input type="hidden" name="action" value="del_structure" />
					 <input type="hidden" name="structure_id" value="<?php echo $s->getId () ?>" />
					 <input type="submit" name="submit" value="<?php echo _('Delete') ?>" />
					 </form>
					 </td>
					 <?php
					 print '</tr>';
		}
		print '</tbody></table>' ;
	} else {
		print _('No legal structures currently defined.') ;
	}
	?>
		<form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post">
			 <input type="hidden" name="action" value="add_structure" />
			 <input type="submit" name="submit" value="<?php echo _('Register new legal structure') ?>" />
			 </form>
			 <?php

			 print '<h1>'._('Existing roles').'</h1>' ;
	$roles = $plugin->getRoles () ;
	if (count ($roles)) {
		print '<table><thead><tr>' ;
		print '<td><strong>'._('Short name').'</strong></td>' ;
		print '<td><strong>'._('Description').'</strong></td>' ;
		print '<td><strong>'._('Actions').'</strong></td>' ;
		print '</tr></thead><tbody>' ;
		foreach ($roles as $r) {
			print '<tr>';
			print '<td>'.htmlspecialchars($r->getName()).'</td>' ;
			print '<td>'.htmlspecialchars($r->getDescription()).'</td>' ;
			?>
				<td>
					 <form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post">
					 <input type="hidden" name="action" value="edit_role" />
					 <input type="hidden" name="role_id" value="<?php echo $r->getId () ?>" />
					 <input type="submit" name="submit" value="<?php echo _('Edit') ?>" />
					 </form>
					 <form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post">
					 <input type="hidden" name="action" value="del_role" />
					 <input type="hidden" name="role_id" value="<?php echo $r->getId () ?>" />
					 <input type="submit" name="submit" value="<?php echo _('Delete') ?>" />
					 </form>
					 </td>
					 <?php
					 print '</tr>';
		}
		print '</tbody></table>' ;
	} else {
		print _('No roles currently defined.') ;
	}
	?>
		<form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post">
			 <input type="hidden" name="action" value="add_role" />
			 <input type="submit" name="submit" value="<?php echo _('Register new role') ?>" />
			 </form>
			 <?php

			 break ;

case 'add_role':
	print '<h1>'._('Register a new role').'</h1>' ;
	?>
		<form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post">
			 <input type="hidden" name="action" value="post_add_role" />
			 <?php echo _('Role name:') ?> <input type="text" name="role_name" size="20" /><br />
			 <?php echo _('Role description:') ?><br />
			 <textarea name="role_desc" rows="20" cols="80"></textarea><br />
			 <input type="submit" name="submit" value="<?php echo _('Submit') ?>" />
			 </form>

			 <?php
			 break ;

case 'edit_role':
	print '<h1>'._('Edit a role').'</h1>' ;
	$role = new ContribTrackerRole ($role_id) ;

	?>
		<form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post">
			 <input type="hidden" name="action" value="post_edit_role" />
			 <input type="hidden" name="role_id" value="<?php echo $role->getId() ?>" />
			 <?php echo _('Role name:') ?> <input type="text" name="role_name" size="20" value="<?php echo htmlspecialchars ($role->getName()) ?>" /><br />
			 <?php echo _('Role description:') ?><br />
			 <textarea name="role_desc" rows="20" cols="80"><?php echo htmlspecialchars ($role->getDescription()) ?></textarea><br />
			 <input type="submit" name="submit" value="<?php echo _('Save') ?>" />
			 </form>
			 <?php
			 break ;

case 'add_structure':
	print '<h1>'._('Register a new legal structure').'</h1>' ;
	?>
		<form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post">
			 <input type="hidden" name="action" value="post_add_structure" />
			 <?php echo _('Structure name:') ?> <input type="text" name="structure_name" size="20" /><br />
			 <input type="submit" name="submit" value="<?php echo _('Submit') ?>" />
			 </form>

			 <?php
			 break ;

case 'edit_structure':
	print '<h1>'._('Edit a legal structure').'</h1>' ;
	$structure = new ContribTrackerLegalStructure ($structure_id) ;

	?>
		<form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post">
			 <input type="hidden" name="action" value="post_edit_structure" />
			 <input type="hidden" name="structure_id" value="<?php echo $structure->getId() ?>" />
			 <?php echo _('Structure name:') ?> <input type="text" name="structure_name" size="20" value="<?php echo htmlspecialchars ($structure->getName()) ?>" /><br />
			 <input type="submit" name="submit" value="<?php echo _('Save') ?>" />
			 </form>
			 <?php
			 break ;

case 'add_actor':
	print '<h1>'._('Register a new actor').'</h1>' ;
	?>
		<form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post" enctype="multipart/form-data">
			 <input type="hidden" name="action" value="post_add_actor" />
			 <?php echo _('Actor name:') ?> <input type="text" name="actor_name" size="20" /><br />
			 <?php echo _('Actor URL:') ?> <input type="text" name="actor_url" size="20" /><br />
			 <?php echo _('Actor email:') ?> <input type="text" name="actor_email" size="20" /><br />
			 <?php echo _('Actor description:') ?><br />
			 <textarea name="actor_desc" rows="20" cols="80"></textarea><br />
			 <?php printf (_('Actor logo (PNG, %d kB max):'), $max_logo_size) ?> <input type="file" name="actor_logo" /><br />
			 <?php
			 echo _('Legal structure:') ?>
			 <select name="structure_id">
			 <?php
			 $structs = $plugin->getLegalStructures () ;
	foreach ($structs as $s) {
		print '<option value="'.$s->getId().'">'.htmlspecialchars($s->getName()).'</option>' ;
	}
	?>
		</select><br />
			  <input type="submit" name="submit" value="<?php echo _('Submit') ?>" />
			  </form>

			  <?php
			  break ;

case 'edit_actor':
	print '<h1>'._('Edit an actor').'</h1>' ;
	$actor = new ContribTrackerActor ($actor_id) ;

	?>
		<form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/global_admin.php') ?>" method="post" enctype="multipart/form-data">
			 <input type="hidden" name="action" value="post_edit_actor" />
			 <input type="hidden" name="actor_id" value="<?php echo $actor->getId() ?>" />
			 <?php echo _('Actor name:') ?> <input type="text" name="actor_name" size="20" value="<?php echo htmlspecialchars ($actor->getName()) ?>" /><br />
			 <?php echo _('Actor URL:') ?> <input type="text" name="actor_url" size="20" value="<?php echo htmlspecialchars ($actor->getUrl()) ?>" /><br />
			 <?php echo _('Actor email:') ?> <input type="text" name="actor_email" size="20" value="<?php echo htmlspecialchars ($actor->getEmail()) ?>" /><br />
			 <?php echo _('Actor description:') ?><br />
			 <textarea name="actor_desc" rows="20" cols="80"><?php echo htmlspecialchars ($actor->getDescription()) ?></textarea><br />
			 <?php printf (_('Actor logo (PNG, %d kB max):'), $max_logo_size) ?> <input type="file" name="actor_logo" /><br />
			 <?php
			 if ($actor->getLogo() != '') {
				 print '<img type="image/png" src="'.util_make_url ('/plugins/'.$plugin->name.'/actor_logo.php?actor_id='.$actor->getId ()).'" />' ;
			 }
	print '<br />' ;
			 echo _('Legal structure:') ?>
			 <select name="structure_id">
			 <?php
			 $structs = $plugin->getLegalStructures () ;
	foreach ($structs as $s) {
		print '<option value="'.$s->getId().'".' ;
		if ($s->getId() == $actor->getLegalStructure()->getId()) {
			print ' selected' ;
		}
		print '>'.htmlspecialchars($s->getName()).'</option>' ;
	}
	?>
		</select><br />
			  <input type="submit" name="submit" value="<?php echo _('Save') ?>" />
			  </form>
			  <?php
			  break ;

}

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
