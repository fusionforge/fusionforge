<?php

/**
 * User importing script for site admin
 *
 * Copyright (c) 2011 Olivier Berger & Institut Telecom
 *
 * This program was developped in the frame of the COCLICO project
 * (http://www.coclico-project.org/) with financial support of the Paris
 * Region council.
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
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

// Import users from a JSON file (Site Admin tool)
// Users are created in the pending queue, and await forge admin moderation

// TODO : add confirmation pass instead of batch insertion

require_once('../../../www/env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

require_once $gfplugins.'projectimport/common/ProjectImporter.class.php';
require_once $gfplugins.'projectimport/common/UploadedFiles.class.php';

include_once('arc/ARC2.php');

/**
 * Manages the display of the page : HTML + forms
 * 
 * @author Olivier Berger
 *
 */
class UsersImportPage extends FileManagerPage {

	protected $importer;

	protected $form_header_already_displayed;
		
	function UsersImportPage($HTML) {
		$this->form_header_already_displayed = false;
		
		$this->importer = ProjectImporter::getInstance();
		
		$storage = new SiteAdminFilesDirectory($HTML);
		
		parent::FileManagerPage($HTML, $storage);
	}
	
	/**
	 * Display initial contents of the page
	 * @param string $message
	 */
	function display_headers($message) {
		global $feedback;
		
		$params= array();
		$params['title']=_('Users importer');
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

		$preselected = False;
		
		if (!$feedback) {
			if ($this->posted_selecteddumpfile) {
				$preselected = basename($this->posted_selecteddumpfile);
			}
			elseif ($this->posted_uploadedfile) {
				$preselected = $this->posted_uploadedfile;
			}
		}
		
		$selectiondialog = $this->storage->displayFileSelectionForm($preselected);
		
		echo $selectiondialog;
		
		// finally, display the file upload form
		echo '<fieldset><legend>Please upload a file :</legend>
		       <p><center>
                          <input type="file" id="uploaded_file" name="uploaded_file" tabindex="2" size="30" />
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

		$filechosen = $this->initialize_chosenfile_from_submitted();
		if($filechosen) {
			//print_r($filechosen);
			$json = fread(fopen($this->posted_selecteddumpfile, 'r'),filesize($this->posted_selecteddumpfile));
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
			
		if ((! $this->posted_selecteddumpfile) && (! $this->posted_uploadedfile)) {
			$this->feedback(_('Please select an existing file to process, or upload a new one'));
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
				
			// start HTML output
			if (! $this->form_header_already_displayed) {
				$this->form_header_already_displayed = true;
				$html .= '<form enctype="multipart/form-data" action="'.getStringFromServer('PHP_SELF').'" method="post">';
			}
				
			$imported_users = $this->importer->get_user_objs();
				
			if (count($imported_users)) {
				$html .= $this->html_generator->boxTop(_("Users found in imported file"));

				foreach($imported_users as $user => $user_obj) {

					$unix_name = $user_obj->getUnixName();
					$email = $user_obj->getEmail();

					$firstname = $user_obj->getFirstname();
					$lastname = $user_obj->getLastname();
					
					$theme_id=$this->html_generator->getThemeIdFromName(forge_get_config('default_theme'));
					$password1 = substr(md5($GLOBALS['session_ser'] . time() . util_randbytes()), 0, 8);
					$password2 = $password1;
					$language_id = language_name_to_lang_id (choose_language_from_context ());
					
					$new_user = new GFUser();
					$res = $new_user->create($unix_name,$firstname,$lastname,$password1,$password2,
						$email,$mail_site,$mail_va,$language_id,$timezone,$jabber_address,$jabber_only,$theme_id,'',
						$address,$address2,$phone,$fax,$title,$ccode,$send_mail);

					if (!$res) {
						$error_msg = $new_user->getErrorMessage();
						if ($feedback) $feedback .= '<br />';
						$feedback .= 'Import of "'. $unix_name . '": '. $error_msg;
							
						$html .= _('Failed to create user'). ': <pre>'. $unix_name .'</pre>';
					}
					else {
						$html .= _('Created user'). ': <pre>'. $unix_name .'</pre>';
					}

					$html .= 'User :<br />';
					$html .= ' account name : '. $unix_name .'<br />';
					$html .= ' email : '. $email .'<br />';
					$html .= ' firstname : '. $firstname .'<br />';
					$html .= ' lastname : '. $lastname .'<br />';
					$html .= '<br/>';
				}

				$html .= $this->html_generator->boxBottom();

			}
		
			else {
				$feedback .= 'Found no users<br />';
			}
		}
		return $html;
	}
	
	
}

// The user should be forge admin
session_require_global_perm ('forge_admin');

global $group_id, $feedback;

$this_page = new UsersImportPage($HTML);

//print_r($_POST);

$message = '';

// when called back by post form we can initialize some elements provided by the user
if (getStringFromRequest('submit')) {

	$this_page->initialize_from_submitted_data();
		
}
else {
	$message .= "You can import a list of users from a JSON RDF document compatible with ForgePlucker's dump format.<br />";
}

$this_page->display_headers($message);

$this_page->display_main();



// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
