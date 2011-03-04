<?php

/**
 * ProjectImport plugin for FusionForge 5.0.x
 *
 *
 * This is the beginning of a project import pugin
 * 
 * Author : Olivier Berger <olivier.berger@it-sudparis.eu>
 * 
 * Copyright (c) Olivier Berger & Institut Télécom
 * 
 * Released under the GNU GPL v2 or later
 * 
 */

require_once('../../../www/env.inc.php');
require_once $gfwww.'include/pre.php';

require_once $gfwww.'include/role_utils.php';

// don't include this in ProjectImporter, for unit test purposes, so do it here, in caller
require_once $gfcommon.'import/import_users.php';
//print_r($gfplugins.'projectimport/common/ProjectImporter.class.php');

require_once $gfplugins.'projectimport/common/ProjectImporter.class.php';

// Dependency on php-arc
//include_once('arc/ARC2.php');

/**
 * Manages the display of the page : HTML + forms
 * 
 * @author Olivier Berger
 *
 */
class ProjectImportPage {

	protected $message;
	
	protected $importer;

	// will contain the list of spaces to be imported
	protected $posted_spaces_imported;

	// will contain mapping of imported users to forge users
	protected $posted_user_mapping;

	// will contain roles of users added to the project
	protected $posted_new_member_roles;
	
	protected $form_header_already_displayed;
	
	protected $html_generator;
	
	function ProjectImportPage($HTML) {
		$this->html_generator = $HTML;
		$this->message = '';
		$this->form_header_already_displayed = false;
		$this->importer = False;
		$this->posted_user_mapping = array();
		$this->posted_new_member_roles = array();
		$this->posted_spaces_imported = array();
	}

	/**
	 * Initializes data structures from POSTed data coming from the form input
	 */
	function initialize_from_submitted_data() {
		global $group_id, $feedback;

		$group_id = getIntFromRequest('group_id');
		$json = getUploadedFile('json');
		$imported_file = $json['tmp_name'];
		$json = fread(fopen($imported_file, 'r'), $json['size']);
		if(! $json) {
			$feedback = "Error : missing data";
		}
		else {

			//			print_r($imported_file);
			$this->importer = new ProjectImporter($group_id);
			$this->importer->parse_OSLCCoreRDFJSON($json);

			/*
			 $triples = $importer->parse_OSLCCoreRDFJSON($json);
			 $ser = ARC2::getTurtleSerializer();

			 if(count($triples)) {
				$message .= '<pre>'. nl2br(htmlspecialchars($ser->toTurtle($triples))) . '</pre>';
				}
				*/

			// Get the user mappings posted (if any)
			if (getStringFromPost('submit_mappings')) {
				foreach (array_keys($_POST) as $key) {
					//					print_r('key : '. $key);
					if(!strncmp($key, 'map_', 4)) {
						$imported_user = substr($key, 4);
						$mapped_user = getStringFromPost($key);
						// print_r('Mapped : '. $imported_user . ' to ' . $mapped_user);
						//						echo '<br />';
						if($mapped_user) {
							$this->posted_user_mapping[$imported_user] = $mapped_user;
						}
					}
				}
			}
			if (getStringFromPost('submit_new_roles')) {
				foreach (array_keys($_POST) as $key) {
					//					print_r('key : '. $key);
					if(!strncmp($key, 'role_', 5)) {
						$added_user = substr($key, 5);
						$new_role = getStringFromPost($key);
						// print_r('Mapped : '. $imported_user . ' to ' . $mapped_user);
						//						echo '<br />';
						if($new_role) {
							$this->posted_new_member_roles[$added_user] = $new_role;
						}
					}
				}
			}
			// Get the spaces checked as to be imported (if any)
			if (getStringFromPost('submit_spaces')) {
				foreach (array_keys($_POST) as $key) {
					if(!strncmp($key, 'import_', 7)) {
						$spacesha1 = substr($key, 7);
						//						print_r('Selected for import : '. $spacesha1);
						//						echo '<br />';
						$this->posted_spaces_imported[] = $spacesha1;
					}
				}
			}
			//	echo '$posted_spaces_imported : ';
			//	print_r($posted_spaces_imported);
			//	echo '<br />';
			
		}
	}
	
	/**
	 * Display initial contents of the page
	 * @param string $message
	 */
	function display_headers($message) {
		global $group_id, $feedback;
		
		$params= array();
		$params['title']=_('Project importer');
		$params['toptab']='projectimport';
		$params['group']=$group_id;

		site_project_header($params);
		
		$this->message .= $message;
	}
	
