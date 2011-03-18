<?php
/**
 * FusionForge file release system
 *
 * Copyright 2007 SoftwareEntwicklung Beratung Schulung
 * Copyright 2007 Karl Heinz Marbaise
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once $gfcommon.'include/Error.class.php';

function get_frs_filetypes() {
	$res=db_query_params('SELECT * FROM frs_filetype', array());
	if (db_numrows($res) < 1) {
		return false;
	}
	$ps = array();
	while($arr = db_fetch_array($res)) {
		$ps[]=new FRSFileType($arr['type_id'],$arr['name']);
	}
	return $ps;
}

class FRSFileType extends Error {

	/**
	 * Associative array of data from db.
	 *
	 * @var  array   $data_array.
	 */
	var $data_array;

	function FRSFileType ($type_id, $name) {
		$this->Error();
		$this->data_array = array( 'type_id' => $type_id, 'name' => $name);
	}

	/**
	 *  fetchData - re-fetch the data for this FRSFileType from the database.
	 *
	 *  @param  int  The type_id
	 *  @return boolean	success.
	 */
	function fetchData($type_id) {
		$res=db_query_params('SELECT * FROM frs_filetype WHERE type_id=$1', array($type_id));
		if (!$res || db_numrows($res) < 1) {
			$this->setError('FRSFileType::fetchData()  Invalid type_id');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *  getID - get this file_id.
	 *
	 *  @return	int	The id of this file.
	 */
	function getID() {
		return $this->data_array['type_id'];
	}

	/**
	 *  getName - get the name of this file.
	 *
	 *  @return string  The name of this file.
	 */
	function getName() {
		return $this->data_array['name'];
	}

}

?>
