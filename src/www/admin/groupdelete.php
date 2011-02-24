<?php
/**
 * Site Admin group properties editing page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

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
		session_redirect('/admin/?feedback='.urlencode($feedback));
	}
}

$title = _('Permanently and irretrievably delete project').': '.$group->getPublicName();
site_admin_header(array('title'=>$title));
?>

<form action="<?php echo '?group_id='.$group_id; ?>" method="post">
<input type="checkbox" value="1" name="sure" /> <?php echo _('Confirm Delete'); ?><br />
<input type="checkbox" value="1" name="reallysure" /> <?php echo _('Confirm Delete'); ?><br />
<input type="checkbox" value="1" name="reallyreallysure" /> <?php echo _('Confirm Delete'); ?><br />

<input type="submit" name="submit" value="<?php echo _('Permanently Delete'); ?>" />
</form>

<?php

site_admin_footer(array());

?>
