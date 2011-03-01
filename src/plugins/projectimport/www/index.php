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
require_once $gfcommon.'import/import_users.php';
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
	 * Initializes data structurs from POSTed data coming from the form input
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
						//						print_r('Mapped : '. $imported_user . ' to ' . $mapped_user);
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
	
	/**
	 * Tries to match imported users to forge users, and display mapping form bits if needed
	 * @param array of ARC2 resources $imported_users
	 * @return html string
	 */
	function match_users($imported_users)
	{
		global $group_id, $feedback;
		
		$html = '';
		
		$res_memb = db_query_params('SELECT users.realname,users.user_id,
			users.user_name,user_group.admin_flags,user_group.role_id
			FROM users,user_group 
			WHERE users.user_id=user_group.user_id 
			AND user_group.group_id=$1 ORDER BY users.realname',
		array($group_id));

		$existing_users = array();
		while ($row_memb=db_fetch_array($res_memb)) {
			$existing_users[] = $row_memb['user_name'];
		}
			
		$matching_users_header_displayed = False;
			
		//$missing_users = array();
		foreach($imported_users as $user => $userres) {
			$imported_username = $this->importer->get_user_name($user);
			$email = $this->importer->get_user_email($user);
			if (array_key_exists($imported_username, $this->posted_user_mapping)) {
				$username = $this->posted_user_mapping[$imported_username];
			}
			else {
				$username = $imported_username;
			}
			//			print_r('check for '.$username);
			$user_object = &user_get_object_by_name($username);
			if (!$user_object) {
				//		$missing_users[] = $username;
				if ($feedback) $feedback .= '<br />';
				$feedback .= sprintf(_('Failed to find existing user matching imported user "%s"'), $username);

				if (! $matching_users_header_displayed) {
					$matching_users_header_displayed = True;

					$html .= $this->html_generator->boxTop(_("Matching imported users"));

					$html .= '<p>'._('Failed to find existing users matching the following imported users.').'<br />'.
					_('If you wish to map their data to existing users, choose them in the form bellow, and re-submit it:'). '</p>';

					$html .= '<table width="100%"><thead><tr>';
					$html .= '<th>'._('User logname').'</th>';
					$html .= '<th>'._('email').'</th>';
					$html .= '<th>'._('map to').'</th>';
					$html .= '</tr></thead><tbody>';
					$html .= '<input type="hidden" name="submit_mappings" value="y" />';
				}

				$html .= '<tr>';
				$html .= '<td style="white-space: nowrap;">'. $username .'</td>';
				$html .= '<td style="white-space: nowrap;">'. $email .'</td>';
				$html .= '<td><select name="map_'.$username.'">';

				$html .= '<option value="0" selected="selected">'._('Select existing user').'</option>';
				foreach($existing_users as $existing_user) {
					$html .= '<option value="'. $existing_user .'">'. $existing_user .'</option>';
				}
				$html .= '</select></td>';
				$html .= '</tr>';

			} else {
				if ($this->message) {
					$this->message .= '<br />';
				}
				$this->message .= sprintf(_('Found matching user for imported user "%s" : "%s"'), $imported_username, $username);
			}
		} // foreach
			
		if ($matching_users_header_displayed) {
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
		
		// If the posted JSON file indeed contains a project dump, an importer was created, and if it has data we can work
		if($this->importer) {
			// If it indeed has valid data
			if ($this->importer->has_project_dump()) {
				$this->message .= "Here are the results from your upload :";

				$imported_users = $this->importer->get_users();

				$this->importer->get_tools();

				$projects = $this->importer->get_projects();

				// always display the form : to be improved TODO
				if (! $this->form_header_already_displayed) {
					$this->form_header_already_displayed = true;
					$html .= '<form enctype="multipart/form-data" action="'.getStringFromServer('PHP_SELF').'" method="post">';
				}
				
				// Handle missing users, taking into account the user mapping form elements that may have been provided
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
						$html .= '<table id="project-summary-and-devs" class="my-layout-table" summary="">
	                             <tr>
		                     <td>
			             <h2>'._('Details of imported project : ').'<pre>'.$this->importer->get_project_name($project).'</pre></h2>
			             <h3>'._('Project summary').'</h3>';
						$html .= '<p><pre>'.$this->importer->get_project_description($project).'</pre></p>';

						$spaces = $this->importer->project_get_spaces($project);

						// if no spaces posted to be imported, display checkboxes to prompt user for spaces to be imported for next POST 
						if(!count($this->posted_spaces_imported)) {

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
							foreach($spaces as $space => $spaceres) {
								$uri = $space;
								$sha_uri = sha1($uri);
								//						$html .= 'sha1 :'.$sha_uri.'<br />';
								if (in_array($sha_uri, $this->posted_spaces_imported)) {
									$html .= 'Importing :'.$uri.'<br />';
									$this->importer->decode_space($space, $spaceres);
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

		
		if(count($this->posted_user_mapping)) {
			foreach ($this->posted_user_mapping as $imported_user => $mapped_user) {
				echo '<input type="hidden" name="map_'. $imported_user .'" value="'. $mapped_user .'" />';
			}
			echo '<input type="hidden" name="submit_mappings" value="y" />';
		}

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

	//	print_r($_POST);
	
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
