<?php
/**
 *
 * SourceForge Generic Tracker facility
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2011, Franck Villaume - Capgemini
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfcommon.'tracker/ArtifactFile.class.php';

class ArtifactFileHtml extends ArtifactFile {

	/**
	 *  ArtifactFileHtml() - constructor
	 *
	 *  Use this constructor if you are modifying an existing artifact
	 *
	 *	@param $Artifact object
	 *  @param $data associative array (all fields from artifact_file_user_vw) OR id from database
	 *  @return true/false
	 */
	function ArtifactFileHtml(&$Artifact, $data=false) {
		return $this->ArtifactFile($Artifact,$data);
	}

	function upload($input_file,$input_file_name,$input_file_type,$description) {
		if (!util_check_fileupload($input_file)) {
			$this->setError('ArtifactFile: Invalid filename');
			return false;
		}
		$size = @filesize($input_file);
		$input_data = fread(fopen($input_file, 'r'), $size);
		return $this->create($input_file_name,$input_file_type,$size,$input_data,$description);
	}

}

?>
