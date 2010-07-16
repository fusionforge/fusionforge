<?php
/**
 * GForge Mailing Lists Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) Guillaume Smet - Open Wide
 *
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'mail/../mail/mail_utils.php';

require_once $gfcommon.'mail/MailingList.class.php';
require_once $gfcommon.'mail/MailingListFactory.class.php';

$group_id = getIntFromGet('group_id');

if ($group_id) {
	$Group =& group_get_object($group_id);
	if (!$Group || !is_object($Group)) {
		exit_error(_('Error'), 'Could Not Get Group');
	} elseif ($Group->isError()) {
		exit_no_group();
	}
	
	$mlFactory = new MailingListFactory($Group);
	if (!$mlFactory || !is_object($mlFactory)) {
		exit_error(_('Error'), 'Could Not Get MailingListFactory');
	} elseif ($mlFactory->isError()) {
		exit_error(_('Error'), $mlFactory->getErrorMessage());
	}

	mail_header(array(
		'title' => sprintf(_('Mailing Lists for %1$s'), $Group->getPublicName())
	));

	plugin_hook ("blocks", "mail index");

	$mlArray =& $mlFactory->getMailingLists();

	if ($mlFactory->isError()) {
		echo '<h1>'._('Error').' '.sprintf(_('Unable to get the list %s'), $Group->getPublicName()) .'</h1>';
		echo $mlFactory->getErrorMessage();
		mail_footer(array());
		exit;
	}
	
	$mlCount = count($mlArray);
	if($mlCount == 0) {
		echo '<p>'.sprintf(_('No Lists found for %1$s'), $Group->getPublicName()) .'</p>';
		echo '<p>'._('Project administrators use the admin link to request mailing lists.').'</p>';
		mail_footer(array());
		exit;
	}
	
	echo _('<p>Choose a list to browse, search, and post messages.</p>');
	
	$tableHeaders = array(
		_('Mailing list'),
		_('Description'),
		_('Subscription')
	);
	echo $HTML->listTableTop($tableHeaders);

	for ($j = 0; $j < $mlCount; $j++) {
		$currentList =& $mlArray[$j];
		echo '<tr '. $HTML->boxGetAltRowStyle($j) .'>';
		if ($currentList->isError()) {
			echo '<td colspan="3">'.$currentList->getErrorMessage().'</td></tr>';
		} else if($currentList->getStatus() == MAIL__MAILING_LIST_IS_REQUESTED) {
			echo '<td width="33%">'.
				'<strong>'.$currentList->getName().'</strong></td>'.
				'<td width="33%">'.htmlspecialchars($currentList->getDescription()). '</td>'.
				'<td width="33%" style="text-align:center">'._('Not activated yet').'</td></tr>';
		} else {
			echo '<td width="33%">'.
				'<strong><a href="'.$currentList->getArchivesUrl().'">' .
				sprintf(_('%1$s Archives'), $currentList->getName()).'</a></strong></td>'.
				'<td>'.htmlspecialchars($currentList->getDescription()). '</td>'.
				'<td width="33%" style="text-align:center"><a href="'.$currentList->getExternalInfoUrl().'">'._('Subscribe/Unsubscribe/Preferences').'</a>'.
				'</td>';
		}
		echo '</tr>';
	}

	echo $HTML->listTableBottom();
	
	mail_footer(array());

} else {

	exit_no_group();

}

?>
