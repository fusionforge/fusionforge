#! /usr/bin/php5 -f
<?php
/**
 * Copyright 2009, Roland Mas
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

require (dirname (__FILE__).'/../www/env.inc.php');
require $gfwww.'include/squal_pre.php';

db_begin ();

/*
 * Line format:
 * unixname:fullname:description:license:licenseother:ispublic:username
 * username is login of admin user
 * ispublic is 0/1
 * license to pick from "SELECT * from licenses":
 101 | GNU General Public License (GPL)
 102 | GNU Library Public License (LGPL)
 103 | BSD License
 104 | MIT License
 105 | Artistic License
 [...]
 124 | Public Domain
 125 | Website Only
 126 | Other/Proprietary License
 * Pick 126 (and use the licenseother field) for a non-standard license
 * Beware of colons in text fields (fullname, description, licenseother)!
*/

$f = fopen ('groups.txt', 'r') ;
while (! feof ($f)) {
        $l = trim (fgets ($f, 1024)) ;
	if ($l == "") { continue ; } ;
	$array = explode (':', $l) ;
	$unixname = $array[0] ;
	$fullname = $array[1] ;
	$description = $array[2] ;
	$license = $array[3] ;
	$licenseother = $array[4] ;
	$is_public = $array[5] ;
	$username = $array[6] ;

	$u = user_get_object_by_name($username) ;
	if (! $u) {
		print "Error: invalid user\n" ;
		db_rollback () ;
		exit (1) ;
	}
	
	$g = new Group () ;
	$r = $g->create ($u, $fullname, $unixname, $description, $license, $licenseother, '', 'shell', 'scm', $is_public, false) ;

	if (!$r) {
		print "Error: ". $g->getErrorMessage () . "\n" ;
		db_rollback () ;
		exit (1) ;
	}

	$g->setStatus ('A') ;
}
fclose ($f);

// If everything went well so far, we can commit
db_commit () ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
