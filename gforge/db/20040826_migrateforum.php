#! /usr/bin/php4 -f
<?php
/**
 * GForge Forum Renamer - forum names are now unix-format for email gateway
 *
 * Copyright 2004 GForge, LLC
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

require_once('www/include/squal_pre.php');

//
//	Convert forum names to new legal syntax
//
db_begin();
$res=db_query("SELECT group_forum_id,forum_name FROM forum_group_list");
if (!$res) {
	echo db_error();
	db_rollback();
	exit();
}

for ($i=0; $i<db_numrows($res); $i++) {

	$sql="UPDATE forum_group_list 
		SET forum_name='". ereg_replace('[^_\.0-9a-z-]','-', strtolower(db_result($res,$i,'forum_name')) )."' 
		WHERE group_forum_id='".db_result($res,$i,'group_forum_id')."'";
	$res2=db_query($sql);
	if (!$res2) {
		echo db_error();
		db_rollback();
		exit();
	}
}

//
//	Long-standing oddity in GForge - 
//	forums were ZERO-pen Discussion, not Oh-pen Discussion
//
$res = db_query("UPDATE forum_group_list SET forum_name='open-discussion' 
	WHERE forum_name='0pen-discussion'");

if (!$res) {
	echo db_error();
	db_rollback();
	exit();
}

db_commit();
echo "SUCCESS\n";
?>
