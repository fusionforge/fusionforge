<?php
/**
 * GForge Mailing Lists Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003 (c) Guillaume Smet
 *
 * @version $Id$
 */

require_once('pre.php');
require_once('../mail/mail_utils.php');

require_once('common/include/escapingUtils.php');
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
		echo '<h1>'.$Language->getText('mail', 'no_list_found', array($Group->getPublicName())) .'</h1>';
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
		if ($currentList->isError()) {
			echo '<tr '. $HTML->boxGetAltRowStyle($j) .'><td colspan="2">';
			echo $currentList->getErrorMessage();
			echo '</td></tr>';
		} else if($currentList->getStatus()!='2') {
		        echo '<tr '. $HTML->boxGetAltRowStyle($j) .'><td colspan="2">';
			echo html_image('ic/cfolder15.png', '15', '13', array('border' => '0')).' &nbsp; ';
			echo $currentList->getName(). " is not Activated yet.";
			echo '</td></tr>';
		} else {
			echo '<tr '. $HTML->boxGetAltRowStyle($j) . '><td width="60%">'.
				'<a href="'.$currentList->getArchivesUrl().'">' .
				html_image('ic/cfolder15.png', '15', '13', array('border' => '0')).' &nbsp; '.
				$Language->getText('mail', 'archives', array($currentList->getName())).'</a><br />'.
				'&nbsp;'.  htmlspecialchars($currentList->getDescription()). '</td>'.
				'<td width="40%" align="center"><a href="'.$currentList->getExternalInfoUrl().'">'.$Language->getText('mail', 'external_administration').'</a>'.
				'</td></tr>';
		}
	}

	echo $HTML->listTableBottom();
	
	mail_footer(array());

} else {

	exit_no_group();

}

?>
