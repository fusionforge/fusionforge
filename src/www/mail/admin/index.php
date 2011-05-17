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
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */


require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'mail/admin/../mail_utils.php';

require_once $gfcommon.'mail/MailingList.class.php';
require_once $gfcommon.'mail/MailingListFactory.class.php';

$group_id = getIntFromRequest('group_id');

$feedback = getStringFromRequest('feedback');

if ($group_id) {
	$group = group_get_object($group_id);
	if (!$group || !is_object($group) || $group->isError()) {
		exit_no_group();
	}
	
	session_require_perm ('project_admin', $group->getID()) ;
	
	//
	//	Post Changes to database
	//
	if (getStringFromRequest('post_changes') == 'y') {
		//
		//	Add list
		//
		if (getStringFromRequest('add_list') == 'y') {

			if (check_email_available($group, $group->getUnixName() . '-' . getStringFromPost('list_name'), $error_msg)) {
				$mailingList = new MailingList($group);
			
				if (!form_key_is_valid(getStringFromRequest('form_key'))) {
					exit_form_double_submit('mail');
				}
				if(!$mailingList || !is_object($mailingList)) {
					form_release_key(getStringFromRequest("form_key"));
					exit_error(_('Error getting the list'),'mail');
				} elseif($mailingList->isError()) {
					form_release_key(getStringFromRequest("form_key"));
					exit_error($mailingList->getErrorMessage(),'mail');
				}
			
				if(!$mailingList->create(
					getStringFromPost('list_name'),
					getStringFromPost('description'),
					getIntFromPost('is_public', 1)
				)) {
					form_release_key(getStringFromRequest("form_key"));
					exit_error($mailingList->getErrorMessage(),'mail');
				} else {
					$feedback .= _('List Added');
				}
			}
			else {
				form_release_key(getStringFromRequest("form_key"));
			}
		//
		//	Change status
		//
		} elseif (getStringFromPost('change_status') == 'y') {
			$mailingList = new MailingList($group, getIntFromGet('group_list_id'));
			
			if(!$mailingList || !is_object($mailingList)) {
				exit_error(_('Error getting the list'),'mail');
			} elseif($mailingList->isError()) {
				exit_error($mailingList->getErrorMessage(),'mail');
			}
			
			if(!$mailingList->update(
				unInputSpecialChars(getStringFromPost('description')),
				getIntFromPost('is_public', MAIL__MAILING_LIST_IS_PUBLIC)
			)) {
				exit_error($mailingList->getErrorMessage(),'mail');
			} else {
				$feedback .= _('List updated');
			}
		}
	}

	//
	//	Reset admin password
	//
	if (getIntFromRequest('reset_pw') == 1) {
		$mailingList = new MailingList($group, getIntFromGet('group_list_id'));
		
		if(!$mailingList || !is_object($mailingList)) {
			exit_error(_('Error getting the list'),'mail');
		} elseif($mailingList->isError()) {
			exit_error($mailingList->getErrorMessage(),'mail');
		}
		
		if($mailingList->getStatus() == MAIL__MAILING_LIST_IS_CONFIGURED) {
			if(!$mailingList->update(
				   $mailingList->getDescription(),
				   $mailingList->isPublic(),
				   MAIL__MAILING_LIST_PW_RESET_REQUESTED
				   )) {
				exit_error($mailingList->getErrorMessage(),'mail');
			} else {
				$feedback .= _('Password reset requested');
			}
		}
	}


//
//	Form to add list
//
	if(getIntFromGet('add_list')) {
		mail_header(array('title' => _('Add a Mailing List')));
		print '<p>';
		printf(_('Lists are named in this manner:<br /><strong>projectname-listname@%1$s</strong>'), forge_get_config('lists_host'));
		print '</p>';

		print '<p>';
		print _('It will take <span class="important">6-24 Hours</span> for your list to be created.');
		print '</p>';
		
		$mlFactory = new MailingListFactory($group);
		if (!$mlFactory || !is_object($mlFactory) || $mlFactory->isError()) {
			exit_error($mlFactory->getErrorMessage(),'mail');
		}
		
		$mlArray = $mlFactory->getMailingLists();

		if ($mlFactory->isError()) {
			echo '<p class="error">'._('Error').' '._('Unable to get the lists') .$mlFactory->getErrorMessage().'</p>';
			mail_footer(array());
			exit;
		}
		
		$tableHeaders = array(
			_('Existing mailing lists')
		);
//
//	Show lists
//
		$mlCount = count($mlArray);
		if($mlCount > 0) {
			echo $HTML->listTableTop($tableHeaders);
			for ($j = 0; $j < $mlCount; $j++) {
				$currentList =& $mlArray[$j];
				if ($currentList->isError()) {
					echo '<tr '. $HTML->boxGetAltRowStyle($j) . '><td>';
					echo $currentList->getErrorMessage();
					echo '</td></tr>';
				} else {
					echo '<tr '. $HTML->boxGetAltRowStyle($j) . '><td>'.$currentList->getName().'</td></tr>';
				}
			}
			echo $HTML->listTableBottom();
		}
//
//	Form to add list
//
		?>
		<form method="post" action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id ?>">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="add_list" value="y" />
			<input type="hidden" name="form_key" value="<?php echo form_generate_key();?>" />
			<p><strong><?php echo _('Mailing List Name:'); ?></strong><br />
			<strong><?php echo $group->getUnixName(); ?>-<input type="text" name="list_name" value="" size="10" maxlength="12" />@<?php echo forge_get_config('lists_host'); ?></strong><br /></p>
			<p>
			<strong><?php echo _('Is Public?'); ?></strong><br />
			<input type="radio" name="is_public" value="<?php echo MAIL__MAILING_LIST_IS_PUBLIC; ?>" checked="checked" /> <?php echo _('Yes'); ?><br />
			<input type="radio" name="is_public" value="<?php echo MAIL__MAILING_LIST_IS_PRIVATE; ?>" /> <?php echo _('No'); ?></p><p>
			<strong><?php echo _('Description:'); ?></strong><br />
			<input type="text" name="description" value="" size="40" maxlength="80" /><br /></p>
			<p>
			<input type="submit" name="submit" value="<?php echo _('Add This List'); ?>" /></p>
		</form>
		<?php
		mail_footer(array());

//
//	Form to modify list
//
	} elseif(getIntFromGet('change_status') && getIntFromGet('group_list_id')) {
		$mailingList = new MailingList($group, getIntFromGet('group_list_id'));
			
		if(!$mailingList || !is_object($mailingList)) {
			exit_error(_('Error getting the list'),'mail');
		} elseif($mailingList->isError()) {
			exit_error($mailingList->getErrorMessage(),'mail');
		}
   	
		mail_header(array(
			'title' => _('Mail admin')));
		?>
		<h3><?php echo $mailingList->getName(); ?></h3>
		<form method="post" action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;group_list_id=<?php echo $mailingList->getID(); ?>">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="change_status" value="y" />
			<p>
			<strong><?php echo _('Is Public?'); ?></strong><br />
			<input type="radio" name="is_public" value="<?php echo MAIL__MAILING_LIST_IS_PUBLIC; ?>"<?php echo ($mailingList->isPublic() == MAIL__MAILING_LIST_IS_PUBLIC ? ' checked="checked"' : ''); ?> /> <?php echo _('Yes'); ?><br />
			<input type="radio" name="is_public" value="<?php echo MAIL__MAILING_LIST_IS_PRIVATE; ?>"<?php echo ($mailingList->isPublic() == MAIL__MAILING_LIST_IS_PRIVATE ? ' checked="checked"' : ''); ?> /> <?php echo _('No'); ?>
			</p>
			<p><strong><?php echo _('Description:'); ?></strong><br />
			<input type="text" name="description" value="<?php echo inputSpecialChars($mailingList->getDescription()); ?>" size="40" maxlength="80" /><br /></p>
			<p>
			<input type="submit" name="submit" value="<?php echo _('Update'); ?>" /></p>
		</form>
		<a href="deletelist.php?group_id=<?php echo $group_id; ?>&amp;group_list_id=<?php echo $mailingList->getID(); ?>">[<?php echo _('Permanently Delete List'); ?>]</a>
	<?php
		mail_footer(array());
	} else {
//
//	Show lists
//
		$mlFactory = new MailingListFactory($group);
		if (!$mlFactory || !is_object($mlFactory) || $mlFactory->isError()) {
			exit_error($mlFactory->getErrorMessage(),'mail');
		}

		mail_header(array(
			'title' => _('Mailing List Administration'))
		);

		$mlArray = $mlFactory->getMailingLists();

		if ($mlFactory->isError()) {
			echo '<p>'._('Error').' '.sprintf(_('Unable to get the list %s'), $group->getPublicName()) .'</p>';
			echo '<div class="error">'.$mlFactory->getErrorMessage().'</div>';
			mail_footer(array());
			exit;
		}
		echo '<p>'.sprintf(_('You can administrate lists from here. Please note that private lists can still be viewed by members of your project, but are not listed on %1$s.'), forge_get_config ('forge_name')).'</p>';
		echo '<ul>
			<li>
				<a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;add_list=1">'._('Add Mailing List').'</a>
			</li>
		</ul>';
		$mlCount = count($mlArray);
		if($mlCount > 0) {
			$tableHeaders = array(
				_('Mailing list'),
				'',
				'',
				''
			);
			echo $HTML->listTableTop($tableHeaders);
			for ($i = 0; $i < $mlCount; $i++) {
				$currentList =& $mlArray[$i];
				if ($currentList->isError()) {
					echo '<tr '. $HTML->boxGetAltRowStyle($i) .'><td colspan="4">';
					echo $currentList->getErrorMessage();
					echo '</td></tr>';
				} else {
					echo '<tr '. $HTML->boxGetAltRowStyle($i) . '><td width="60%">'.
					'<strong>'.$currentList->getName().'</strong><br />'.
					htmlspecialchars($currentList->getDescription()).'</td>'.
					'<td style="text-align:center"><a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;group_list_id='.$currentList->getID().'&amp;change_status=1">'._('Update').'</a></td>' ;
					echo '<td style="text-align:center">';
					if($currentList->getStatus() == MAIL__MAILING_LIST_IS_REQUESTED) {
						echo _('Not activated yet');
					} else {
						echo '<a href="'.$currentList->getExternalAdminUrl().'">'._('Administration').'</a>';
					}
					echo '</td>';
					echo '<td style="text-align:center">';
					if($currentList->getStatus() == MAIL__MAILING_LIST_IS_CONFIGURED) {
						print '<a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;group_list_id='.$currentList->getID().'&amp;reset_pw=1">'._('Reset admin password').'</a></td>' ;

					}
					echo '</tr>';
				}
			}
			echo $HTML->listTableBottom();
		}
		mail_footer(array());
	}
} else {
	exit_no_group();
}
?>
