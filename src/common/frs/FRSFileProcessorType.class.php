<?php
/**
 * FusionForge file release system
 *
 * Copyright 2007 SoftwareEntwicklung Beratung Schulung
 * Copyright 2007 Karl Heinz Marbaise
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

require_once $gfcommon.'include/Error.class.php';

function get_frs_fileprocessortypes() {
	$res=db_query_params('SELECT * FROM frs_processor', array());
	if (db_numrows($res) < 1) {
		return false;
	}
	$ps = array();
	while($arr = db_fetch_array($res)) {
		$ps[]=new FRSFileProcessorType($arr['processor_id'],$arr['name']);
	}
	return $ps;
}

class FRSFileProcessorType extends Error {

	/**
	 * Associative array of data from db.
	 *
	 * @var  array   $data_array.
	 */
	var $data_array;

	function FRSFileProcessorType($processor_id, $name) {
		$this->Error();
		$this->data_array = array( 'processor_id' => $processor_id, 'name' => $name);
	}

	/**
	 *  fetchData - re-fetch the data for this FRSFileType from the database.
	 *
	 *  @param  int  The type_id
	 *  @return boolean	success.
	 */
	function fetchData($processor_id) {
		$res=db_query_params('SELECT * FROM frs_processor WHERE processor_id=$1', array($processor_id));
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
		return $this->data_array['processor_id'];
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
