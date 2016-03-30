#!/usr/bin/php -f
<?php
/**
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
require_once $gfcommon."include/pre.php";
$f = array(
	'groups' => 'short_description',
	'artifact' => 'summary',
	'artifact_message' => 'body',
	'doc_data' => 'title',
	'forum' => 'subject',
	'frs_file' => 'filename',
	'frs_release' => 'name',
	'news_bytes' => 'summary',
	'project_task' => 'summary',
	'project_messages' => 'body',
	'skills_data' => 'keywords',
	'users' => 'realname',
);

foreach ($f as $table => $column) {
	echo "Regenerating FTI indices for table $table.\n";
	$res = db_query_params ("UPDATE $table SET $column=$column");
}
