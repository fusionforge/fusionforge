<?php

/**
 * GForge Mailing Lists Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) Guillaume Smet - Open Wide
 *
 * @version $Id$
 */

require_once('../../env.inc.php');
require_once('pre.php');
require_once('../mail_utils.php');

require_once('common/mail/MailingList.class');
require_once('common/mail/MailingListFactory.class');

$group_id = getIntFromRequest('group_id');

$feedback = '';

if ($group_id) {
	$Group =& group_get_object($group_id);
	if (!$Group || !is_object($Group) || $Group->isError()) {
		exit_no_group();
	}
	
	$perm =& $Group->getPermission(session_get_user());
	if (!$perm || !is_object($perm) || $perm->isError() || !$perm->isAdmin()) {
		exit_permission_denied();
	}
	
//
//	Post Changes to database
//
	if (getStringFromRequest('post_changes') == 'y') {
		//
		//	Add list
		//
		if (getStringFromRequest('add_list') == 'y') {
			$mailingList = new MailingList($Group);
			
			if (!form_key_is_valid(getStringFromRequest('form_key'))) {
				exit_form_double_submit();
			}
			if(!$mailingList || !is_object($mailingList)) {
				form_release_key(getStringFromRequest("form_key"));
				exit_error($Language->getText('general', 'error'), $Language->getText('mail_admin', 'error_getting_list'));
			} elseif($mailingList->isError()) {
				form_release_key(getStringFromRequest("form_key"));
				exit_error($Language->getText('general', 'error'), $mailingList->getErrorMessage());
			}
			
			if(!$mailingList->create(
				getStringFromPost('list_name'),
				getStringFromPost('description'),
				getIntFromPost('is_public', 1)
			)) {
				form_release_key(getStringFromRequest("form_key"));
				exit_error($Language->getText('general', 'error'), $mailingList->getErrorMessage());
			} else {
				$feedback .= $Language->getText('mail_admin_addlist', 'list_added');
			}
		//
		//	Change status
		//
		} elseif (getStringFromPost('change_status') == 'y') {
			$mailingList = new MailingList($Group, getIntFromGet('group_list_id'));
			
			if(!$mailingList || !is_object($mailingList)) {
				exit_error($Language->getText('general', 'error'), $Language->getText('mail_admin', 'error_getting_list'));
			} elseif($mailingList->isError()) {
				exit_error($Language->getText('general', 'error'), $mailingList->getErrorMessage());
			}
			
			if(!$mailingList->update(
				unInputSpecialChars(getStringFromPost('description')),
				getIntFromPost('is_public', MAIL__MAILING_LIST_IS_PUBLIC)
			)) {
				exit_error($Language->getText('general', 'error'), $mailingList->getErrorMessage());
			} else {
				$feedback .= $Language->getText('mail_admin_updatelist', 'list_updated');
			}
		}

	}

//
//	Form to add list
//
	if(getIntFromGet('add_list')) {
		mail_header(array(
			'title' => $Language->getText('mail_admin_addlist', 'pagetitle')));
		echo $Language->getText('mail_admin_addlist', 'list_information', array($GLOBALS['sys_lists_host']));
		
		$mlFactory = new MailingListFactory($Group);
		if (!$mlFactory || !is_object($mlFactory) || $mlFactory->isError()) {
			exit_error($Language->getText('general','error'), $mlFactory->getErrorMessage());
		}
		
		$mlArray =& $mlFactory->getMailingLists();

		if ($mlFactory->isError()) {
			echo '<h1>'.$Language->getText('general','error').' '.$Language->getText('mail', 'unable_to_get_lists') .'</h1>';
			echo $mlFactory->getErrorMessage();
			mail_footer(array());
			exit;
		}
		
		$tableHeaders = array(
			$Language->getText('mail_admin_addlist', 'existing_mailing_lists')
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
			<input type="hidden" name="form_key" value="<?php echo form_generate_key();?>">
			<p><strong><?php echo $Language->getText('mail_admin_addlist', 'form_name'); ?></strong><br />
			<strong><?php echo $Group->getUnixName(); ?>-<input type="text" name="list_name" value="" size="10" maxlength="12" />@<?php echo $GLOBALS['sys_lists_host']; ?></strong><br /></p>
			<p>
			<strong><?php echo $Language->getText('mail_admin_addlist', 'form_ispublic'); ?></strong><br />
			<input type="radio" name="is_public" value="<?php echo MAIL__MAILING_LIST_IS_PUBLIC; ?>" checked="checked" /> <?php echo $Language->getText('general', 'yes'); ?><br />
			<input type="radio" name="is_public" value="<?php echo MAIL__MAILING_LIST_IS_PRIVATE; ?>" /> <?php echo $Language->getText('general', 'no'); ?></p><p>
			<strong><?php echo $Language->getText('mail_admin_addlist', 'form_description'); ?></strong><br />
			<input type="text" name="description" value="" size="40" maxlength="80" /><br /></p>
			<p>
			<input type="submit" name="submit" value="<?php echo $Language->getText('mail_admin_addlist', 'form_addlist'); ?>" /></p>
		</form>
		<?php
		mail_footer(array());

//
//	Form to modify list
//
	} elseif(getIntFromGet('change_status') && getIntFromGet('group_list_id')) {
		$mailingList = new MailingList($Group, getIntFromGet('group_list_id'));
			
		if(!$mailingList || !is_object($mailingList)) {
			exit_error($Language->getText('general', 'error'), $Language->getText('mail_admin', 'error_getting_list'));
		} elseif($mailingList->isError()) {
			exit_error($Language->getText('general', 'error'), $mailingList->getErrorMessage());
		}
   	
		mail_header(array(
			'title' => $Language->getText('mail_admin_updatelist', 'pagetitle')));
		?>
		<h3><?php echo $mailingList->getName(); ?></h3>
		<form method="post" action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;group_list_id=<?php echo $mailingList->getID(); ?>">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="change_status" value="y" />
			<p>
			<strong><?php echo $Language->getText('mail_admin_updatelist', 'form_ispublic'); ?></strong><br />
			<input type="radio" name="is_public" value="<?php echo MAIL__MAILING_LIST_IS_PUBLIC; ?>"<?php echo ($mailingList->isPublic() == MAIL__MAILING_LIST_IS_PUBLIC ? ' checked="checked"' : ''); ?> /> <?php echo $Language->getText('general', 'yes'); ?><br />
			<input type="radio" name="is_public" value="<?php echo MAIL__MAILING_LIST_IS_PRIVATE; ?>"<?php echo ($mailingList->isPublic() == MAIL__MAILING_LIST_IS_PRIVATE ? ' checked="checked"' : ''); ?> /> <?php echo $Language->getText('general', 'no'); ?><br />
			</p>
			<p><strong><?php echo $Language->getText('mail_admin_updatelist', 'form_description'); ?></strong><br />
			<input type="text" name="description" value="<?php echo inputSpecialChars($mailingList->getDescription()); ?>" size="40" maxlength="80" /><br /></p>
			<p>
			<input type="submit" name="submit" value="<?php echo $Language->getText('mail_admin_updatelist', 'form_updatelist'); ?>" /></p>
		</form>
		<a href="deletelist.php?group_id=<?php echo $group_id; ?>&amp;group_list_id=<?php echo $mailingList->getID(); ?>">[<?php echo $Language->getText('mail_admin_deletelist', 'title'); ?>]</a>
	<?php
		mail_footer(array());
	} else {
//
//	Show lists
//
		$mlFactory = new MailingListFactory($Group);
		if (!$mlFactory || !is_object($mlFactory) || $mlFactory->isError()) {
			exit_error($Language->getText('general', 'error'), $mlFactory->getErrorMessage());
		}

		mail_header(array(
			'title' => $Language->getText('mail_admin', 'pagetitle'))
		);

		$mlArray =& $mlFactory->getMailingLists();

		if ($mlFactory->isError()) {
			echo '<p>'.$Language->getText('general', 'error').' '.$Language->getText('mail', 'unable_to_get_lists', array($Group->getPublicName())) .'</p>';
			echo $mlFactory->getErrorMessage();
			mail_footer(array());
			exit;
		}
		echo '<p>'.$Language->getText('mail_admin_updatelist', 'information', array($GLOBALS['sys_name'])).'</p>';
		echo '<ul>
			<li>
				<a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;add_list=1">'.$Language->getText('mail_admin', 'add_list').'</a>
			</li>
		</ul>';
		$mlCount = count($mlArray);
		if($mlCount > 0) {
			$tableHeaders = array(
				$Language->getText('mail_common', 'mailing_list'),
				'',
				''
			);
			echo $HTML->listTableTop($tableHeaders);
			for ($i = 0; $i < $mlCount; $i++) {
				$currentList =& $mlArray[$i];
				if ($currentList->isError()) {
					echo '<tr '. $HTML->boxGetAltRowStyle($i) .'><td colspan="3">';
					echo $currentList->getErrorMessage();
					echo '</td></tr>';
				} else {
					echo '<tr '. $HTML->boxGetAltRowStyle($i) . '><td width="60%">'.
					'<strong>'.$currentList->getName().'</strong><br />'.
					htmlspecialchars($currentList->getDescription()).'</td>'.
					'<td width="20%" align="center"><a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;group_list_id='.$currentList->getID().'&amp;change_status=1">'.$Language->getText('mail_admin', 'update_list').'</a></td>'.
					'<td width="20%" align="center">';
					if($currentList->getStatus() == MAIL__MAILING_LIST_IS_REQUESTED) {
						echo $Language->getText('mail_common', 'list_not_activated');
					} else {
						echo '<a href="'.$currentList->getExternalAdminUrl().'">'.$Language->getText('mail_admin', 'admin_in_mailman').'</a></td>';
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
