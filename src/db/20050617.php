#! /usr/bin/php
<?php
/**
 * Extra field alias - Create aliases for the extra fields
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

// these names can't be assigned to an extra field's alias because they are already
// being used by the CLI
$reserved_alias = array(
	"project",
	"type",
	"priority",
	"assigned_to",
	"summary",
	"details"
);

// First of all, try to create the "alias" field if it doesn't exist
$res = db_query_params ('SELECT alias FROM artifact_extra_field_list',
			array()) ;

if (!$res) {		// error, the field doesn't exist
	$res = db_query_params ('ALTER TABLE artifact_extra_field_list ADD COLUMN alias TEXT',
			array()) ;

	if (!$res) {
		echo db_error();
		exit(1);
	}
} 

// Now fill all the data
db_query_params ('BEGIN WORK',
			array()) ;


$res = db_query_params ('SELECT * FROM artifact_extra_field_list',
			array()) ;

if (!$res) {
	echo db_error();
	exit(2);
}

while ($row = db_fetch_array($res)) {
	$name = $row["field_name"];
	
	// for some weird reason the alias was already set... don't try to change it
	if (array_key_exists("alias", $row) && !empty($row["alias"])) {
		continue;
	}
	
	// Convert the original name to a valid alias (i.e., if the extra field is 
	// called "Quality test", make an alias called "quality_test").
	// The alias can be seen as a "unix name" for this field
	$alias = preg_replace("/ /", "_", $name);
	$alias = preg_replace("/[^[:alpha:]_]/", "", $alias);
	$alias = strtolower($alias);
	
	// no alias is suitable... do nothing
	if (strlen($alias) == 0) continue;
	
	// alias is reserved?
	if (in_array($alias, $reserved_alias)) {
		// prepend "extra_" to the alias (indicates it is an extra field)
		$alias = "extra_".$alias;
	}
	
	// check for conflicting names
	$conflict = false;
	$count = 1;
	do {
		$previous_def = db_query_params ('SELECT * FROM artifact_extra_field_list WHERE group_artifact_id=$1 AND LOWER(alias)=$2 AND extra_field_id <> $3',
						 array ($row["group_artifact_id"],
							$alias,
							$row["extra_field_id"])) ;
		if (db_numrows($previous_def) > 0) {	// alias exists...
			$conflict = true;
			$alias = $alias.$count;		// do something like "alias1"
			$count++;
		} else {
			$conflict = false;			// alias doesn't exists... we can use it
		}
	} while ($conflict);

	// at this point we can safely insert the alias
	$update = db_query_params ('UPDATE artifact_extra_field_list SET alias=$1 WHERE extra_field_id=$2',
				   array ($alias,
					  $row["extra_field_id"])) ;
	if (!$update) {
		echo db_error();
		exit(3);
	}	
}
db_commit ();

echo "SUCCESS\n";
exit(0);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
