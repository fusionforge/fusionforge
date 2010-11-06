<?php

/**
 * FusionForge Mailing Lists Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) Guillaume Smet - Open Wide
 *
 * @version $Id$
 * 
 * Portions Copyright 2010 (c) Mélanie Le Bail
 */
require_once ('env.inc.php');
require_once 'pre.php';
require_once 'preplugins.php';
require_once('plugins_utils.php');
require_once '../mailman_utils.php';

$request =& HTTPRequest::instance();
$group_id = $request->get('group_id');

$feedback = '';

if (!$group_id) {
	exit_no_group();
}
$pm = ProjectManager::instance();
$Group = $pm->getProject($group_id);
if (!$Group || !is_object($Group) || $Group->isError()) {
	exit_no_group();
}
if(isLogged()) {
	if (!$current_user->isMember($group_id,'A')) {
		exit_permission_denied();
	}
}


$ml = new MailmanList($group_id,$request->get('id'));

if ($request->exist('submit')) {
	$sure = $request->get('sure');
	$really_sure = $request->get('really_sure');
	if (!$ml->deleteList($sure,$really_sure)) {
		exit_error('Error',$ml->getErrorMessage());
	} else {
		htmlRedirect('/plugins/mailman/index.php?group_id='.$group_id.'&feedback=DELETED');
	}
}

mailman_header(array(
	'title' => _('Permanently Delete List')
));

?>
<h3><?php echo $ml->getName(); ?></h3>
<p>
<form method="post" action="<?php echo $request->get('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;id=<?php echo $ml->getID(); ?>">
<input type="checkbox" name="sure" value="1"><?php echo _('Confirm Delete'); ?><br />
<input type="checkbox" name="really_sure" value="1"><?php echo _('Confirm Delete'); ?><br />
<input type="submit" name="submit" value="<?php echo _('Permanently Delete'); ?>">
</form>
</p>
<?php

mail_footer(array());

?>
