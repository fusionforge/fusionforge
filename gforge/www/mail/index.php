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
require_once('../mail/mail_utils.php');

require_once('common/mail/MailingList.class');
require_once('common/mail/MailingListFactory.class');

$group_id = getIntFromGet('group_id');

if ($group_id) {
	$Group =& group_get_object($group_id);
	if (!$Group || !is_object($Group) || $Group->isError()) {
		exit_no_group();
	}
	
	$mlFactory = new MailingListFactory($Group);
	if (!$mlFactory || !is_object($mlFactory) || $mlFactory->isError()) {
		exit_error($Language->getText('general', 'error'), $mlFactory->getErrorMessage());
	}

	mail_header(array(
		'title' => $Language->getText('mail', 'mailinglists_for', array($Group->getPublicName())),
		'pagename' => 'mail',
		'sectionvals' => array($Group->getPublicName())
	));


	$mlArray =& $mlFactory->getMailingLists();

	if ($mlFactory->isError()) {
		echo '<h1>'.$Language->getText('general', 'error').' '.$Language->getText('mail', 'unable_to_get_lists', array($Group->getPublicName())) .'</h1>';
		echo $mlFactory->getErrorMessage();
		mail_footer(array());
		exit;
	}
	
	$mlCount = count($mlArray);
	if($mlCount == 0) {
		echo '<p>'.$Language->getText('mail', 'no_list_found', array($Group->getPublicName())) .'</p>';
		echo '<p>'.$Language->getText('mail', 'help_to_request').'</p>';
		mail_footer(array());
		exit;
	}
	
	echo $Language->getText('mail', 'provided_by');
	echo $Language->getText('mail', 'choose_a_list');
	
	$tableHeaders = array(
		$Language->getText('mail_common', 'mailing_list'),
		''
	);
	echo $HTML->listTableTop($tableHeaders);

	for ($j = 0; $j < $mlCount; $j++) {
		$currentList =& $mlArray[$j];
		echo '<tr '. $HTML->boxGetAltRowStyle($j) .'>';
		if ($currentList->isError()) {
			echo '<td colspan="2">'.$currentList->getErrorMessage().'</td></tr>';
		} else if($currentList->getStatus() == MAIL__MAILING_LIST_IS_REQUESTED) {
			echo '<td width="60%">'.
				'<strong>'.$currentList->getName().'</strong><br />'.
				htmlspecialchars($currentList->getDescription()). '</td>'.
				'<td width="40%" align="center">'.$Language->getText('mail_common', 'list_not_activated').'</td></tr>';
		} else {
			echo '<td width="60%">'.
				'<strong><a href="'.$currentList->getArchivesUrl().'">' .
				$Language->getText('mail', 'archives', array($currentList->getName())).'</a></strong><br />'.
				htmlspecialchars($currentList->getDescription()). '</td>'.
				'<td width="40%" align="center"><a href="'.$currentList->getExternalInfoUrl().'">'.$Language->getText('mail', 'external_administration').'</a>'.
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
