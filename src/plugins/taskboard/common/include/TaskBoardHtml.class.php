<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
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
		global $HTML, $group_id;

		html_use_tablesorter();
		use_stylesheet('/plugins/taskboard/css/agile-board.css');
		use_javascript('/plugins/taskboard/js/agile-board.js');
		html_use_jqueryui();

		$params['toptab'] = 'taskboard';
		$params['group'] = $group_id;

		$labels[] = _('View Taskboard');
		$links[]  = '/plugins/taskboard/?group_id='.$group_id;

		if( $this->getReleaseField()) {
			$labels[] = _('Releases');
			$links[]  = '/plugins/taskboard/releases/?group_id='.$group_id;
		}

		if (session_loggedin()) {
			if (forge_check_perm('tracker_admin', $this->Group->getID())) {
				$release_id = getIntFromRequest('release_id','');
				$view = getStringFromRequest('view','');
				if($release_id) {
					if( $view == 'edit_release' ) {
						$labels[] = _('Delete release');
						$links[]  = '/plugins/taskboard/releases/?group_id='.$group_id.'&view=delete_release&release_id='.$release_id;
					} else {
						$labels[] = _('Edit release');
						$links[]  = '/plugins/taskboard/releases/?group_id='.$group_id.'&view=edit_release&release_id='.$release_id;
					}
				}
			}
		}

		if (session_loggedin()) {
			if (forge_check_perm('tracker_admin', $this->Group->getID())) {
				$labels[] = _('Administration');
				$links[]  = '/plugins/taskboard/admin/?group_id='.$group_id;

				$view = getStringFromRequest('view');
				if ($view == 'edit_column') {
					$labels[] = _('Configure Columns');
					$links[]  = '/plugins/taskboard/admin/?group_id='.$group_id.'&view=columns';

					$column_id = getIntFromRequest('column_id', '');
					if ($column_id) {
						$labels[] = _('Delete Column');
						$links[]  = '/plugins/taskboard/admin/?group_id='.$group_id.'&view=delete_column&column_id='.$column_id;
					}
				}
			}
		}
		$params['submenu'] = $HTML->subMenu($labels, $links);
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
