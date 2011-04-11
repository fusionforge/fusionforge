<?php 
/**
 * This file is (c) Copyright 2011 by Sabri LABBENE, Institut TELECOM
 *
 * This file is part of FusionForge.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * This program has been developed in the frame of the HELIOS
 * project with financial support of its funders.
 *
 */

class compactResource {
	public $params;
	
	public function __construct($params) {
		$this->params = $params;
	}
	
	public function getResourceLink() {
		if($this->params['resource_type'] == 'user') {
			return $this->getUserLink($this->params['username'], $this->params['user_id']);
		} elseif($this->params['resource_type'] == 'group') {
			return $this->getProjectLink($this->params['group_name'], $this->params['group_id'], $this->params['link_text']);
		} elseif($this->params['resource_type'] == 'artifact') {
			return $this->getArtifactLink();
		} else {
			return 'Unknown resource type !';
		}
	}
	
	public function getUserLink($username, $user_id) {
		return '<a class="personPopupTrigger" href="'. util_make_url_u ($username, $user_id) .
				'" rel="user,' . $username . '">'. $username . '</a>';
	}	
	
	public function getProjectLink($group_name, $group_id, $link_text) {
		return '<a class="personPopupTrigger" href="'. util_make_url_g ($group_name, $group_id) .
				'" rel="project,' . $group_name . '">'. $link_text . '</a>';
	}
	
	public function getArtifactLink() {
		// TBD.
	}
} 

?>