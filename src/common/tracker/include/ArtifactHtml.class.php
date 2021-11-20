<?php
/**
 * FusionForge Generic Tracker facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2011-2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2015-2017,2019, Franck Villaume - TrivialDev
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
	 * @param	array	$editattrs
	 * @return	string
	 */
	function showDetails($editable = false, $editattrs = array()) {
		global $HTML, $gfcommon;
		$return = '';
		$result = $this->getDetails();
		$result_html = util_gen_cross_ref($result, $this->ArtifactType->Group->getID());
		$parsertype = forge_get_config('tracker_parser_type');
		switch ($parsertype) {
			case 'markdown':
				require_once $gfcommon.'include/Markdown.include.php';
				$result_html = FF_Markdown($result_html);
				break;
			default:
				$result_html = nl2br($result_html);
		}

		$title_arr = array();
		if ($editable === true) {
			$title_arr[] = '<div style="width:100%; line-height: 20px;">' .
				'<div style="float:left;">' . _('Detailed description')._(':') .utils_requiredField().'</div>' .
				'<div>' . $HTML->getEditFilePic(_('Edit this message'), _('Edit this message'), array('class' => 'mini_buttons tip-ne', 'onclick'=>"switch2edit(this, 'showdescription', 'editdescription')")) . '</div>' .
				'</div>';
		} else {
			$title_arr[] = _('Detailed description');
		}
		$return .= $HTML->listTableTop($title_arr, array(), 'full');
		$return .= $HTML->multiTableRow(array('id' => 'editdescription', 'style' => 'display:none'), array(array(html_e('textarea', array_merge($editattrs, array('id' => 'tracker-description', 'required' => 'required', 'name' => 'description', 'rows' => 20, 'style' => 'box-sizing: box-border; width: 99%;', 'title' => util_html_secure(html_get_tooltip_description('description')))), $result), 'style' => 'display: block; box-sizing:border-box;')));
		$return .= $HTML->multiTableRow(array('id' => 'showdescription'), array(array($result_html)));
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
		if (!isset($order) || !$order) {
			$order = 'up';
		}
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
			$thArray = array('<a name="sort" href="#sort" class="sortheader" onclick="thead = true;ts_resortTable(this, 0);submitOrder();return false;">'._('Message').'<span id="order_span" sortdir="'.$order.'" class="sortarrow">&nbsp;&nbsp;<img src="/images/sort_'.$img_order.'.gif" alt="'.$char_order.'" /></span></a>');
			$return .= $HTML->listTableTop($thArray, array(), 'listing full sortable', 'messages_list');

			for ($i=0; $i < $rows; $i++) {
				$return .= '<tr onmouseover="show_edit_button(\'edit_bt_'.$i.'\')" onmouseout="hide_edit_button(\'edit_bt_'.$i.'\')" ><td>';

				$params = array('user_id' => db_result($result,$i,'user_id'), 'size' => 's', 'content' => '');
				plugin_hook_by_reference("user_logo", $params);
				$return .= $params['content'];

				$return .= '<span style="float:left">';
				$return .= _('Date')._(': ').
					date(_('Y-m-d H:i'), db_result($result, $i, 'adddate')) .'<br />'.
					_('Sender')._(': ');
				if(db_result($result, $i, 'user_id') == 100) {
					$return .= db_result($result, $i, 'realname');
				} else {
					$return .= util_make_link_u(db_result($result, $i, 'user_name'), db_result($result, $i, 'realname'));
				}
				$return .= '</span>';

				$return .= '<p style="clear: both;padding-top: 1em;">';
				$text = db_result($result, $i, 'body');
				$text = util_gen_cross_ref($text, $this->ArtifactType->Group->getID());
				$parsertype = forge_get_config('tracker_parser_type');
				switch ($parsertype) {
					case 'markdown':
						require_once $gfcommon.'include/Markdown.include.php';
						$text = FF_Markdown($text);
						break;
					default:
						$text = nl2br($text);
				}
				$return .= $text;
				$return .= '</p>';
				$return .= '</td></tr>';
			}

			$return .= $HTML->listTableBottom();

		} else {
			$return .= $HTML->information(_('No comments have been posted'));
		}
		return $return;
	}

	function showHistory() {
		global $HTML;
		$result = $this->getHistory();
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
				<tr><td>'.$field.'</td><td>';

				if ($field == 'status_id') {

					$return .= $artifactType->getStatusName(db_result($result, $i, 'old_value'));

				} elseif ($field == 'assigned_to' || $field == 'submitted_by') {

					$return .= user_getname(db_result($result, $i, 'old_value'));

				} elseif ($field == 'close_date') {
					if (db_result($result, $i, 'old_value')) {
						$return .= date(_('Y-m-d H:i'),db_result($result, $i, 'old_value'));
					} else {
						$return .= '<i>'._('None').'</i>';
					}
				} else {

					$return .= db_result($result, $i, 'old_value');

				}
				$return .= '</td>'.
					'<td>'. date(_('Y-m-d H:i'),db_result($result, $i, 'entrydate')) .'</td>';
				$user = user_get_object_by_name(db_result($result, $i, 'user_name'));
				if ($user && is_object($user)) {
					$return .= '<td>'.util_display_user($user->getUnixName(), $user->getID(), $user->getRealName()).'</td></tr>';
				} else {
					$return .= '<td>'.db_result($result, $i, 'user_name').'</td></tr>';
				}
			}

			$return .= $HTML->listTableBottom();

		} else {
			$return .= $HTML->information(_('No changes have been made to this item'));
		}
		return $return;
	}

	function showRelations() {
		global $HTML;
		$result=$this->getRelations();
		$rows= db_numrows($result);
		$return = '';
		if ($rows > 0){
			$return = '<table class="fullwidth">
							<tr>
								<td colspan="2">';
			$current = '';
			$end = '';
			while ($arr = db_fetch_array($result)) {
				if (forge_check_perm('tracker', $arr['group_artifact_id'], 'read')) {
					$title = $arr['group_name']._(': ').$arr['name'];
					if ($title != $current) {
						$return .= $end.'<strong>'.$title.'</strong>';
						$current = $title;
						$end = '<br /><br />';
					}
					$text = '[#'.$arr['artifact_id'].']';
					$url = '/tracker/?func=detail&aid='.$arr['artifact_id'].'&group_id='.$arr['group_id'].'&atid='.$arr['group_artifact_id'];
					$arg['title'] = util_html_secure($arr['summary']);
					if ($arr['status_id'] == 2) {
						$arg['class'] = 'artifact_closed';
					}
					$return .= '<br/>&nbsp;&nbsp;&nbsp;';
					$return .= util_make_link($url, $text, $arg).' '.util_make_link($url, $arr['summary']).' <i>('._('Relation')._(': ').$arr['field_name'].')</i>';
				}
			}
			$return .= '</td>
				</tr>
				</table>';
		} else {
			$return .= $HTML->information(_('No relations found.'));
		}
		return $return;
	}

	function showChildren() {
		global $HTML;
		global $atid;
		$readonly = false;
		if (!forge_check_perm('tracker', $atid, 'submit')) {
			$readonly = true;
		}
		$children = $this->getChildren();
		$rows= count($children);
		$return = '';
		if ($rows > 0){
			$return = '	<table class="children fullwidth">
							<tr>
								<td colspan="2">';
			$current = '';
			$end = '';
			foreach ($children as $arr) {
				if (forge_check_perm('tracker', $arr['group_artifact_id'], 'read')) {
					$title = $arr['group_name']._(': ').$arr['name'];
					if ($title != $current) {
						$return .= $end.'<strong>'.$title.'</strong>';
						$current = $title;
						$end = '<br /><br />';
					}
					$text = '[#'.$arr['artifact_id'].']';
					$url = '/tracker/a_follow.php/'.$arr['artifact_id'];
					$arg['title'] = util_html_secure($arr['summary']);
					if ($arr['status_id'] == 2) {
						$arg['class'] = 'artifact_closed';
					}
					$return .= html_ao('span',array('id'=>'child'.$arr['artifact_id']));
					$return .= '<br/>&nbsp;&nbsp;&nbsp;';
					$return .= util_make_link($url, $text, $arg).' '.util_make_link($url, $arr['summary']);
					if (!$readonly) {
						$return .= $HTML->getMinusPic(_('Click to remove child'), _('Click to remove child'), array('class'=>'removechild', 'data-id'=>$arr['artifact_id']));
					}
					$return .= html_ac(html_ap()-1);
				}
			}
			$return .= '</td>
				</tr>
				</table>';
		}
		return $return;
	}

	function showParent() {
		global $HTML;
		global $atid;
		$readonly = false;
		if (!forge_check_perm('tracker', $atid, 'submit')) {
			$readonly = true;
		}
		$parentId = $this->getParent();
		$return = '';
		if ($parentId){
			$parent = artifact_get_object($parentId);
			$return = $HTML->listTableTop(array(), array(), 'parent fullwidth', 'parent'.$parent->getID()).'
							<tr>
								<td colspan="2">';
			$parentAt = $parent->getArtifactType();
			if (forge_check_perm('tracker', $parentAt->getID(), 'read')) {
				$parentG = $parentAt->getGroup();
				$title = $parentG->getPublicName()._(': ').$parentAt->getName();
				$return .= '<strong>'.$title.'</strong>';
				$text = '[#'.$parent->getID().']';
				$url = '/tracker/a_follow.php/'.$parent->getID();
				$arg['title'] = util_html_secure($parent->getSummary());
				if ($parent->getStatusID() == 2) {
					$arg['class'] = 'artifact_closed';
				}
				$return .= html_ao('span');
				$return .= '<br/>&nbsp;&nbsp;&nbsp;';
				$return .= util_make_link($url, $text, $arg).' '.util_make_link($url, $parent->getSummary());
				if (!$readonly) {
					$return .= $HTML->getMinusPic(_('Click to remove parent'), _('Click to remove parent'), array('class'=>'removeparent', 'data-id'=>$parent->getID()));
				}
				$return .= html_ac(html_ap()-1);
			}
			$return .= '</td>
				</tr>'.$HTML->listTableBottom();
		} else {
			$return = '	<table class="parent fullwidth"></table>';
		}
		return $return;
	}

	function showDependencies() {
		global $HTML;
		global $atid;
		$readonly = false;
		if (!forge_check_perm('tracker', $atid, 'submit')) {
			$readonly = true;
		}
		$return = '';
		$ef_parent = $this->getArtifactType()->getExtraFields(array(ARTIFACT_EXTRAFIELDTYPE_PARENT));
		if (count($ef_parent)) {
			$return .= html_e('input', array('type'=>'hidden','id'=>'aid', 'value'=>$this->getID()));
			$return .= html_ao('div',array('class'=>'fullwidth'));
			$return .= html_e('strong',array(),_('Parent')).html_e('br');
			$return .= $this->showParent();
			if (!$readonly) {
				if ($this->hasParent()) {
					$return .= html_ao('div',array('class'=>'fullwidth addparent hide'));
				} else {
					$return .= html_ao('div',array('class'=>'fullwidth addparent'));
				}
				$return .= html_e('input', array('type'=>'text', 'id'=>'parent_id', 'value'=>'', 'size'=>20, 'maxlength'=>80, 'pattern'=>'^(?!'.$this->getID().'$)\d*$'));
				$return .= $HTML->getAddPic(_('Click to add parent'), _('Click to add parent'), array('class'=>'addparent')).html_e('br');
				$return .= html_ac(html_ap()-1);
			}
			$return .= html_ac(html_ap()-1).html_e('br');
			$return .= html_ao('div',array('class'=>'fullwidth'));
			$return .= html_e('strong',array(),_('Children')).html_e('br');
			$return .= $this->showChildren();
			if (!$readonly) {
				$return .= html_ao('div',array('class'=>'fullwidth addchild'));
				$return .= html_e('input', array('type'=>'text', 'id'=>'child_id', 'value'=>'', 'size'=>20, 'maxlength'=>80, 'pattern'=>'^(?!'.$this->getID().'$)\d*$'));
				$return .= $HTML->getAddPic(_('Click to add child'), _('Click to add child'), array('class'=>'addchild'));
				$return .= html_ac(html_ap()-1);
			}
			$return .= html_ac(html_ap()-1);
		} else {
			$return .= $HTML->information(_('No dependency'));
		}
		return $return;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
