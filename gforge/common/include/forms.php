<?php
/**
 * Form management functions
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
		      

/**
 *  form_generate_key() - Returns a new key, and registers it in the db.
 *
 *  @return	int	A new identifier. 
 *
 */
function form_generate_key() {
	global $sys_database_type;

	$is_new=false;
	db_begin();
	// there's about 99.999999999% probability this loop will run only once :) 
	while(!$is_new) {
		$key = md5(microtime() + rand() + $_SERVER["REMOTE_ADDR"]);
	    if ( $sys_database_type == "mysql" ) {
			$sql = "SELECT * FROM form_keys WHERE `key`='".$key."'";
		} else {
			$sql = "SELECT * FROM form_keys WHERE key='".$key."'";
		}
		$res=db_query($sql);
		$res=db_query($sql);
		if (!db_numrows($res)) {
			$is_new=true;	
		}
	}
	if ( $sys_database_type == "mysql" ) {
		$res = db_query("INSERT INTO form_keys (`key`,is_used,creation_date) VALUES ('".$key."',0,".time().")");
	} else {
		$res = db_query("INSERT INTO form_keys (key,is_used,creation_date) VALUES ('".$key."',0,".time().")");
	}
	if (!$res) {
		db_rollback();
		return false;
	}
	db_commit();
	return $key;	
}	

/**
 *  form_key_is_valid() - Checks the db to see if the given key is already used. In case it�s not already used
 * 	it updates the db.
 *
 *	@param	int	The key.			
 *  @return	boolean	True if the given key is already used. False if not. 
 *
 */
function form_key_is_valid($key) {
	global $sys_database_type;

	db_begin();
	if ( $sys_database_type == "mysql" ) {
		$sql = "SELECT * FROM form_keys WHERE `key`='$key' and is_used=0 FOR UPDATE";
	} else {
		$sql = "SELECT * FROM form_keys WHERE key='$key' and is_used=0 FOR UPDATE";
	}
	$res=db_query($sql);
	if (!$res || !db_numrows($res)) {
		db_rollback();
		return false;
	}
	if ( $sys_database_type == "mysql" ) {
		$sql = "UPDATE form_keys SET is_used=1 WHERE `key`='$key'";
	} else {
		$sql = "UPDATE form_keys SET is_used=1 WHERE key='$key'";
	}
	$res=db_query($sql);
	if (!$res) {
		db_rollback();
		return false;
	}
	db_commit();
	return true;
}

/**
 *  form_release_key() - Releases the given key if it is already used. If the given key it�s not in the db, it returns false.
 *
 *	@param	int	The key.			
 *  @return	boolean	True if the given key is successfully released. False if not. 
 *
 */
function form_release_key($key) {
	global $sys_database_type;

	db_begin();
	if ( $sys_database_type == "mysql" ) {
		$sql = "SELECT * FROM form_keys WHERE `key`='$key' FOR UPDATE";
	} else {
		$sql = "SELECT * FROM form_keys WHERE key='$key' FOR UPDATE";
	}
	$res=db_query($sql);
	if (!$res || !db_numrows($res)) {
		db_rollback();
		return false;
	}
	if ( $sys_database_type == "mysql" ) {
		$sql = "UPDATE form_keys SET is_used=0 WHERE `key`='$key'";
	} else {
		$sql = "UPDATE form_keys SET is_used=0 WHERE key='$key'";
	}
	$res=db_query($sql);
	if (!$res) {
		db_rollback();
		return false;
	}
	db_commit();
	return true;
}


?>
