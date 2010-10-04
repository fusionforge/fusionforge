<?php
/**
 * FusionForge Mailing Lists Facility
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2003-2004 (c) Guillaume Smet - Open Wide
 * Copyright 2010 (c) Franck Villaume
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'mail/admin/../mail_utils.php';

require_once $gfcommon.'mail/MailingList.class.php';

$group_id = getIntFromRequest('group_id');

$feedback = '';

if (!$group_id) {
	exit_no_group();
}

$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
} else if ($group->isError()) {
	exit_error($group->getErrorMessage(),'home');
}

session_require_perm ('project_admin', $group->getID()) ;

$ml = new MailingList($group,getIntFromGet('group_list_id'));

if (getStringFromPost('submit')) {
	$sure = getStringFromPost('sure');
	if (!$ml->delete($sure,$sure)) {
		exit_error($ml->getErrorMessage(),'home');
	} else {
		$feedback= _('Mailing List Successfully deleted');
		session_redirect('?group_id='.$group_id.'&feedback='.urlencode($feedback));
	}
}

mail_header(array('title' => _('Permanently Delete List')));

?>
<h2><?php echo $ml->getName(); ?></h2>
<form method="post" action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;group_list_id=<?php echo $ml->getID(); ?>">
<p>
<input type="checkbox" name="sure" value="1" /><?php echo _('Confirm Delete'); ?><br />
</p>
<p>
<input type="submit" name="submit" value="<?php echo _('Permanently Delete'); ?>" />
</p>
</form>
<?php

mail_footer(array());

?>
