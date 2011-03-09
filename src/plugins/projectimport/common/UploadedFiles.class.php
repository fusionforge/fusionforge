<?php

/**
 * Manages uploaded files
 * 
 * Copyright (c) 2011 Olivier Berger & Institut Telecom
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

/**
 * 
 * Per user directory of files, which are stored on the filesystem
 * @author olivier
 *
 */
class ProjectFilesDirectory extends Error {
	
	protected $dir_path;
		
	protected $html_generator;
	
	public function ProjectFilesDirectory($group_id, $HTML) {
		
		$this->html_generator = $HTML;

		$storage_base = forge_get_config('storage_base', 'projectimport-plugin');

		$group = group_get_object($group_id);
		
		$this->dir_path = $storage_base.'/'.$group->getUnixName().'/';
		
		// initialize the group storage dir
		if (!is_dir($this->dir_path)) {
			mkdir($this->dir_path,0755);
		} else {
			if ( fileperms($this->dir_path) != 0x4755 ) {
				chmod($this->dir_path,0755);
			}
		}
	}
	
	public function addFile($filename, $newfilename=FALSE) {
		if($newfilename) {
			$newpath = $this->dir_path . $newfilename;
		} else {
			$newpath = $this->dir_path . basename($filename);
		}
		$ret = rename($filename, $newpath);
		if (!$ret) {
			$this->setError(_('File %s cannot be moved to the storage location %s'), $filename, $newpath);
			return false;
		}
		return $newpath;
	}
	
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
								$html .= '<td><input type="radio" name="file_'.$sha_filename.'" value="'.$sha_filename.'" /></td>';
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