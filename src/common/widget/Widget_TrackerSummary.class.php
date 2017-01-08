<?php
/**
 * Summary Tracker Content Widget Class
 *
 * Copyright 2016, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';
require_once $gfwww.'include/note.php';

class Widget_TrackerSummary extends Widget {

	var $content;

	function __construct() {
		$request =& HTTPRequest::instance();
		$owner_id   = (int)substr($request->get('owner'), 1);
		parent::__construct('trackersummary', $owner_id, WidgetLayoutManager::OWNER_TYPE_TRACKER);
		$this->content['title'] = _('Description');
	}

	function getTitle() {
		return $this->content['title'];
	}

	function getDescription() {
		return _("Default widget where summary & description fields are stored & displayed.");
	}

	function isAvailable() {
		return isset($this->content['title']);
	}

	function getContent() {
		global $ath;
		global $ah;
		global $group_id;
		global $group;
		global $aid;
		global $atid;
		global $HTML;
		global $func;

		//manage redirect in case of missing required fields
		global $summary;
		global $details;

		$return = '';
		$inputAttrs = array('form' => 'trackerform', 'type' => 'text', 'name' => 'summary', 'style' => 'width:99%', 'value' => $summary);
		if ($func == 'detail') {
			$inputAttrs['value'] = $ah->getSummary();
			if (forge_check_perm('tracker', $atid, 'manager')) {
				$inputAttrs['required'] = 'required';
				$requiredInfo = utils_requiredField();
			} else {
				$inputAttrs['disabled'] = 'disabled';
				$requiredInfo = '';
			}
			$return .= html_e('input', array('type' => 'hidden', 'name' => 'summary', 'value' => $ah->getSummary()));

		} else {
			$inputAttrs['required'] = 'required';
			$requiredInfo = utils_requiredField();
		}
		$return .= html_e('p', array(), _('Summary')._(': ').$requiredInfo.html_e('input', $inputAttrs));
		if ($func == 'detail') {
			if (forge_check_perm('tracker', $atid, 'manager')) {
				$editable = true;
			} else {
				$editable = false;
			}
			$return .= $ah->showDetails($editable, array('form' => 'trackerform'));
		} elseif ($func == 'add') {
			$return .= notepad_func();
			$return .= $HTML->listTableTop();
			$content = html_e('strong', array(), _('Detailed description').$requiredInfo._(':'));
			$content .= notepad_button('document.forms.trackerform.details');
			$content .= html_e('textarea', array('form' => 'trackerform', 'id'=>'tracker-description', 'required'=>'required', 'name'=>'details', 'rows'=>'20', 'style'=>'box-sizing: border-box; width: 100%', 'title'=>util_html_secure(html_get_tooltip_description('description'))), $details, false);
			$cells = array();
			$cells[][] = $content;
			$return .= $HTML->multiTableRow(array(), $cells);
			$return .= $HTML->listTableBottom();

		}
		if (forge_check_perm('tracker', $atid, 'submit')) {
			$return .= $HTML->addRequiredFieldsInfoBox();
			$return .= html_e('p', array('class' => 'middleRight'), html_e('input', array('form' => 'trackerform', 'type' => 'submit', 'name' => 'submit', 'value' => _('Save Changes'), 'title' => _('Save is validating the complete form'), 'onClick' => 'iefixform()')));
		}
		return $return;
	}

	function canBeRemove() {
		return false;
	}

	function canBeMinize() {
		return false;
	}

	function getCategory() {
		return _('Trackers');
	}
}
