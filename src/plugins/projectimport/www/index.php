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

// don't include this in ProjectImporter, for unit test purposes, so do it here, in caller
//require_once $gfcommon.'import/import_users.php';
//print_r($gfplugins.'projectimport/common/ProjectImporter.class.php');

require_once $gfplugins.'projectimport/common/ProjectImporter.class.php';

// Dependency on php-arc
include_once('arc/ARC2.php');

/**
 * Manages the display of the page : HTML + forms
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

	protected $form_header_already_displayed;
	
	protected $html_generator;
	
	function ProjectImportPage($HTML) {
		$this->html_generator = $HTML;
		$this->message = '';
		$this->form_header_already_displayed = false;
		$this->importer = False;
		$this->posted_user_mapping = array();
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
	 * @return html string
	 */
	function match_users($imported_users)
	{
		global $group_id, $feedback;
		
		$html = '';
		$html_tbody = '';
					
		$needs_to_warn = FALSE;
		
		// displays all imported users with the found matching existing forge user if any
		foreach($imported_users as $user => $userres) {
			
			$imported_username = $this->importer->get_user_name($user);
			$email = $this->importer->get_user_email($user);
			
			$already_mapped = FALSE;
			// check if the user already chose to map it
			if (array_key_exists($imported_username, $this->posted_user_mapping)) {
				$already_mapped = $this->posted_user_mapping[$imported_username];
				$username = $this->posted_user_mapping[$imported_username];
				if ($this->message) {
						$this->message .= '<br />';
				}
				$this->message .= sprintf(_('you asked to map user "%s" to existing forge user "%s"'), $imported_username, $username);
			}
			else {
				// try to find user with same login
				$username = $imported_username;
			}
			
			$automatically_matched = FALSE;
			
			$user_object = user_get_object_by_name($username);
			// if the user hasn't mapped it already, try some automatic mapping
			if ( ! $already_mapped ) {
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
					$emails = array(strtolower($email));
					$user_objects = user_get_objects_by_email($emails);
					if (count($user_objects) == 1) {
						$user_object=$user_objects[0];
						$username = $user_object->getUnixName();
						$automatically_matched = $username;
						if ($this->message) {
							$this->message .= '<br />';
						}
						$this->message .= sprintf(_('Found matching existing forge user "%s" with same email "%s"'), $username, $email);
					}
				}
			}

			if (! $user_object) {
				if ($feedback) $feedback .= '<br />';
				$feedback .= sprintf(_('Failed to find existing user matching imported user "%s"'), $username);
				$needs_to_warn = TRUE;
			}
			
			$html_tbody .= '<tr>';
			$html_tbody .= '<td style="white-space: nowrap;">'. $imported_username .'</td>';
			$html_tbody .= '<td style="white-space: nowrap;">'. $email .'</td>';
			$html_tbody .= '<td><select name="map_'.$imported_username.'">';

			if ($user_object) {
				$html_tbody .= '<option value="0">'._('Optionally change for another existing user').'</option>';
			}
			else {
				$html_tbody .= '<option value="0" selected="selected">'._('Select existing user').'</option>';
			}
				
			// Load users members of the project
			/*
			$res_memb = db_query_params('SELECT users.realname,users.user_id,
			users.user_name,user_group.admin_flags,user_group.role_id
			FROM users,user_group
			WHERE users.user_id=user_group.user_id
			AND user_group.group_id=$1 ORDER BY users.realname',
			array($group_id));

			$existing_users = array();
			while ($row_memb=db_fetch_array($res_memb)) {
			$existing_users[] = $row_memb['user_name'];
			}*/
			$active_users = user_get_active_users();
		
			$existing_users = array();
			foreach($active_users as $user) {
				if ($user->isMember($group_id)) {
					$existing_users[] = $user->getUnixName();
				}
			}
			
			foreach($existing_users as $existing_user) {
				if ( ($already_mapped && ($existing_user == $already_mapped)) ||
					 ($automatically_matched && ($existing_user == $automatically_matched)) ) {
					$html_tbody .= '<option value="'. $existing_user .'" selected="selected">'. $existing_user .'</option>';
				}
				else {
					$html_tbody .= '<option value="'. $existing_user .'">'. $existing_user .'</option>';
				}
			}
			$html_tbody .= '</select></td>';
			$html_tbody .= '</tr>';

		} // foreach
		
		if (count($imported_users)) {
			
			if ($needs_to_warn) {
				$html .= '<p>'._('Failed to find existing users matching some imported users.').'<br />'.
					_('If you wish to map their data to existing users, choose them in the form bellow, and re-submit it:'). '</p>';
			}
			else {
				$html .= '<p>'._('You may change the mappings and re-submit.');
			}
			
			
			$html .= $this->html_generator->boxTop(_("Matching imported users to existing forge users"));
			
			$html .= '<table width="100%"><thead><tr>';
			$html .= '<th>'._('Imported user logname').'</th>';
			$html .= '<th>'._('Imported user email').'</th>';
			$html .= '<th>'._('To map to existing user').'</th>';
			$html .= '</tr></thead><tbody>';
			$html .= '<input type="hidden" name="submit_mappings" value="y" />';
			
			$html .= $html_tbody;
		
			//			$html .= '</form>';
			$html .= '</tbody></table>';
			$html .= $this->html_generator->boxBottom();

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

				$html .= $this->display_users($imported_users);
		
				
				// Handle missing users, taking into account the user mapping form elements 
				// that may have been provided
				$html .= $this->match_users($imported_users);
				
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
			                             '<pre>'.$this->importer->get_project_name($project).'</pre>
			                            </h2>
			                            <h3>'._('Project summary').'</h3>';
						$html .= '<p><pre>'.$this->importer->get_project_description($project).'</pre></p>';

						$spaces = $this->importer->project_get_spaces($project);

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
