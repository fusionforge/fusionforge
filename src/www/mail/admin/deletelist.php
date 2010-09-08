<?php

/**
 * GForge Mailing Lists Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) Guillaume Smet - Open Wide
 *
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

$group =& group_get_object($group_id);
if (!$group || !is_object($group) || $group->isError()) {
	exit_no_group();
}

session_require_perm ('project_admin', $group->getID()) ;

$ml = new MailingList($group,getIntFromGet('group_list_id'));

if (getStringFromPost('submit')) {
	$sure = getStringFromPost('sure');
	$really_sure = getStringFromPost('really_sure');
	if (!$ml->delete($sure,$really_sure)) {
		exit_error('Error',$ml->getErrorMessage());
	} else {
		header("Location: index.php?group_id=$group_id&feedback=Mailing+List+successfully+deleted");
	}
}

mail_header(array(
	'title' => _('Permanently Delete List')
));

?>
<h3><?php echo $ml->getName(); ?></h3>
<form method="post" action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;group_list_id=<?php echo $ml->getID(); ?>">
<input type="checkbox" name="sure" value="1" /><?php echo _('Confirm Delete'); ?><br />
<input type="checkbox" name="really_sure" value="1" /><?php echo _('Confirm Delete'); ?><br />
<p />
<input type="submit" name="submit" value="<?php echo _('Permanently Delete'); ?>" />
</form>
<?php

mail_footer(array());

?>
