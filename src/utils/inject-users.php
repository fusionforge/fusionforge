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
 * login:email:fname:lname:password
 * password is cleartext
 * login might be optional if sys_require_unique_email is true
*/

$f = fopen ('users.txt', 'r') ;
while (! feof ($f)) {
        $l = trim (fgets ($f, 1024)) ;
	if ($l == "") { continue ; } ;
	$array = explode (':', $l, 5) ;
	$login = $array[0] ;
	$email = $array[1] ;
	$fname = $array[2] ;
	$lname = $array[3] ;
	$password = $array[4] ;

	$u = new GFUser () ;

	$r = $u->create ($login, $fname, $lname, $password, $password, $email,
			 1, 0, 1, 'UTC', '', '', 1,
			 'shell', '', '', '', '', '', 'US', false);

	if (!$r) {
		print "Error: ". $u->getErrorMessage () . "\n" ;
		db_rollback () ;
		exit (1) ;
	}

	$u->setStatus ('A') ;
}
fclose ($f);

// If everything went well so far, we can commit
db_commit () ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
