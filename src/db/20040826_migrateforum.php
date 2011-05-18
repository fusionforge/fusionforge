#! /usr/bin/php
<?php
/**
 * GForge Forum Renamer - forum names are now unix-format for email gateway
 *
 * Copyright 2004 GForge, LLC
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';

//
//	Convert forum names to new legal syntax
//
db_begin();
$res=db_query_params ('SELECT group_forum_id,forum_name FROM forum_group_list',
			array()) ;

if (!$res) {
	echo db_error();
	db_rollback();
	exit();
}

for ($i=0; $i<db_numrows($res); $i++) {

	$res2 = db_query_params ('UPDATE forum_group_list SET forum_name=$1 WHERE group_forum_id=$2',
				 array (ereg_replace('[^_\.0-9a-z-]','-', strtolower(db_result($res,$i,'forum_name'))),
					db_result($res,$i,'group_forum_id'))) ;
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
$res = db_query_params ('UPDATE forum_group_list SET forum_name=$1 
	WHERE forum_name=$2',
			array('open-discussion',
			'0pen-discussion')) ;


if (!$res) {
	echo db_error();
	db_rollback();
	exit();
}

db_commit();
echo "SUCCESS\n";

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
