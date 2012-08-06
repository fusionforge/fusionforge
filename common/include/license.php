<?php
/**
 * FusionForge license functions
 *
 * Copyright 2004, GForge, LLC
 * Copyright 2009, Roland Mas
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

function license_getname($id) {
	global $license_arr;
	if (!isset($license_arr[$id])) {
		$res = db_query_params ('SELECT * FROM licenses WHERE license_id=$1',
					array ($id));
		$license_arr[$id]=db_result($res,0,'license_name');
	}
	return $license_arr[$id];
}

function license_add($name) {
	global $feedback;
	$res = db_query_params ('INSERT INTO licenses (license_name) VALUES ($1)',
				array (htmlspecialchars ($name))) ;
	if (!$res) {
		$feedback .= ' Error adding License: '.db_error();
		return false;
	} else {
		return true;
	}
}

function license_update($id,$name) {
	global $feedback;
	$res = db_query_params ('UPDATE licenses SET license_name=$1 WHERE license_id=$2',
				array (htmlspecialchars($name),
				       $id)) ;
	if (!$res) {
		$feedback .= ' Error updating License: '.db_error();
		return false;
	} else {
		return true;
	}
}

function license_delete($id) {
	global $feedback;
	$res = db_query_params ('UPDATE groups SET license_id=100 WHERE license_id=$1',
				array ($id)) ;
	if (!$res) {
		$feedback .= ' Error deleting License: '.db_error();
		return false;
	} else {
		$res = db_query_params ('DELETE FROM licenses WHERE license_id=$1',
					array ($id)) ;
		if (!$res) {
			$feedback .= ' Error deleting License: '.db_error();
			return false;
		} else {
			return true;
		}
	}
}

function license_selectbox($title='license_id',$selected='xzxz') {
	$res = db_query_params ('SELECT license_id, license_name FROM licenses ORDER BY license_name',
				array()) ;
    return html_build_select_box($res,$title,$selected,false);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
