#! /usr/bin/php -f
<?php
/**
 * Copyright 2009, Roland Mas
 * Copyright 2017, Franck Villaume
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
/*
 * Line format:
 * unixname:fullname:description:username
 * username is login of admin user
 * Beware of colons in text fields (fullname, description)!
*/
require (dirname (__FILE__).'/../common/include/env.inc.php');
require_once $gfcommon.'include/pre.php';

if (count($argv) != 2) {
	echo "Usage: .../inject-groups.php groups.txt\n";
	exit(1);
}

if (!is_file('groups.txt')) {
	echo "Cannot open groups.txt\n";
	exit(1);
}

$f = fopen('groups.txt', 'r');
db_begin();

while (! feof ($f)) {
	$l = trim (fgets ($f, 1024));
	if ($l == "") { continue ; }
	$array = explode(':', $l);
	$unixname = $array[0];
	$fullname = $array[1];
	$description = $array[2];
	$username = $array[3];

	$u = user_get_object_by_name($username);
	if (!$u) {
		print "Error: invalid user\n";
		db_rollback();
		exit(1);
	}

	$g = new Group();
	$r = $g->create($u, $fullname, $unixname, $description, 'Project injected into the database by inject-groups.php', 'shell', 'scm', false);

	if (!$r) {
		print "Error: ".$g->getErrorMessage()."\n";
		db_rollback();
		exit(1);
	}

	$admin = user_get_object_by_name('admin');
	session_set_new($admin->getID());
	$r = $g->approve($admin);
	if (!$r) {
		print "Error: ".$g->getErrorMessage()."\n";
		db_rollback();
		exit(1);
	}
}
fclose ($f);

// If everything went well so far, we can commit
db_commit();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
