<?php
/**
 * MantisBPlugin Class
 *
 * Copyright 2009, Fabien Dubois - Capgemini
 * Copyright 2009-2010, Franck Villaume - Capgemini
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

/*
 * @todo :	need a massive cleanup
 *		deal correctly with password (might need direct db access ?)
 *		limit non SOAP call aka direct db access to mantisbt
 */

require_once 'include/database-pgsql.php';

class MantisBTPlugin extends Plugin {
	function MantisBTPlugin () {
		$this->Plugin() ;
		$this->name = "mantisbt" ;
		$this->text = "MantisBT" ; // To show in the tabs, use...
		$this->hooks[] = "user_personal_links"; //to make a link to the user's personal part of the plugin
		$this->hooks[] = "usermenu" ;
		$this->hooks[] = "groupmenu" ; // To put into the project tabs
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; //
		$this->hooks[] = "userisactivecheckbox" ; // The "use ..." checkbox in user account
		$this->hooks[] = "userisactivecheckboxpost" ; //
		$this->hooks[] = "project_admin_plugins"; // to show up in the admin page fro group
		$this->hooks[] = "change_cal_permission";
		$this->hooks[] = "change_cal_mail";
		$this->hooks[] = "add_cal_link_father";
		$this->hooks[] = "del_cal_link_father";
		$this->hooks[] = "group_approved";
		$this->hooks[] = "group_delete";
		$this->hooks[] = "group_update";
	}

	function CallHook ($hookname, $params) {
		global $G_SESSION, $HTML;
		switch ($hookname) {
			case "usermenu": {
				$text = $this->text; // this is what shows in the tab
				if ($G_SESSION->usesPlugin($this->name)) {
					$param = '?type=user&id=' . $G_SESSION->getId() . "&pluginname=" . $this->name; // we indicate the part we're calling is the user one
					echo ' | ' . $HTML->PrintSubMenu (array ($text), array ('/plugins/mantisbt/index.php' . $param ));
				}
				break;
			}
			case "groupmenu": {
				$group_id=$params['group'];
				$project = group_get_object($group_id);
				if (!$project || !is_object($project) || $project->isError() || !$project->isProject()) {
					return;
				}
				if ($project->usesPlugin($this->name)) {
					$params['TITLES'][]=$this->text;
					$params['DIRS'][]='/plugins/' . $this->name . '/?type=group&id=' . $group_id . "&pluginname=" . $this->name;
				}
				if ($params['toptab'] == $this->name) {
					$params['selected']=(count($params['TITLES'])-1);
				}
				break;
			}
			case "groupisactivecheckboxpost": {
				// update users and roles in mantis
				$members = array ();
				foreach($group->getMembers() as $member){
					$members[] = $member->data_array['user_name'];
				}
				$this->updateUsersProjectMantis($group->data_array['group_id'],$members);
				break;
			}
			case "user_personal_links": {
				// this displays the link in the user's profile page to it's personal MantisBT (if you want other sto access it, youll have to change the permissions in the index.php
				$userid = $params['user_id'];
				$user = user_get_object($userid);
				$text = $params['text'];
				//check if the user has the plugin activated
				if ($user->usesPlugin($this->name)) {
					echo '	<p>' ;
					echo util_make_link ("/plugins/mantisbt/index.php?id=$userid&type=user&pluginname=".$this->name,
					_('View Personal MantisBT')
					);
					echo '</p>';
				}
				break;
			}
			case "project_admin_plugins": {
				// this displays the link in the project admin options page to it's  MantisBT administration
				$group_id = $params['group_id'];
				$group = group_get_object($group_id);
				if ( $group->usesPlugin ( $this->name ) ) {
					echo util_make_link ("/plugins/mantisbt/index.php?id=$group_id&type=admin&pluginname=".$this->name,
					_('View Admin MantisBT')
					);
					echo '<br/>';
				}
				break;
			}
			case "group_approved": {
				$group_id=$params['group_id'];
				$group = group_get_object($group_id);
				if ($group->usesPlugin($this->name)) {
					// ajout du projet mantis s'il n'existe pas
					if (!$this->isProjetMantisCreated($group->data_array['group_id'])){
						$this->addProjetMantis($group->data_array['group_id'],$group->data_array['group_name'],$group->data_array['is_public'], $group->data_array['short_description']);
					}
					// mise a jour des utilisateurs avec les roles
					$members = array ();
					foreach($group->getMembers() as $member){
						$members[] = $member->data_array['user_name'];
					}
					$this->updateUsersProjetMantis($group->data_array['group_id'],$members);
				}
				break;
			}
			case "change_cal_permission": {
				// mise a jour des utilisateurs avec les roles
				$group_id=$params[1];
				$group = group_get_object($group_id);
				$members = array ();
				foreach($group->getMembers() as $member){
					$members[] = $member->data_array['user_name'];
				}
				$this->updateUsersProjetMantis($group->data_array['group_id'],$members);
				break;
			}
			// mise a jour de l'adresse mail utilisateur
			case "change_cal_mail": {
				$user_id=$params[1];
				$this->updateUserInMantis($user_id);
				break;
			}
			case "add_cal_link_father":
			case "del_cal_link_father": {
				$sub_group_id = $params[0];
				$group_id = $params[1];
				$this->refreshHierarchyMantisBt();
				break;
			}
			case "group_delete": {
				$group_id=$params['group_id'];
				$group = group_get_object($group_id);
				if ($group->usesPlugin($this->name)) {
					if ($this->isProjectMantisCreated($group->data_array['group_id'])) {
						$this->removeProjectMantis($group->data_array['group_id']);
					}
				}
				break;
			}
			case "group_update": {
				$group_id=$params['group_id'];
				$group = group_get_object($group_id);
				if ($group->usesPlugin($this->name)) {
					if ($this->isProjectMantisCreated($group_id)) {
						$this->updateProjectMantis($group_id, $params['group_name'], $group->data_array['is_public'], $group->data_array['short_description']);
					}
				}
				break;
			}
		}
	}

