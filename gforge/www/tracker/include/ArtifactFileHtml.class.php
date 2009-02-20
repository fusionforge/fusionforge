<?php
/**
  *
  * SourceForge Generic Tracker facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
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
