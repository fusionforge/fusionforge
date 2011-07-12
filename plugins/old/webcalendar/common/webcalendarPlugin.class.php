<?php
/**
 * webcalendarPlugin Class
 *
 * Copyright 2006, Fabien Regnier <fabien.regnier@sogeti.com>
 * Copyright 2006, Julien Jeany <julien.jeany@sogeti.com>
 * Copyright 2010, Roland Mas
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

class webcalendarPlugin extends Plugin {
	function webcalendarPlugin () {
		$this->Plugin() ;
		$this->name = "webcalendar" ;
		$this->text = "Webcalendar" ; // To show in the tabs, use...
		$this->hooks[] = "user_personal_links";//to make a link to the user's personal part of the plugin
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
		$this->hooks[] = "user_setstatus";
		$this->hooks[] = "group_approved";
		$this->hooks[] = "change_cal_permission"; //change permission pour webcal user (admin or not)
		$this->hooks[] = "change_cal_permission_auto"; //change permission pour webcal user when you modify role
		$this->hooks[] = "add_cal_link_father"; // add a link between son and father
		$this->hooks[] = "del_cal_link_father"; // del a link between son and father
		$this->hooks[] = "add_cal_link_father_event"; // add a link between son and father
		$this->hooks[] = "change_cal_password"; //change the password a webcal user
		$this->hooks[] = "change_cal_mail"; //change the mail a webcal user
	        $this->hooks[] = "cal_link_group"; //a link to group calendar
		$this->hooks[] = "role_get";
		$this->hooks[] = "role_normalize";
		$this->hooks[] = "role_translate_strings";
		$this->hooks[] = "role_has_permission";
		$this->hooks[] = "list_roles_by_permission";
	}

	function CallHook ($hookname, &$params) {
		global $use_webcalendarplugin,$G_SESSION,$HTML;
		if ($hookname == "usermenu") {
			$text = $this->text; // this is what shows in the tab
			if ($G_SESSION->usesPlugin("webcalendar")) {
				$param = '?type=user&id=' . $G_SESSION->getId() . "&pluginname=" . $this->name; // we indicate the part we're calling is the user one
				echo ' | ' . $HTML->PrintSubMenu (array ($text),
						  array (util_make_url('/plugins/webcalendar/index.php' . $param)));
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
				$params['DIRS'][]=util_make_url ('/plugins/webcalendar/index2.php?type=group&group_id='.$group_id) ;
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
			// this displays the link in the user's profile page to it's personal webcalendar (if you want other sto access it, youll have to change the permissions in the index.php
			$userid = $params['user_id'];
			$user = user_get_object($userid);
			$text = $params['text'];
			//check if the user has the plugin activated
			if ($user->usesPlugin($this->name)) {
				echo '	<p>
					'.util_make_link('/plugins/webcalendar/index.php?id=' . $userid . '&type=user&pluginname=' . $this->name,_('View Personal webcalendar')) .'</p>';
			}
		} elseif ($hookname == "project_admin_plugins") {
			// this displays the link in the project admin options page to it's  webcalendar administration
			$group_id = $params['group_id'];
			$group = &group_get_object($group_id);

			if ( $group->usesPlugin ( $this->name ) ) {
				echo util_make_link('/plugins/webcalendar/index.php?id=' . $group->getID() . '&type=admin&pluginname=' . $this->name,_('View the webcalendar Administration')) . '<br />';
			}

		}
		elseif ($hookname == "call_user_cal") {
			//my/index.php line 365
			?>

			<div id="cal" class="tabbertab" title="WebCalendar"  >
			<table width="100%" cellspacing="0" cellpadding="0" border="0" ><tr align="center" ><td >
			<iframe name="webcal" src="<?php echo util_make_url('/plugins/webcalendar/login.php?type=user'); ?>" border=no scrolling="yes" width="100%" height="700"></iframe>
			</td></tr></table>
			</div>
			<script>
			function reload_webcal() {
			frames['webcal'].location.replace('<?php echo util_make_url("/plugins/webcalendar/login.php?type=user"); ?>');

			}
			</script>
			<?php
		}
		elseif ($hookname == "call_user_js") {
			// my/index.php line 67

			?>
			onclick="reload_webcal()"
			<?php
		} elseif ($hookname == "role_get") {
			$role =& $params['role'] ;

			// Read access
			$right = new PluginSpecificRoleSetting ($role,
								'plugin_webcalendar_access') ;
			$right->SetAllowedValues (array ('0', '1', '2')) ;
			$right->SetDefaultValues (array ('Admin' => '1',
							 'Senior Developer' => '1',
							 'Junior Developer' => '2',
							 'Doc Writer' => '2',
							 'Support Tech' => '2')) ;
		} elseif ($hookname == "role_normalize") {
			$role =& $params['role'] ;
			$new_pa =& $params['new_pa'] ;

			if (USE_PFO_RBAC) {
				$projects = $role->getLinkedProjects() ;
				foreach ($projects as $p) {
					$role->normalizePermsForSection ($new_pa, 'plugin_webcalendar_access', $p->getID()) ;
				}
			}
		} elseif ($hookname == "role_translate_strings") {
			$right = new PluginSpecificRoleSetting ($role,
							       'plugin_webcalendar_access') ;
			$right->setDescription (_('Webcalendar read access')) ;
			$right->setValueDescriptions (array ('0' => _('No reading'),
							     '1' => _('Write access'),
							     '2' => _('Read access'))) ;
		} elseif ($hookname == "role_has_permission") {
			if ($params['section'] == 'plugin_webcalendar_access') {
				switch ($params['action']) {
				case 'read':
					$params['result'] |= ($value >= 1) ;
					break ;
				case 'write':
					$params['result'] |= ($value == 1) ;
					break ;
				}
			}
		} elseif ($hookname == "list_roles_by_permission") {
			if ($params['section'] == 'plugin_webcalendar_access') {
				switch ($params['action']) {
				case 'read':
					$params['qpa'] = db_construct_qpa ($params['qpa'], ' AND perm_val >= 1') ;
					break ;
				case 'write':
					$params['qpa'] = db_construct_qpa ($params['qpa'], ' AND perm_val = 1') ;
					break ;
				}
			}
		}
		elseif ($hookname == "user_setstatus") {
			$user = $params['user'] ;
			$status = $params['status'] ;

			if ($status == 'A') {
				$res_cal = db_query_params ('SELECT COUNT(*) FROM webcal_user WHERE cal_login=$1',
							    array ($user->getUnixName())) ;
				$row = db_fetch_array($res);
				if ($row[0] == 0) {
					db_query_params ('INSERT INTO webcal_user (cal_login, cal_passwd, cal_email,cal_firstname, cal_is_admin) VALUES ($1,$2,$3,$4,$5)',
								    array ($user->getUnixName(),
									   $user->getUserPw(),
									   $user->getEmail(),
									   $user->getFirstName(),
									   'N'));
				}
			} else {
				db_query_params ('DELETE FROM webcal_user WHERE cal_login = $1',
							    array ($user->getUnixName()));
				db_query_params ('DELETE FROM webcal_asst WHERE cal_boss = $1 OR cal_assistant = $2',
						 array ($user->getUnixName(),
							$user->getUnixName())) ;
				db_query_params ('DELETE FROM webcal_entry_user WHERE cal_login = $1 ',
						 array ($user->getUnixName())) ;
			}
		} elseif ($hookname == "group_approved") {
			$project = group_get_object ($params['group_id']) ;

			$emails = array () ;
			foreach ($project->getAdmins() as $u) {
				$emails[] = $u->getEmail() ;
			}

			db_query_params ('INSERT INTO webcal_user (cal_login, cal_passwd, cal_firstname,cal_email) VALUES ($1,$2,$3,$4)',
					 array ($project->getUnixName(),
						'cccc',
						$project->getUnixName(),
						implode (',', $emails)));
		}
		elseif ($hookname == "group_approve") {
			$res = db_query_params ('SELECT admin_flags FROM user_group WHERE user_id = $1 AND group_id = $2',
						array ($params[0],
						       $params[1]));
			$row_flags = db_fetch_array($res);



				//get user name
				$res_nom_boss = db_query_params ('SELECT unix_group_name FROM groups WHERE group_id = $1 ',
			array ($params[1]));
				$row_nom_boss = db_fetch_array($res_nom_boss);


				$res_nom_user = db_query_params ('SELECT user_name,email FROM users WHERE user_id = $1 ',
			array ($params[0]));
				$row_nom_user = db_fetch_array($res_nom_user);

				//verif du flag sur webcal
				$res = db_query_params ('SELECT COUNT(*) FROM webcal_asst WHERE cal_boss = $1 AND cal_assistant = $2',
			array ($row_nom_boss['unix_group_name'],
				$row_nom_user['user_name']));
				$row_num = db_fetch_array($res);

				 //select email
				$res_mail = db_query_params ('SELECT cal_email FROM webcal_user WHERE  cal_login = $1',
			array ($row_nom_boss['unix_group_name']));
				$row_mail = db_fetch_array($res_mail);
				$mail = $row_mail['cal_email'];

				if(($row_num[0] != 1 ) && (trim($row_flags['admin_flags']) == 'A')){
					//recuperer le nom du user et du group
					$res_insert = db_query_params ('INSERT INTO webcal_asst (cal_boss, cal_assistant) VALUES ($1,$2)',
			array ($row_nom_boss['unix_group_name'],
				$row_nom_user['user_name']));

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
				db_query_params ('UPDATE webcal_user SET cal_email = $1 WHERE cal_login = $2',
						 array (trim($mail,','),
							$row_nom_boss['unix_group_name']));
				}
				elseif($row_num[0] == 1 && (trim($row_flags['admin_flags']) != 'A')){
					$res_del = db_query_params ('DELETE FROM webcal_asst WHERE cal_boss = $1 AND cal_assistant = $2',
			array ($row_nom_boss['unix_group_name'],
				$row_nom_user['user_name']));

				//we del email of the old admin
				$mail = str_replace(",".$row_nom_user['email'],"",$row_mail['cal_email']) ;
db_query_params ('UPDATE webcal_user SET cal_email = $1 WHERE cal_login = $2',
			array ($mail,
				$row_nom_boss['unix_group_name']));
				}

		}
		elseif ($hookname == "change_cal_permission") {
			//argument user_id -> $params[0]et project_id -> $params[1]
			//project/admin/index.php line 72,87,103
			//project/admin/massfinish.php line 50

			$user_id = $params[0] ;
			$project_id = $params[1] ;

			$project = group_get_object ($project_id) ;
			$user = user_get_object ($user_id) ;

			if (USE_PFO_RBAC) {
				if (forge_check_perm_for_user ($user, 'plugin_webcalendar_access', $project_id, 'write')) {
					$user_perm = 1 ;
				} elseif (forge_check_perm_for_user ($user, 'plugin_webcalendar_access', $project_id, 'read')) {
					$user_perm = 2 ;
				} else {
					$user_perm = 0 ;
				}
			} else {
				$res = db_query_params ('SELECT value,admin_flags FROM user_group,role_setting WHERE role_setting.role_id = user_group.role_id AND user_group.user_id = $1 AND user_group.group_id = $2 AND role_setting.section_name = $3',
							array ($user_id,
							       $project_id,
							       'webcal'));
				$row_flags = db_fetch_array($res);
				$user_perm = $row_flags['value'] ;
			}

			//flag verification
			$res = db_query_params ('SELECT COUNT(*) FROM webcal_asst WHERE cal_boss = $1 AND cal_assistant = $2',
						array ($project->getUnixName(),
						       $user->getUnixName()));
			$row_num = db_fetch_array($res);

			//select email
			$res_mail = db_query_params ('SELECT cal_email FROM webcal_user WHERE cal_login = $1',
						     array ($project->getUnixName()));
			$row_mail = db_fetch_array($res_mail);
			$mail = $row_mail['cal_email'] ;

			//if group admin
			if($project_id == 1){
				$res_flags_admin = db_query_params ('SELECT admin_flags FROM user_group WHERE user_id = $1 AND group_id = $2',
								    array ($user_id,
									   $project_id));
				$row_flags_admin = db_fetch_array($res_flags_admin);
				if(trim($row_flags_admin['admin_flags']) == 'A'  ) {
					$cia = 'Y' ;
				} else {
					$cia = 'N' ;
				}
				db_query_params ('UPDATE webcal_user SET cal_is_admin = $1 WHERE cal_login = $2',
						 array ($cia,
							$user->getUnixName()));
			}

			if(($row_num[0] != 1 ) && ($user_perm == 1)){

				$res_insert = db_query_params ('INSERT INTO webcal_asst (cal_boss, cal_assistant) VALUES ($1,$2)',
							       array ($project->getUnixName(),
								      $user->getUnixName()));

				//we add email of the new admin
				$mail = str_replace($user->getEmail(),"",$mail);
				$mail = str_replace(",".$user->getEmail(),"",$mail);

				if($mail == ""){
					$virgule = "";
				}
				else {
					$virgule = ",";
				}

				$mail = $mail.$virgule.$user->getEmail() ;



				//$mail = $row_mail['cal_email'].",".$row_nom_user['email'] ;
				db_query_params ('UPDATE webcal_user SET cal_email = $1 WHERE cal_login = $2',
						 array (trim($mail,','),
							$project->getUnixName()));
			}
			elseif($row_num[0] == 1 && ($user_perm != 1)){
				$res_del = db_query_params ('DELETE FROM webcal_asst WHERE cal_boss = $1 AND cal_assistant = $2',
							    array ($project->getUnixName(),
								   $user->getUnixName()));

				//we del email of the old admin
				$mail = str_replace(",".$row_nom_user['email'],"",$row_mail['cal_email']) ;
				db_query_params ('UPDATE webcal_user SET cal_email = $1 WHERE cal_login = $2',
						 array ($mail,
							$project->getUnixName()));
			}
		}
		elseif ($hookname == "change_cal_permission_auto") {
			$res = db_query_params ('SELECT value, user_id FROM user_group,role_setting WHERE role_setting.role_id = user_group.role_id AND role_setting.section_name = $1 AND group_id = $2',
			array ('webcal',
				$params));
				if($res){
						while( $row_flags = db_fetch_array($res)){



								//get the group and user names
							$res_nom_boss = db_query_params ('SELECT unix_group_name FROM groups WHERE group_id = $1 ',
			array ($params));
							$row_nom_boss = db_fetch_array($res_nom_boss);


							$res_nom_user = db_query_params ('SELECT user_name,email FROM users WHERE user_id = $1 ',
			array ($row_flags['user_id']));
								$row_nom_user = db_fetch_array($res_nom_user);

								//verif if the user is admin
								$res_count = db_query_params ('SELECT COUNT(*) FROM webcal_asst WHERE cal_boss = $1 AND cal_assistant = $2',
			array ($row_nom_boss['unix_group_name'],
				$row_nom_user['user_name']));
								$row_num = db_fetch_array($res_count);

								//select email
								$res_mail = db_query_params ('SELECT cal_email FROM webcal_user WHERE  cal_login = $1',
			array ($row_nom_boss['unix_group_name']));
								$row_mail = db_fetch_array($res_mail);
								$mail = $row_mail['cal_email'];

								if(($row_num[0] != 1 ) && ($row_flags['value'] == 1)){
								//recuperer le nom du user et du group
									$res_insert = db_query_params ('INSERT INTO webcal_asst (cal_boss, cal_assistant) VALUES ($1,$2)',
			array ($row_nom_boss['unix_group_name'],
				$row_nom_user['user_name']));

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
								db_query_params ('UPDATE webcal_user SET cal_email = $1 WHERE cal_login = $2',
										 array (trim($mail,','),
											$row_nom_boss['unix_group_name']));
								}
								elseif($row_num[0] == 1 && ($row_flags['value'] != 1)){
									$res_del = db_query_params ('DELETE FROM webcal_asst WHERE cal_boss = $1 AND cal_assistant = $2',
			array ($row_nom_boss['unix_group_name'],
				$row_nom_user['user_name']));

								//we del email of the old admin
								$mail = str_replace(",".$row_nom_user['email'],"",$row_mail['cal_email']) ;
db_query_params ('UPDATE webcal_user SET cal_email = $1 WHERE cal_login = $2',
			array ($mail,
				$row_nom_boss['unix_group_name']));
								}
						}
				}

		}
		elseif ($hookname == "add_cal_link_father") {
				//argument id du fils --> $params[0], id du pere--> $params[1]
				//plugin hierachy wait_son.php line 36
			$res_hierarchy = db_query_params ('select p1.group_id as father_id,p1.unix_group_name as father_unix_name,p1.group_name as father_name,p2.group_id as son_id,p2.unix_group_name as son_unix_name,p2.group_name as son_name from groups as p1,groups as p2,plugin_projects_hierarchy where p1.group_id=plugin_projects_hierarchy.project_id AND plugin_projects_hierarchy.project_id = $1 and p2.group_id=plugin_projects_hierarchy.sub_project_id AND plugin_projects_hierarchy.sub_project_id = $2and plugin_projects_hierarchy.activated=$3 AND plugin_projects_hierarchy.link_type=$4',
			array ($params[1],
				$params[0],
				't',
				'shar'));
				if($res_hierarchy){
						while($row_hierarchy = db_fetch_array($res_hierarchy)) {
							$res_entry = db_query_params ('SELECT cal_id FROM webcal_entry_user WHERE cal_login = $1 AND cal_status = $2',
			array ($row_hierarchy['son_unix_name'],
				'A'));
							if($res_entry){
								while($row_entry = db_fetch_array($res_entry)) {
									$res_insert_entry = db_query_params ('INSERT INTO webcal_entry_user (cal_id,cal_login,cal_status) VALUES ($1,$2,$3)',
			array ($row_entry['cal_id'],
				$row_hierarchy['father_unix_name'],
				'A'));
								}
							}
						}

					}
		}
		elseif ($hookname == "add_cal_link_father_event") {
				//argument name of the son --> $params[0], id_cal--> $params[1]
				//webcalendar/edit_entry_handler.php line 390
				//webcalendar/approve_entry.php line 21
			$res_nom = db_query_params ('SELECT group_id FROM groups WHERE unix_group_name = $1',
			array ($params[0]));
				$row_nom = db_fetch_array($res_nom);
				$res_pere = db_query_params ('SELECT project_id, unix_group_name FROM plugin_projects_hierarchy, groups WHERE plugin_projects_hierarchy.project_id = groups.group_id AND sub_project_id = $1 AND link_type = $2 AND activated = true',
			array ($row_nom['group_id'],
				'shar'));
				if($res_pere){
					$row_pere = db_fetch_array($res_pere);
					$res_insert = db_query_params ('INSERT INTO webcal_entry_user (cal_id,cal_login,cal_status) VALUES ($1,$2,$3)',
			array ($params[1],
				$row_pere['unix_group_name'],
				'A'));
				}

		}
		elseif ($hookname == "del_cal_link_father") {
				//argument id son --> $params[0], id father--> $params[1]
				//plugin hierachy wait_son.php line 36
			$res_hierarchy = db_query_params ('select p1.group_id as father_id,p1.unix_group_name as father_unix_name,p1.group_name as father_name,p2.group_id as son_id,p2.unix_group_name as son_unix_name,p2.group_name as son_name from groups as p1,groups as p2,plugin_projects_hierarchy where p1.group_id=plugin_projects_hierarchy.project_id AND plugin_projects_hierarchy.project_id = $1 and p2.group_id=plugin_projects_hierarchy.sub_project_id AND plugin_projects_hierarchy.sub_project_id = $2and plugin_projects_hierarchy.activated=$3 AND plugin_projects_hierarchy.link_type=$4',
			array ($params[1],
				$params[0],
				't',
				'shar'));
				if($res_hierarchy){
						while($row_hierarchy = db_fetch_array($res_hierarchy)) {
							$res_entry = db_query_params ('SELECT cal_id FROM webcal_entry_user WHERE cal_login = $1 ',
			array ($row_hierarchy['son_unix_name']));
							if($res_entry){
								while($row_entry = db_fetch_array($res_entry)) {
									$res_insert_entry = db_query_params ('DELETE FROM webcal_entry_user WHERE cal_id = $1 AND cal_login = $2',
			array ($row_entry['cal_id'],
				$row_hierarchy['father_unix_name']));
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
			$res_name = db_query_params ('SELECT user_name,user_pw,email  FROM users WHERE user_id = $1',
			array ($params));
				$row_name = db_fetch_array($res_name);

				$res_update = db_query_params ('UPDATE webcal_user SET cal_passwd = $1, cal_email = $2 WHERE cal_login = $3',
			array ($row_name['user_pw'],
				$row_name['email'],
				$row_name['user_name']));

		}
		elseif ($hookname == "change_cal_mail") {
				//argument user_id
				//account/change_email-complete.php line 63

			$res_name = db_query_params ('SELECT user_name,user_pw,email  FROM users WHERE user_id = $1',
			array ($params));
				$row_name = db_fetch_array($res_name);

				$res_old = db_query_params ('SELECT cal_email FROM webcal_user WHERE cal_login = $1',
			array ($row_name['user_name']));
				$row_old = db_fetch_array($res_old);

				//get all the cal_login where you need to change mail
				$res_all_mail = db_query_params ('SELECT cal_login, cal_email FROM webcal_user WHERE lower(cal_email) LIKE $1',
								 array ("%".$row_old['cal_email']."%"));
				print $query_all_mail;
				while($row_all_mail = db_fetch_array($res_all_mail)){
					$mail = str_replace($row_old['cal_email'],$row_name['email'],$row_all_mail['cal_email']);
										$res_update = db_query_params ('UPDATE webcal_user SET cal_passwd = $1, cal_email = $2 WHERE cal_login = $3',
			array ($row_name['user_pw'],
				$mail,
				$row_all_mail['cal_login']));

				}


		}
		elseif ($hookname == "cal_link_group" ){
		// www/include/project_home.php line 418
		//params = group_id
		print '<hr size="1" />';
		print util_make_link('/plugins/webcalendar/index2.php?type=group&group_id='.$params,_('Webcalendar'));
		}

	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
