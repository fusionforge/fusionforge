<?php
/**
 * CompactPreviewPlugin Classes
 *
 * Copyright 2011, Sabri LABBENE, Institut TELECOM
 * Copyright 2016, Franck Villaume, TrivialDev
 * http://fusionforge.org/
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
 * TODO Enter description here ...
 * @param unknown_type $params
 */
class CompactResource {
	public $params;

	/**
	 * @param unknown_type $params
	 */
	function __construct($params) {
		$this->params = $params;
	}

	/**
	 * TODO Enter description here ... to be overloaded in subclasses
	 */
	public function getResourceLink() {
		// TBD.
	}

	/**
	 * Enter description here ...
	 * @param array $params : 'resource_type' => 'user' | 'group', 'username' => $username, 'user_id' => $user_id, 'size' => $size, 'user_link' => ''
	 * @return UserCompactResource|GroupCompactResource|CompatResource|string
	 */
	public static function createCompactResource($params) {
		switch ($params['resource_type']) {
			case 'user' :
				return new LocalUserCompactResource($params);
				break;
			case 'group' :
				return new LocalGroupCompactResource($params);
				break;
			case 'artifact' :
				return new CompatResource($params);
				break;
			default :
				return _('Unknown resource type!');
				break;
		}
	}
}

/**
 * Enter description here ...
 * @author olivier
 *
 */
class UserCompactResource extends CompactResource {

	protected function getCssClass() {
		// TBD
	}

	public function getResourceLink() {
		$username = $this->params['username'];
		$user_id = $this->params['user_id'];
		if (isset($this->params['link_text'])) {
			$link_text = $this->params['link_text'];
		} else {
			$link_text = $username;
		}

		// invoke user_logo hook
		$logo_params = array('user_id' => $user_id, 'size' => $this->params['size'], 'content' => '');
		plugin_hook_by_reference('user_logo', $logo_params);

		$html = '';
		// construct a link that is the base for a hover popup (see oslcTooltip.js)
		//$url = '<a class="resourcePopupTrigger" href="'. util_make_url_u ($username, $user_id) .
		//		'" rel="user,' . $username . '">'. $username . '</a>';
		$resource_url = util_make_url_u ($username, $user_id);

		//$url = '<a class="resourcePopupTrigger" href="'. $resource_url .
		//				'" rel="'. $resource_url .'">'. $username . '</a>';
		$css_class = $this->getCssClass();

		$url = util_make_link($resource_url, $link_text, array('class' => $css_class), true);
		if ($logo_params['content']) {
			$html = $logo_params['content'] . $url .'<div class="new_line"></div>';
		} else {
			$html = $url;
		}
		return $html;
	}

}

class LocalUserCompactResource extends UserCompactResource {
	protected function getCssClass() {
		return 'resourceLocalPopupTrigger';
	}
}

class OslcUserCompactResource extends UserCompactResource {
	protected function getCssClass() {
		return 'resourceOslcPopupTrigger';
	}
}

/**
 * Enter description here ...
 * @author olivier
 *
 */
class GroupCompactResource extends CompactResource {

	protected function getCssClass() {
	// TBD
	}

}

class LocalGroupCompactResource extends GroupCompactResource {
	protected function getCssClass() {
		return 'resourceLocalPopupTrigger';
	}
	public function getResourceLink() {
		$group_name = $this->params['group_name'];
		$group_id = $this->params['group_id'];
		$link_text = $this->params['link_text'];
		$resource_url = util_make_url_g ($group_name, $group_id);
		$css_class = $this->getCssClass();
		return util_make_link($resource_url, $link_text, array('class' => $css_class), true);
	}
}

class OslcGroupCompactResource extends GroupCompactResource {
	protected function getCssClass() {
		return 'resourceOslcPopupTrigger';
	}
	public function getResourceLink() {
		$name = $this->params['name'];
		$resource_url = $this->params['url'];
		$css_class = $this->getCssClass();
		return util_make_link($resource_url, $name, array('class' => $css_class), true);
	}
}
