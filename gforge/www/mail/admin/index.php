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
require_once('../mail_utils.php');

require_once('common/include/escapingUtils.php');
require_once('common/mail/MailingList.class');
require_once('common/mail/MailingListFactory.class');

$group_id = getIntFromGet('group_id');

$feedback = '';

if ($group_id) {
	$Group =& group_get_object($group_id);
	if (!$Group || !is_object($Group) || $Group->isError()) {
		exit_no_group();
	}
	
	if(! MailingListFactory::userCanAdminMailingLists($Group)) {
		exit_permission_denied();
	}
	
	if (getStringFromPost('post_changes') == 'y') {
		if (getStringFromPost('add_list') == 'y') {
			$mailingList = new MailingList($Group);
			
			if(!$mailingList || !is_object($mailingList)) {
				exit_error($Language->getText('general', 'error'), $Language->getText('mail_admin', 'error_getting_list'));
			} elseif($mailingList->isError()) {
				exit_error($Language->getText('general', 'error'), $mailingList->getErrorMessage());
			}
			
			if(!$mailingList->create(
				getStringFromPost('list_name'),
				getStringFromPost('description'),
				getIntFromPost('is_public', 1)
			)) {
				exit_error($Language->getText('general', 'error'), $mailingList->getErrorMessage());
			} else {
				$feedback .= $Language->getText('mail_admin_addlist', 'list_added');
			}
			
		} elseif (getStringFromPost('change_status') == 'y') {
			$mailingList = new MailingList($Group, getIntFromPost('group_list_id'));
			
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

	if(getIntFromGet('add_list')) {
		mail_header(array(
			'title' => $Language->getText('mail_admin_addlist', 'pagetitle'),
			'pagename' => 'mail_admin_addlist'
		));

		echo $Language->getText('mail_admin_addlist', 'list_information', array($GLOBALS['sys_lists_host']));
		
		$mlFactory = new MailingListFactory($Group);
		if (!$mlFactory || !is_object($mlFactory) || $mlFactory->isError()) {
			exit_error($Language->getText('general','error'), $mlFactory->getErrorMessage());
		}
		
		$mlArray =& $mlFactory->getMailingLists(true);

		if ($mlFactory->isError()) {
			echo '<h1>'.$Language->getText('general','error').' '.$Language->getText('mail', 'unable_to_get_lists') .'</h1>';
			echo $mlFactory->getErrorMessage();
			mail_footer(array());
			exit;
		}
		
		$tableHeaders = array(
			$Language->getText('mail_admin_addlist', 'existing_mailing_lists')
		);

		echo $HTML->listTableTop($tableHeaders);

		$mlCount = count($mlArray);
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
		
		?>
		<form method="post" action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id ?>">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="add_list" value="y" />
			<p><strong><?php echo $Language->getText('mail_admin_addlist', 'form_name'); ?></strong><br />
			<strong><?php echo $Group->getUnixName(); ?>-<input type="text" name="list_name" value="" size="10" maxlength="12" />@<?php echo $GLOBALS['sys_lists_host']; ?></strong><br /></p>
			<p>
			<strong><?php echo $Language->getText('mail_admin_addlist', 'form_ispublic'); ?></strong><br />
			<input type="radio" name="is_public" value="<?php echo MAIL__MAILING_LIST_IS_PUBLIC; ?>" checked="checked" /> <?php echo $Language->getText('general', 'yes'); ?><br />
			<input type="radio" name="is_public" value="<?php echo MAIL__MAILING_LIST_IS_PRIVATE; ?>" /> <?php echo $Language->getText('general', 'no'); ?></p><p>
			<strong><?php echo $Language->getText('mail_admin_addlist', 'form_description'); ?></strong><br />
			<input type="text" name="description" value="" size="40" maxlength="80" /><br /></p>
			<p>
			<strong><span style="color:red"><?php echo $Language->getText('mail_admin_addlist', 'warning'); ?></span></strong></p>
			<p>
			<input type="submit" name="submit" value="<?php echo $Language->getText('mail_admin_addlist', 'form_addlist'); ?>" /></p>
			</form>
		<?php

	} elseif(getIntFromGet('change_status')) {
		
		$mlFactory = new MailingListFactory($Group);

		if(!$mlFactory || !is_object($mlFactory) || $mlFactory->isError()) {
      	exit_error($Language->getText('general','error'), $mlFactory->getErrorMessage());
   	}
   	
   	mail_header(array(
			'title' => $Language->getText('mail_admin_updatelist', 'pagetitle'),
			'pagename' => 'mail_admin_updatelist'
		));
   	
   	$mlArray =& $mlFactory->getMailingLists(true);
   	$mlCount = count($mlArray);
   	
		if($mlCount == 0) {
			echo '<p>'.$Language->getText('mail_admin_updatelist', 'no_list_found').'</p>';
		} else {
			echo '<p>'.$Language->getText('mail_admin_updatelist', 'information', array($GLOBALS['sys_name'])).'</p>';

			$tableHeaders = array();
			$tableHeaders[] = $Language->getText('mail_admin_updatelist', 'list');
			$tableHeaders[] = $Language->getText('mail_admin_updatelist', 'status');
			$tableHeaders[] = '';
			$tableHeaders[] = '';

			echo $HTML->listTableTop($tableHeaders);

			for ($i = 0; $i < $mlCount; $i++) {
				$currentList =& $mlArray[$i];
				?>
					<tr <?php echo $HTML->boxGetAltRowStyle($i); ?>>
						<td><?php echo $currentList->getName(); ?></td>
						<td colspan="3">
							<form action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id ?>" method="post">
								<input type="hidden" name="post_changes" value="y" />
								<input type="hidden" name="change_status" value="y" />
								<input type="hidden" name="group_list_id" value="<?php echo $currentList->getID(); ?>" />
								<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
							<table width="100%">
								<tr>
									<td>
										<div style="font-size:smaller">
										<strong><?php echo $Language->getText('mail_admin_updatelist', 'form_ispublic'); ?></strong><br />
										<input type="radio" name="is_public" value="<?php echo MAIL__MAILING_LIST_IS_PUBLIC; ?>"<?php echo ($currentList->isPublic() == MAIL__MAILING_LIST_IS_PUBLIC ? ' checked="checked"' : ''); ?> /> <?php echo $Language->getText('general', 'yes'); ?><br />
										<input type="radio" name="is_public" value="<?php echo MAIL__MAILING_LIST_IS_PRIVATE; ?>"<?php echo ($currentList->isPublic() == MAIL__MAILING_LIST_IS_PRIVATE ? ' checked="checked"' : ''); ?> /> <?php echo $Language->getText('general', 'no'); ?><br />
										<input type="radio" name="is_public" value="<?php echo MAIL__MAILING_LIST_IS_DELETED; ?>"<?php echo ($currentList->isPublic() == MAIL__MAILING_LIST_IS_DELETED ? ' checked="checked"' : ''); ?> /> <?php echo $Language->getText('general', 'deleted'); ?><br />
										</div>
									</td>
									<td align="right">
										<div style="font-size:smaller">
											<input type="submit" name="submit" value="<?php echo $Language->getText('mail_admin_updatelist', 'form_updatelist'); ?>" />
										</div>
									</td>
									<td align="center">
										<a href="<?php echo $currentList->getExternalAdminUrl(); ?>">
											<?php echo $Language->getText('mail_admin_updatelist', 'admin_in_mailman'); ?>
										</a>
				       			</td>
				       		</tr>
				       		<tr <?php echo $HTML->boxGetAltRowStyle($i); ?>>
				       			<td colspan="3">
				       				<strong><?php echo $Language->getText('mail_admin_updatelist', 'form_description'); ?></strong><br />
										<input type="text" name="description" value="<?php echo inputSpecialChars($currentList->getDescription()); ?>" size="40" maxlength="80" /><br />
									</td>
								</tr>
							</table>
							</form>
						</td>
					</tr>
				<?php
			}

			echo $HTML->listTableBottom();

		}

	} else {
		/*
			Show main page for choosing
			either add or update
		*/
		mail_header(array(
			'title' => $Language->getText('mail_admin', 'pagetitle'),
			'pagename' => 'mail_admin')
		);
		?>
		<ul>
			<li>
				<a href="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;add_list=1"><?php echo $Language->getText('mail_admin', 'add_list'); ?></a>
			</li>
			<li>
				<a href="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;change_status=1"><?php echo $Language->getText('mail_admin', 'update_list'); ?></a>
			</li>
		</ul>
		<?php
	}
	
	mail_footer(array());
	
} else {
	exit_no_group();
}
?>