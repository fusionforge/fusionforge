#! /usr/bin/php -f
<?php
/**
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

require (dirname (__FILE__).'/../www/env.inc.php');
require_once $gfcommon.'include/pre.php';

db_begin ();

/*
 * Line format:
 * unixname:fullname:description:ispublic:username
 * username is login of admin user
 * ispublic is 0/1
 * Beware of colons in text fields (fullname, description)!
*/

$f = fopen ('groups.txt', 'r') ;
while (! feof ($f)) {
        $l = trim (fgets ($f, 1024)) ;
	if ($l == "") { continue ; } ;
	$array = explode (':', $l) ;
	$unixname = $array[0] ;
	$fullname = $array[1] ;
	$description = $array[2] ;
	$is_public = $array[3] ;
	$username = $array[4] ;

	$u = user_get_object_by_name($username) ;
	if (! $u) {
		print "Error: invalid user\n" ;
		db_rollback () ;
		exit (1) ;
	}
	
	$g = new Group () ;
	$r = $g->create ($u, $fullname, $unixname, $description, 'Project injected into the database by inject-groups.php', 'shell', 'scm', $is_public, false) ;

	if (!$r) {
		print "Error: ". $g->getErrorMessage () . "\n" ;
		db_rollback () ;
		exit (1) ;
	}

	$admin = user_get_object_by_name ('admin') ;
	session_set_new ($admin->getID ()) ;
	$r = $g->setStatus ($admin, 'A') ;
        if (!$r) {
                print "Error: ". $g->getErrorMessage () . "\n" ;
                db_rollback () ;
                exit (1) ;
        }

}
fclose ($f);

// If everything went well so far, we can commit
db_commit () ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
