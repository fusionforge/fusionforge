<?php
/**
 * Default Action Tracker Content Widget Class
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

class Widget_TrackerDefaultActions extends Widget {

	var $content;

	function __construct() {
		global $func;
		$request =& HTTPRequest::instance();
		$owner_id   = (int)substr($request->get('owner'), 1);
		if (!$owner_id) {
			$owner_id = $request->get('atid');
		}
		if ($func == 'detail' || forge_check_perm('tracker_admin', $owner_id)) {
			parent::__construct('trackerdefaultactions', $owner_id, WidgetLayoutManager::OWNER_TYPE_TRACKER);
			$this->content['title'] = _('Actions');
		}
	}

	function getTitle() {
		return $this->content['title'];
	}

	function getDescription() {
		return _('Default actions widget for monitor, votes & delete.');
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
		global $func;
		global $HTML;
		if ($func == 'detail') {
			$return = $HTML->listTableTop();
			$cells = array();
			if ($ah->isMonitoring()) {
				$img="xmail16w.png";
				$text=_('Stop monitoring');
			} else {
				$img="mail16w.png";
				$text=_('Monitor');
			}
			$cells[][] = util_make_link('/tracker/?group_id='.$group_id.'&artifact_id='.$ah->getID().'&atid='.$ath->getID().'&func=monitor', html_e('strong', array(), html_image('ic/'.$img, 20, 20).' '.$text), array('id' => 'tracker-monitor', 'title' => util_html_secure(html_get_tooltip_description('monitor'))));
			$return .= $HTML->multiTableRow(array(), $cells);
			$votes = $ah->getVotes();
			if ($votes[1]) {
				$cells = array();
				$cellContent = html_e('span', array('id' => 'tracker-votes', 'title' => html_get_tooltip_description('votes')), html_e('strong', array(), _('Votes') . _(': ')).sprintf('%1$d/%2$d (%3$d%%)', $votes[0], $votes[1], $votes[2]));
				if ($ath->canVote()) {
					if ($ah->hasVote()) {
						$key = 'pointer_down';
						$txt = _('Retract Vote');
					} else {
						$key = 'pointer_up';
						$txt = _('Cast Vote');
					}
					$cellContent .= util_make_link('/tracker/?group_id='.$group_id.'&aid='.$ah->getID().'&atid='.$ath->getID().'&func='.$key, html_image('ic/'.$key.'.png', 16, 16), array('id' => 'tracker-vote', 'alt' => $txt, 'title' => util_html_secure(html_get_tooltip_description('vote'))));
				}
				$cells[][] = $cellContent;
				$return .= $HTML->multiTableRow(array(), $cells);
			}
			if (forge_check_perm('tracker', $atid, 'manager')) {
				$cells = array();
				$cells[][] = util_make_link('/tracker/?func=deleteartifact&aid='.$aid.'&group_id='.$group_id.'&atid='.$atid, $HTML->getDeletePic().html_e('strong', array(), _('Delete')));
				$return .= $HTML->multiTableRow(array(), $cells);
			}
			$return .= $HTML->listTableBottom();
			return $return;
		} else {
			return $HTML->information(_('No action available.'));
		}
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
