<?php
/**
 * Site Admin group properties editing page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2016, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

global $HTML;

session_require_global_perm('forge_admin');

$group_id = getIntFromGet('group_id');

$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(), 'admin');
}

if (getStringFromPost('submit')) {
	$sure = getIntFromPost('sure');
	$reallysure = getIntFromPost('reallysure');
	$reallyreallysure = getIntFromPost('reallyreallysure');
	if (!$group->delete($sure, $reallysure, $reallyreallysure)) {
		exit_error($group->getErrorMessage(), 'admin');
	} else {
		$feedback = _('Project successfully deleted');
		session_redirect('/admin/');
	}
}

$title = _('Permanently and irretrievably delete project')._(': ').$group->getPublicName();
site_admin_header(array('title'=>$title));
echo $HTML->openForm(array('action' => '/admin/groupdelete.php?group_id='.$group_id, 'method' => 'post'));
?>
<input id="sure" type="checkbox" value="1" name="sure" />
<label for="sure">
<?php echo _('Confirm Delete'); ?><br />
</label>
<input id="reallysure" type="checkbox" value="1" name="reallysure" />
<label for="reallysure">
<?php echo _('Confirm Delete'); ?><br />
</label>
<input id="reallyreallysure" type="checkbox" value="1" name="reallyreallysure" />
<label for="reallyreallysure">
<?php echo _('Confirm Delete'); ?><br />
</label>
<input type="submit" name="submit" value="<?php echo _('Permanently Delete'); ?>" />
<?php
echo $HTML->closeForm();
site_admin_footer();
