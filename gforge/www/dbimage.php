<?php
/**
 * Fetch a multimedia data from database
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

require_once('env.inc.php');
require_once('squal_pre.php');

$id = getStringFromRequest('id');

$res=db_query("SELECT * FROM db_images WHERE id='$id'");

$filename=db_result($res,0,'filename');
$type=db_result($res,0,'filetype');
$data=base64_decode(db_result($res,0,'bin_data'));

Header('Content-disposition: filename="'.str_replace('"', '', $filename).'"');
Header("Content-type: $type");
echo $data;

?>
