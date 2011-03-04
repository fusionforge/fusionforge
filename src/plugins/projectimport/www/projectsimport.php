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

// Import projects from a JSON file (Site Admin tool)
// will just create the project as if submitted from register/index.php
// it will stay there until approved by and an admin (no auto approval)
// Nothing more done, like importing users/roles/data : will need to be approved first


require_once('../../../www/env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

require_once $gfplugins.'projectimport/common/ProjectImporter.class.php';

include_once('arc/ARC2.php');

/**
 * Manages the display of the page : HTML + forms
 * 
 * @author Olivier Berger
 *
 */
class ProjectsImportPage {

	protected $message;
	
	protected $importer;

	protected $form_header_already_displayed;
	
	protected $html_generator;
	
	function ProjectsImportPage($HTML) {
		$this->html_generator = $HTML;
		$this->message = '';
		$this->form_header_already_displayed = false;
		
		$this->importer = ProjectImporter::getInstance();
	}
	
	/**
	 * Display initial contents of the page
	 * @param string $message
	 */
	function display_headers($message) {
		global $feedback;
		
		$params= array();
		$params['title']=_('Project importer');
		$params['toptab']='projectimport';
		
		site_admin_header($params);
		
		$this->message .= $message;
	}
	
	/**
	 * Display the page
	 */
	function display_main() {
		
		global $feedback;
		
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

		// finally, display the file upload form
		echo '<fieldset><legend>Please upload a file :</legend>
		       <p><center>
                          <input type="file" id="json" name="json" tabindex="2" size="30" />
                       </center></p>
                    </fieldset>
                    <div style="text-align:center;">
                      <input type="submit" name="submit" value="Submit" />
                    </div>
              </form>';

		site_footer(array());
	}
	
	/**
	 * Initializes data structures from POSTed data coming from the form input
	 */
	function initialize_from_submitted_data() {
		global $feedback;

		$json = getUploadedFile('json');
		$imported_file = $json['tmp_name'];
		$json = fread(fopen($imported_file, 'r'), $json['size']);
		if(! $json) {
			$feedback = "Error : missing data";
		}
		else {

			//			print_r($imported_file);
			$this->importer->parse_OSLCCoreRDFJSON($json);

			$debug = FALSE;
			if($debug) {
			 // Debug the loaded triples 
			 $triples = $this->importer->parse_OSLCCoreRDFJSON($json);
			 $ser = ARC2::getTurtleSerializer();

			 if(count($triples)) {
				$this->message .= '<pre>'. nl2br(htmlspecialchars($ser->toTurtle($triples))) . '</pre>';
				}
			}
		}
	}
	
	
	/**
	 * Does the main work
	 * @return html string
	 */
	function do_work() {
		global $feedback;

		$html = '';

		// If the posted JSON file indeed contains a project dump, an importer was created,
		// and if it has data we can work
		// If it indeed has valid data
		if ($this->importer->has_project_dump()) {
			$this->message .= "Here are the results from your upload :";

			$projects = $this->importer->get_projects();

			// start HTML output
			if (! $this->form_header_already_displayed) {
				$this->form_header_already_displayed = true;
				$html .= '<form enctype="multipart/form-data" action="'.getStringFromServer('PHP_SELF').'" method="post">';
			}
			// Then handle project(s)

			if(count($projects)) {

				// Display project's general description
				$html .= '<table id="project-summary-and-devs" class="my-layout-table" summary="">';
					
				// Display project attributes
				foreach($projects as $project) {

					$full_name = $project->getFullName();
					$unix_name = $project->getUnixName();
					$description = $project->getDescription();
					$purpose = 'Imported from JSON file';
					$scm_host = '';
					$is_public = $project->getIsPublic();
					$send_mail = ! forge_get_config ('project_auto_approval') ;
					$built_from_template = 0 ;

					$group = new Group();
					$u = session_get_user();
					$res = $group->create(
						$u,
						$full_name,
						$unix_name,
						$description,
						$purpose,
						'shell1',
						$scm_host,
						$is_public,
						$send_mail,
						$built_from_template);
						
					if (!$res) {
						$error_msg = $group->getErrorMessage();
						if ($feedback) $feedback .= '<br />';
						$feedback .= 'Import of "'. $unix_name . '": '. $error_msg;
							
						$html .= '<tr>
		                             <td>
			                            <h2>'._('Failed to create project'). ': <pre>'. $unix_name .'</pre>
			                            </h2>';
					}
					else {
						$html .= '<tr>
		                             <td>
			                            <h2>'._('Created project'). ': <pre>'. $unix_name .'</pre>
			                            </h2>';

					}
					$html .= '<h3>'._('Project summary').'</h3>';
					$html .= '<p><pre>'. $description .'</pre></p>';

					$html .= '<p>full_name : '. $full_name .'</p>';
					//$html .= '<p>purpose : '. $project->getPurpose() .'</p>';
					$html .= '<p>is_public : '. $is_public .'</p>';
					$html .= '</td></tr>';

				}
				$html .= '</table>';
			}
			else {
				$feedback .= 'parsing problem <br />';
			}
		}
		return $html;
	}
	
	
}

// The user should be forge admin
session_require_global_perm ('forge_admin');

global $group_id, $feedback;

$this_page = new ProjectsImportPage($HTML);

//print_r($_POST);

$message = '';

// when called back by post form we can initialize some elements provided by the user
if (getStringFromRequest('submit')) {

	$this_page->initialize_from_submitted_data();
		
}
else {
	$message .= "You can import a list of projects from a JSON RDF document compatible with ForgePlucker's dump format.<br />";
}

$this_page->display_headers($message);

$this_page->display_main();



// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
