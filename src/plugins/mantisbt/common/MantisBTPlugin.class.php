<?php

/**
 * MantisBPlugin Class
 *
 * Copyright 2009 - 2010 (c) : Franck Villaume - Capgemini
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once 'include/database-pgsql.php';

class MantisBTPlugin extends Plugin {
	function MantisBTPlugin () {
		$this->Plugin() ;
		$this->name = "mantisbt" ;
		$this->text = "MantisBT" ; // To show in the tabs, use...
		$this->hooks[] = "user_personal_links";//to make a link to the user's personal part of the plugin
		$this->hooks[] = "usermenu" ;
		$this->hooks[] = "groupmenu" ;	// To put into the project tabs
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

	function CallHook ($hookname, &$params) {
		global $use_mantisbtplugin,$G_SESSION,$HTML;
		switch ($hookname) {
			case "usermenu":
				$text = $this->text; // this is what shows in the tab
				if ($G_SESSION->usesPlugin($this->name)) {
					$param = '?type=user&id=' . $G_SESSION->getId() . "&pluginname=" . $this->name; // we indicate the part we're calling is the user one
					echo ' | ' . $HTML->PrintSubMenu (array ($text),
					array ('/plugins/mantisbt/index.php' . $param ));
				}
				break;

			case "groupmenu":
				$group_id=$params['group'];
				$project = &group_get_object($group_id);
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

			case "groupisactivecheckbox":
				//Check if the group is active
				// this code creates the checkbox in the project edit public info page to activate/deactivate the plugin
				$group_id=$params['group'];
				$group = &group_get_object($group_id);
				echo "<tr>";
				echo "<td>";
				echo ' <input type="CHECKBOX" name="use_mantisbtplugin" value="1" ';
				// CHECKED OR UNCHECKED?
				if ( $group->usesPlugin ( $this->name ) ) {
					echo 'checked="checked"';
				}
				echo "><br/>";
				echo "</td>";
				echo "<td>";
				echo "<strong>Use ".$this->text." Plugin</strong>";
				echo "</td>";
				echo "</tr>";
				break;

			case "groupisactivecheckboxpost":
				// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
				$group_id=$params['group'];
				$group = &group_get_object($group_id);
				$use_mantisbtplugin = getIntFromRequest('use_mantisbtplugin');
				if ( $use_mantisbtplugin == "1" ) {
					if (! $group->usesPlugin($this->name)) {
						// activation plugin
						$group->setPluginUse($this->name, true);
					
						// ajout du projet mantis s'il n'existe pas
						if (!isProjetMantisCreated($group->data_array['group_id'])){
							addProjetMantis($group->data_array['group_id'],$group->data_array['group_name'],$group->data_array['is_public'], $group->data_array['short_description']);
						}
						// mise a jour des utilisateurs avec les roles
						$members = array ();
						foreach($group->getMembers() as $member){
							$members[] = $member->data_array['user_name'];
						}
						//updateUsersProjetMantis($group->data_array['group_id'],$members);
					}
				} else if ( $use_mantisbtplugin == "0" ) {
					$group->setPluginUse ( $this->name, false );
				}
				break;

			case "userisactivecheckbox":
				//check if user is active
				// this code creates the checkbox in the user account manteinance page to activate/deactivate the plugin
				$user = $params['user'];
				echo "<tr>";
				echo "<td>";
				echo ' <input type="CHECKBOX" name="use_mantisbtplugin" value="1" ';
				// CHECKED OR UNCHECKED?
				if ( $user->usesPlugin ( $this->name ) ) {
					echo 'checked="CHECKED"';
				}
				echo ">    Use ".$this->text." Plugin";
				echo "</td>";
				echo "</tr>";
				break;

			case "userisactivecheckboxpost":
				// this code actually activates/deactivates the plugin after the form was submitted in the user account manteinance page
				$user = $params['user'];
				$use_mantisbtplugin = getIntFromRequest('use_mantisbtplugin');
				if ( $use_mantisbtplugin == 1 ) {
					$user->setPluginUse ( $this->name );
				} else {
					$user->setPluginUse ( $this->name, false );
				}
                break;

			case "user_personal_links":
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

			case "project_admin_plugins":
				// this displays the link in the project admin options page to it's  MantisBT administration
				$group_id = $params['group_id'];
				$group = &group_get_object($group_id);
				if ( $group->usesPlugin ( $this->name ) ) {
					echo util_make_link ("/plugins/mantisbt/index.php?id=$group_id&type=admin&pluginname=".$this->name,
					_('View Admin MantisBT')
					);
					echo '<br/>';
				}
				break;

			case "group_approved":
				$group_id=$params['group_id'];
				$group = &group_get_object($group_id);
				if ($group->usesPlugin($this->name)) {
					// ajout du projet mantis s'il n'existe pas
					if (!isProjetMantisCreated($group->data_array['group_id'])){
						addProjetMantis($group->data_array['group_id'],$group->data_array['group_name'],$group->data_array['is_public'], $group->data_array['short_description']);
					}
                    exit;
					// mise a jour des utilisateurs avec les roles
					$members = array ();
					foreach($group->getMembers() as $member){
						$members[] = $member->data_array['user_name'];
					}
					//updateUsersProjetMantis($group->data_array['group_id'],$members);
				}
				break;

			case "change_cal_permission":
				// mise a jour des utilisateurs avec les roles
				$group_id=$params[1];
				$group = &group_get_object($group_id);
				$members = array ();
				foreach($group->getMembers() as $member){
					$members[] = $member->data_array['user_name'];
				}
				//updateUsersProjetMantis($group->data_array['group_id'],$members);
				break;

            // mise a jour de l'adresse mail utilisateur
            case "change_cal_mail":
                $user_id=$params[1];
                updateUserInMantis($user_id);
                break;

			case "add_cal_link_father":
			case "del_cal_link_father":
				$sub_group_id = $params[0];
				$group_id = $params[1];
				refreshHierarchyMantisBt();
				break;

			case "group_delete":
				$group_id=$params['group_id'];
				$group = &group_get_object($group_id);
				if ($group->usesPlugin($this->name)) {
					if (isProjetMantisCreated($group->data_array['group_id'])) {
						removeProjetMantis($group->data_array['group_id']);
					}
				}
				break;

			case "group_update":
				$group_id=$params['group_id'];
				$group = &group_get_object($group_id);
				if ($group->usesPlugin($this->name)) {
					if (isProjetMantisCreated($group_id)) {
						updateProjetMantis($group_id, $params['group_name'], $group->data_array['is_public'], $group->data_array['short_description']);
					}
				}
				break;
		}
	}
}

function addProjetMantis($idProjet, $nomProjet, $isPublic, $description) {
	
	$projet = array();
	$project['name'] = $nomProjet;
	$project['status'] = "development";
	
	if ($isPublic == "1"){
		$project['view_state'] = 10;
	}else{
		$project['view_state'] = 50;
	}

	$project['description'] = $description;
	
	try{
	    $clientSOAP = new SoapClient("http://".forge_get_config('server','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
		$idProjetMantis = $clientSOAP->__soapCall('mc_project_add', array("username" => forge_get_config('adminsoap_user','mantisbt'), "password" => forge_get_config('adminsoap_password','mantisbt'), "project" => $project));
	}catch (SoapFault $soapFault) {
		echo $soapFault->faultstring;
        exit;
	}	
	if (!isset($idProjetMantis) || !is_int($idProjetMantis)){
		echo "Error : Impossible de créer le projet dans mantis";
        exit;
	}else{
		db_query_params('INSERT INTO group_mantisbt (id_group, id_mantisbt) VALUES ($1,$2)',
				array($idProjet, $idProjetMantis));
		echo db_error();
	}
}

function removeProjetMantis($idProjet) {
	
	$resIdProjetMantis = db_query_params('SELECT group_mantisbt.id_mantisbt FROM group_mantisbt WHERE group_mantisbt.id_group = $1',
					array($idProjet));
	
	echo db_error();
	$row = db_fetch_array($resIdProjetMantis);

	if ($row == null || count($row)>2) {
		echo "Erreur : impossible de retrouver le projet au sein de mantisbt";
	}else{
		$idMantisbt = $row['id_mantisbt'];
		try{
		    $clientSOAP = new SoapClient("http://".forge_get_config('server','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
			$delete = $clientSOAP->__soapCall('mc_project_delete', array("username" => forge_get_config('adminsoap_user','mantisbt'), "password" => forge_get_config('adminsoap_password','mantisbt'), "project_id" => $idMantisbt));
		}catch (SoapFault $soapFault) {
			echo $soapFault->faultstring;
		}
		if (!isset($delete)){
			echo "Error : Impossible de supprimer le projet dans mantis : ".$idProjet;
			echo "<br/>";
		}else{
			db_query_params('DELETE FROM group_mantisbt WHERE group_mantisbt.id_mantisbt = $1',
					array($idMantisbt));
			echo db_error();
		}
	}
}

function updateProjetMantis($idProjet,$nomProjet,$isPublic, $description) {

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
		echo "Erreur : impossible de retrouver le projet au sein de mantisbt";
	}else{
		$idMantisbt = $row['id_mantisbt'];
		try{
		    $clientSOAP = new SoapClient("http://".forge_get_config('server','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
			$update = $clientSOAP->__soapCall('mc_project_update', array("username" => forge_get_config('adminsoap_user','mantisbt'), "password" => forge_get_config('adminsoap_password','mantisbt'), "project_id" => $idMantisbt, "project" => $project));;
		} catch (SoapFault $soapFault) {
			echo $soapFault->faultstring;
		}
		if (!isset($update))
			echo "Error : update MantisBT projet";

	}
}

function isProjetMantisCreated($idProjet){
	
	$resIdProjetMantis = db_query_params('SELECT group_mantisbt.id_mantisbt FROM group_mantisbt WHERE group_mantisbt.id_group = $1',
					array($idProjet));
	echo db_error();
	$row = db_fetch_array($resIdProjetMantis);

	if ($row == null) {
		return false;
	}else{
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

function updateUserInMantis($user_id) {
	global $sys_mantisbt_host, $sys_mantisbt_db_user, $sys_mantisbt_db_password, $sys_mantisbt_db_port, $sys_mantisbt_db_name;
    // recuperation du nouveau mail
	$resUser = db_query_params ('SELECT user_name, email FROM users WHERE user_id = $1',array($user_id));
	echo db_error();
	$row = db_fetch_array($resUser);
	$dbConnection = db_connect_host($sys_mantisbt_db_name, $sys_mantisbt_db_user, $sys_mantisbt_db_password, $sys_mantisbt_host, $sys_mantisbt_db_port);
	if(!$dbConnection){
		$errMantis1 =  "Error : Could not open connection" . db_error($dbConnection);
		echo $errMantis1;
		db_rollback($dbConnection);
	} else {
        db_query_params('UPDATE mantis_user_table set email = $1 where username = $2',array($row['email'],$row['user_name']),'-1','0',$dbConnection);
        echo db_error();
    }
}

function updateUsersProjetMantis($idProjet, $members) {
	
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
					array($member,$idProjet));
		echo db_error();
		$row = db_fetch_array($resUserRole);
		$stateForge[$member]['name'] = $member;
		$stateForge[$member]['role'] = $row['role_name']; 
	}
	// on supprime les precedentes relations dans mantis
	$stateMantis = array ();
	$dbConnection = db_connect_host($sys_mantisbt_db_name, $sys_mantisbt_db_user, $sys_mantisbt_db_password, $sys_mantisbt_host, $sys_mantisbt_db_port);
	if(!$dbConnection){
		$errMantis1 =  "Error : Could not open connection" . db_error($dbConnection);
		echo $errMantis1;
		db_rollback($dbConnection);
	}else{
		$result = pg_delete($dbConnection,"mantis_project_user_list_table",array("project_id"=>$idMantis));
		if (!$result){
			echo "Error : Impossible de nettoyer les roles dans mantisbt";
		}else{
			foreach($stateForge as $member => $array){
				
				// recuperation de l'id user dans mantis
				$resultIdUser = db_query_params('SELECT mantis_user_table.id FROM mantis_user_table WHERE mantis_user_table.username = $1',
							array($member),'-1',0,$dbConnection);
				
				$rowIdUser = db_fetch_array($resultIdUser);
				$idUser = $rowIdUser['id'];
				// insertion de la relation
				$resultInsert = pg_insert($dbConnection,
								"mantis_project_user_list_table",
								array ("project_id" => $idMantis, "user_id" => $idUser, "access_level" => $role[$array['role']])
							);
				if (!isset($resultInsert))
					echo "Error : Impossible de mettre à jour les roles dans mantisbt";

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
    db_query_params('TRUNCATE TABLE mantis_project_hierarchy_table',array() , '-1', 0, $dbConnection);
    while ($hierarchy = db_fetch_array($hierarchies)) {
        $result = db_query_params ('INSERT INTO mantis_project_hierarchy_table (child_id, parent_id, inherit_parent) VALUES ($1, $2, $3)',
                                array (getIdProjetMantis($hierarchy['sub_project_id']), getIdProjetMantis($hierarchy['project_id']), 1),
                                '-1',
                                0,
                                $dbConnection) ;

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

function getGroupIdByName($name){

    $child_query = db_query_params('select group_id from groups where group_name = $1',array($name));
    echo db_error();
    $row = db_fetch_array($child_query);

    if ($row == null) {
		return 0;
    }else{
		return $row['group_id'];
    }
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