	function groupisactivecheckboxpost(&$params) {
		// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
		$group = group_get_object($params['group']);
		$flag = strtolower('use_'.$this->name);
		if ( getStringFromRequest($flag) == 1 ) {
			if (!$this->isProjectMantisCreated($group->data_array['group_id'])){
				if($this->addProjectMantis($group)) {
					$group->setPluginUse($this->name);
				}
			}
		} else {
			$group->setPluginUse($this->name, false);
		}
	}

	/*
	 * @param	object	The Group
	 * @return	bool	success or not
	 */
	function addProjectMantis(&$groupObject) {

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
			$clientSOAP = new SoapClient(forge_get_config('server_url','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
			$idProjetMantis = $clientSOAP->__soapCall('mc_project_add', array("username" => forge_get_config('adminsoap_user', 'mantisbt'), "password" => forge_get_config('adminsoap_passwd', 'mantisbt'), "project" => $project));
		} catch (SoapFault $soapFault) {
			$groupObject->setError('addProjectMantis::Error: ' . $soapFault->faultstring);
			return false;
		}
		if (!isset($idProjetMantis) || !is_int($idProjetMantis)){
			$groupObject->setError('addProjectMantis::Error: ' . _('Unable to create project in Mantisbt'));
			return false;
		}else{
			$res = db_query_params('INSERT INTO group_mantisbt (id_group, id_mantisbt) VALUES ($1,$2)',
					array($groupObject->getID(), $idProjetMantis));
			if (!$res) {
				$groupObject->setError('addProjectMantis::Error: ' . _('db_error') . ' ' .db_error());
				return false;
			}
		}
		return true;
	}

	function removeProjectMantis($idProjet) {
	
		$resIdProjetMantis = db_query_params('SELECT group_mantisbt.id_mantisbt FROM group_mantisbt WHERE group_mantisbt.id_group = $1',
						array($idProjet));

		echo db_error();
		$row = db_fetch_array($resIdProjetMantis);

		if ($row == null || count($row)>2) {
			echo 'removeProjetMantis:: ' . _('No project found');
		}else{
			$idMantisbt = $row['id_mantisbt'];
			try {
				$clientSOAP = new SoapClient(forge_get_config('server_url','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
				$delete = $clientSOAP->__soapCall('mc_project_delete', array("username" => forge_get_config('adminsoap_user','mantisbt'), "password" => forge_get_config('adminsoap_password','mantisbt'), "project_id" => $idMantisbt));
			} catch (SoapFault $soapFault) {
				echo $soapFault->faultstring;
			}
			if (!isset($delete)){
				echo 'removeProjetMantis:: ' . _('No project found in MantisBT') . ' ' .$idProjet;
			}else{
				db_query_params('DELETE FROM group_mantisbt WHERE group_mantisbt.id_mantisbt = $1',
						array($idMantisbt));
				echo db_error();
			}
		}
	}

	function updateProjectMantis($idProjet, $nomProjet, $isPublic, $description) {

		$projet = array();
		$project['name'] = $nomProjet;
		$project['status'] = "development";

		if ($isPublic == "1"){
			$project['view_state'] = 10;
		}else{
			$project['view_state'] = 50;
		}

		$resIdProjetMantis = db_query_params('SELECT group_mantisbt.id_mantisbt FROM group_mantisbt WHERE group_mantisbt.id_group = $1',
						array($idProjet));
		echo db_error();
		$row = db_fetch_array($resIdProjetMantis);
		if ($row == null || count($row)>2) {
			echo 'updateProjectMantis:: ' . _('No project found');
		}else{
			$idMantisbt = $row['id_mantisbt'];
			try {
				$clientSOAP = new SoapClient(forge_get_config('server_url','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
				$update = $clientSOAP->__soapCall('mc_project_update', array("username" => forge_get_config('adminsoap_user','mantisbt'), "password" => forge_get_config('adminsoap_password','mantisbt'), "project_id" => $idMantisbt, "project" => $project));;
			} catch (SoapFault $soapFault) {
				echo $soapFault->faultstring;
			}
			if (!isset($update))
				echo 'updateProjectMantis::Error ' . _('Update MantisBT project');
		}
	}

	function isProjectMantisCreated($idProjet){

		$resIdProjetMantis = db_query_params('SELECT group_mantisbt.id_mantisbt FROM group_mantisbt WHERE group_mantisbt.id_group = $1',
					array($idProjet));
		if (!$resIdProjetMantis)
			return false;

		if (db_numrows($resIdProjetMantis) > 0) {
			return true;
		}else{
			return false;
		}
	}

	function updateUserInMantis($user_id) {
		global $sys_mantisbt_host, $sys_mantisbt_db_user, $sys_mantisbt_db_password, $sys_mantisbt_db_port, $sys_mantisbt_db_name;
		// recuperation du nouveau mail
		$resUser = db_query_params ('SELECT user_name, email FROM users WHERE user_id = $1',array($user_id));
		echo db_error();
		$row = db_fetch_array($resUser);
		$dbConnection = db_connect_host($sys_mantisbt_db_name, $sys_mantisbt_db_user, $sys_mantisbt_db_password, $sys_mantisbt_host, $sys_mantisbt_db_port);
		if(!$dbConnection){
			$errMantis1 = "Error : Could not open connection" . db_error($dbConnection);
			echo $errMantis1;
			db_rollback($dbConnection);
		} else {
			db_query_params('UPDATE mantis_user_table set email = $1 where username = $2',array($row['email'],$row['user_name']),'-1','0',$dbConnection);
			echo db_error();
		}
	}

	function updateUsersProjectMantis($idProjet, $members) {

		global $role;
		global $sys_mantisbt_host, $sys_mantisbt_db_user, $sys_mantisbt_db_password, $sys_mantisbt_db_port, $sys_mantisbt_db_name;

		// recuperation de id mantis
		$idMantis = getIdProjetMantis($idProjet);

		// TODO corriger inclusion bug
		if ($role == null){
			$role['Manager'] = 70;
			$role['Concepteur'] = 55;
			$role['Collaborateur'] = 55;
			$role['Rapporteur'] = 55;
		}

		// etat forge
		$stateForge = array ();
		foreach ($members as $key => $member){
			$resUserRole = db_query_params('SELECT role.role_name
							FROM role, user_group, users
							WHERE users.user_name = $1
							AND ( user_group.user_id = users.user_id AND user_group.group_id = $2 )
							AND user_group.role_id = role.role_id',
							array($member, $idProjet));
			echo db_error();
			$row = db_fetch_array($resUserRole);
			$stateForge[$member]['name'] = $member;
			$stateForge[$member]['role'] = $row['role_name']; 
		}
		// on supprime les precedentes relations dans mantis
		$stateMantis = array ();
		$dbConnection = db_connect_host($sys_mantisbt_db_name, $sys_mantisbt_db_user, $sys_mantisbt_db_password, $sys_mantisbt_host, $sys_mantisbt_db_port);
		if(!$dbConnection){
			$errMantis1 = _('Error : Could not open connection') . db_error($dbConnection);
			echo $errMantis1;
			db_rollback($dbConnection);
		}else{
			$result = pg_delete($dbConnection,"mantis_project_user_list_table",array("project_id"=>$idMantis));
			if (!$result){
				echo 'updateUsersProjectMantis::Error '. _('Unable to clean roles in Mantisbt');
			}else{
				foreach($stateForge as $member => $array){

					// recuperation de l'id user dans mantis
					$resultIdUser = db_query_params('SELECT mantis_user_table.id FROM mantis_user_table WHERE mantis_user_table.username = $1',
								array($member), '-1', 0, $dbConnection);

					$rowIdUser = db_fetch_array($resultIdUser);
					$idUser = $rowIdUser['id'];
					// insertion de la relation
					$resultInsert = pg_insert($dbConnection,
									"mantis_project_user_list_table",
									array("project_id" => $idMantis, "user_id" => $idUser, "access_level" => $role[$array['role']])
								);
					if (!isset($resultInsert))
						echo 'updateUsersProjectMantis::Error '. _('Unable to update roles in mantisbt');

				}
			}
		}
	}

	function refreshHierarchyMantisBt(){
		global $sys_mantisbt_host, $sys_mantisbt_db_user, $sys_mantisbt_db_password, $sys_mantisbt_db_port, $sys_mantisbt_db_name;

		$hierarchies=db_query_params('SELECT project_id, sub_project_id FROM plugin_projects_hierarchy WHERE activated=true',array());
		echo db_error();
		$dbConnection = db_connect_host($sys_mantisbt_db_name, $sys_mantisbt_db_user, $sys_mantisbt_db_password, $sys_mantisbt_host, $sys_mantisbt_db_port);
		if(!$dbConnection){
			db_rollback($dbConnection);
			return false;
		}

		db_begin($dbConnection);
		db_query_params('TRUNCATE TABLE mantis_project_hierarchy_table', array() , '-1', 0, $dbConnection);
		while ($hierarchy = db_fetch_array($hierarchies)) {
			$result = db_query_params ('INSERT INTO mantis_project_hierarchy_table (child_id, parent_id, inherit_parent) VALUES ($1, $2, $3)',
						array (getIdProjetMantis($hierarchy['sub_project_id']), getIdProjetMantis($hierarchy['project_id']), 1),
						'-1',
						0,
						$dbConnection);

			if (!$result) {
				$this->setError(_('Insert Failed') . db_error($dbConnection));
				db_rollback();
				return false;
			}
		}

		db_commit($dbConnection);
		pg_close($dbConnection);
		return true;
	}

}

function getIdProjetMantis($idProjet){

	$resIdProjetMantis = db_query_params('SELECT group_mantisbt.id_mantisbt FROM group_mantisbt WHERE group_mantisbt.id_group = $1',
				array($idProjet));
	echo db_error();
	$row = db_fetch_array($resIdProjetMantis);
	if ($row == null) {
		return 0;
	}else{
		return $row['id_mantisbt'];
	}

}
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
