<?php
/**
 * FusionForge Generic Tracker facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2011-2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2015-2016, Franck Villaume - TrivialDev
 * Copyright 2016, Stéphane-Eymeric Bredthauer - TrivialDev
 * http://fusionforge.org
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

require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'include/utils_crossref.php';

class ArtifactHtml extends Artifact {

	/**
	 * showDetails - show details preformatted (like followups)
	 *
	 * @param	bool	$editable	is the detail editable or not? default is false.
	 */
	function showDetails($editable = false) {
		global $HTML;
		$return = '';
		$result = $this->getDetails();
		$result_html = util_gen_cross_ref($result, $this->ArtifactType->Group->getID());
		//$result = util_line_wrap( $result, 120,"\n");
		$result_html = nl2br($result_html);

		$title_arr = array();
		if ($editable === true) {
			$title_arr[] = '<div style="width:100%; line-height: 20px;">' .
				'<div style="float:left;">' . _('Detailed description')._(':') .utils_requiredField().'</div>' .
				'<div>' . $HTML->getEditFilePic(_('Edit this message'), _('Edit this message'), array('class' => 'mini_buttons tip-ne', 'onclick'=>"switch2edit(this, 'showdescription', 'editdescription')")) . '</div>' .
				'</div>';
		} else {
			$title_arr[] = _('Detailed description');
		}
		$return .= $HTML->listTableTop($title_arr);
		$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle(0, true), 'id' => 'editdescription', 'style' => 'display:none'), array(array(html_e('textarea', array('id' => 'tracker-description', 'required' => 'required', 'name' => 'description', 'rows' => 20, 'style' => 'box-sizing: box-border; width: 99%;', 'title' => util_html_secure(html_get_tooltip_description('description'))), $result), 'style' => 'display: block; box-sizing:border-box;')));
		$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle(0, true), 'id' => 'showdescription'), array(array($result_html)));
		$return .= $HTML->listTableBottom();
		return $return;
	}

	function showMessages() {
		global $HTML;
		$return = '';
		if (session_loggedin()) {
			$u = session_get_user();
			$order = $u->getPreference('tracker_messages_order');
		}
		if (!isset($order) || !$order) $order = 'up';

		$result = $this->getMessages($order);
		$rows = db_numrows($result);

		if ($rows > 0) {
			$title_arr=array();
			$title_arr[]=_('Message');

			if ($order == 'up') {
				$img_order = 'down';
				$char_order = '↓';
			} else {
				$img_order = 'up';
				$char_order = '↑';
			}
			$return .= '
<script type="text/javascript">/* <![CDATA[ */
function show_edit_button(id) {
    var element = document.getElementById(id);
	if (element) element.style.display = "block";
}
function hide_edit_button(id) {
    var element = document.getElementById(id);
	if (element) element.style.display = "none";
}
/* ]]> */</script>';
			$return .= '<img style="display: none;" id="img_order" src="" alt="" />';
			$return .= '<table class="listing full" id="messages_list">
<thead>
<tr>
<th>
<a name="sort" href="#sort" class="sortheader" onclick="thead = true;ts_resortTable(this, 0);submitOrder();return false;">'._('Message').'<span id="order_span" sortdir="'.$order.'" class="sortarrow">&nbsp;&nbsp;<img src="/images/sort_'.$img_order.'.gif" alt="'.$char_order.'" /></span></a></th>
</tr>
</thead>
<tbody>';

			for ($i=0; $i < $rows; $i++) {
				$return .= '<tr onmouseover="show_edit_button(\'edit_bt_'.$i.'\')" onmouseout="hide_edit_button(\'edit_bt_'.$i.'\')" ><td>';

				$params = array('user_id' => db_result($result,$i,'user_id'), 'size' => 's', 'content' => '');
				plugin_hook_by_reference("user_logo", $params);
				$return .= $params['content'];

				$return .= '<span style="float:left">';
				$return .= _('Date')._(': ').
					date(_('Y-m-d H:i'),db_result($result, $i, 'adddate')) .'<br />'.
					_('Sender')._(': ');
				if(db_result($result,$i,'user_id') == 100) {
					$return .= db_result($result,$i,'realname');
				} else {
					$return .= util_make_link_u (db_result($result,$i,'user_name'),db_result($result,$i,'user_id'),db_result($result,$i,'realname'));
				}
				$return .= '</span>';

				$return .= '<p style="clear: both;padding-top: 1em;">';
				$text = db_result($result, $i, 'body');
				$text = util_gen_cross_ref($text, $this->ArtifactType->Group->getID());
				//$text = util_line_wrap( $text, 120,"\n");
				$text = nl2br($text);
				$return .= $text;
				$return .= '</p>';
				$return .= '</td></tr>';
			}

			$return .= '</tbody></table>';

		} else {
			$return .= $HTML->information(_('No Comments Have Been Posted'));
		}
		return $return;
	}

	function showHistory() {
		global $HTML;
		$result=$this->getHistory();
		$rows= db_numrows($result);
		$return = '';

		if ($rows > 0) {

			$title_arr=array();
			$title_arr[] = _('Field');
			$title_arr[] = _('Old Value');
			//$title_arr[] = _('New Value');
			$title_arr[] = _('Date');
			$title_arr[] = _('By');

			$return .= $HTML->listTableTop($title_arr);

			$artifactType =& $this->getArtifactType();

			for ($i=0; $i < $rows; $i++) {
				$field=db_result($result, $i, 'field_name');
				$return .= '
				<tr '. $HTML->boxGetAltRowStyle($i) .'><td>'.$field.'</td><td>';

				if ($field == 'status_id') {

					$return .= $artifactType->getStatusName(db_result($result, $i, 'old_value'));

				} elseif ($field == 'assigned_to') {

					$return .= user_getname(db_result($result, $i, 'old_value'));

				} elseif ($field == 'close_date') {
					if (db_result($result, $i, 'old_value'))
						$return .= date(_('Y-m-d H:i'),db_result($result, $i, 'old_value'));
					else
						$return .= '<i>'._('None').'</i>';
				} else {

					$return .= db_result($result, $i, 'old_value');

				}
				$return .= '</td>'.
					'<td>'. date(_('Y-m-d H:i'),db_result($result, $i, 'entrydate')) .'</td>'.
					'<td>'. db_result($result, $i, 'user_name'). '</td></tr>';
			}

			$return .= $HTML->listTableBottom();

		} else {
			$return .= $HTML->information(_('No Changes Have Been Made to This Item'));
		}
		return $return;
	}

	function showRelations() {
		$aid = $this->getID();
		$return = '';

		// Search for all relations pointing to this record.

		$res = db_query_params ('SELECT *
		FROM artifact_extra_field_list, artifact_extra_field_data, artifact_group_list, artifact, groups
		WHERE field_type=9
		AND artifact_extra_field_list.extra_field_id=artifact_extra_field_data.extra_field_id
		AND artifact_group_list.group_artifact_id = artifact_extra_field_list.group_artifact_id
		AND artifact.artifact_id = artifact_extra_field_data.artifact_id
		AND groups.group_id = artifact_group_list.group_id
		AND (field_data = $1 OR field_data LIKE $2 OR field_data LIKE $3 OR field_data LIKE $4)
		AND artifact.is_deleted = 0
		ORDER BY artifact_group_list.group_id ASC, name ASC, artifact.artifact_id ASC',
					array($aid,
					      "$aid %",
					      "% $aid %",
					      "% $aid"));
		if (db_numrows($res)>0) {
			$return = '<div id="tabber-relations" class="tabbertab">
					<table class="fullwidth">
						<tr>
							<td colspan="2">';
			$current = '';
			$end = '';
			while ($arr = db_fetch_array($res)) {
				if (forge_check_perm('tracker', $arr['group_artifact_id'], 'read')) {
					$title = $arr['group_name']._(': ').$arr['name'];
					if ($title != $current) {
						$return .= $end.'<strong>'.$title.'</strong>';
						$current = $title;
						$end = '<br /><br />';
					}
					$text = '[#'.$arr['artifact_id'].']'.' '.$arr['summary'];
					$url = '/tracker/?func=detail&aid='.$arr['artifact_id'].'&group_id='.$arr['group_id'].'&atid='.$arr['group_artifact_id'];
					$arg['title'] = util_html_secure($arr['summary']);
					if ($arr['status_id'] == 2) {
						$arg['class'] = 'artifact_closed';
					}
					$return .= '<br/>&nbsp;&nbsp;&nbsp;';
					$return .= util_make_link($url, $text, $arg).' <i>('._('Relation')._(': ').$arr['field_name'].')</i>';
				}
			}
			$return .= '</td>
				</tr>
				</table>
				</div>';
		}
		return $return;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
