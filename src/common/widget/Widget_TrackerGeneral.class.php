<?php
/**
 * General Tracker Content Widget Class
 *
 * Copyright 2016-2017, Franck Villaume - TrivialDev
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

class Widget_TrackerGeneral extends Widget {

	var $content;

	function __construct() {
		$request =& HTTPRequest::instance();
		$owner_id = (int)substr($request->get('owner'), 1);
		if (!$owner_id) {
			$owner_id = (int)$request->get('atid');
		}
		parent::__construct('trackergeneral', $owner_id, WidgetLayoutManager::OWNER_TYPE_TRACKER);
		$this->content['title'] = _('General Information');
	}

	function getTitle() {
		return $this->content['title'];
	}

	function getDescription() {
		return _('Default widget where general informations are stored & displayed. Open/Modified/Close dates, submitted By, last Modified by.');
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

		if ($func == 'add') {
			$return = html_e('p', array(), _('Submit Instruction'));
			$renderSubmitInstructions = $ath->renderSubmitInstructions();
			if (strlen($renderSubmitInstructions) > 0) {
				$return .= $renderSubmitInstructions;
			} else {
				$return .= $HTML->information('No specific instruction');
			}
		} elseif ($func == 'detail') {
			$i = 0;
			$return = $HTML->listTableTop();
			$cells = array();
			$cells[][] = html_e('strong', array(), _('Submitted by')._(':'));
			if($ah->getSubmittedBy() != 100) {
				$cells[][] = util_display_user($ah->getSubmittedUnixName(), $ah->getSubmittedBy(), $ah->getSubmittedRealName());
			} else {
				$cells[][] = $ah->getSubmittedRealName();
			}
			$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
			$cells = array();
			$cells[][] = html_e('strong', array(), _('Date Submitted')._(':'));
			$cells[][] = date(_('Y-m-d H:i'), $ah->getOpenDate());
			$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
			$cells = array();
			$cells[][] = html_e('strong', array(), _('Last Modified by')._(':'));
			if($ah->getLastModifiedBy() != 100) {
				$cells[][] = util_display_user($ah->getLastModifiedUnixName(), $ah->getLastModifiedBy(), $ah->getLastModifiedRealName());
			} else {
				$cells[][] = $ah->getLastModifiedRealName();
			}
			$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
			$cells = array();
			$cells[][] = html_e('strong', array(), _('Last Modified')._(':'));
			$cells[][] = date(_('Y-m-d H:i'), $ah->getLastModifiedDate());
			$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
			$close_date = $ah->getCloseDate();
			if ($ah->getStatusID()==2 && $close_date > 1) {
				$cells = array();
				$cells[][] = html_e('strong', array(), _('Date Closed')._(':'));
				$cells[][] = date(_('Y-m-d H:i'), $close_date);
				$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
			}
			$return .= $HTML->listTableBottom();
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
