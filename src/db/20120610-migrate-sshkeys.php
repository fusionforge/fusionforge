<?php
/**
 * ssh keys backend migration
 *
 * Copyright 2012, Franck Villaume - TrivialDev
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

require_once dirname(__FILE__).'/../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/User.class.php';

ini_set('memory_limit', -1);
ini_set('max_execution_time', 0);

$res = db_query_params('SELECT authorized_keys, user_id FROM users WHERE authorized_keys != $1', array(''));
if (!$res) {
	echo 'UPGRADE ERROR: '.db_error();
	exit(1);
}

db_begin();
while($row = db_fetch_array($res)) {
	$sshKeys = explode('###', $row['authorized_keys']);
	foreach($sshKeys as $key) {
		$tempfile = tempnam("/tmp", "migauthkey");
		$ft = fopen($tempfile, 'w');
		fwrite($ft, $key);
		fclose($ft);
		$returnExec = array();
		exec("/usr/bin/ssh-keygen -lf ".$tempfile, $returnExec);
		unlink($tempfile);
		$returnExecExploded = explode(' ', $returnExec[0]);
		$fingerprint = $returnExecExploded[1];
		$now = time();
		$explodedKey = explode(' ', $key);
		$res_insert = db_query_params('insert into sshkeys (userid, fingerprint, upload, sshkey, name, algorithm)
							values ($1, $2, $3, $4, $5, $6)',
					array($row['user_id'], $fingerprint, $now, $key, $explodedKey[2], $explodedKey[0]));
		if (!$res_insert) {
			echo 'UPGRADE ERROR: '.db_error();
			db_rollback();
			exit(1);
		}
	}
	db_query_params('update users set authorized_keys = $1 where user_id = $2',
				array('', $row['user_id']));
}
db_commit();
echo "SUCCESS\n";
exit(0);
?>