	function display_users($imported_users) 
	{
		$html = '';
		
		$html .= $this->html_generator->boxTop(_("Users found in imported file"));
		
		foreach($imported_users as $user => $userres) {
			$html .= $this->importer->display_user($user);
		}
		
		$html .= $this->html_generator->boxBottom();
		
		return $html;
	}
	
	/**
	 * Tries to match imported users to forge users, and display mapping form bits if needed
	 * @param array of ARC2 resources $imported_users
	 * @param boolean apply the changes
	 * @return html string
	 */
	function match_users($imported_users, $apply = FALSE)
	{
		global $group_id, $feedback, $message;
		
		$html = '';
		$html_tbody = '';
					
		$needs_to_warn = FALSE;
		
		// if mapping has been provided for all imported users
		$mapping_all_users_provided = TRUE;
		
		// if all mapped users are already in the project
		$all_mapped_users_in_project = TRUE;
		
		// if all new project members roles posted by user
		$all_new_project_members_roles_set = TRUE;
		
		// array of existing forge users and the imported users that have been mapped to it
		$new_member_map_users = array();
		
		/*
		$role_names = array();
		$group_object = group_get_object($group_id);
		$existing_roles = $group_object->getRoles();
		foreach ($existing_roles as $role) {
			$name = $role->getName();
			$role_names[$name] = & $role;
		}
		*/
		
		// Load users members of the project (may be needed later for display of user mapping selection widgets)
		$existing_users = array();
		$active_users = user_get_active_users();
		foreach($active_users as $user_object) {
			$username = $user_object->getUnixName();
			//print_r('User : '.$username .'<br />');
			$role = '';
			if ($user_object->isMember($group_id)) {
				//print_r('member of project as ');
				$role = $user_object->getRole($group_id);
				if ($role) {
					$role = $role->getName();
					//print_r($role . '<br />');
				}
				/*
				 else {
					print_r('dunno...<br />');				
				}*/
			}
			/*
			else {
				print_r('not member of project...<br />');	
			}
			*/
			$existing_users[] = array( 'name' => $username,
											 'role' => $role);
		}

		// displays all imported users, with the found matching existing forge user, if any
		foreach($imported_users as $user => $userres) {
			
			$imported_username = $this->importer->get_user_name($user);
			$imported_email = $this->importer->get_user_email($user);
			
			$already_mapped = FALSE;
			// check if the user already chose to map it
			if (array_key_exists($imported_username, $this->posted_user_mapping)) {
				$already_mapped = $this->posted_user_mapping[$imported_username];
				$username = $this->posted_user_mapping[$imported_username];
			}
			else {
				// try to find user with same login
				$username = $imported_username;
			}
			
			$automatically_matched = FALSE;
			
			$user_object = user_get_object_by_name($username);
			// if the user hasn't mapped it already, try some automatic mapping
			if ( ! $already_mapped ) {
				$mapping_all_users_provided = FALSE;
				
				// if we have found an existing user with the same login, try to match it automatically
				if ($user_object) {
					$automatically_matched = $username;
					if ($this->message) {
						$this->message .= '<br />';
					}
					$this->message .= sprintf(_('Found matching existing forge user with same login "%s"'), $username);
				}
				else {
					// try to match by email
					$emails = array(strtolower($imported_email));
					$user_objects = user_get_objects_by_email($emails);
					if (count($user_objects) == 1) {
						$user_object=$user_objects[0];
						$username = $user_object->getUnixName();
						$automatically_matched = $username;
						if ($this->message) {
							$this->message .= '<br />';
						}
						$this->message .= sprintf(_('Found matching existing forge user "%s" with same email "%s"'), $username, $imported_email);
					}
				}
			}
			
			if (! $user_object) {
				if ($feedback) $feedback .= '<br />';
				$feedback .= sprintf(_('Failed to find existing user matching imported user "%s"'), $username);
				$needs_to_warn = TRUE;
			}
			
			// now construct mapping table to be displayed later
			$html_tbody .= '<tr>';
			$html_tbody .= '<td style="white-space: nowrap;">'. $imported_username .'</td>';
			$html_tbody .= '<td style="white-space: nowrap;">'. $imported_email .'</td>';
			$html_tbody .= '<td>'. $this->importer->get_user_role($user) . '</td>';
			
			// if not all mapping of users has been provided, then must display selection widgets
			if (! $mapping_all_users_provided ) {
				
				$html_tbody .= '<td><select name="map_'.$imported_username.'">';

				if ($user_object) {
					$html_tbody .= '<option value="0">'._('Optionally change for another existing user').'</option>';
				}
				else {
					$html_tbody .= '<option value="0" selected="selected">'._('Select existing user').'</option>';
				}
				// TODO : use html_build_select_box_from_arrays(...); ?
				foreach($existing_users as $existing_user) {
					$name = $existing_user['name'];
					$role = $existing_user['role'];
					if ($role) {
						$line = $name . ' (' . $role . ')';
					} else {
						$line = $name . ' ('. _('to be added to project') . ')';
					}
					if ( ($already_mapped && ($name == $already_mapped)) ||
					($automatically_matched && ($name == $automatically_matched)) ) {
						$html_tbody .= '<option value="'. $name .'" selected="selected">'. $line.'</option>';
					}
					else {
						$html_tbody .= '<option value="'. $name .'">'. $line .'</option>';
					}
				}
				$html_tbody .= '</select></td>';
			}
			else { // will display the mapped user anyway
				$role = ' ('. _('need to add to project'). ')';
				$user_object = user_get_object_by_name($already_mapped);
				// if mapped user is already project member
				if ($user_object->isMember($group_id)) {
					$role = $user_object->getRole($group_id);
					if ($role) {
						$role = ' ('. $role->getName() . ')';
					}
				}
				else {
					// memorize the list of users that need to be added to the project
					if (! array_key_exists($already_mapped, $new_member_map_users)) {
						$new_member_map_users[$already_mapped] = array();
					}
					$new_member_map_users[$already_mapped][] = $imported_username;
					$all_mapped_users_in_project = FALSE;
					
					if ( ! array_key_exists($already_mapped, $this->posted_new_member_roles) ) {
						$all_new_project_members_roles_set = FALSE;
					}
				}
				$html_tbody .= '<td>'. $already_mapped . $role;
				$html_tbody .= '<input type="hidden" name="map_'.$imported_username.'" value="'.$already_mapped.'" />';
				$html_tbody .= '</td>';
			}
			$html_tbody .= '</tr>';

		} // foreach

		// OK, now, will be able to render the HTML

		if (count($imported_users)) {

			// If we have to provide the user with some dialog about mapping
			if (! $mapping_all_users_provided) {
				
				$html .= $this->display_users($imported_users);
				
				if ($needs_to_warn) {
					$html .= '<p>'._('Failed to find existing users matching some imported users.').'<br />'.
					_('If you wish to map their data to existing users, choose them in the form bellow, and re-submit it:'). '</p>';
				}
				else {
					$html .= '<p>'._('You may change some mappings and re-submit.');
				}
			}
			
			// display users mapping table
			$html .= $this->html_generator->boxTop(_("Matching imported users to existing forge users"));
				
			$html .= '<table width="100%"><thead><tr>';
			$html .= '<th>'._('Imported user logname').'</th>';
			$html .= '<th>'._('Imported user email').'</th>';
			$html .= '<th>'._('Initial role').'</th>';
			if (! $mapping_all_users_provided) {
				$html .= '<th>'._('Map to existing user (role)').'</th>';
			} else {
				$html .= '<th>'._('Mapped to existing user').'</th>';
			}
			$html .= '</tr></thead><tbody>';
			$html .= '<input type="hidden" name="submit_mappings" value="y" />';
				
			$html .= $html_tbody;

			$html .= '</tbody></table>';
			$html .= $this->html_generator->boxBottom();
			
			
			if ($mapping_all_users_provided) {
				// the mapping must be applied as all users mapping has been posted
				
				//if ($apply) {
				$can_proceed = TRUE;
				
				// now, need to check if new (mapped to) users need to be added to (roles of) the project
				if ( ! $all_mapped_users_in_project ) {
					
					// if the new project members haven't been posted by the user display box
					if ( ! $all_new_project_members_roles_set ) { 
					
						$html .= $this->html_generator->boxTop(_("Matching new project members roles"));
							
						$html .= '<table width="100%"><thead><tr>';
						$html .= '<th>'._('New project member').'</th>';
						$html .= '<th>'._('Imported users mapped to it').'</th>';
						$html .= '<th>'._('New role').'</th>';
							
						$html .= '</tr></thead><tbody>';
							
						foreach($new_member_map_users as $new_member => $imported_users_mapped) {
							$html .= '<tr>';
							$html .= '<td>'. $new_member . '</td>';
							$html .= '<td>'. implode(', ', $imported_users_mapped) . '</td>';

							// TODO : use a more sophisticated select box maybe : the selection by default of the first may not be the right thing to suggest ?
							$html .= '<td>'. role_box($group_id, 'role_'.$new_member) . '</td>';
							$html .= '</tr>';
						}
							
						$html .= '<input type="hidden" name="submit_new_roles" value="y" />';

						$html .= '</tbody></table>';
						$html .= $this->html_generator->boxBottom();
					}
				}

				// Last check if we can proceed to the user's import
				$users = array();
				foreach ($imported_users as $user => $userres) {
					
					//print_r('Check for : '. $user. '<br />');
					
					$imported_username = $this->importer->get_user_name($user);
					$mapped_to_username = $this->posted_user_mapping[$imported_username];
					$user_object = user_get_object_by_name($mapped_to_username);
					
					if ($user_object) {
						if ( ! $user_object->isMember($group_id) ) {
							// no need to add it, already in the group
							// $this->message .= sprintf(_('Imported user "%s", mapped as "%s" which is already in the project : no need to add it.'), $imported_username, $mapped_to_username);
						
							// need to add it to the group
							if ( array_key_exists($mapped_to_username, $this->posted_new_member_roles) ) {
								$role = $this->posted_new_member_roles[$mapped_to_username];
								$rolename = $this->importer->get_user_role($user);
								
								$users[$mapped_to_username] = array( 'role' => $role );
								
								if ($this->message) {
									$this->message .= '<br />';
								}
								$this->message .= sprintf(_('Imported user "%s" (role "%s"), mapped as "%s" which is not yet in the project : need to add it as role "%s".'), 
												$imported_username, $rolename, $mapped_to_username, $role);
							}
							else {
								$can_proceed = FALSE;
							}
							
						}
					}
					else {
						// user not found : probably messing with the form post
						$feedback .= sprintf(_('Failed to find mapped user "%s"'), $mapped_to_username);
						$can_proceed = FALSE;
					}
				} // foreach
				
				if($can_proceed) {
					//print_r('We can proceed !');
					$check=TRUE;
					if($apply) $check = FALSE;
					
					// For security, for now : TODO to be removed later
					//$check = TRUE;
					user_fill($users, $group_id, $check);
					$html .= $message;
				}
				else {
					$feedback .= "Couldn't proceed!";
				}
				$html .= "All (mapped) imported users added to the group.";
				// }
			}
			
		}
		
		return $html;

	}
	
