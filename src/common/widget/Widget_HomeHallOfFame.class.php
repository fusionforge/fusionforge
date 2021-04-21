<?php
/**
 * Copyright 2020-2021, Franck Villaume - TrivialDev
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
require_once $gfcommon.'include/FusionForge.class.php';

class Widget_HomeHallOfFame extends Widget {
	var $_hall_show = 'PDA';
	function __construct() {
		parent::__construct('homehalloffame');
		if (session_loggedin()) {
			$userPrefValue = UserManager::instance()->getCurrentUser()->getPreference('my_home_hall_of_fame');
			if ($userPrefValue) {
				$this->_hall_show = $userPrefValue;
			}
		}
	}

	function getTitle() {
		return _('Hall Of Fame');
	}

	function getContent() {
		$ff = new FusionForge();
		$objects = $ff->getHallOfFameObjects($this->_hall_show);
		$content = '';
		foreach($objects as $object) {
			if (method_exists($object, 'getAbstract')) {
				$content .= $object->getAbstract();
			} else {
				switch (get_class($object)) {
					case 'Group':
						if (strstr($object->getDescription(),'<br/>')) {
							// the news is html, fckeditor made for example
							$arr = explode('<br/>', $object->getDescription());
						} else {
							$arr = explode("\n", $object->getDescription());
						}
						$summ_txt = util_make_links($arr[0]);
						$arr_v = $object->getVotes();
						$news = html_e('div', array('class' => 'widget-sticker-header box'), html_e('div', array(), util_make_link($object->getHomePage(), $object->getPublicName(), false, true).' - '.$arr_v[0].' '._('Vote(s)')));
						$news .= html_e('div', array('class' => 'widget-sticker-body'), $summ_txt.html_e('br').util_make_link($object->getHomePage(), _('... Read more'), false, true));
						$news .= html_e('div', array('class' => 'widget-sticker-footer'), _('Registered')._(': ').date(_('Y-m-d H:i'), $object->getStartDate()));
						$content .= html_e('div', array('class' => 'widget-sticker-container'), $news);
						break;
					default:
						$content .= 'BUILT The Abstract';
						break;
				}
			}
		}

		if (strlen($content)) {
			return $content.html_e('div', array('style' => 'clear:both'), '&nbsp;');
		}
	}

	function getDescription() {
		return _('Display most voted projets, diaries, artifacts.');
	}

	function hasPreferences() {
		if (session_loggedin()) {
			return true;
		}
		return false;
	}

	function updatePreferences() {
		$request->valid(new Valid_String('cancel'));
		$vShow = new Valid_WhiteList('show', array('P', 'D', 'A', 'PA', 'PD', 'DA', 'PDA'));
		$vShow->required();
		if (!$request->exist('cancel')) {
			if ($request->valid($vShow)) {
				switch($request->get('show')) {
					case 'P':
						$this->_hall_show = 'P';
						break;
					case 'D':
						$this->_hall_show = 'D';
						break;
					case 'A':
						$this->_hall_show = 'A';
						break;
					case 'PA':
						$this->_hall_show = 'PA';
						break;
					case 'PD':
						$this->_hall_show = 'PD';
						break;
					case 'DA':
						$this->_hall_show = 'DA';
						break;
					case 'PDA':
					default:
						$this->_hall_show = 'PDA';
				}
				UserManager::instance()->getCurrentUser()->setPreference('my_home_hall_of_fame', $this->_hall_show);
			}
		}
		return true;
	}

	function getPreferences() {
		$optionsArray = array('P', 'D', 'A', 'PA', 'PD', 'DA', 'PDA');
		$textsArray = array();
		$textsArray[] = _('Projets'.' [P]');
		$textsArray[] = _('Diaries'.' [D]');
		$textsArray[] = _('Artifacts'.' [A]');
		$textsArray[] = _('Projects & Artifacts'.' [PA]');
		$textsArray[] = _('Projects & Diaries'.' [PD]');
		$textsArray[] = _('Diaries & Artifacts'.' [DA]');
		$textsArray[] = _('Projects, Diaries & Artifacts'.' [PDA]');
		$prefs = _('Select objects')._(': ').html_build_select_box_from_arrays($optionsArray, $textsArray, 'show', $this->_hall_show, false);
		return $prefs;
	}
}
 
