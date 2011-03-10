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

require_once dirname(dirname(__FILE__)).'/www/CompactRessourceView.class.php';
require_once 'utils.php';

class CompactRessource {
	
	private $view = null;
	
	public function __construct($params){
		$this->params = $params;
		/*if (isset($params['username'])) {
			//$this->ressource_type = $params['ressourceType'];
			$this->ressource_uri = $this->serverUrl().'/users/'.$params['username'];
			//$this->username = $params['username'];
			//$this->user_link = $params['user_link'];
		}*/

		$this->view = new CompactRessourceView();
	}
	
	public function compactUserLink($username, $user_id) {
		$ressource_uri = util_make_url('/plugins/oslc/compact/user/'.$username);
		$url = '<a href="'. util_make_url_u ($username, $user_id) . '"' .
		' onmouseover="hover(\''. $ressource_uri . '\', \'compact_user_' . $username . '\');" onmouseout="closeHover();">' . 
		$username . '</a>';
		// Add div that will contain the popup
		$url .= '<div id="compact_user_'.$username.'"></div>';
		return $url;
	}
	
	public function CompactUser() {
		$params['userUri'] = util_make_url_u ($this->params['user']->getUnixName(), $this->params['user']->getID());
		$params['userCompactUri'] = util_make_url('/plugins/oslc/compact/user/'.$this->params['user']->getUnixName());
		// full name
		$user_title = $this->params['user']->getTitle();
		$params['title'] = ($user_title ? $user_title .' ' :''). $this->params['user']->getRealName();
		// login name
		$params['shortTitle'] = $this->params['user']->getUnixName();
		
		$params['iconUrl'] = "";
		
		return $this->view->CompactUserView($params);
	}
	
	public function CompactChangeRequest(){
		$params['ressourceUri'] = "";
		$params['title'] = "";
		$params['shortTitle'] = "";
		$params['iconUrl'] = "";
		$params['smUrl'] = "";
		$params['lgUrl'] = "";
		
		return $this->view->CompactChangeRequestView($params);
	}
	
}
?>