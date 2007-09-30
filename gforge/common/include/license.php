<?php
/**
 * Licensing - licenses have been moved into tables.
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2004-07-22
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

function license_getname($id) {
	global $license_arr;
	if (!isset($license_arr[$id])) {
		$res=db_query("SELECT * FROM licenses WHERE license_id='$id'");
		$license_arr[$id]=db_result($res,0,'license_name');
	}
	return $license_arr[$id];
}

function license_add($name) {
	global $feedback;
	$res=db_query("INSERT INTO licenses(license_name) 
		values ('".htmlspecialchars($name)."')");
	if (!$res) {
		$feedback .= ' Error adding License: '.db_error();
		return false;
	} else {
		return true;
	}
}

function license_update($id,$name) {
	global $feedback;
	$res=db_query("UPDATE licenses 
		SET license_name='".htmlspecialchars($name)."'
		WHERE license_id='$id'");
	if (!$res) {
		$feedback .= ' Error adding License: '.db_error();
		return false;
	} else {
		return true;
	}
}

function license_delete($id) {
	global $feedback;
	$res=db_query("UPDATE groups
		SET license_id='100'
		WHERE license_id='$id'");
	if (!$res) {
		$feedback .= ' Error deleting License: '.db_error();
		return false;
	} else {
		$res=db_query("DELETE FROM licenses WHERE license_id='$id'");
		if (!$res) {
			$feedback .= ' Error deleting License: '.db_error();
			return false;
		} else {
			return true;
		}
	}
}

function license_selectbox($title='license_id',$selected='xzxz') {
    $res=db_query("SELECT license_id, license_name FROM licenses ORDER BY license_name");
    return html_build_select_box($res,$title,$selected,false);
}

?>
