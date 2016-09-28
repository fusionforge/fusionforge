<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2016, Stéphane-Eymeric Bredtthauer - TrivialDev
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $gfplugins;
require_once $gfplugins.'taskboard/common/TaskBoard.class.php';

class TaskBoardHtml extends TaskBoard {

	// the header that displays for the user portion of the plugin
	function header($params) {
		global $HTML;

		html_use_tablesorter();
		use_stylesheet('/plugins/taskboard/css/agile-board.css');
		use_javascript('/plugins/taskboard/js/agile-board.js');
		html_use_jqueryui();

		$group_id = $this->Group->getID();
		$taskboard_id = $this->getID();
		$params['toptab'] = 'taskboard';
		$params['group'] = $group_id;

		$labels[] = _('View Taskboards');
		$links[]  = '/plugins/taskboard/?group_id='.$group_id;
		$attr   = array(array('title' => _('Get the list of available taskboards')));
		if (session_loggedin()) {
			if (forge_check_perm('tracker_admin', $group_id)) {
				$labels[] = _('Taskboards Administration');
				$links[]  = 'plugins/taskboard/admin/?group_id='.$group_id;
				$attr[]   = array('title' => _('Global administration for taskboards.'));
			}
		}
		if ($taskboard_id) {
			$labels[] = $this->getName();
			$links[]  = '/plugins/taskboard/?group_id='.$group_id.'&taskboard_id='.$taskboard_id;
			$attr[]   = array('title' => _('View this taskboard.'));

			if( $this->getReleaseField()) {
				$labels[] = _('Releases');
				$links[]  = '/plugins/taskboard/releases/?group_id='.$group_id.'&taskboard_id='.$taskboard_id;
				$attr[]   = array('title' => _('Manage releases.'));
			}
		}

		if (session_loggedin()) {
			if (forge_check_perm('tracker_admin', $group_id)) {
				$release_id = getIntFromRequest('release_id','');
				$view = getStringFromRequest('view','');
				if($release_id) {
					if( $view == 'edit_release' ) {
						$labels[] = _('Delete release');
						$links[]  = '/plugins/taskboard/releases/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&release_id='.$release_id.'&view=delete_release';
					} else {
						$labels[] = _('Edit release');
						$links[]  = '/plugins/taskboard/releases/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&release_id='.$release_id.'&view=edit_release';
					}
				}
			}
		}

		if (session_loggedin()) {
			if (forge_check_perm('tracker_admin', $group_id)) {
				if ($taskboard_id) {
					$labels[] = _('Administration');
					$links[]  = '/plugins/taskboard/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id;
					$attr[]   = array('title' => _('Administration for this taskboard.'));
				}
				$view = getStringFromRequest('view');
				if ($view == 'edit_column') {
					$labels[] = _('Configure Columns');
					$links[]  = '/plugins/taskboard/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&view=columns';

					$column_id = getIntFromRequest('column_id', '');
					if ($column_id) {
						$labels[] = _('Delete Column');
						$links[]  = '/plugins/taskboard/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&column_id='.$column_id.'&view=delete_column';
					}
				}
			}
		}
		$params['submenu'] = $HTML->subMenu($labels, $links, $attr);
		site_project_header($params);
	}

	function trackersColorChooser( $name, $color='Silver' ) {
		if( method_exists($this->TrackersAdapter, 'trackersColorChooser' ) ) {
			return $this->TrackersAdapter->trackersColorChooser( $name, $color );
		} else {
			$l_aColors = array( 'White', 'Khaki', 'Gold', 'LawnGreen', 'PaleGreen', 'Salmon', 'PeachPuff', 'LightBlue', 'Silver' );
			return $this->_colorChooser( $name, $l_aColors, 'Silver', $color );
		}
	}

	function colorBgChooser($name, $color='Silver') {
		$l_aColors = array('White', 'Khaki', 'Gold', 'LawnGreen', 'PaleGreen', 'Salmon', 'PeachPuff', 'LightBlue', 'Silver');
		return $this->_colorChooser($name, $l_aColors, 'Silver', $color);
	}

	private function _colorChooser( $name, $colors, $default_color, $selected_color=NULL ) {
		$ret = '<table><tr>';
		if( !$selected_color ) {
			$selected_color = $default_color;
		}
		foreach( $colors as $color ) {
			$selected = '';
			if( $color == $selected_color ) {
				$selected = ' checked';
			}
			$ret .= '<td style="background-color: '.$color.'; padding: 0;"><input type="radio" name="'.$name.'" value="'.$color.'" style="margin: 6px;"'.$selected.'></td>';
		}
		$ret .= '</tr></table>';

		return $ret;
	}

}