	/**
	 * Does the main work
	 * @return html string
	 */
	function do_work() {
		global $group_id, $feedback;
		
		$html = '';
		
		// If the posted JSON file indeed contains a project dump, an importer was created, 
		// and if it has data we can work
		if($this->importer) {
			// If it indeed has valid data
			if ($this->importer->has_project_dump()) {
				$this->message .= "Here are the results from your upload :";

				$imported_users = $this->importer->get_users();

				$this->importer->get_tools();

				$projects = $this->importer->get_projects();

				// start HTML output
				if (! $this->form_header_already_displayed) {
					$this->form_header_already_displayed = true;
					$html .= '<form enctype="multipart/form-data" action="'.getStringFromServer('PHP_SELF').'" method="post">';
				}		
				
				// Handle missing users, taking into account the user mapping form elements 
				// that may have been provided
				$apply = TRUE;
				$html .= $this->match_users($imported_users, $apply);
				
				// Then handle project(s)

				if(count($projects)) {

					//			$output = '<b>Project parsed:</b><br />';
					//			$output .= '<pre>'. nl2br(htmlspecialchars(print_r($projects,True))) . '</pre>';
					/*			$output .= '<b>Trackers parsed:</b><br />';
					foreach ($this->importer->trackers as $tracker) {
					$output .= '<pre>'. nl2br(htmlspecialchars(print_r($tracker, True))) . '</pre>';
					}
					*/
					//			$html .= $output;

					// Display project attributes
					foreach($projects as $project) {
						
						// Display project's general description
						$html .= '<table id="project-summary-and-devs" class="my-layout-table" summary="">
	                               <tr>
		                             <td>
			                            <h2>'._('Details of imported project : ').
			                             '<pre>'.$project->getUnixName().'</pre>
			                            </h2>
			                            <h3>'._('Project summary').'</h3>';
						$html .= '<p><pre>'.$project->getDescription().'</pre></p>';

						$spaces = $project->getSpaces();

						// if no spaces posted to be imported, display checkboxes to prompt user 
						// for spaces to be imported for next POST 
						if( ! count($this->posted_spaces_imported) ) {

							// spaces header first
							if(count($spaces)) {
								$html .= $this->html_generator->boxTop(_("Project's spaces found"));
								if (! $this->form_header_already_displayed) {
									$html .= '<form enctype="multipart/form-data" action="'.getStringFromServer('PHP_SELF').'" method="post">';
									$this->form_header_already_displayed = true;
								}
								$html .= '<table width="100%"><thead><tr>';
								$html .= '<th>'._('uri').'</th>';
								$html .= '<th>'._('type').'</th>';
								$html .= '<th>'._('Import space ?').'</th>';
								$html .= '</tr></thead><tbody>';
							}
							foreach($spaces as $space => $spaceres) {
								$uri = $space;
								$sha_uri = sha1($uri);
								$type = $spaceres->getPropValue('rdf:type');

								$html .= '<tr>';
								$html .= '<td style="white-space: nowrap;">'. $uri .'</td>';
								$html .= '<td style="white-space: nowrap;">'. $type .'</td>';
								if(array_key_exists($sha_uri, $this->posted_spaces_imported)) {
									$html .= '<td><input type="checkbox" name="import_'.$sha_uri.'" value="'.$sha_uri.'" selected="selected" /></td>';
								}
								else {
									$html .= '<td><input type="checkbox" name="import_'.$sha_uri.'" value="'.$sha_uri.'" /></td>';
								}
								$html .= '</tr>';
							}
							if(count($spaces)) {
								$html .= '<input type="hidden" name="submit_spaces" value="y" />';
								$html .= '</tbody></table>';
								$html .= $this->html_generator->boxBottom();
							}
						}
						// else, user tells us we have to import the spaces
						else {
							//					$html .= 'to be imported:';
							//					print_r($this->posted_spaces_imported);
							//					$html .= '<br />';
							foreach($spaces as $uri => $spaceres) {
								$sha_uri = sha1($uri);
								//						$html .= 'sha1 :'.$sha_uri.'<br />';
								if (in_array($sha_uri, $this->posted_spaces_imported)) {
									$html .= 'Importing :'.$uri.'<br />';
									$this->importer->decode_space($uri, $spaceres);
								}
							}
						}
					}
				}
			}
			else {
				$feedback .= 'parsing problem <br />';
			}
		}
		return $html;
	}
	
