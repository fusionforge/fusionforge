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

db_begin();

/*
 * Line format:
 * login:email:fname:lname:password
 * password is cleartext
*/

$f = fopen ('users.txt', 'r') ;
while (! feof ($f)) {
        $l = fgets ($f, 1024);
	$array = explode (':', $l, 5) ;
	$login = $array[0] ;
	$email = $array[1] ;
	$fname = $array[2] ;
	$lname = $array[3] ;
	$password = $array[4] ;

	$u = new GFUser () ;

	$r = $user->create($login,$fname,$lname,$password,$password,$email,
			   1, 0, 1, 'UTC', '', '', 1);

	if ($r) {
		print "Error: ". $u->getErrorMessage() . "\n" ;
		exit (1) ;
	}
}
fclose ($f);

db_rollback() ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
