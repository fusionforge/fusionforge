<?php
/**
 * FusionForge form management
 *
 * Copyright 2005, GForge, LLC
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

/**
 *  form_generate_key() - Returns a new key, and registers it in the db.
 *
 *  @return	int	A new identifier. 
 *
 */
function form_generate_key() {
	$is_new=false;
	db_begin();
	// there's about 99.999999999% probability this loop will run only once :) 
	while(!$is_new) {
		$key = md5(microtime() + util_randbytes() + $_SERVER["REMOTE_ADDR"]);
		$res = db_query_params ('SELECT * FROM form_keys WHERE key=$1', array ($key));
		if (!db_numrows($res)) {
			$is_new=true;	
		}
	}
	$res = db_query_params('INSERT INTO form_keys (key,is_used,creation_date) VALUES ($1, 0, $2)', array ($key,time()));
	if (!$res) {
		db_rollback();
		return false;
	}
	db_commit();
	return $key;	
}	

/**
 *  form_key_is_valid() - Checks the db to see if the given key is already used. In case it's not already used
 * 	it updates the db.
 *
 *	@param	int	The key.			
 *  @return	boolean	True if the given key is already used. False if not. 
 *
 */
function form_key_is_valid($key) {
	// Fail back mode if key is empty. This can happen when there is
	// a problem with the generation. In this case, it may be better
	// to disable this check instead of blocking all the application.
	if (empty($key))
		return true;

	db_begin();
	$res = db_query_params ('SELECT * FROM form_keys WHERE key=$1 and is_used=0 FOR UPDATE', array ($key));
	if (!$res || !db_numrows($res)) {
		db_rollback();
		return false;
	}
	$res = db_query_params ('UPDATE form_keys SET is_used=1 WHERE key=$1', array ($key));
	if (!$res) {
		db_rollback();
		return false;
	}
	db_commit();
	return true;
}

/**
 *  form_release_key() - Releases the given key if it is already used. If the given key it's not in the db, it returns false.
 *
 *	@param	int	The key.			
 *  @return	boolean	True if the given key is successfully released. False if not. 
 *
 */
function form_release_key($key) {
	db_begin();
	$res = db_query_params ('SELECT * FROM form_keys WHERE key=$1 FOR UPDATE', array ($key));
	if (!$res || !db_numrows($res)) {
		db_rollback();
		return false;
	}
	$res = db_query_params ('UPDATE form_keys SET is_used=0 WHERE key=$1', array ($key));
	if (!$res) {
		db_rollback();
		return false;
	}
	db_commit();
	return true;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
