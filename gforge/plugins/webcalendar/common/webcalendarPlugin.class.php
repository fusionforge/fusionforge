<?php

/**
 * webcalendarPlugin Class
 *
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

class webcalendarPlugin extends Plugin {
	function webcalendarPlugin () {
		$this->Plugin() ;
		$this->name = "webcalendar" ;
		$this->text = "Webcalendar" ; // To show in the tabs, use...
		$this->hooks[] = "user_personal_links";//to make a link to the user´s personal part of the plugin
		$this->hooks[] = "usermenu" ;
		$this->hooks[] = "groupmenu" ;	// To put into the project tabs
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; //
		$this->hooks[] = "userisactivecheckbox" ; // The "use ..." checkbox in user account
		$this->hooks[] = "userisactivecheckboxpost" ; //
		$this->hooks[] = "project_admin_plugins"; // to show up in the admin page fro group
		$this->hooks[] = "call_user_cal"; // to show the calendar of the user (file my/index.php line 434)
		$this->hooks[] = "call_user_js"; // call a function when you click on webcal (file my/index.php line 434)
		$this->hooks[] = "call_group_cal"; // to show the calendar of the group (file layout.class.php ligne 627)
		//$this->hooks[] = "iframe_group_calendar"; // to show the calendar of the group (file  ligne 627)
		$this->hooks[] = "add_cal_user"; //add a gforge user in calendar base 
		$this->hooks[] = "del_cal_user"; //dell a gforge user in calendar base
		$this->hooks[] = "add_cal_group"; //add a group user in calendar base
		$this->hooks[] = "del_cal_group"; //del a gforge user in calendar base
		$this->hooks[] = "change_cal_permission"; //change permission pour webcal user (admin or not)
		$this->hooks[] = "change_cal_permission_default"; //change permission pour webcal user (admin or not)
		$this->hooks[] = "change_cal_permission_auto"; //change permission pour webcal user when you modify role
		$this->hooks[] = "add_cal_link_father"; // add a link between son and father
		$this->hooks[] = "del_cal_link_father"; // del a link between son and father
		$this->hooks[] = "add_cal_link_father_event"; // add a link between son and father
		$this->hooks[] = "change_cal_password"; //change the password a webcal user
		$this->hooks[] = "change_cal_mail"; //change the mail a webcal user
	        $this->hooks[] = "cal_link_group"; //a link to group calendar
	}

	function CallHook ($hookname, $params) {
		global $use_webcalendarplugin,$G_SESSION,$HTML,$Language;
		if ($hookname == "usermenu") {
			$text = $this->text; // this is what shows in the tab
			if ($G_SESSION->usesPlugin("webcalendar")) {
				$param = '?type=user&id=' . $G_SESSION->getId() . "&pluginname=" . $this->name; // we indicate the part we´re calling is the user one
				echo ' | ' . $HTML->PrintSubMenu (array ($text),
						  array ('/plugins/webcalendar/index.php' . $param ));				
			}
		} elseif ($hookname == "groupmenu") {
			$group_id=$params['group'];
			$project = &group_get_object($group_id);
			if (!$project || !is_object($project)) {
				return;
			}
			if ($project->isError()) {
				return;
			}
			if (!$project->isProject()) {
				return;
			}
			if ( !$project->usesPlugin ( $this->name ) ) {
				$params['TITLES'][]=$this->text;
				$params['DIRS'][]='/plugins/webcalendar/index2.php?type=group&group_id='.$group_id;
				} else {
				$params['TITLES'][]=$this->text." is [Off]";
				$params['DIRS'][]='';
			}	
			(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
		} elseif ($hookname == "groupisactivecheckbox") {
			///Check if the group is active
		} elseif ($hookname == "groupisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			$use_webcalendarplugin = getStringFromRequest('use_webcalendarplugin');
			if ( $use_webcalendarplugin == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
		} elseif ($hookname == "userisactivecheckbox") {
			//check if user is active
			// this code creates the checkbox in the user account manteinance page to activate/deactivate the plugin
		} elseif ($hookname == "userisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the user account manteinance page
			$user = $params['user'];
			$use_webcalendarplugin = getStringFromRequest('use_webcalendarplugin');
			if ( $use_webcalendarplugin == 1 ) {
				$user->setPluginUse ( $this->name );
			} else {
				$user->setPluginUse ( $this->name, false );
			}
			echo "<tr>";
			echo "<td>";
			echo ' <input type="CHECKBOX" name="use_webcalendarplugin" value="1" ';
			// CHECKED OR UNCHECKED?
			if ( $user->usesPlugin ( $this->name ) ) {
				echo "CHECKED";
			}
			echo ">    Use ".$this->text." Plugin";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "user_personal_links") {
			global $Language;
			// this displays the link in the user´s profile page to it´s personal webcalendar (if you want other sto access it, youll have to change the permissions in the index.php
			$userid = $params['user_id'];
			$user = user_get_object($userid);
			$text = $params['text'];
			//check if the user has the plugin activated
			if ($user->usesPlugin($this->name)) {
				echo '	<p>
					<a href="/plugins/webcalendar/index.php?id=' . $userid . '&type=user&pluginname=' . $this->name . '">' . _('View Personal webcalendar') .'</a></p>';
			}
		} elseif ($hookname == "project_admin_plugins") {
			global $Language;
			// this displays the link in the project admin options page to it´s  webcalendar administration
			$group_id = $params['group_id'];
			$group = &group_get_object($group_id);
			
			if ( $group->usesPlugin ( $this->name ) ) {
				echo '<a href="/plugins/webcalendar/index.php?id=' . $group->getID() . '&type=admin&pluginname=' . $this->name . '">' . _('View the webcalendar Administration') . '</a><br />';
			}
			
		}												    
		elseif ($hookname == "call_user_cal") {
			//my/index.php line 365
			?>
			
			<div id="cal" class="tabbertab" title="WebCalendar"  >
			<table width="100%" cellspacing="0" cellpadding="0" border="0" ><tr align="center" ><td >
			<iframe name="webcal" src="/plugins/webcalendar/login.php?type=user" border=no scrolling="yes" width="100%" height="700"></iframe>
			</td></tr></table>
			</div>
			<script>
			function reload_webcal() {
			frames['webcal'].location.replace('/plugins/webcalendar/login.php?type=user');
			
			}
			</script>
			<?php		
		}
		elseif ($hookname == "call_user_js") {
			// my/index.php line 67
			
			?>
			onclick="reload_webcal()"
			<?php		
		}
		elseif ($hookname == "add_cal_user") { 
				//argument user_id
				//user.class.php line 590
				//admin/userlist.php line 129
				$query = "SELECT user_name,user_pw,email FROM users WHERE user_id = '".$params."'";
				$res = db_query($query);
				$row = db_fetch_array($res);
				$cal_query = "INSERT INTO webcal_user (cal_login, cal_passwd, cal_email,cal_firstname, cal_is_admin) VALUES ('" . $row['user_name'] . "','" . $row['user_pw'] . "','" . $row['email'] . "','" . $row['user_name'] . "','N')";
				$res_cal = db_query($cal_query);	
		}
		elseif ($hookname == "del_cal_user") { 
				//argument user_id
				//admin/userlist.php line 122
				$query = "SELECT user_name,user_pw,email FROM users WHERE user_id = '".$params."'";
				$res = db_query($query);
				$row = db_fetch_array($res);
				$cal_query = "DELETE FROM webcal_user WHERE cal_login = '" . $row['user_name'] . "'";
				$res_cal = db_query($cal_query);	
				db_query("DELETE FROM webcal_asst WHERE cal_boss = '" . $row['user_name'] . "' OR cal_assistant = '" . $row['user_name'] . "'");
				db_query("DELETE FROM webcal_entry_user WHERE cal_login = '" . $row['user_name'] . "' ");
		}
		elseif ($hookname == "add_cal_group") {
				//argument group_id
				//approve_pending.php line 69,80 
				$query = "SELECT  unix_group_name,groups.group_id,group_name,email FROM groups,users,user_group WHERE groups.group_id = '".$params."' AND groups.group_id = user_group.group_id AND user_group.user_id = users.user_id AND user_group.admin_flags = 'A' ";
				$res = db_query($query);
				$row = db_fetch_array($res);
				$cal_query = "INSERT INTO webcal_user (cal_login, cal_passwd, cal_firstname,cal_email) VALUES ('" . $row['unix_group_name'] . "','cccc','" . addslashes($row['group_name']) . "','".$row['email']."')";
				$res_cal = db_query($cal_query);
				
		
		}
		elseif ($hookname == "del_cal_group") {
				//argument group_id
				//approve_pending.php line 90 
				$query = "SELECT  unix_group_name,group_id,group_name FROM groups WHERE group_id = '".$params."' ";
				$res = db_query($query);
				$row = db_fetch_array($res);
				$cal_query = "DELETE FROM webcal_user WHERE cal_login = '" . $row['unix_group_name'] . "'";
				$res_cal = db_query($cal_query);
				db_query("DELETE FROM webcal_asst WHERE cal_boss = '" . $row['unix_group_name'] . "' OR cal_assistant = '" . $row['unix_group_name'] . "'");
				db_query("DELETE FROM webcal_entry_user WHERE cal_login = '" . $row['unix_group_name'] . "' ");
		}
		elseif ($hookname == "change_cal_permission") {
				//argument user_id -> $params[0]et group_id -> $params[1]
				//project/admin/index.php line 72,87,103
				//project/admin/massfinish.php line 50
				
				
				
				$query_flags = "SELECT value,admin_flags FROM user_group,role_setting WHERE role_setting.role_id = user_group.role_id AND user_group.user_id = '".$params[0]."' AND user_group.group_id = '".$params[1]."' AND role_setting.section_name = 'webcal'";
				
				$res = db_query($query_flags);
				$row_flags = db_fetch_array($res);
				
				//get user name :
				$query_nom_boss = "SELECT unix_group_name FROM groups WHERE group_id = '".$params[1]."' ";
				$res_nom_boss = db_query($query_nom_boss);
				$row_nom_boss = db_fetch_array($res_nom_boss);
				
				
				$query_nom_user = "SELECT user_name,email FROM users WHERE user_id = '".$params[0]."' ";
				$res_nom_user = db_query($query_nom_user);
				$row_nom_user = db_fetch_array($res_nom_user);
				
				//flag verification
				$query_flags = "SELECT COUNT(*) FROM webcal_asst WHERE cal_boss = '".$row_nom_boss['unix_group_name']."' AND cal_assistant = '".$row_nom_user['user_name']."'";
				$res = db_query($query_flags);
				$row_num = db_fetch_array($res);
				
				//select email
				$query_mail ="SELECT cal_email FROM webcal_user WHERE  cal_login = '".$row_nom_boss['unix_group_name']."'";			
				$res_mail = db_query($query_mail);
				$row_mail = db_fetch_array($res_mail);	
				$mail = $row_mail['cal_email'] ;
				
				//if group admin
				if($params[1] == 1){
				$query_flags_admin = "SELECT admin_flags FROM user_group WHERE user_id = '".$params[0]."' AND group_id = '".$params[1]."'";
				$res_flags_admin = db_query($query_flags_admin);
				$row_flags_admin = db_fetch_array($res_flags_admin);
					if(trim($row_flags_admin['admin_flags']) == 'A'  ) {
						$update_admin = "UPDATE webcal_user SET cal_is_admin = 'Y' WHERE cal_login = '".$row_nom_user['user_name']."'" ;
					}
					else {
						$update_admin = "UPDATE webcal_user SET cal_is_admin = 'N' WHERE cal_login = '".$row_nom_user['user_name']."'" ;
				
					}
					db_query($update_admin);
				
				}
				
				if(($row_num[0] != 1 ) && ($row_flags['value'] == 1)){
					
				$insert_ass =  "INSERT INTO webcal_asst (cal_boss, cal_assistant) VALUES ('".$row_nom_boss['unix_group_name']."','".$row_nom_user['user_name']."')";	
				$res_insert  = db_query($insert_ass);
				
				//we add email of the new admin
				$mail = str_replace($row_nom_user['email'],"",$mail);
				$mail = str_replace(",".$row_nom_user['email'],"",$mail);
								
				if($mail == ""){
					$virgule = "";	
					}
				else {
					$virgule = ",";	
					}
									
				$mail = $mail.$virgule.$row_nom_user['email'] ;
				
				
				
				//$mail = $row_mail['cal_email'].",".$row_nom_user['email'] ;
				$update = "UPDATE webcal_user SET cal_email = '".trim($mail,',')."' WHERE cal_login = '".$row_nom_boss['unix_group_name']."'" ;
				db_query($update);
				}
				elseif($row_num[0] == 1 && ($row_flags['value'] != 1)){
				$del_ass = "DELETE FROM webcal_asst WHERE cal_boss = '".$row_nom_boss['unix_group_name']."' AND cal_assistant = '".$row_nom_user['user_name']."'";
				$res_del = db_query($del_ass);	
				
				//we del email of the old admin
				$mail = str_replace(",".$row_nom_user['email'],"",$row_mail['cal_email']) ;
				$update = "UPDATE webcal_user SET cal_email = '".$mail."' WHERE cal_login = '".$row_nom_boss['unix_group_name']."'" ;
				db_query($update);
				}
				
				
		}
		elseif ($hookname == "change_cal_permission_default") {
				//argument user_id -> $params[0]et group_id -> $params[1]
				// Group.class.php line 2085
				//$query_flags = "SELECT value FROM user_group,role_setting WHERE role_setting.role_id = user_group.role_id AND user_group.user_id = '".$params[0]."' AND user_group.group_id = '".$params[1]."' AND role_setting.section_name = 'test'";
				
				$query_flags = "SELECT admin_flags FROM user_group WHERE user_id = '".$params[0]."' AND group_id = '".$params[1]."'";
				$res = db_query($query_flags);
				$row_flags = db_fetch_array($res);
				 
				
				
				//get user name
				$query_nom_boss = "SELECT unix_group_name FROM groups WHERE group_id = '".$params[1]."' ";
				$res_nom_boss = db_query($query_nom_boss);
				$row_nom_boss = db_fetch_array($res_nom_boss);
				
				
				$query_nom_user = "SELECT user_name,email FROM users WHERE user_id = '".$params[0]."' ";
				$res_nom_user = db_query($query_nom_user);
				$row_nom_user = db_fetch_array($res_nom_user);
				
				//verif du flag sur webcal
				$query_flags = "SELECT COUNT(*) FROM webcal_asst WHERE cal_boss = '".$row_nom_boss['unix_group_name']."' AND cal_assistant = '".$row_nom_user['user_name']."'";
				$res = db_query($query_flags);
				$row_num = db_fetch_array($res);
				
				 //select email
				$query_mail ="SELECT cal_email FROM webcal_user WHERE  cal_login = '".$row_nom_boss['unix_group_name']."'";			
				$res_mail = db_query($query_mail);
				$row_mail = db_fetch_array($res_mail);
				$mail = $row_mail['cal_email']; 
				
				if(($row_num[0] != 1 ) && (trim($row_flags['admin_flags']) == 'A')){
					//recuperer le nom du user et du group
				$insert_ass =  "INSERT INTO webcal_asst (cal_boss, cal_assistant) VALUES ('".$row_nom_boss['unix_group_name']."','".$row_nom_user['user_name']."')";	
				$res_insert  = db_query($insert_ass);
				
				//we add email of the new admin
				$mail = str_replace($row_nom_user['email'],"",$mail);
				$mail = str_replace(",".$row_nom_user['email'],"",$mail);
								
				if($mail == ""){
					$virgule = "";	
					}
				else {
					$virgule = ",";	
					}
									
				$mail = $mail.$virgule.$row_nom_user['email'] ;
								
				//$mail = $row_mail['cal_email'].",".$row_nom_user['email'] ;
				$update = "UPDATE webcal_user SET cal_email = '".trim($mail,',')."' WHERE cal_login = '".$row_nom_boss['unix_group_name']."'" ;
				db_query($update);
				}
				elseif($row_num[0] == 1 && (trim($row_flags['admin_flags']) != 'A')){
				$del_ass = "DELETE FROM webcal_asst WHERE cal_boss = '".$row_nom_boss['unix_group_name']."' AND cal_assistant = '".$row_nom_user['user_name']."'";
				$res_del = db_query($del_ass);	
				
				//we del email of the old admin
				$mail = str_replace(",".$row_nom_user['email'],"",$row_mail['cal_email']) ;
				$update = "UPDATE webcal_user SET cal_email = '".$mail."' WHERE cal_login = '".$row_nom_boss['unix_group_name']."'" ;
				db_query($update);
				}
				
		}
		elseif ($hookname == "change_cal_permission_auto") {
				//argument $params group_id
				// project/admin/roleedit.php line 85
				
				$query_flags = "SELECT value, user_id FROM user_group,role_setting WHERE role_setting.role_id = user_group.role_id AND role_setting.section_name = 'webcal' AND group_id = '".$params."'" ;				
				$res = db_query($query_flags);
				if($res){
						while( $row_flags = db_fetch_array($res)){
						
						
						
								//get the group and user names
								$query_nom_boss = "SELECT unix_group_name FROM groups WHERE group_id = '".$params."' ";
								$res_nom_boss = db_query($query_nom_boss);
								$row_nom_boss = db_fetch_array($res_nom_boss);
								
								
								$query_nom_user = "SELECT user_name,email FROM users WHERE user_id = '".$row_flags['user_id']."' ";
								$res_nom_user = db_query($query_nom_user);
								$row_nom_user = db_fetch_array($res_nom_user);
								
								//verif if the user is admin
								$query_flags = "SELECT COUNT(*) FROM webcal_asst WHERE cal_boss = '".$row_nom_boss['unix_group_name']."' AND cal_assistant = '".$row_nom_user['user_name']."'";
								$res_count = db_query($query_flags);
								$row_num = db_fetch_array($res_count);
								 								
								//select email
								$query_mail ="SELECT cal_email FROM webcal_user WHERE  cal_login = '".$row_nom_boss['unix_group_name']."'";			
								$res_mail = db_query($query_mail);
								$row_mail = db_fetch_array($res_mail);
								$mail = $row_mail['cal_email'];
								
								if(($row_num[0] != 1 ) && ($row_flags['value'] == 1)){
								//recuperer le nom du user et du group
								$insert_ass =  "INSERT INTO webcal_asst (cal_boss, cal_assistant) VALUES ('".$row_nom_boss['unix_group_name']."','".$row_nom_user['user_name']."')";	
								$res_insert  = db_query($insert_ass);
								
								//we add email of the new admin
								$mail = str_replace($row_nom_user['email'],"",$mail);
								$mail = str_replace(",".$row_nom_user['email'],"",$mail);
								
								if($mail == ""){
									$virgule = "";	
									}
									else {
									$virgule = ",";	
									}
									
								$mail = $mail.$virgule.$row_nom_user['email'] ;
								
								//$mail = $row_mail['cal_email'].",".$row_nom_user['email'] ;
								$update = "UPDATE webcal_user SET cal_email = '".trim($mail,',')."' WHERE cal_login = '".$row_nom_boss['unix_group_name']."'" ;
								db_query($update);
								
								}
								elseif($row_num[0] == 1 && ($row_flags['value'] != 1)){
								$del_ass = "DELETE FROM webcal_asst WHERE cal_boss = '".$row_nom_boss['unix_group_name']."' AND cal_assistant = '".$row_nom_user['user_name']."'";
								$res_del = db_query($del_ass);	
								
								//we del email of the old admin
								$mail = str_replace(",".$row_nom_user['email'],"",$row_mail['cal_email']) ;
								$update = "UPDATE webcal_user SET cal_email = '".$mail."' WHERE cal_login = '".$row_nom_boss['unix_group_name']."'" ;
								db_query($update);
								}
						}
				}
				
		}
		elseif ($hookname == "add_cal_link_father") {
				//argument id du fils --> $params[0], id du pere--> $params[1]
				//plugin hierachy wait_son.php line 36
				$query_hierarchy = "select p1.group_id as father_id,p1.unix_group_name as father_unix_name,p1.group_name as father_name,p2.group_id as son_id,p2.unix_group_name as son_unix_name,p2.group_name as son_name from groups as p1,groups as p2,plugin_projects_hierarchy where p1.group_id=plugin_projects_hierarchy.project_id AND plugin_projects_hierarchy.project_id = '".$params[1]."' and p2.group_id=plugin_projects_hierarchy.sub_project_id AND plugin_projects_hierarchy.sub_project_id = '".$params[0]."'and plugin_projects_hierarchy.activated='t' AND plugin_projects_hierarchy.link_type='shar'";
				$res_hierarchy = db_query($query_hierarchy);
				if($res_hierarchy){
						while($row_hierarchy = db_fetch_array($res_hierarchy)) {
							$query_entry = "SELECT cal_id FROM webcal_entry_user WHERE cal_login = '".$row_hierarchy['son_unix_name']."' AND cal_status = 'A'" ;
							$res_entry = db_query($query_entry);
							if($res_entry){
								while($row_entry = db_fetch_array($res_entry)) {
								$insert_entry = "INSERT INTO webcal_entry_user (cal_id,cal_login,cal_status) VALUES ('".$row_entry['cal_id']."','".$row_hierarchy['father_unix_name']."','A')";	
								$res_insert_entry = db_query($insert_entry);
								}
							}
						}	
						
					}
		}
		elseif ($hookname == "add_cal_link_father_event") {
				//argument name of the son --> $params[0], id_cal--> $params[1]
				//webcalendar/edit_entry_handler.php line 390
				//webcalendar/approve_entry.php line 21
				$query_nom = "SELECT group_id FROM groups WHERE unix_group_name = '".$params[0]."'";
				$res_nom = db_query($query_nom);
				$row_nom = db_fetch_array($res_nom);
				$query_pere = "SELECT project_id, unix_group_name FROM plugin_projects_hierarchy, groups WHERE plugin_projects_hierarchy.project_id = groups.group_id AND sub_project_id = '".$row_nom['group_id']."' AND link_type = 'shar' AND activated = true";
				$res_pere = db_query($query_pere);
				if($res_pere){
					$row_pere = db_fetch_array($res_pere);
					$insert_entry = "INSERT INTO webcal_entry_user (cal_id,cal_login,cal_status) VALUES ('".$params[1]."','".$row_pere['unix_group_name']."','A')";	
					$res_insert  = db_query($insert_entry);
				}
				
		}
		elseif ($hookname == "del_cal_link_father") {
				//argument id son --> $params[0], id father--> $params[1]
				//plugin hierachy wait_son.php line 36
				$query_hierarchy = "select p1.group_id as father_id,p1.unix_group_name as father_unix_name,p1.group_name as father_name,p2.group_id as son_id,p2.unix_group_name as son_unix_name,p2.group_name as son_name from groups as p1,groups as p2,plugin_projects_hierarchy where p1.group_id=plugin_projects_hierarchy.project_id AND plugin_projects_hierarchy.project_id = '".$params[1]."' and p2.group_id=plugin_projects_hierarchy.sub_project_id AND plugin_projects_hierarchy.sub_project_id = '".$params[0]."'and plugin_projects_hierarchy.activated='t' AND plugin_projects_hierarchy.link_type='shar'";
				$res_hierarchy = db_query($query_hierarchy);
				if($res_hierarchy){
						while($row_hierarchy = db_fetch_array($res_hierarchy)) {
							$query_entry = "SELECT cal_id FROM webcal_entry_user WHERE cal_login = '".$row_hierarchy['son_unix_name']."' " ;
							$res_entry = db_query($query_entry);
							if($res_entry){
								while($row_entry = db_fetch_array($res_entry)) {
								$insert_entry = "DELETE FROM webcal_entry_user WHERE cal_id = '".$row_entry['cal_id']."' AND cal_login = '".$row_hierarchy['father_unix_name']."'";	
								$res_insert_entry = db_query($insert_entry);
								}
							}
						}	
						
					}
		}
		elseif ($hookname == "del_cal_link_father_event") {
				//argument id son --> $params[0], id_cal--> $params[1]
				
		}
		elseif ($hookname == "change_cal_password") {
				//argument user_id
				//account/change_pw.php line 79
				$query_name = "SELECT user_name,user_pw,email  FROM users WHERE user_id = '".$params."'" ;
				$res_name = db_query($query_name);
				$row_name = db_fetch_array($res_name);
				
				$update = "UPDATE webcal_user SET cal_passwd = '".$row_name['user_pw']."', cal_email = '".$row_name['email']."' WHERE cal_login = '".$row_name['user_name']."'";
				$res_update = db_query($update); 
						
		}
		elseif ($hookname == "change_cal_mail") {
				//argument user_id
				//account/change_email-complete.php line 63
				 
				$query_name = "SELECT user_name,user_pw,email  FROM users WHERE user_id = '".$params."'" ;
				$res_name = db_query($query_name);
				$row_name = db_fetch_array($res_name);
				
				$query_old = "SELECT cal_email FROM webcal_user WHERE cal_login = '".$row_name['user_name']."'" ;
				$res_old = db_query($query_old);
				$row_old = db_fetch_array($res_old);
				
				//get all the cal_login where you need to change mail
				$query_all_mail = "SELECT cal_login, cal_email FROM webcal_user WHERE cal_email LIKE '%".$row_old['cal_email']."%'" ;
				$res_all_mail = db_query($query_all_mail);
				print $query_all_mail;
				while($row_all_mail = db_fetch_array($res_all_mail)){
					$mail = str_replace($row_old['cal_email'],$row_name['email'],$row_all_mail['cal_email']);
					$update = "UPDATE webcal_user SET cal_passwd = '".$row_name['user_pw']."', cal_email = '".$mail."' WHERE cal_login = '".$row_all_mail['cal_login']."'";
					$res_update = db_query($update); 
					
				}
								
						
		}
		elseif ($hookname == "cal_link_group" ){
		// www/include/project_home.php line 418
		//params = group_id
		print '<hr size="1" />';
		print '<a href="/plugins/webcalendar/index2.php?type=group&group_id='.$params.'">Webcalendar</a> ';
		}
		 
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
