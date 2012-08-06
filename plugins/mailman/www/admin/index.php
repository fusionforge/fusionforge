<?php

/**
 * Fusionforge Mailing Lists Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) Guillaume Smet - Open Wide
 *
 * @version $Id$
 *
 * Portions Copyright 2010 (c) Mélanie Le Bail
 */
require_once 'env.inc.php';
require_once 'pre.php';
require_once 'preplugins.php';
require_once 'plugins_utils.php';
require_once '../mailman_utils.php';

$request =& HTTPRequest::instance();
$group_id=$request->get('group_id');
$group_list_id=$request->get('group_list_id');
$pm = ProjectManager::instance();
$Group = $pm->getProject($group_id);
$post_changes= $request->get('post_changes');
$add_list=$request->get('add_list');
$action=$request->get('action');
$change_status=$request->get('change_status');
$list_name=$request->get('list_name');
$is_public=$request->get('is_public');
$description=$request->get('description');
$PHP_SELF = $request->get('PHP_SELF');
$feedback = '';


if ($group_id) {
	if (!$Group || !is_object($Group) || $Group->isError()) {

		exit_no_group();
	}

	if (!$current_user->isMember($group_id,'A')) {
		exit_permission_denied();
	}
	//
	//	RE-CREATE List with problems
	//
	if($action=='recreate') {
		$mailingList = new MailmanList($group_id, $group_list_id);
		if(!$mailingList || !is_object($mailingList)) {
			exit_error(_('Error'), _('Error getting the list'));
			echo 'error';
		} elseif($mailingList->isError()) {
			exit_error(_('Error'), $mailingList->getErrorMessage());
			echo 'error';
		}
		$mailingList->recreate();
		$feedback .=_('List re-created');
		htmlRedirect('/plugins/mailman/index.php?group_id='.$group_id);

	}

	//
	//	Post Changes to database
	//
	if ($post_changes == 'y') {
		//
		//	Add list
		//
		if ($add_list == 'y') {
			$mailingList = new MailmanList($group_id);

			if(!$mailingList || !is_object($mailingList)) {
				exit_error(_('Error'), _('Error getting the list'));
			} elseif($mailingList->isError()) {
				exit_error(_('Error'), $mailingList->getErrorMessage());
			}

			if(!$mailingList->create(
						$list_name,
						$description,
						$is_public
						)) {
				exit_error(_('Error'), $mailingList->getErrorMessage());
			} else {
				$feedback .= _('List Added');
			}
			//
			//	Change status
			//
		} elseif ($change_status == 'y') {
			$mailingList = new MailmanList($group_id, $group_list_id);

			if(!$mailingList || !is_object($mailingList)) {
				exit_error(_('Error'), _('Error getting the list'));
			} elseif($mailingList->isError()) {
				exit_error(_('Error'), $mailingList->getErrorMessage());
			}

			if(!$mailingList->update(
						$description,
						$is_public
						)) {

				exit_error(_('Error'), $mailingList->getErrorMessage());
			} else {
				$feedback .= _('List updated');
			}
		}
	}

	//
	//	Form to add list
	//
	if($add_list) {
		mailman_header(array(
					'title' => _('Add a Mailing List'),
					'help'=>'CommunicationServices.html#MailingLists',
					'admin' => '1'));
		echo '<p>';
		printf(_('Lists are named in this manner:<br /><strong>projectname-listname@%1$s</strong></p><p>It will take <span class="important">few minutes</span> for your list to be created.'), forge_get_config('lists_host'));
		echo '</p>';

		$mlFactory = new MailmanListFactory($Group);
		if (!$mlFactory || !is_object($mlFactory) || $mlFactory->isError()) {
			exit_error(_('Error'), $mlFactory->getErrorMessage());
		}

		$mlArray =& $mlFactory->getMailmanLists();

		if ($mlFactory->isError()) {
			echo '<h1>'._('Error').' '._('Unable to get the lists') .'</h1>';
			echo $mlFactory->getErrorMessage();
			mail_footer(array());
			exit;
		}

		//
		//	Form to add list
		//
		?>
			<form method="post" action="<?php echo $PHP_SELF; ?>?group_id=<?php echo $group_id ?>">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="add_list" value="y" />
			<p><strong><?php echo _('Mailing List Name:'); ?></strong><br />
			<strong><?php echo $Group->getUnixName(); ?>-<input type="text" name="list_name" value="" size="10" maxlength="12" />@<?php echo forge_get_config('lists_host'); ?></strong><br /></p>
			<p>
			<strong><?php echo _('Is Public?'); ?></strong><br />
			<input type="radio" name="is_public" value="1" checked="checked" /> <?php echo _('Yes'); ?><br />
			<input type="radio" name="is_public" value="0" /> <?php echo _('No'); ?></p><p>
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
	} elseif($change_status && $group_list_id) {
		$mailingList = new MailmanList($group_id, $group_list_id);
		if(!$mailingList || !is_object($mailingList)) {
			exit_error(_('Error'), _('Error getting the list'));
		} elseif($mailingList->isError()) {
			exit_error(_('Error'), $mailingList->getErrorMessage());
		}
		mailman_header(array(
					'title' => _('Mail admin'),
					'help'=>'CommunicationServices.html#MailingLists',
					'admin' => 1));
		?>

			<h3><?php echo $mailingList->getName(); ?></h3>
			<form method="post" action="<?php echo $PHP_SELF; ?>?group_id=<?php echo $group_id; ?>&amp;group_list_id=<?php echo $mailingList->getID(); ?>">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="change_status" value="y" />
			<p>
			<strong><?php echo _('Is Public?'); ?></strong><br />
			<input type="radio" name="is_public" value="1"<?php echo ($mailingList->isPublic() == 1 ? ' checked="checked"' : ''); ?> /> <?php echo _('Yes'); ?><br />
			<input type="radio" name="is_public" value="0"<?php echo ($mailingList->isPublic() == 0 ? ' checked="checked"' : ''); ?> /> <?php echo _('No'); ?><br />
			</p>
			<p><strong><?php echo _('Description:'); ?></strong><br />
			<input type="text" name="description" value="<?php echo $mailingList->getDescription(); ?>" size="40" maxlength="80" /><br /></p>
			<p>
			<input type="submit" name="submit" value="<?php echo _('Update'); ?>" /></p>
			</form>
			<?php
			mail_footer(array());
	} else {
		//
		//	Show lists
		//
		$mlFactory = new MailmanListFactory($Group);
		if (!$mlFactory || !is_object($mlFactory) || $mlFactory->isError()) {
			exit_error(_('Error'), $mlFactory->getErrorMessage());
		}

		mailman_header(array(
					'title' => _('Mailing List Administration'),
					'help'=>'CommunicationServices.html#MailingLists',
					'admin'=>1)
			      );

		$mlArray =& $mlFactory->getMailmanLists();

		if ($mlFactory->isError()) {
			echo '<p>'._('Error').' '.sprintf(_('Unable to get the list %s'), $Group->getPublicName()) .'</p>';
			echo $mlFactory->getErrorMessage();
			mail_footer(array());
			exit;
		}
		echo '<p>'.sprintf(_('You can administrate lists from here. Please note that private lists can still be viewed by members of your project, but are not listed on %1$s.'), forge_get_config ('forge_name')).'</p>';
		echo '<ul>
			<li>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;add_list=1">'._('Add Mailing List').'</a>
			</li>
			</ul>';
		$mlCount = count($mlArray);

		if($mlCount > 0) {
			table_begin_admin();
			for ($j = 0; $j < $mlCount; $j++) {

                                $currentList =& $mlArray[$j];
                                display_list_admin($currentList);
                        }

                        table_end();
                }
                mail_footer(array());
        }
} else {
        exit_no_group();
}
?>
