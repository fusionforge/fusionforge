<?php

/**
 * Utility classes to manage uploaded files
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

// TODO : add cleanup mechanism for storage


/**
 * Generic file storage management class
 * The files are stored in an arbitrary dir on the server side
 * 
 * @author Olivier Berger
 *
 */
class AbstractFilesDirectory extends Error {
	
	protected $dir_path;
		
	protected $html_generator;
	
	/**
	 * Initializes the directory permissions
	 */
	protected function createStorage() {
		// initialize the group storage dir
		if (!is_dir($this->dir_path)) {
			mkdir($this->dir_path,0755);
		} else {
			if ( fileperms($this->dir_path) != 0x4755 ) {
				chmod($this->dir_path,0755);
			}
		}
	}
	
	/**
	 * Constructor
	 * @param HTML generator $HTML
	 * @param string $storage_base path to the storage directory (if omitted, uses a temprary dir in /tmp)
	 */
	public function AbstractFilesDirectory($HTML, $storage_base=False) {
		
		$this->html_generator = $HTML;
		if(! $storage_base) {
			$storage_base = tempnam("/tmp", "ff-projectimport");
		}
		$this->dir_path = $storage_base;
		$this->createStorage();
	}
	
	/**
	 * Move a file on the server (typically an uploaded one) into the storage dir
	 * @param string $filename
	 * @param string $newfilename (optional) rename file on the fly
	 * @return boolean|string
	 */
	public function addFileMovingIt($filename, $newfilename=FALSE) {
		if($newfilename) {
			$newpath = $this->dir_path . $newfilename;
		} else {
			$newpath = $this->dir_path . basename($filename);
		}
		// do the move of the file
		$ret = rename($filename, $newpath);
		if (!$ret) {
			$this->setError(sprintf(_('File %s cannot be moved to the storage location %s'), $filename, $newpath));
			return false;
		}
		return $newpath;
	}
	
	/**
	 * Displays an HTML list of the directory contents
	 * @return string HTML
	 */
	public function displayContents() {
		$html = '';
		if (is_dir($this->dir_path)) {
			// maybe use scandir instead ?
    		if ($dh = opendir($this->dir_path)) {
    			$html.='<ul>';
    	
        		while (($file = readdir($dh)) !== false) {
        			if ($file != '.' && $file != '..') {
            			$html.='<li>'.'filename: '. $file .': filetype: ' . filetype($this->dir_path . $file) . '</li>';
        			}
	        	}
    	    	closedir($dh);
    	    	$html.='</ul>';
    		}
		}
		return $html;
	}
	
	/**
	 * Returns an HTML box/table containing (single) file selection radio buttons
	 * @param string $preselected filename
	 * @return boolean|string
	 */
	public function displayFileSelectionForm($preselected = False) {
		$html = '';
		$finfo = new finfo(FILEINFO_MIME, "/usr/share/misc/magic"); // return mime type ala mimetype extension

		if (!$finfo) {
			$this->setError(_('Opening fileinfo database failed'));
			return false;
		}
		
		if (is_dir($this->dir_path)) {
			$contents = scandir($this->dir_path);
			if(count($contents) > 2) {
				$html .= $this->html_generator->boxTop(_("Uploaded files available"));
				$html .= '<table width="100%"><thead><tr>';
				$html .= '<th>'._('name').'</th>';
				$html .= '<th>'._('type').'</th>';
				$html .= '<th>'._('selected ?').'</th>';
				$html .= '</tr></thead><tbody>';
			
				foreach($contents as $file) {
					if ($file != '.' && $file != '..') {
						$filepath = $this->dir_path . $file;
						$filetype = filetype($filepath);
						$html .= '<tr>';
						$html .= '<td style="white-space: nowrap;"><tt>'. $file .'</tt></td>';
						if ($filetype == 'file') {
							$mimetype = $finfo->file($filepath);
							$html .= '<td style="white-space: nowrap;">' . $mimetype . '</td>';
							$sha_filename = sha1($file);
							if ($file == $preselected) {
								$html .= '<td><input type="radio" name="file_'.$sha_filename.'" value="'.$sha_filename.'" checked="checked" /></td>';
							} else {
								$html .= '<td><input type="radio" 		$group = group_get_object($group_id);
								name="file_'.$sha_filename.'" value="'.$sha_filename.'" /></td>';
							}
						}
						else {
							$html .= '<td style="white-space: nowrap;">' . $filetype . '</td>';
							$html .= '<td style="white-space: nowrap;" />';
						}
					}
				}
				$html .= '<input type="hidden" name="submit_file" value="y" />';
				$html .= '</tbody></table>';
				$html .= $this->html_generator->boxBottom();
			}
    	}
	
		return $html;
	}
	
	/**
	 * Returns the path given a SHA1 hash for a filename
	 * @param unknown_type $filesha1
	 * @return Ambigous <boolean, string>
	 */
	public function getFilePath($filesha1) {
		$filepath = False;
		if (is_dir($this->dir_path)) {
			$contents = scandir($this->dir_path);	
			foreach($contents as $file) {
				if ($filesha1 == sha1($file)) {
					$filepath = $this->dir_path . $file;
					break;
				}
			}
		}
		return $filepath;
	}
}


/**
 * Specialized file storage management class for site-level files
 * 
 * Files are stored inside $storage_base/projectimport-plugin (for instance '$core/data_path/plugins/projectimport/)
 * 
 * @author Olivier Berger
 *
 */
