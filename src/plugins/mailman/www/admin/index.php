<?php
/**
 * Fusionforge Mailing Lists Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) Guillaume Smet - Open Wide
 * Portions Copyright 2010 (c) Mélanie Le Bail
 * Copyright 2016, 2021, Franck Villaume - TrivialDev
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once 'env.inc.php';
require_once 'pre.php';
require_once 'preplugins.php';
require_once 'plugins_utils.php';
require_once '../mailman_utils.php';

global $HTML, $feedback;

$request =& HTTPRequest::instance();
$group_id      = getIntFromRequest('group_id');
$group_list_id = getIntFromRequest('group_list_id');
$post_changes= $request->get('post_changes');
$add_list=$request->get('add_list');
$action=$request->get('action');
$change_status=$request->get('change_status');
$list_name=$request->get('list_name');
$is_public=$request->get('is_public');
$description=$request->get('description');

if ($group_id) {
	$Group = group_get_object($group_id);
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
		session_redirect('/plugins/mailman/index.php?group_id='.$group_id);

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
		printf(_('Lists are named in this manner:<br /><strong>projectname-listname@%s</strong>'),
				forge_get_config('lists_host'));
		echo '</p>';
		echo '<p>';
		echo _('It will take <span class="important">few minutes</span> for your list to be created.');
		echo '</p>';

		$mlFactory = new MailmanListFactory($Group);
		if (!$mlFactory || !is_object($mlFactory) || $mlFactory->isError()) {
			exit_error(_('Error'), $mlFactory->getErrorMessage());
		}

		$mlArray =& $mlFactory->getMailmanLists();

		if ($mlFactory->isError()) {
			echo '<h1>'._('Error').' '._('Unable to get the lists') .'</h1>';
			echo $mlFactory->getErrorMessage();
			mail_footer();
			exit;
		}

		//
		//	Form to add list
		//
		echo $HTML->openForm(array('method' => 'post', 'action' => getStringFromServer('PHP_SELF').'?group_id='.$group_id));
		?>
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="add_list" value="y" />
			<p><strong><?php echo _('Mailing List Name')._(':'); ?></strong><br />
			<strong><?php echo $Group->getUnixName(); ?>-<input type="text" name="list_name" value="" size="10" maxlength="12" />@<?php echo forge_get_config('lists_host'); ?></strong><br /></p>
			<p>
			<strong><?php echo _('Is Public?'); ?></strong><br />
			<input type="radio" name="is_public" value="1" checked="checked" /> <?php echo _('Yes'); ?><br />
			<input type="radio" name="is_public" value="0" /> <?php echo _('No'); ?></p><p>
			<strong><?php echo _('Description')._(':'); ?></strong><br />
			<input type="text" name="description" value="" size="40" maxlength="80" /><br /></p>
			<p>
			<input type="submit" name="submit" value="<?php echo _('Add This List'); ?>" /></p>
		<?php
		echo $HTML->closeForm();
		mail_footer();

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
		<?php echo $HTML->openForm(array('method' => 'post', 'action' => getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&group_list_id='.$mailingList->getID())); ?>
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="change_status" value="y" />
			<p>
			<strong><?php echo _('Is Public?'); ?></strong><br />
			<input type="radio" name="is_public" value="1"<?php echo ($mailingList->isPublic() == 1 ? ' checked="checked"' : ''); ?> /> <?php echo _('Yes'); ?><br />
			<input type="radio" name="is_public" value="0"<?php echo ($mailingList->isPublic() == 0 ? ' checked="checked"' : ''); ?> /> <?php echo _('No'); ?><br />
			</p>
			<p><strong><?php echo _('Description')._(':'); ?></strong><br />
			<input type="text" name="description" value="<?php echo $mailingList->getDescription(); ?>" size="40" maxlength="80" /><br /></p>
			<p>
			<input type="submit" name="submit" value="<?php echo _('Update'); ?>" /></p>
			<?php
		echo $HTML->closeForm();
		mail_footer();
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
			mail_footer();
			exit;
		}
		echo '<p>'.sprintf(_('You can administrate lists from here. Please note that private lists can still be viewed by members of your project, but are not listed on %s.'), forge_get_config ('forge_name')).'</p>';
		echo '<ul>
			<li>';
		echo util_make_link(getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;add_list=1', _('Add Mailing List'));
		echo '	</li>
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
                mail_footer();
        }
} else {
        exit_no_group();
}
