<?php
/**
 * This file is (c) Copyright 2010 by Sabri LABBENE, Institut TELECOM
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
 */

$controller_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
require_once($controller_dir . 'CompactController.php');

require(APPLICATION_PATH.'/../../../../../common/include/env.inc.php');
require_once $gfwww.'include/pre.php';

/**
 * TODO: Enter description here ...
 *
 */
class FusionForgeCompactController extends CompactController {

	private $actionMimeType;
	private static $supportedAcceptMimeTypes = array();

	public function setActionMimeType($action) {
		if(!isset($this->actionMimeType)) {
			$this->actionMimeType = parent::checkSupportedActionMimeType(self::$supportedAcceptMimeTypes, $action);
		}
	}

	public function init(){
		self::$supportedAcceptMimeTypes = parent::getSupportedAcceptMimeTypes();

		// now do things that relate to the REST framework
		$req = $this->getRequest();


		$action = $req->getActionName();

		if($action =='get')	{
			$accept = $req->getHeader('Accept');
		}

		// Set the mime type for action.
		$this->setActionMimeType($action);

		if(isset($this->actionMimeType)) {
		  $accept = $this->actionMimeType;
		}
		//print(self::$supportedAcceptMimeTypes[$action][$accept]); die();
		// determine output format
		if (isset(self::$supportedAcceptMimeTypes[$action])) {
			if (isset(self::$supportedAcceptMimeTypes[$action][$accept])) {
				$format = self::$supportedAcceptMimeTypes[$action][$accept];
				//print_r('format :'.$format); die();
				$req->setParam('format', $format);
			}
		}

		$contextSwitch = $this->_helper->getHelper('contextSwitch');

		$types = array();
		foreach (self::$supportedAcceptMimeTypes as $action => $typesarr) {
			$types = array_unique(array_values($typesarr));
			$contextSwitch->addActionContext($action, $types)->initContext();
		}
	}

	public function getAction(){
		$params = $this->getRequest()->getParams();

		if(isset($params['user']) && isset($params['type']) && $params['type'] == "small") {
				$this->_forward('oslcCompactUserSmall');
				return;
		}
		if(isset($params['user']) && !isset($params['type'])) {
			$this->_forward('oslcCompactUser');
			return;
		}
		if(isset($params['project']) && isset($params['type']) && $params['type'] == "small") {
			$this->_forward('oslcCompactProjectSmall');
			return;
		}
		if(isset($params['project']) && !isset($params['type'])) {
			$this->_forward('oslcCompactProject');
			return;
		}
	}

	/**
	 * TODO: Enter description here ...
	 */
	public function oslccompactuserAction() {
		if (! isset($this->actionMimeType)) {
			$this->_forward('UnknownAcceptType','error');
			return;
		}

		$req = $this->getRequest();
		$params = $req->getParams();

		$user_obj = user_get_object_by_name($params['user']);
		$this->view->user = $user_obj;

		$this->getResponse()->setHeader('Content-Type', 'application/x-oslc-compact+xml');
	}

	/**
	 * Enter description here ...
	 */
	public function oslccompactprojectAction() {
		if (! isset($this->actionMimeType)) {
			$this->_forward('UnknownAcceptType','error');
			return;
		}

		$req = $this->getRequest();
		$params = $req->getParams();

		$group_obj = group_get_object_by_name($params['project']);
		$this->view->project = $group_obj;

		$this->getResponse()->setHeader('Content-Type', 'application/x-oslc-compact+xml');
	}

	/**
	 * TODO: Enter description here ...
	 */
	public function oslccompactusersmallAction() {
		if (! isset($this->actionMimeType)) {
			$this->_forward('UnknownAcceptType','error');
			return;
		}

		$req = $this->getRequest();
		$params = $req->getParams();

		$user_obj = user_get_object_by_name($params['user']);
		$this->view->user = $user_obj;

		$this->getResponse()->setHeader('Content-Type', $this->actionMimeType);
	}

	public function oslccompactprojectsmallAction() {
		if (! isset($this->actionMimeType)) {
			$this->_forward('UnknownAcceptType','error');
			return;
		}

		$req = $this->getRequest();
		$params = $req->getParams();

		$group_obj = group_get_object_by_name($params['project']);
		$this->view->project = $group_obj;

		$this->getResponse()->setHeader('Content-Type', $this->actionMimeType);
	}
}
?>
