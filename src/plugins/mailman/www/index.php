<?php


/*
 * mailman plugin
 *
 * Daniel Perez <danielperez.arg@gmail.com>
 *
 * This is an example to watch things in action. You can obviously modify things and logic as you see fit
 *
 * Portions Copyright 2010 (c) Mélanie Le Bail
 */
require_once ('env.inc.php');
require_once 'pre.php';
require_once 'preplugins.php';
require_once 'plugins_utils.php';
require_once 'mailman_utils.php';
$request =& HTTPRequest::instance();

$group_id = $request->get('group_id');
$pm = ProjectManager::instance();
$Group = $pm->getProject($group_id);
if (isset ($group_id)) {

	if (!$Group || !is_object($Group)) {
		exit_error(_('Error'), 'Could Not Get Group');
	} elseif ($Group->isError()) {
		exit_no_group();
	}

	$mlFactory = new MailmanListFactory($Group);
	if (!$mlFactory || !is_object($mlFactory)) {
		exit_error(_('Error'), 'Could Not Get MailmanListFactory');
	}
	elseif ($mlFactory->isError()) {
		exit_error(_('Error'), $mlFactory->getErrorMessage());
	}

	mailman_header(array (
		'title' => _('Mailing Lists for') . $Group->getPublicName(),
		'help' => 'CommunicationServices.html#MailingLists',
		'pv' => isset ($pv) ? $pv : false
	));

	$mlArray = & $mlFactory->getMailmanLists();

	if ($mlFactory->isError()) {
		echo '<h1>' . _('Error') . ' ' . sprintf(_('Unable to get the list %s'), $Group->getPublicName()) . '</h1>';
		echo $mlFactory->getErrorMessage();
		mail_footer(array ());
		exit;
	}

	$mlCount = count($mlArray);
	if ($mlCount == 0) {
		echo '<p>' . sprintf(_('No Lists found for %1$s'), $Group->getPublicName()) . '</p>';
		echo '<p>' . _('Project administrators use the admin link to request mailing lists.') . '</p>';
		mail_footer(array ());
		exit;
	}


	if (isLogged()){
		if ($mlFactory->compareInfos()) {
			echo '<p>';
			echo _('You seem to have mailman account with a different name or password. If you want to update mailman information, click on ');
			echo '<a href="index.php?group_id=' . $group_id . '&action=update">' . _('Update') . '</a>';
			echo '</p>';
	}
	}

	echo '<p>';
	echo _('Choose a list to browse, search, and post messages.');
	echo '</p>';

	table_begin();
	for ($j = 0; $j < $mlCount; $j++) {
		$currentList = & $mlArray[$j];
		display_list($currentList);
	}

	table_end();
	if ($request->exist('action')) {
		if ($request->exist('id')) {
			$list = new MailmanList($group_id, $request->get('id'));
			switch ($request->get('action')) {
				case 'options' :
					$list->getOptionsURL();
					break;
				case 'subscribe' :
					$list->subscribe();
					break;
				case 'unsubscribe' :
					$list->unsubscribe();
					break;
				case 'pipermail' :
					$list->getArchivesUrl();
					break;
				case 'admin' :
					$list->getExternalAdminUrl();
					break;
				default :
					break;
			}
		}
		if ($request->get('action') == 'update') {
			$mlFactory->updateInfos();
		}

	}
	mail_footer(array ());

} else {

	exit_no_group();

}
?>
