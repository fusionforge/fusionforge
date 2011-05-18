#! /usr/bin/php
<?php
/**
 * Copyright 2003 (c) GFDL
 *
 * This file is part of FMS.
 *
 * FMS is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * -------------- Gforge team comment ------------
 * This file was proposed by Brett N DiFrischia
 * with the following description:
 * The GFDL uses many large files that could not be uploaded to our GForge site.
 * At this time, such files are updated manually. This cronjob updates file sizes
 * for all files that have the incorrect size. Note that this cronjob checks all files
 * instead of recently updated ones. This could put a major damper on large systems.
 * Updates are only performed for incorrect file sizes.
 *
 * Thanks
 */

require_once $gfcommon.'include/pre.php';

db_begin();

$fms_filesize_res = db_query_params ('SELECT frs_file.filename,frs_file.file_id,
             groups.unix_group_name,frs_file.file_size
             FROM frs_package,frs_release,frs_file,groups
             WHERE frs_release.release_id=frs_file.release_id
             AND groups.group_id=frs_package.group_id
             AND frs_release.package_id=frs_package.package_id
             AND frs_file.post_date > $1',
				     array (time() - (7 * 24 * 60 * 60))) ;
echo db_error();

while ( $fms_filesize_row = db_fetch_array( $fms_filesize_res ) ) {

  $fms_file_path = forge_get_config('upload_dir') . '/' .
    $fms_filesize_row['unix_group_name'] . '/' .
    $fms_filesize_row['filename'];

  $fms_curr_size = filesize( $fms_file_path );

  if ( $fms_curr_size != $fms_filesize_row['file_size'] ) {
db_query_params ('UPDATE frs_file SET file_size=$1 WHERE file_id=$2',
		 array ($fms_curr_size,
			$fms_filesize_row['file_id']));
    echo db_error();
  }

}

db_commit();
echo db_error();

?>