	/**
	 * Display the page
	 */
	function display_main() {
		global $feedback, $group_id;
		
		// Do the work, first !
		$html = $this->do_work();
		
		if($this->message) {
			echo $this->message . '<br />';
		}
		html_feedback_top($feedback);
		
		echo $html;
		
		// If invoked initially (not on callback) or if more details needed
		// display the last part of the form for JSON file upload
		if (! $this->form_header_already_displayed) {
			echo '<form enctype="multipart/form-data" action="'.getStringFromServer('PHP_SELF').'" method="post">';
			$this->form_header_already_displayed = True;
		}

		// If user mapping has been provided, then display it
		if(count($this->posted_user_mapping)) {
			foreach ($this->posted_user_mapping as $imported_user => $mapped_user) {
				echo '<input type="hidden" name="map_'. $imported_user .'" value="'. $mapped_user .'" />';
			}
			echo '<input type="hidden" name="submit_mappings" value="y" />';
		}

		// finally, display the file upload form
		echo '<input type="hidden" name="group_id" value="' . $group_id . '" />
                    <fieldset><legend>Please upload a file :</legend>
		       <p><center>
                          <input type="file" id="json" name="json" tabindex="2" size="30" />
                       </center></p>
                    </fieldset>
                    <div style="text-align:center;">
                      <input type="submit" name="submit" value="Submit" />
                    </div>
              </form>';


		site_project_footer(array());
		
	}
}

// OK, we need a session
if (session_loggedin()) {

	// The user should be project admin
	if (!user_ismember($group_id,'A')) {
		exit_permission_denied(_('You cannot import project unless you are an admin on that project'));
	}

	global $group_id, $feedback;

	$this_page = new ProjectImportPage($HTML);

	//print_r($_POST);
	
	$message = '';
	
	// when called back by post form we can initialize some elements provided by the user
	if (getStringFromRequest('submit')) {
		
		$this_page->initialize_from_submitted_data();
			
	}
	else {
		$message .= "You can import a project from a JSON RDF document compatible with ForgePlucker's dump format.<br />";
	}

	$this_page->display_headers($message);
	
	$this_page->display_main();
	
} else {

	exit_not_logged_in();

}


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