class SiteAdminFilesDirectory extends AbstractFilesDirectory {
	public function SiteAdminFilesDirectory($HTML) {

		$storage_base = forge_get_config('storage_base', 'projectimport-plugin');

		parent::AbstractFilesDirectory($HTML, $storage_base);
				
	}
}

/**
 * Specialized file storage management class for project-level files
 * 
 * Files are stored inside subdirs of $storage_base/projectimport-plugin (for instance '$core/data_path/plugins/projectimport/_projname_/)
 * 
 * @author Olivier Berger
 *
 */
class ProjectFilesDirectory extends AbstractFilesDirectory {
	
	/**
	 * Constructor
	 * @param HTML generator $HTML
	 * @param integer $group_id
	 */
	public function ProjectFilesDirectory($HTML, $group_id) {

		// store the project files inside a group unix name's subdir
		$group = group_get_object($group_id);
		$storage_base = forge_get_config('storage_base', 'projectimport-plugin');
		$storage_base .= '/'. $group->getUnixName().'/';

		parent::AbstractFilesDirectory($HTML, $storage_base);
				
	}
}


/**
 * Utility HTML display class for pages containing a file upload and selection form
 * 
 * @author Olivier Berger
 *
 */
class FileManagerPage {

	/**
	 * filename of the selected file POSTed
	 * @var string
	 */
	protected $posted_selecteddumpfile;
	/**
	 * filename of the uploaded file POSTed
	 * @var string
	 */
	protected $posted_uploadedfile;
	
	protected $html_generator;
	
	protected $message;
	
	/**
	 * File storage
	 * @var AbstractFilesDirectory
	 */
	protected $storage;
	
	/**
	 * Constructor
	 * @param HTML generator $HTML
	 * @param AbstractFilesDirectory $storage (optional)
	 */
	function FileManagerPage($HTML, $storage=False) {
		$this->html_generator = $HTML;
		$this->message = '';

		// If specialized storage provided, then use it
		if($storage) {
			$this->storage = $storage;
		} else {
			// otherwise create one with temporary directory
			$this->storage = new AbstractFilesDirectory($this->html_generator);	
		}
		$this->posted_selecteddumpfile = False;
		$this->posted_uploadedfile = False;
	}

	/**
	 * Adds a $feedback message
	 * @param string $message
	 */
	function feedback($message) {
		global $feedback;
		if ($feedback) $feedback .= '<br />';
		$feedback .= $message;
	}
	
	/**
	 * Parses the POSTed data to initialize the $posted_selecteddumpfile and $posted_uploadedfile and returns selected file name (if any)
	 * @return Ambigous <boolean, Ambigous, string>
	 */
	function initialize_chosenfile_from_submitted() {
		
		$filechosen = FALSE;
		
		$uploaded_file = getUploadedFile('uploaded_file');
		//print_r($uploaded_file);
		
		// process chosen file -> $filechosen set after this (or not)
		if (getStringFromPost('submit_file')) {
			$filesha1s = array();
			foreach (array_keys($_POST) as $key) {
				if(!strncmp($key, 'file_', 5)) {
					$filesha1 = substr($key, 5);
					$filesha1s[] = $filesha1;
				}
			}
			if (count($filesha1s) > 1) {
				
				$this->feedback(_('Please select only one file'));
			} else {
				if (count($filesha1s) == 1) {
					$filechosen = $this->storage->getFilePath($filesha1s[0]);
					if(!$filechosen) {
						$this->feedback(_('File not found on server'));
					}
				}
			}
		}
		
		// Process uploaded file : $this->posted_selecteddumpfile set afterwards (or not)
		if($uploaded_file) {
			// May use codendi's rules to check results of upload ?
			//$rule_file = new Rule_File(); 
			//if ($rule_file->isValid($uploaded_file)) {
			if($uploaded_file['error'] == UPLOAD_ERR_OK  ) {
				if ($filechosen) {
					$this->feedback(_('Please either select existing file OR upload new file'));
					$filechosen = False;
				}
				else {
					$imported_file = $uploaded_file['tmp_name'];
					$imported_file = $this->storage->addFileMovingIt($imported_file, $uploaded_file['name']);
					if(! $imported_file) {
						$this->feedback($this->storage->getErrorMessage());
					}
					else {
						$this->posted_uploadedfile = $uploaded_file['name'];
						$this->message .= sprintf(_('File "%s" uploaded and pre-selected'),$this->posted_uploadedfile);
					}
				}
			}
			else {
				$error_code = $uploaded_file['error'];
				if ($error_code != UPLOAD_ERR_NO_FILE ) {
					switch ($error_code) {
	        			case UPLOAD_ERR_INI_SIZE:
	            			$this->feedback(_('The uploaded file exceeds the upload_max_filesize directive in php.ini'));
	        			case UPLOAD_ERR_FORM_SIZE:
	            			$this->feedback(_('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'));
	        			case UPLOAD_ERR_PARTIAL:
	            			$this->feedback(_('The uploaded file was only partially uploaded'));
	        			/* case UPLOAD_ERR_NO_FILE:
	            			return 'No file was uploaded';*/
	        			case UPLOAD_ERR_NO_TMP_DIR:
	            			$this->feedback(_('Missing a temporary folder'));
	        			case UPLOAD_ERR_CANT_WRITE:
	            			$this->feedback(_('Failed to write file to disk'));
	        			case UPLOAD_ERR_EXTENSION:
	            			$this->feedback(_('File upload stopped by extension'));
	        			default:
	            			$this->feedback(_('Unknown upload error %d', $error_code));
	    			} 
				}
			}
		}
		if($filechosen) {
			$this->posted_selecteddumpfile = $filechosen;
		}
		
		return $filechosen;
	}
}

