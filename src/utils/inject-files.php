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
require_once $gfcommon.'frs/FRSPackage.class.php';
require_once $gfcommon.'frs/FRSRelease.class.php';
require_once $gfcommon.'frs/FRSFile.class.php';

db_begin ();

/*
 * Line format:
 * project:packagename:releasename:filepath:notes:changes:type:processor
 * type taken from SELECT * from frs_filetype:
 1000 | .deb
[...]
 5000 | Source .zip
 5010 | Source .bz2
 5020 | Source .gz
[...]
 5900 | Other Source File
[...]
 9999 | Other
 * processor taken from SELECT * from frs_processor:
 1000 | i386
 6000 | IA64
[...]
 8000 | Any
[...]
 9999 | Other
*/

$f = fopen ('files.txt', 'r') ;
while (! feof ($f)) {
        $l = trim (fgets ($f, 1024)) ;
	if ($l == "") { continue ; } ;
	$array = explode (':', $l) ;
	$projectname = $array[0] ;
	$packagename = $array[1] ;
	$releasename = $array[2] ;
	$filepath = $array[3] ;
	$notes = $array[4] ;
	$changes = $array[5] ;
	$typeid = $array[6] ;
	$processorid = $array[7] ;

	$admin = user_get_object_by_name ('admin') ;
	session_set_new ($admin->getID ()) ;

	$g = group_get_object_by_name ($projectname);
	if (! $g) {
		print "Error: invalid group\n" ;
		db_rollback () ;
		exit (1) ;
	}

	$packages = get_frs_packages ($g) ;
	$package = false ;
	if ($packages) {
		foreach ($packages as $cur) {
			if ($cur->getName () == $packagename) {
				$package = $cur ;
				break ;
			}
		}
	}
	if (!$package) {
		$package = new FRSPackage ($g) ;
		$r = $package->create ($packagename) ;
	}
	if (!$r || !$package) {
		print "Error when creating FRS package\n" ;
		db_rollback () ;
		exit (1) ;
	}

	$releases = $package->getReleases () ;
	$release = false ;
	if ($releases) {
		foreach ($releases as $cur) {
			if ($cur->getName () == $releasename) {
				$release = $cur ;
				break ;
			}
		}
	}
	if (!$release) {
		$release = new FRSRelease ($package) ;
		$r = $release->create ($releasename, $notes, $changes, false) ;
	}
	if (!$r || !$release) {
		print "Error when creating FRS release\n" ;
		db_rollback () ;
		exit (1) ;
	}

	$file = new FRSFile ($release) ;
	$pathcomponents = explode ('/', $filepath) ;
	$filename = $pathcomponents[count($pathcomponents)-1] ;
	$r = $file->create ($filename, $filepath, $typeid, $processorid) ;

	if (!$r) {
		print "Error when creating FRS file\n" ;
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
