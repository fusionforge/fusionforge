<?php

/**
 * GForge Mailing Lists Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) Guillaume Smet - Open Wide
 *
 * @version $Id$
 */

require_once('pre.php');
require_once('../mail_utils.php');

require_once('common/mail/MailingList.class');

$group_id = getIntFromRequest('group_id');

$feedback = '';

if (!$group_id) {
	exit_no_group();
}

$Group =& group_get_object($group_id);
if (!$Group || !is_object($Group) || $Group->isError()) {
	exit_no_group();
}

$perm =& $Group->getPermission(session_get_user());
if (!$perm || !is_object($perm) || $perm->isError() || !$perm->isAdmin()) {
	exit_permission_denied();
}

$ml = new MailingList($Group,getIntFromGet('group_list_id'));

if (getStringFromPost('submit')) {
	if (!$ml->delete($sure,$really_sure)) {
		exit_error('Error',$ml->getErrorMessage());
	} else {
		header("Location: index.php?group_id=$group_id&feedback=DELETED");
	}
}

mail_header(array(
	'title' => $Language->getText('mail_admin_deletelist', 'title')
));

?>
<h3><?php echo $ml->getName(); ?></h3>
<p>
<form method="post" action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;group_list_id=<?php echo $ml->getID(); ?>">
<input type="checkbox" name="sure" value="1"><?php echo $Language->getText('mail_admin_deletelist', 'confirmdelete'); ?><br />
<input type="checkbox" name="really_sure" value="1"><?php echo $Language->getText('mail_admin_deletelist', 'confirmdelete'); ?><br />
<input type="submit" name="submit" value="<?php echo $Language->getText('mail_admin_deletelist', 'permanentlydelete'); ?>">
</form>
</p>
<?php

mail_footer(array());

?>
