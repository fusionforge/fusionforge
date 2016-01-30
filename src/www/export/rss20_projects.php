<?php
/**
 * FusionForge export projects list in RSS 2.0
 * Copyright Scott Grayban <sgrayban@borgnet.us>
 * http://fusionforge.org/
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'export/rss_utils.inc';

header("Content-Type: text/xml; charset=utf-8");
print '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
';

$limit = getIntFromRequest('limit', 10);

$res = db_query_params('SELECT group_id,group_name,unix_group_name,homepage,short_description,register_time FROM groups
			WHERE status = $1 AND type_id=1 AND is_template=0 AND register_time > 0
			ORDER BY register_time DESC',
			array ('A'),
			$limit);

rss_dump_project_result_set($res,forge_get_config ('forge_name')._(': ')._('New Projects Listing'));
?>
</rss>
