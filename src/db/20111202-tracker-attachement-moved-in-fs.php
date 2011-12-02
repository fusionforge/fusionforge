#! /usr/bin/php
<?php
/**
 * GForge Group Role Generator
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

require_once dirname(__FILE__).'/../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'tracker/ArtifactStorage.class.php';

ini_set('memory_limit', -1);
ini_set('max_execution_time', 0);

$res = db_query_params ('SELECT id FROM artifact_file WHERE bin_data !=$1', array('')) ;
if (!$res) {
	echo 'UPGRADE ERROR: '.db_error();
	exit(1);
}

$data = forge_get_config('data_path');
if (!is_dir($data)) {
	system("mkdir -p $data");
	system("chown ".forge_get_config('apache_user').':'.forge_get_config('apache_group')." $data");
	system("chmod 0700 $data");
}

$as = new ArtifactStorage();
$tmp = tempnam('/tmp', 'tracker');

while($row = db_fetch_array($res)) {
	$res2 = db_query_params ('SELECT filesize, bin_data FROM artifact_file WHERE id=$1', 
		array($row['id'])) ;
	$row2 = db_fetch_array($res2);
	$ret = file_put_contents($tmp, base64_decode($row2['bin_data']));
	if ($ret === false) {
		echo "UPGRADE ERROR: file_put_contents($tmp) error: returned false\n";
		$as->rollback();
		exit(1);
	}
	if ($ret != $row2['filesize']) {
		echo "UPGRADE ERROR: file_put_contents($tmp): size error ($ret != ".$row2['filesize'].")\n";
		$as->rollback();
		exit(1);
	}
	$ret = $as->store($row['id'], $tmp);
	if (!$ret) {
		echo "UPGRADE ERROR: $ret: ".$as->getErrorMessage()."\n";
		$as->rollback();
		exit(1);
	}
}

$as->commit();

db_query_params ('UPDATE artifact_file SET bin_data=$1', array(''));

echo "SUCCESS\n";

?>
