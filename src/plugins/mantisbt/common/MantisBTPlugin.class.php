<?php
/**
 * MantisBPlugin Class
 *
 * Copyright 2009, Fabien Dubois - Capgemini
 * Copyright 2009-2011, Franck Villaume - Capgemini
 * Copyright 2011, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

class MantisBTPlugin extends Plugin {

	function MantisBTPlugin() {
		$this->Plugin() ;
		$this->name = "mantisbt" ;
		$this->text = "MantisBT" ; // To show in the tabs, use...
		$this->_addHook('user_personal_links'); //to make a link to the user's personal part of the plugin
		$this->_addHook('usermenu');
		$this->_addHook('groupmenu'); // To put into the project tabs
		$this->_addHook('groupisactivecheckbox'); // The "use ..." checkbox in editgroupinfo
		$this->_addHook('groupisactivecheckboxpost'); //
		$this->_addHook('userisactivecheckbox'); // The "use ..." checkbox in user account
		$this->_addHook('userisactivecheckboxpost'); //
		$this->_addHook('project_admin_plugins'); // to show up in the admin page fro group
		$this->_addHook('group_delete');
		$this->_addHook('group_update');
		$this->_addHook('site_admin_option_hook');
		$this->_addHook('widget_instance', 'myPageBox', false);
		$this->_addHook('widgets', 'widgets', false);
	}

	function CallHook($hookname, &$params) {
		global $G_SESSION, $HTML;
		$returned = false;
		switch ($hookname) {
			case "usermenu": {
				if ($G_SESSION->usesPlugin($this->name)) {
					$param = '?type=user&user_id=' . $G_SESSION->getId() . '&pluginname=' . $this->name; // we indicate the part we're calling is the user one
					echo $HTML->PrintSubMenu(array($this->text), array('/plugins/mantisbt/index.php' . $param), array(array('class'=> 'tabtitle', 'title' => _('Personal MantisBT page'))));
				}
				$returned = true;
				break;
			}
			case "groupmenu": {
				$group_id = $params['group'];
				$project = group_get_object($group_id);
				if (!$project || !is_object($project) || $project->isError() || !$project->isProject()) {
					return;
				}
				if ($project->usesPlugin($this->name)) {
					$params['TITLES'][] = $this->text;
					$params['DIRS'][] = '/plugins/'.$this->name.'/?type=group&group_id=' . $group_id . '&pluginname=' . $this->name;
					$params['TOOLTIPS'][] = _('Tickets Management');
					if (session_loggedin()) {
						$user = session_get_user();
						$userperm = $project->getPermission($user);
						if ($userperm->isAdmin()) {
							$params['ADMIN'][] = '/plugins/'.$this->name.'/?type=admin&group_id=' . $group_id . '&pluginname=' . $this->name;
						}
					}
					if (isset($params['toptab'])) {
						(($params['toptab'] == $this->name) ? $params['selected'] = (count($params['TITLES'])-1) : '' );
					}
				}
				$returned = true;
				break;
			}
			case "user_personal_links": {
				// this displays the link in the user's profile page to it's personal MantisBT (if you want other sto access it, youll have to change the permissions in the index.php
				$userid = $params['user_id'];
				$user = user_get_object($userid);
				//check if the user has the plugin activated
				if ($user->usesPlugin($this->name)) {
					echo '<p>';
					$arr_t = array();
					$arr_t[] = array('title' => _('Manage your mantisbt account and follow your tickets'), 'class' => 'tabtitle');
					echo util_make_link('/plugins/'.$this->name.'/?user_id=$userid&type=user&pluginname='.$this->name, _('View Personal MantisBT'), $arr_t);
					echo '</p>';
				}
				$returned = true;
				break;
			}
			case "project_admin_plugins": {
				// this displays the link in the project admin options page to it's  MantisBT administration
				$group_id = $params['group_id'];
				$group = group_get_object($group_id);
				if ($group->usesPlugin($this->name)) {
					echo '<p>';
					echo util_make_link("/plugins/mantisbt/?group_id=$group_id&type=admin&pluginname=".$this->name, _('View Admin MantisBT'), array('class' => 'tabtitle', 'title' => _('MantisBT administration page')));
					echo '</p>';
				}
				$returned = true;
				break;
			}
			case "site_admin_option_hook": {
				echo '<li>'.$this->getAdminOptionLink().'</li>';
				$returned = true;
				break;
			}
			case "group_delete": {
				$group_id = $params['group_id'];
				$group = group_get_object($group_id);
				if ($group->usesPlugin($this->name)) {
					if ($this->isProjectMantisCreated($group_id)) {
						if ($this->removeProjectMantis($group_id)) {
							$returned = true;
						}
					} else {
						$returned = true;
					}
				} else {
					$returned = true;
				}
				break;
			}
			case "group_update": {
				$group_id = $params['group_id'];
				$group_name =$params['group_name'];
				$group_ispublic = $params['group_ispublic'];
				$group = group_get_object($group_id);
				if ($group->usesPlugin($this->name)) {
					if ($this->isProjectMantisCreated($group_id)) {
						if ($this->updateProjectMantis($group_id, $group_name, $group_ispublic)) {
							$returned = true;
						}
					} else {
						$returned = true;
					}
				} else {
					$returned = true;
				}
				break;
			}
			case "widgets": {
				$group = group_get_object($GLOBALS['group_id']);
				if ($group->usesPlugin($this->name)) {
					return $this->widgets($params);
				}
				break;
			}
			case "widget_instance": {
				$group = group_get_object($GLOBALS['group_id']);
				if ($group->usesPlugin($this->name)) {
					return $this->myPageBox($params);
				}
				break;
			}
		}
		return $returned;
	}

	/**
	 * addProjectMantis - inject the Group into Mantisbt thru SOAP
	 *
	 * @param	array	Configuration Array (url, soap_user, soap_password, sync_roles)
	 * @return	bool	success or not
	 */
	function addProjectMantis($groupId, $confArr) {
		$groupObject = group_get_object($groupId);
		$project = array();
		$project['name'] = $groupObject->getPublicName();
		$project['status'] = "development";

		if ($groupObject->isPublic()) {
			$project['view_state'] = 10;
		}else{
			$project['view_state'] = 50;
		}

		$project['description'] = $groupObject->getDescription();

		try {
			$clientSOAP = new SoapClient($confArr['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
			$idProjetMantis = $clientSOAP->__soapCall('mc_project_add', array("username" => $confArr['soap_user'], "password" => $confArr['soap_password'], "project" => $project));
		} catch (SoapFault $soapFault) {
			$groupObject->setError('addProjectMantis::Error: ' . $soapFault->faultstring);
			return false;
		}
		if (!isset($idProjetMantis) || !is_int($idProjetMantis)){
			$groupObject->setError('addProjectMantis::Error: ' . _('Unable to create project in Mantisbt'));
			return false;
		}
		return $idProjetMantis;
	}

	function removeProjectMantis($idProjet) {
		$groupObject = group_get_object($idProjet);
		$localMantisbtConf = $this->getMantisBTConf($groupObject->getID());

		if (!$localMantisbtConf) {
			$groupObject->setError('removeProjetMantis::Error' . ' '. _('No project found'));
			return false;
		} else {
			try {
				$clientSOAP = new SoapClient($localMantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
				$delete = $clientSOAP->__soapCall('mc_project_delete', array("username" => $localMantisbtConf['soap_user'], "password" => $localMantisbtConf['soap_password'], "project_id" => $localMantisbtConf['id_mantisbt']));
			} catch (SoapFault $soapFault) {
				$groupObject->setError('removeProjetMantis::Error' . ' '.$soapFault->faultstring);
				return false;
			}
			if (!isset($delete)) {
				$groupObject->setError('removeProjetMantis:: ' . _('No project found in MantisBT') . ' ' .$localMantisbtConf['id_mantisbt']);
				return false;
			} else {
				$res = db_query_params('DELETE FROM plugin_mantisbt WHERE id_mantisbt = $1',
						array($localMantisbtConf['id_mantisbt']));
				if (!$res) {
					$groupObject->setError('removeProjetMantis:: ' . _('Cannot delete in database') . ' ' .$localMantisbtConf['id_mantisbt']);
					return false;
				}
			}
			return true;
		}
	}

	/**
	 * updateProjectMantis - update the Group informations into Mantisbt
	 * @param	int	id of the Group
	 * @param	string	group name
	 * @param	int	public or private
	 * @return	bool	success or not
	 */
	function updateProjectMantis($groupId, $groupName, $groupIspublic) {
		$groupObject = group_get_object($groupId);
		$projet = array();
		$localMantisbtConf = $this->getMantisBTConf($groupObject->getID());
		$project['name'] = $groupName;
		$project['status'] = "development";

		// should check the config on mantisbt side and not used hard coded values
		if ($groupIspublic) {
			$project['view_state'] = 10;
		} else {
			$project['view_state'] = 50;
		}

		if ($localMantisbtConf['id_mantisbt'] != 0) {
			try {
				$clientSOAP = new SoapClient($localMantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
				$update = $clientSOAP->__soapCall('mc_project_update', array("username" => $localMantisbtConf['soap_user'], "password" => $localMantisbtConf['soap_password'], "project_id" => $localMantisbtConf['id_mantisbt'], "project" => $project));
			} catch (SoapFault $soapFault) {
				$groupObject->setError('updateProjectMantis::Error' . ' '. $soapFault->faultstring);
				return false;
			}
			if (!isset($update)) {
				$groupObject->setError('updateProjectMantis::Error' . ' ' . _('Update MantisBT project'));
				return false;
			}
		} else {
			$groupObject->setError('updateProjectMantis::Error ' . _('ID MantisBT project not found'));
			return false;
		}
		return true;
	}

	/**
	 * isProjectMantisCreated - check if the Project is already created
	 *
	 * @param	int	the Group Id
	 * @return	boolean	created or not
	 */
	function isProjectMantisCreated($idProjet){
		$resIdProjetMantis = db_query_params('SELECT id_mantisbt FROM plugin_mantisbt WHERE id_group = $1',
					array($idProjet));
		if (!$resIdProjetMantis)
			return false;

		if (db_numrows($resIdProjetMantis) > 0) {
			return true;
		}else{
			return false;
		}
	}

	/**
	 * getMantisBTConf - get the mantisbt configuration id for a specific group_id
	 *
	 * @param	integer	the group_id
	 * @return	array	the mantisbt configuration array
	 * @access	public
	 */
	function getMantisBTConf($group_id) {
		$group = group_get_object($group_id);
		$mantisbtConfArray = array();
		$resIdProjetMantis = db_query_params('SELECT * FROM plugin_mantisbt WHERE id_group = $1', array($group_id));
		if (!$resIdProjetMantis) {
			$group->setError('getMantisBTId::error '.db_error());
			return $mantisbtConfArray;
		}

		$row = db_numrows($resIdProjetMantis);

		if ($row == null || count($row) > 2) {
			$mantisbtConfArray['id_mantisbt'] = 0;
			return $mantisbtConfArray;
		}

		$row = db_fetch_array($resIdProjetMantis);
		$mantisbtConfArray['id_mantisbt'] = $row['id_mantisbt'];
		$mantisbtConfArray['sync_roles'] = $row['sync_roles'];
		$mantisbtConfArray['use_global'] = $row['use_global'];
		if ($mantisbtConfArray['use_global']) {
			$mantisbtGlobConf = $this->getGlobalconf();
			$mantisbtConfArray['url'] = $mantisbtGlobConf['url'];
			$mantisbtConfArray['soap_user'] = $mantisbtGlobConf['soap_user'];
			$mantisbtConfArray['soap_password'] = $mantisbtGlobConf['soap_password'];
		} else {
			$mantisbtConfArray['url'] = $row['url'];
			$mantisbtConfArray['soap_user'] = $row['soap_user'];
			$mantisbtConfArray['soap_password'] = $row['soap_password'];
		}

		return $mantisbtConfArray;
	}

	/**
	 * getInitDisplay - display the init page
	 * @return	bool	true only currently
	 */
	function getInitDisplay() {
		global $gfplugins;
		require_once $gfplugins.$this->name.'/view/init.php';
		return true;
	}

	/**
	 * getAdminView - display the admin page
	 * @return	bool	true only currently
	 */
	function getAdminView() {
		global $gfplugins;
		require_once $gfplugins.$this->name.'/www/admin/index.php';
		return true;
	}

	/**
	 * getSubMenu - display the submenu
	 *
	 * @param	int	enable tooltips : default NO
	 * @return	bool	true only currently
	 */
	function getSubMenu() {
		global $HTML;
		global $group_id;
		global $user;
		$group = group_get_object($group_id);
		// submenu
		$labelTitle = array();
		$labelTitle[] = _('Roadmap');
		$labelTitle[] = _('Tickets');
		$labelPage = array();
		$labelPage[] = "/plugins/".$this->name."/?type=group&group_id=".$group_id."&pluginname=".$this->name."&view=roadmap";
		$labelPage[] = "/plugins/".$this->name."/?type=group&group_id=".$group_id."&pluginname=".$this->name;
		$labelAttr = array();
		$labelAttr[] = array('title' => _('View the roadmap, per version tickets'), 'id' => 'roadmapView', 'class' => 'tabtitle-nw');
		$labelAttr[] = array('title' => _('View all tickets.'), 'id' => 'ticketView', 'class' => 'tabtitle');
		$userperm = $group->getPermission($user);
		if ($userperm->isAdmin()) {
			$labelTitle[] = _('Administration');
			$labelPage[] = "/plugins/".$this->name."/?type=admin&group_id=".$group_id."&pluginname=".$this->name;
			$labelTitle[] = _('Statistics');
			$labelPage[] = "/plugins/".$this->name."/?type=admin&group_id=".$group_id."&pluginname=".$this->name."&view=stat";
			$labelAttr[] = array('title' => _('Manage versions, categories and general configuration.'), 'id' => 'adminView', 'class' => 'tabtitle');
			$labelAttr[] = array('title' => _('View global statistics.'), 'id' => 'statView', 'class' => 'tabtitle');
		}

		echo $HTML->subMenu($labelTitle, $labelPage, $labelAttr);
	}

	/**
	 * getHeader - initialize header and js
	 * @param	string	type : user, project (aka group)
	 * @return	bool	success or not
	 */
	function getHeader($type) {
		global $gfplugins;
		$returned = false;
		use_javascript('/plugins/'.$this->name.'/scripts/MantisBTController.js');
		use_stylesheet('/plugins/'.$this->name.'/style.css');
		switch ($type) {
			case 'project': {
				global $group_id;
				$params['toptab'] = $this->name;
				$params['group'] = $group_id;
				$params['title'] = $this->name.' Project Plugin!';
				$params['pagename'] = $this->name;
				$params['sectionvals'] = array(group_getname($group_id));
				site_project_header($params);
				$returned = true;
				break;
			}
			case 'user': {
				global $user_id;
				$params['user_id'] = $user_id;
				site_user_header($params);
				$returned = true;
				break;
			}
			case 'globaladmin': {
				session_require_global_perm('forge_admin');
				global $gfwww;
				require_once($gfwww.'admin/admin_utils.php');
				site_admin_header(array('title'=>_('Site Global MantisBT Admin'), 'toptab' => ''));
				$returned = true;
				break;
			}
			default: {
				break;
			}
		}
		return $returned;
	}

	/**
	 * initialize - initialize the mantisbt plugin
	 *		create mantisbt project if needed
	 *		save config in db
	 * @param	int	the group id
	 * @param	array	configuration array
	 * @return	bool	success or not
	 */
	function initialize($group_id, $confArr) {
		if ($confArr['mantisbtcreate']) {
			$idProjectMantis = $this->addProjectMantis($group_id, $confArr);
		} else {
			$idProjectMantis = $this->getProjectMantisByName($group_id, $confArr);
		}
		if ($idProjectMantis) {
			$result = db_query_params('insert into plugin_mantisbt (id_group, id_mantisbt, url, soap_user, soap_password, sync_roles)
							values ($1, $2, $3, $4, $5, $6)',
							array($group_id,
								$idProjectMantis,
								$confArr['url'],
								$confArr['soap_user'],
								$confArr['soap_password'],
								$confArr['sync_roles']));
			if (!$result)
				return false;

			return true;
		}
		return false;
	}

	/**
	 * initialize - initialize the mantisbt user
	 *		save config in db
	 * @param	array	configuration array
	 * @return	bool	success or not
	 */
	function initializeUser($confArr) {
		global $user;
		$result = db_query_params('insert into plugin_mantisbt_users (id_user, mantisbt_user, mantisbt_password)
							values ($1, $2, $3)',
							array($user->getID(),
								$confArr['mantisbt_user'],
								$confArr['mantisbt_password']));
		if (!$result)
			return false;

		return true;
	}

	/**
	 * updateConf - update the MantisBT plugin configuration
	 *
	 * @param	int	the group_id
	 * @param	array	configuration array
	 * @return	bool	success or not
	 */
	function updateConf($group_id, $confArr) {
		$result = db_query_params('update plugin_mantisbt set url = $1 , soap_user = $2, soap_password = $3, use_global = $4
						where id_group = $5',
					array($confArr['url'],
						$confArr['soap_user'],
						$confArr['soap_password'],
						$confArr['global_conf'],
						$group_id));
		if (!$result)
			return false;

		return true;
	}

	/**
	 * updateUserConf - update the MantisBT User configuration
	 *
	 * @param	array	configuration array
	 * @return	bool	success or not
	 */
	function updateUserConf($confArr) {
		global $user;
		$result = db_query_params('update plugin_mantisbt_users set mantisbt_user = $1 , mantisbt_password = $2
						where id_user = $3',
					array($confArr['mantisbt_user'],
						$confArr['mantisbt_password'],
						$user->getID()));
		if (!$result)
			return false;

		return true;
	}

	/**
	 * getProjectMantisByName - find the project to link with
	 *
	 * @param	array	configuration array
	 * @return	int	the mantisbt id
	 */
	function getProjectMantisByName($group_id, $confArr) {
		$groupObject = group_get_object($group_id);
		try {
			$clientSOAP = new SoapClient($confArr['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
			$mantisbtProjectArr = $clientSOAP->__soapCall('mc_projects_get_user_accessible', array("username" => $confArr['soap_user'], "password" => $confArr['soap_password']));
		} catch (SoapFault $soapFault) {
			$groupObject->setError('getProjectMantisByName::Error: ' . $soapFault->faultstring);
			return false;
		}
		foreach ($mantisbtProjectArr as $mantisbtProject) {
			if ($mantisbtProject->name == $confArr['mantisbtname']) {
				return $mantisbtProject->id;
			}
		}
		$groupObject->setError('getProjectMantisByName::Error: mantisbt project not found');
		return false;
	}

	/**
	 * getUserConf - return the user / password for the user id mantisbt account
	 *
	 * @return	array	the user configuration
	 */
	function getUserConf() {
		global $user;
		$userConf = array();
		$resIdUser = db_query_params('SELECT mantisbt_user, mantisbt_password FROM plugin_mantisbt_users WHERE id_user = $1', array($user->getID()));
		if (!$resIdUser) {
			$user->setError('getUserConf::error '.db_error());
			return false;
		}

		$row = db_numrows($resIdUser);

		if ($row == null || count($row) > 2) {
			return false;
		}

		$row = db_fetch_array($resIdUser);
		$userConf['user'] = $row['mantisbt_user'];
		$userConf['password'] = $row['mantisbt_password'];
		$userConf['url'] = array();
		foreach ($user->getGroups() as $groupObject) {
			if ($groupObject->usesPlugin($this->name)) {
				$mantisbtGroupConf = $this->getMantisBTConf($groupObject->getID());
				$userConf['url'][] = $mantisbtGroupConf['url'];
			}
		}
		return $userConf;
	}

	/**
	 * getGlobalconf - return the global configuration defined at forge level
	 *
	 * @return	array	the global configuration array
	 */
	function getGlobalconf() {
		$resGlobConf = db_query_params('SELECT * from plugin_mantisbt_global',array());
		if (!$resGlobConf) {
			return false;
		}

		$row = db_numrows($resGlobConf);

		if ($row == null || count($row) > 2) {
			return false;
		}

		return db_fetch_array($resGlobConf);
	}

	/**
	 * updateGlobalConf - update the global configuration in database
	 *
	 * @param	array	configuration array (url, soap_user, soap_password)
	 * @return	bool	true on success
	 */
	function updateGlobalConf($confArr) {
		if (!isset($confArr['url']) || !isset($confArr['soap_user']) || !isset($confArr['soap_password']))
			return false;

		$res = db_query_params('truncate plugin_mantisbt_global', array());
		if (!$res)
			return false;

		$res = db_query_params('insert into plugin_mantisbt_global (url, soap_user, soap_password)
					values ($1, $2, $3)',
					array(
						$confArr['url'],
						$confArr['soap_user'],
						$confArr['soap_password'],
					));
		if (!$res)
			return false;

		return true;
	}

	/**
	 * getGlobalAdminView - display the Global Admin View
	 *
	 * @return	bool	true
	 */
	function getGlobalAdminView() {
		global $gfplugins;
		$user = session_get_user();
		include $gfplugins.$this->name.'/view/admin/viewGlobalConfiguration.php';
		return true;
	}

	function getAdminOptionLink() {
		return util_make_link('/plugins/'.$this->name.'/?type=globaladmin&pluginname='.$this->name, _('Global MantisBT admin'), array('class' => 'tabtitle', 'title' => _('Direct link to global configuration of this plugin')));
	}

	function widgets($params) {
 		require_once('common/widget/WidgetLayoutManager.class.php');
		if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_GROUP) {
			$params['fusionforge_widgets'][] = 'plugin_mantisbt_project_latestissues';
		}
		return true;
	}

	function myPageBox($params) {
		global $gfplugins;
		require_once('common/widget/WidgetLayoutManager.class.php');
		if ($params['widget'] == 'plugin_mantisbt_project_latestissues') {
			require_once $gfplugins.$this->name.'/common/mantisbt_Widget_ProjectLastIssues.class.php';
			$params['instance'] = new mantisbt_Widget_ProjectLastIssues($this);
		}
	}
}
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
