<?php
/**
 * Mailing Lists Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2003-2004 (c) Guillaume Smet - Open Wide
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
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
require_once $gfwww.'mail/../mail/mail_utils.php';

require_once $gfcommon.'mail/MailingList.class.php';
require_once $gfcommon.'mail/MailingListFactory.class.php';

$group_id = getIntFromGet('group_id');

if ($group_id) {
	$group = group_get_object($group_id);
	if (!$group || !is_object($group)) {
		exit_no_group();
	} elseif ($group->isError()) {
		exit_error($group->getErrorMessage(),'mail');
	}
	
	$mlFactory = new MailingListFactory($group);
	if (!$mlFactory || !is_object($mlFactory)) {
		exit_error(_('Could Not Get MailingListFactory'),'mail');
	} elseif ($mlFactory->isError()) {
		exit_error($mlFactory->getErrorMessage(),'mail');
	}

	mail_header(array(
		'title' => sprintf(_('Mailing Lists for %1$s'), $group->getPublicName())
	));

	plugin_hook ("blocks", "mail index");

	$mlArray = $mlFactory->getMailingLists();

	if ($mlFactory->isError()) {
		echo '<p class="error">'.sprintf(_('Unable to get the list %s : %s'), $group->getPublicName(), $mlFactory->getErrorMessage()) .'</p>';
		mail_footer(array());
		exit;
	}
	
	$mlCount = count($mlArray);
	if($mlCount == 0) {
		echo '<p>'.sprintf(_('No Lists found for %1$s'), $group->getPublicName()) .'</p>';
		echo '<p>'._('Project administrators use the admin link to request mailing lists.').'</p>';
		mail_footer(array());
		exit;
	}
	
	echo '<p>' . _('Choose a list to browse, search, and post messages.') . '</p>';
	
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
			echo '<td colspan="3">'.$currentList->getErrorMessage().'</td>';
		} else if($currentList->getStatus() == MAIL__MAILING_LIST_IS_REQUESTED) {
			echo '<td width="33%">'.
				'<strong>'.$currentList->getName().'</strong></td>'.
				'<td width="33%">'.htmlspecialchars($currentList->getDescription()). '</td>'.
				'<td width="33%" style="text-align:center">'._('Not activated yet').'</td>';
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
