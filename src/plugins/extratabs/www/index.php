<?php

/**
 * Extra tabs plugin
 * Copyright 2005, Raphaël Hertzog
 * Copyright 2006-2009, Roland Mas
 * Copyright 2009-2010, Alain Peyrat
 * Copyright 2010, Franck Villaume
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
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

require_once ('../../../www/env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';

$group_id = getIntFromRequest ('group_id') ;
$index = getIntFromRequest ('index') ;

$tab_name = htmlspecialchars(trim(getStringFromRequest ('tab_name')));
$tab_rename = htmlspecialchars(trim(getStringFromRequest ('tab_rename')));
$tab_url = htmlspecialchars(trim(getStringFromRequest ('tab_url', 'http://')));
$tab_new_url = htmlspecialchars(trim(getStringFromRequest ('tab_new_url')));
$type = getIntFromRequest('type', 0);
$new_type = getIntFromRequest('new_type', -1);

session_require_perm ('project_admin', $group_id) ;

// get current information
$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(),'home');
}

db_begin();

// Calculate new index field
$res = db_query_params ('SELECT COUNT(*) as c FROM plugin_extratabs_main WHERE group_id = $1',
			array ($group_id)) ;
$row = db_fetch_array($res);
$newid = $row['c'] + 1;

$selected = 0; // No item selected by default

// Do work before displaying so that the result is immediately visible
if (getStringFromRequest ('addtab') != '') {
	if ($tab_name == '' || $tab_url == '' || $tab_url == 'http://') {
		$error_msg = _('ERROR: Missing Name or URL for the new tab');
	} else if (!util_check_url($tab_url)) {
		$error_msg = _('ERROR: Malformed URL (only http, https and ftp allowed)');
	} else {
		$res = db_query_params('SELECT * FROM plugin_extratabs_main WHERE group_id=$1 AND tab_name=$2',
			array($group_id, $tab_name));
		if ($res && db_numrows($res) > 0) {
			$error_msg = _('ERROR: Name for tab is already used.');
		} else {
			$res = db_query_params ('INSERT INTO plugin_extratabs_main (group_id, index, tab_name, tab_url, type) VALUES ($1,$2,$3,$4, $5)',
						array ($group_id,
						       $newid,
						       $tab_name,
						       $tab_url,
						       $type)) ;
			if (!$res || db_affected_rows($res) < 1) {
				$error_msg = sprintf (_('Cannot insert new tab entry: %s'),
						      db_error());
			} else {
				$tab_name = '';
				$tab_url = 'http://';
				$feedback = _('Tab successfully added');
			}
		}
	}
} elseif (getStringFromRequest ('delete') != '') {
	$res = db_query_params ('DELETE FROM plugin_extratabs_main WHERE group_id=$1 AND index=$2',
				array ($group_id,
				       $index)) ;
	if (!$res || db_affected_rows($res) < 1) {
		$error_msg = sprintf (_('Cannot delete tab entry: %s'), db_error());
	} else {
		$res = db_query_params ('SELECT index FROM plugin_extratabs_main WHERE group_id=$1 AND index > $2 ORDER BY index ASC',
					array ($group_id,
					       $index)) ;
		if (db_numrows($res) > 0) {
			$todo = array () ;
			while ($row = db_fetch_array($res)) {
				$todo[] = $row['index'] ;
			}
			foreach ($todo as $i) {
				$res = db_query_params ('UPDATE plugin_extratabs_main SET index = index - 1 WHERE group_id = $1 AND index = $2',
							array ($group_id,
							       $i)) ;
			}
		}
		if ($res) {
			$feedback = _('Tab successfully deleted');
		} else {
			$error_msg = sprintf (_('Cannot delete tab entry: %s'), db_error());
		}
	}
} elseif (getStringFromRequest ('up') != '') {
	if ($index > 1) {
		$previous = $index - 1;
		$res = db_query_params('UPDATE plugin_extratabs_main SET index=0 WHERE group_id=$1 AND index=$2',
				       array ($group_id,
					      $index)) ;
		$res = db_query_params('UPDATE plugin_extratabs_main SET index=$1 WHERE group_id=$2 AND index=$3',
				       array ($index,
					      $group_id,
					      $previous)) ;
		$res = db_query_params('UPDATE plugin_extratabs_main SET index=$1 WHERE group_id=$2 AND index=0',
				       array ($previous,
					      $group_id)) ;
		$selected = $previous;
		$feedback = _('Tab successfully moved');
	} else {
		$warning_msg = _('Tab not moved, already at first position');
		$selected = $index;
	}
} elseif (getStringFromRequest ('down') != '') {
	if ($index < $newid - 1) {
		$next = $index + 1;
		$res = db_query_params('UPDATE plugin_extratabs_main SET index=0 WHERE group_id=$1 AND index=$2',
				       array ($group_id,
					      $index)) ;
		$res = db_query_params('UPDATE plugin_extratabs_main SET index=$1 WHERE group_id=$2 AND index=$3',
				       array ($index,
					      $group_id,
					      $next)) ;
		$res = db_query_params('UPDATE plugin_extratabs_main SET index=$1 WHERE group_id=$2 AND index=0',
				       array ($next,
					      $group_id)) ;
		$feedback = _('Tab successfully moved');
		$selected = $next;
	} else {
		$warning_msg = _('Tab not moved, already at last position');
		$selected = $index;
	}
} elseif (getStringFromRequest ('modify') != '') {
	$done = 0;
	if ($tab_rename) {
		$res = db_query_params ('UPDATE plugin_extratabs_main SET tab_name=$1 WHERE group_id=$2 AND index=$3',
					array ($tab_rename,
						   $group_id,
						   $index));
		if (!$res || db_affected_rows($res) < 1) {
			$error_msg = sprintf (_('Cannot rename the tab: %s'), db_error());
		} else {
			$feedback .= ($feedback ? '. ' : '') . _('Tab successfully renamed');
			$done = 1;
		}
	}
	if ($tab_new_url && $tab_new_url != 'http://') {
		if (!util_check_url($tab_new_url)) {
			$error_msg = _('ERROR: Malformed URL (only http, https and ftp allowed)');
		} else {
			$res = db_query_params ('UPDATE plugin_extratabs_main SET tab_url=$1 WHERE group_id=$2 AND index=$3',
					array ($tab_new_url,
						   $group_id,
						   $index));
			if (!$res || db_affected_rows($res) < 1) {
				$error_msg .= ($error_msg ? '. ' : '') . sprintf (_('Cannot change URL: %s'), db_error());
			} else {
				$feedback .= ($feedback ? '. ' : '') . _('URL successfully changed');
				$done = 1;
			}
		}
	}
	if ($new_type != -1) {
		$res = db_query_params ('UPDATE plugin_extratabs_main SET type=$1 WHERE group_id=$2 AND index=$3',
					array ($new_type,
						   $group_id,
						   $index));
		if (!$res || db_affected_rows($res) < 1) {
			$error_msg .= ($error_msg ? '. ' : '') . sprintf (_('Cannot set type: %s'), db_error());
		} else {
			$feedback .= ($feedback ? '. ' : '') . _('Type successfully changed');
			$done = 1;
		}
	}
	if (!$error_msg && !$done) {
		$warning_msg .= ($warning_msg ? '. ' : '') . _('Nothing done');
	}
}
if (!$res) {
	db_rollback();
} else  {
	db_commit();
}

$adminheadertitle=sprintf(_('Manage extra tabs for project %1$s'), $group->getPublicName() );
project_admin_header(array('title'=>$adminheadertitle, 'group'=>$group->getID()));

?>

<h2><?php echo _('Add new tab'); ?></h2>

<p><?php echo _('You can add your own tabs in the menu bar with the form below.') ?></p>

<form name="new_tab" action="<?php echo util_make_uri ('/plugins/extratabs/'); ?>" method="post">
<fieldset>
<legend><?php echo _('Add new tab'); ?></legend>
<p>
<input type="hidden" name="group_id" value="<?php echo $group->getID() ?>" />
<input type="hidden" name="addtab" value="1" />
<strong><?php echo _('Name of the tab:') ?></strong><?php echo utils_requiredField(); ?>
<br />
<input type="text" size="20" maxlength="20" name="tab_name" value="<?php echo $tab_name ?>" /><br />
</p>
<p>
<strong><?php echo _('URL of the tab:') ?></strong><?php echo utils_requiredField(); ?>
<br />
<input type="text" size="60" name="tab_url" value="<?php echo $tab_url ?>" /><br/>
</p>
<p>
<input type="radio" name="type" value="0" checked="checked"/><?php echo _('Link') ?>
<input type="radio" name="type" value="1" /><?php echo _('Iframe') ?>
</p>
<p>
<input type="submit" value="<?php echo _('Add tab') ?>" />
</p>
</fieldset>
</form>

<?php
	$res = db_query_params ('SELECT * FROM plugin_extratabs_main WHERE group_id=$1 ORDER BY index ASC', array ($group_id)) ;
$nbtabs = db_numrows($res) ;
if ($nbtabs > 0) {
	
?>

<h2><?php echo _('Modify extra tabs'); ?></h2>
<p>
<?php echo _('You can modify the tabs that you already added.');
?>
</p>

<form name="modify_tab" action="<?php echo util_make_uri('/plugins/extratabs/'); ?>" method="post">
<fieldset>
<legend><?php echo _('Modify tab'); ?></legend>
<p>
<input type="hidden" name="group_id" value="<?php echo $group->getID() ?>" />
<?php echo _('Tab to modify:') ?> <select name="index">
<?php
$options = '';
while ($row = db_fetch_array($res)) {
    if ($row['index'] == $selected) {
	$options .= "<option selected=\"selected\" value='" . $row['index'] . "'>" . $row['tab_name'] .  "</option>";
    } else {
	$options .= "<option value='" . $row['index'] . "'>" . $row['tab_name'] .  "</option>";
    }
}
echo $options;
?>
</select>
</p>
<p>
<?php echo _('Rename to:'); ?> <input type="text" size="20" maxlength="20" name="tab_rename" value="" />
</p>
<p>
<?php echo _('New URL:'); ?> <input type="text" size="60" name="tab_new_url" value="http://" />
</p>
<p>
<input type="radio" name="new_type" value="0" /><?php echo _('Link') ?>
<input type="radio" name="new_type" value="1" /><?php echo _('Iframe') ?>
</p>
<p>
<input type="submit" name="modify" value="<?php echo _('Modify tab') ?>" />
</p>
</fieldset>
</form>

<h2><?php echo _('Move or delete extra tabs') ;?></h2>
<p>
<?php echo _('You can move and delete the tabs that you already added. Please note that those extra tabs can only appear after the standard tabs. And you can only move them inside the set of extra tabs.');
?>
</p>

<form name="change_tab" action="<?php echo util_make_uri('/plugins/extratabs/'); ?>" method="post">
<fieldset>
<legend><?php echo _('Move or delete tab'); ?></legend>
<p>
<input type="hidden" name="group_id" value="<?php echo $group->getID() ?>" />
<?php
	echo _('Tab to modify:')
?>
<select name="index">
<?php
echo $options;
?>
</select>
</p>
<p>
	  <?php if ($nbtabs > 1) { ?>
<input type="submit" name="up" value="<?php echo _('Move tab before') ?>" />
<input type="submit" name="down" value="<?php echo _('Move tab after') ?>" />
		  <?php } ?>
<input type="submit" name="delete" value="<?php echo _('Delete tab') ?>" />
</p>
</fieldset>
</form>

<?php
	  }
project_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
