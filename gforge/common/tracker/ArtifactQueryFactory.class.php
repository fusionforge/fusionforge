<?php
/**
 * GForge Tracker Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */
 
require_once('common/include/Error.class.php');
require_once('common/tracker/ArtifactQuery.class.php');

class ArtifactQueryFactory extends Error {
	/**
	 * The ArtifactType object.
	 *
	 * @var	 object  $Group.
	 */
	var $ArtifactType;

	/**
	 * The ArtifactQueries array.
	 *
	 * @var	 array	ArtifactQueries.
	 */
	var $ArtifactQueries = null;

	/**
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which this ArtifactQueryFactory is associated
	 *	@return	boolean	success.
	 */
	function ArtifactQueryFactory(&$ArtifactType) {
		$this->Error();
		if (!$ArtifactType || !is_object($ArtifactType)) {
			$this->setError('ArtifactQueryFactory:: No ArtifactType Object');
			return false;
		}
		if ($ArtifactType->isError()) {
			$this->setError('ArtifactQueryFactory:: '.$ArtifactType->getErrorMessage());
			return false;
		}
		$this->ArtifactType =& $ArtifactType;


		return true;
	}
	
	function& getArtifactQueries() {
		if (!is_null($this->ArtifactQueries)) {
			return $this->ArtifactQueries;
		}
		
		$this->ArtifactQueries = array();
		
		$res = db_query("SELECT * FROM artifact_query WHERE user_id='".user_getid()."' ".
					"AND group_artifact_id='".$this->ArtifactType->getID()."'");
		if (!$res) {
			$this->setError("ArtifactQueryFactory:: Database error");
		}
		
		while ($data = db_fetch_array($res)) {
			$artifactQuery = new ArtifactQuery($this->ArtifactType, $data["artifact_query_id"]);
			$this->ArtifactQueries[] = $artifactQuery;
		}
		
		return $this->ArtifactQueries;
	}
}
 ?>
