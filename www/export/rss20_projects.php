<?php
// export projects list in RSS 2.0
// Author: Scott Grayban <sgrayban@borgnet.us>
//

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'export/rss_utils.inc';

header("Content-Type: text/xml; charset=utf-8");
print '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
';

$limit = getIntFromRequest('limit', 10);

$res = db_query_params ('SELECT group_id,group_name,unix_group_name,homepage,short_description,register_time FROM groups WHERE status=$1 ORDER BY group_id',
			array ('A'),
			$limit);

rss_dump_project_result_set($res,forge_get_config ('forge_name').' Full Project Listing');
?>
</rss>
