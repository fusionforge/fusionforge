<?php
/**
 * FusionForge trackers
 *
 * Copyright 2011, Alcatel-Lucent
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014, Franck Villaume - TrivialDev
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

/**
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The Roadmap ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

require_once $gfcommon.'tracker/ArtifactFactory.class.php';
require_once $gfcommon.'tracker/Roadmap.class.php';
require_once $gfcommon.'tracker/RoadmapFactory.class.php';

global $HTML;

$perm = $group->getPermission();
if (!$perm || !is_object($perm) || !$perm->isArtifactAdmin()) {
	exit_permission_denied();
}

$atfh = new ArtifactTypeFactoryHtml($group);
if (!$atfh || !is_object($atfh) || $atfh->isError()) {
	exit_error(_('Error'), _('Could Not Get ArtifactTypeFactoryHtml'));
}

$set_roadmap_failed = false;

if (getStringFromRequest('set_roadmap')) {
	$set_roadmap_failed = false;
	$result = true;
	$roadmap_name = getStringFromRequest('roadmap_name');
	$roadmap_id = getIntFromRequest('roadmap_id', 0);
	$roadmap_list = getArrayFromRequest('roadmap_list');

	if (! $roadmap_name) {
		$error_msg .= _("Cannot create or rename roadmap")._(': ')._('name is empty');
	}
	else {
		$roadmap = new Roadmap($group, $roadmap_id);
		if (! $roadmap_id) {
			$result = $roadmap->create($roadmap_name);
			if ($result) {
				$feedback .= sprintf(_('Roadmap %s created'), $roadmap_name);
			}
			else {
				$error_msg .= _("Cannot create roadmap: ").$roadmap->getErrorMessage();
			}
		}
		else {
			$old_roadmap_name = $roadmap->getName();
			if ($roadmap_name != $old_roadmap_name) {
				$result = $roadmap->rename($roadmap_name);
				if ($result) {
					$feedback .= sprintf(_('Roadmap %s renamed to %s'), $old_roadmap_name, $roadmap_name);
				}
				else {
					$error_msg .= _("Cannot rename roadmap: ").$roadmap->getErrorMessage();
				}
			}
		}
		if (! $error_msg && is_array($roadmap_list) && ! empty($roadmap_list)) {
			$result = $roadmap->setList($roadmap_list);
			if (! $result) {
				$error_msg .= _("Cannot set roadmap: ").$roadmap->getErrorMessage();
			}
		}
	}
	if ($error_msg) {
		$set_roadmap_failed = true;
	}
}
elseif (getStringFromRequest('set_roadmap_state')) {
	$roadmap_states = getArrayFromRequest('roadmap_states');
	$default_roadmap = getIntFromRequest('default_roadmap');

	$roadmap_factory = new RoadmapFactory($group);
	$roadmaps = $roadmap_factory->getRoadmaps();

	$updated = false;
	foreach ($roadmaps as $roadmap) {
		if (!is_object($roadmap)) {
			//just skip it
		} elseif ($roadmap->isError()) {
			echo $roadmap->getErrorMessage();
		} else {
			$result = $roadmap->setState((array_key_exists($roadmap->getID(), $roadmap_states) ? 1 : 0));
			if (! $result) {
				$error_msg .= _("Cannot set roadmap state: ").$roadmap->getErrorMessage();
			}
			$result = $roadmap->isDefault(($default_roadmap == $roadmap->getID() ? 1 : 0));
			if (! $result) {
				$error_msg .= _("Cannot set default value: ").$roadmap->getErrorMessage();
			}
			if (! $error_msg && $updated === false) {
				$feedback .= _('Roadmap configuration is updated');
				$updated = true;
			}
		}
	}
}
elseif (getStringFromRequest('delete_roadmap_sure')) {
	$roadmap_id = getIntFromRequest('roadmap_id', 0);

	$roadmap = new Roadmap($group, $roadmap_id);
	$result = $roadmap->delete();
	if ($result) {
		$feedback .= sprintf(_('Roadmap %s is deleted'), $roadmap->getName());
	}
	else {
		$error_msg .= _("Cannot delete roadmap: ").$roadmap->getErrorMessage();
	}
}

// IHM part
if (getStringFromRequest('delete_roadmap')) {

	$roadmap_id = getIntFromRequest('roadmap_id', 0);

	$roadmap = new Roadmap($group, $roadmap_id);
	$roadmap_name = $roadmap->getName();

	$atfh->header(array('title' => _('Delete roadmap'), 'modal' => 1));

	?>
	<p>
	<strong><?php echo sprintf(_('Are you sure you want to delete the %s roadmap?'), $roadmap_name) ?></strong>
	</p>
	<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;admin_roadmap=1' ?>" method="post">
	<input type="hidden" name="admin_roadmap" value="1" />
	<input type="hidden" name="roadmap_id" value="<?php echo $roadmap_id ?>" />
	<p>
	<input type="submit" name="delete_roadmap_sure" value="<?php echo _('Yes') ?>" />
	<input type="submit" name="cancel" formnovalidate="formnovalidate" value="<?php echo _('Cancel') ?>" />
	</p>
	</form>
	<?php
	$ihm = true;
}

if ($set_roadmap_failed ||
	getStringFromRequest('new_roadmap') ||
	getStringFromRequest('update_roadmap')) {

	$roadmap_id = getIntFromRequest('roadmap_id', 0);

	$roadmap = new Roadmap($group, $roadmap_id);
	$roadmap_list = getArrayFromRequest('roadmap_list', $roadmap->getList());

	if (getStringFromRequest('new_roadmap')) {
		$atfh->header(array('title' => _('Create a new roadmap'), 'modal' => 1));
	}
	else {
		$atfh->header(array('title' => _('Update roadmap'), 'modal' => 1));
	}

	$at_arr = $atfh->getArtifactTypes();

	if (!$at_arr || count($at_arr) < 1) {
		echo $HTML->information(_('No trackers have been set up.'));
	} else {
		echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&admin_roadmap=1', 'method' => 'post'));
		echo '<p>'._('Name'). _(': ') . '<input required="required" type="text" name="roadmap_name" value="'.$roadmap->getName().'" size="40" /></p>';
		if ($roadmap_id) {
			echo '<input type="hidden" name="roadmap_id" value="'.$roadmap_id.'" />';
		}
		echo '<table>'."\n";
		foreach ($at_arr as $artifact_type) {
			if (!is_object($artifact_type)) {
				//just skip it
			} elseif ($artifact_type->isError()) {
				echo $artifact_type->getErrorMessage();
			} else {
				$ath = new ArtifactTypeHtml($group, $artifact_type->getID());

				$field_id = 0;
				if (array_key_exists($artifact_type->getID(), $roadmap_list)) {
					$field_id = $roadmap_list[$artifact_type->getID()];
				}

				echo '<tr>';
				echo '<td>' . $artifact_type->getName() . '</td>'."\n";
				echo '<td><select name="roadmap_list['.$artifact_type->getID().']">'."\n";
				echo '<option value="0"'.(! $field_id ? ' selected="selected"' : '').' >'._('Not used').'</option>'."\n";
				$extra_fields = $ath->getExtraFields( array(ARTIFACT_EXTRAFIELD_FILTER_INT));
				foreach ($extra_fields as $extra_field) {
					if ($extra_field['field_type'] != ARTIFACT_EXTRAFIELDTYPE_CHECKBOX) {
						echo '<option value="'.$extra_field['extra_field_id'].'"'.($extra_field['extra_field_id'] == $field_id ? ' selected="selected"' : '').' >'.$extra_field['field_name'].'</option>'."\n";
					}
				}
				echo '</select></td>'."\n";
				echo '</tr>'."\n";
			}
		}
		echo '</table>'."\n";
		echo '<p>
			<input type="submit" name="set_roadmap" value="'._('Submit').'" />
			<input type="submit" name="cancel" formnovalidate="formnovalidate" value="'._('Cancel').'" />
			</p>'."\n";
		echo $HTML->closeForm();
	}
	$ihm = true;
}

if (getIntFromRequest('manage_release') ||
	getIntFromRequest('updownorder_release') ||
	getStringFromRequest('release_auto_order') ||
	getStringFromRequest('release_changes_order')) {

	$roadmap_id = getIntFromRequest('roadmap_id', 0);

	$roadmap_factory = new RoadmapFactory($group);
	$selected_roadmap = $roadmap_factory->getRoadmapByID($roadmap_id);
	if (! is_object($selected_roadmap)) {
		$error_msg .= sprintf(_('roadmap %s is not available'), 'ID='.$roadmap_id);
	}
	else {
		$artifact_type_list = $selected_roadmap->getList();

		$update_order = 0;
		if (getIntFromRequest('updownorder_release') ||
			getStringFromRequest('release_auto_order') ||
			getStringFromRequest('release_changes_order')) {
			$update_order = 1;
		}

		$release_order = $selected_roadmap->getReleases();
		if (! is_array($release_order)) {
			$release_order = array();
		}

		if ($update_order) {
			if (getIntFromRequest('updownorder_release')) {
				$old_pos = getIntFromRequest('old_pos');
				$new_pos = getIntFromRequest('new_pos');

				$tmp = $release_order[$new_pos];
				$release_order[$new_pos] = $release_order[$old_pos];
				$release_order[$old_pos] = $tmp;

				$result = $selected_roadmap->setReleaseOrder($release_order);
				if ($result) {
					$feedback .= _('Release(s) order updated');
				}
				else {
					$error_msg .= _("Cannot modify release order: ").$selected_roadmap->getErrorMessage();
				}
			}
			elseif (getStringFromRequest('release_auto_order')) {
				usort($release_order, 'version_compare');
			}
			elseif (getStringFromRequest('release_changes_order')) {
				$order = array_reverse(getArrayFromRequest('order'));

				// Items with not modified positions
				$not_changed = array_keys($order, '');

				// Get positions
				$list_size = count($order);
				$not_changed = array();
				$changed = array();
				$out_before = array();
				$out_after = array();
				foreach ($order as $field => $new_pos) {
					if (!is_numeric($new_pos)) {
						$not_changed[] = $field;
						continue;
					}
					$new_pos = intval($new_pos);
					if ($new_pos < 1) {
						if (!isset($out_before[$new_pos]))
							$out_before[$new_pos] = array();
						$out_before[$new_pos][] = $field;
					}
					elseif ($new_pos > $list_size) {
						if (!isset($out_after[$new_pos]))
							$out_after[$new_pos] = array();
						$out_after[$new_pos][] = $field;
					}
					else {
						if (!isset($changed[$new_pos - 1]))
							$changed[$new_pos - 1] = array();
						$changed[$new_pos - 1][] = $field;
					}
				}
				ksort($changed, SORT_NUMERIC);

				// Start of the list
				$start_list = array();
				$index_start = 0;
				if (!empty($out_before)) {
					ksort($out_before, SORT_NUMERIC);
					foreach (array_values($out_before) as $list) {
						foreach ($list as $field) {
							$start_list[] = $field;
							$index_start++;
						}
					}
				}

				// Middle of the list
				$index = $index_start;
				foreach ($changed as $pos => $list) {
					for (; $index < $pos; $index++) {
						$start_list[] = array_shift($not_changed);
					}
					foreach ($list as $field) {
						$start_list[] = $field;
						$index++;
					}
				}

				// End of the list
				$end_list = array();
				if (!empty($out_after)) {
					ksort($out_after, SORT_NUMERIC);
					foreach (array_values($out_after) as $list) {
						foreach ($list as $field) {
							$end_list[] = $field;
						}
					}
				}

				// And we complete the list
				$release_order = array_merge($start_list, $not_changed, $end_list);
			}

			$result = $selected_roadmap->setReleaseOrder($release_order);
			if ($result) {
				$feedback .= _('Release(s) order updated');
			}
			else {
				$error_msg .= _("Cannot modify release order: ").$selected_roadmap->getErrorMessage();
			}
		}

		$rows = array();
		for ($pos = count($release_order) - 1; $pos >= 0; $pos--) {
			$rows[$pos] = '<tr '. $HTML->boxGetAltRowStyle($pos) .'>'.'<td>'.'&#160;&#160;&#160;'.$release_order[$pos].'</td>'."\n".
						'<td class="align-right">'.
						($pos + 1).'&#160;--&gt;&#160;<input type="text" name="order['.$release_order[$pos].']" value="" size="3" maxlength="3" />'.
						'</td>'."\n".
						'<td class="align-center">'.'&#160;&#160;&#160;'.
						util_make_link('/tracker/admin/?group_id='.$group_id.'&roadmap_id='.$roadmap_id.'&admin_roadmap=1&updownorder_release=1&old_pos='.$pos.'&new_pos='.(($pos == count($release_order) - 1) ? $pos : $pos + 1),
								html_image('ic/btn_up.png','19','18',array('alt'=>"Up"))).
						'&#160;&#160;'.
						util_make_link('/tracker/admin/?group_id='.$group_id.'&roadmap_id='.$roadmap_id.'&admin_roadmap=1&updownorder_release=1&old_pos='.$pos.'&new_pos='.(($pos == 0) ? $pos : $pos - 1),
								html_image('ic/btn_down.png','19','18',array('alt'=>"Down"))).
						'</td>'."\n".
						'</tr>'."\n";
		}

		$atfh->header(array('title' => _('Manage releases'), 'modal' => 1));

		if (! empty($rows)) {
			?>
			<p>
			<strong><?php echo sprintf(_('Set order of releases for %s roadmap:'), $selected_roadmap->getName()) ?></strong>
			</p>
			<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;admin_roadmap=1' ?>" method="post">
			<input type="hidden" name="roadmap_id" value="<?php echo $roadmap_id ?>" />
			<?php
			$title_arr = array();
			$title_arr[] = _('Releases');
			$title_arr[] = _('Current / New positions');
			$title_arr[] = _('Up/Down positions');

			echo $HTML->listTableTop($title_arr, false, ' ');
			echo implode('', $rows);
			echo '<tr class="noborder">
					<td>
					<input type="submit" name="release_auto_order" value="'._('Auto order').'" />
					</td>
					<td class="align-right">
					<input type="submit" name="release_changes_order" value="'._('Reorder').'" />
					</td>
					<td>
					</td>
				  </tr>';
			echo $HTML->listTableBottom();
			echo '</form>'."\n";
		}
		else {
			echo '<p>'._('No tracker is selected for this roadmap').'.</p>';
			echo '<p>'._('You can '). util_make_link('/tracker/admin/?group_id='.$group_id.'&roadmap_id='.$roadmap_id.'&admin_roadmap=1&update_roadmap=1', _('select tracker(s) for this roadmap')).
				'</p>';
		}
		echo '<p>'.util_make_link('/tracker/admin/?group_id='.$group_id.'&admin_roadmap=1', _('Return to list of roadmaps')).
			'</p>'."\n";

		$ihm = true;
	}
}

if (! isset($ihm) || $ihm !== true) {
	$atfh->header(array('title' => _('Manage roadmaps'), 'modal' => 1));

	echo '<p><strong>'._('You can define a new roadmap or edit an existing one here.').'</strong></p>';

	$roadmap_factory = new RoadmapFactory($group);
	$roadmaps = $roadmap_factory->getRoadmaps();
	echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id, 'method' => 'post'));
	?>
	<input type="hidden" name="admin_roadmap" value="1" />
	<?php
	$pos = 0;
	foreach ($roadmaps as $roadmap) {
		if (!is_object($roadmap)) {
			//just skip it
		} elseif ($roadmap->isError()) {
			echo $roadmap->getErrorMessage();
		} else {
			$rows[$pos] = '<tr '. $HTML->boxGetAltRowStyle($pos) .'>'.
					'<td><input type="checkbox" name="roadmap_states['.$roadmap->getID().']" value="1"'.($roadmap->getState() ? ' checked="checked"' : '').' /></td>'.
					'<td>'.$roadmap->getName().'</td>'."\n".
					'<td class="align-center"><input type="radio" name="default_roadmap" value="'.$roadmap->getID().'"'.($roadmap->isDefault() ? ' checked="checked"' : '').' /></td>'.
					/*
					'<td class="align-right">'.
					($pos + 1).'&#160;--&gt;&#160;<input type="text" name="order['.$roadmap->getID().']" value="" size="3" maxlength="3" />'.
					'</td>'."\n".
					'<td class="align-center">'.'&#160;&#160;&#160;'.
					'<a href="index.php?group_id='.$group_id.'&amp;roadmap_id='.$roadmap->getID().
					'&amp;customize_list=1&amp;post_changes=1&amp;updownorder_release=1&amp;new_pos='.(($pos == 0)? $pos + 1 : $pos).'">'.html_image('ic/btn_up.png','19','18',array('alt'=>"Up")).'</a>'.
					'&#160;&#160;'.
					'<a href="index.php?group_id='.$group_id.'&amp;roadmap_id='.$roadmap->getID().
					'&amp;customize_list=1&amp;post_changes=1&amp;updownorder_release=1&amp;new_pos='.(($pos == count($browse_fields) - 1)? $pos + 1 : $pos + 2).'">'.html_image('ic/btn_down.png','19','18',array('alt'=>"Down")).'</a>'.
					'</td>'."\n".
					*/
					'<td class="align-center">'.
					util_make_link('/tracker/admin/?group_id='.$group_id.'&roadmap_id='.$roadmap->getID().'&admin_roadmap=1&update_roadmap=1',
							html_image('ic/forum_edit.gif','','',array('alt' => _('Modify roadmap'), 'title' => _('Modify roadmap')))).
					util_make_link('/tracker/admin/?group_id='.$group_id.'&roadmap_id='.$roadmap->getID().'&admin_roadmap=1&manage_release=1',
							html_image('ic/survey-question-add.png','','',array('alt' => _('Manage releases'), 'title' => _('Manage releases')))).
					util_make_link('/tracker/admin/?group_id='.$group_id.'&roadmap_id='.$roadmap->getID().'&admin_roadmap=1&delete_roadmap=1',
							html_image('ic/trash.png','','',array('alt' => _('Delete roadmap'), 'title' => _('Delete roadmap')))).
					'</td>'."\n".
					'</tr>'."\n";
			$pos++;
		}
	}

	if (! empty($rows)) {
		$title_arr = array();
		$title_arr[] = _('Enable');
		$title_arr[] = _('Roadmap name');
		$title_arr[] = _('Default');
		//$title_arr[] = _('Current / New positions');
		//$title_arr[] = _('Up/Down positions');
		$title_arr[] = _('Actions');

		echo $HTML->listTableTop($title_arr, false, ' ');
		echo implode('', $rows);
		echo $HTML->listTableBottom();
	}

	echo '<p>';
	if (! empty($rows)) {
		echo '<input type="submit" name="set_roadmap_state" value="'._('Update').'" />'."\n";
	}
	echo '<input type="submit" name="new_roadmap" value="'._('New Roadmap').'" />'."\n";
	echo '</p>';
	echo $HTML->closeForm();
}

$atfh->footer();
