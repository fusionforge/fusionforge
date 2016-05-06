#! /usr/bin/php -f
<?php
/**
 * FusionForge source control management
 *
 * Copyright 2016, Roland Mas
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

require (dirname(__FILE__).'/../common/include/env.inc.php');
require_once $gfcommon.'include/pre.php';

$known_configs = array(
	'simple' => array(
		'clone' => 'simple',
		'queries' => array()
		),
	);

$langs = array(
	'danish',    
	'dutch',     
	'english',   
	'finnish',   
	'french',    
	'german',    
	'hungarian', 
	'italian',   
	'norwegian', 
	'portuguese',
	'romanian',  
	'russian',   
	'spanish',   
	'swedish',   
	'turkish',
	);

foreach ($langs as $l) {
	$known_configs[$l] = array(
		'clone' => $l,
		'queries' => array(
			'ALTER TEXT SEARCH CONFIGURATION fusionforge ALTER MAPPING FOR hword, hword_part, word WITH unaccent, '.$l.'_stem',
			)
		);
}

function usage() {
	global $known_configs;
	$l = array_keys($known_configs);
	sort($l);
	echo "Usage: .../configure-fti-search.php <configuration>
Currently implemented configurations: ".implode(' ',$l)."\n";
	exit(1);
}

if (count($argv) != 2) {
	usage();
}
$chosen = $argv[1];
if (!array_key_exists($chosen,$known_configs)) {
	usage();
}
$config = $known_configs[$chosen];

function query_and_exit_if_error($q,$p=array()) {
	$res = db_query_params($q,$p);
	if (!$res) {
		db_rollback();
		print db_error();
		exit(1);
	}
}

db_begin();
query_and_exit_if_error('DROP TEXT SEARCH CONFIGURATION fusionforge');
query_and_exit_if_error('CREATE TEXT SEARCH CONFIGURATION fusionforge ( COPY = '.$config['clone'].' )');
foreach ($config['queries'] as $q) {
	query_and_exit_if_error ($q);
}
query_and_exit_if_error('SELECT to_tsvector($1,$2)',array('fusionforge','Hôtels camping forge Iıİi'));
query_and_exit_if_error("SELECT rebuild_fti_indices()");
db_commit();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
