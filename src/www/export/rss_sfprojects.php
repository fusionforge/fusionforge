<?php
/**
  *
  * SourceForge Exports: Export project list in RSS
  *
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'export/rss_utils.inc';

$showall = getIntFromRequest('showall', 0);
if ($showall) {
	$limit = 0 ;
} else {
	$limit = getIntFromRequest('limit', 10);
	if ($limit > 100) $limit = 100;
}

header("Content-Type: text/xml; charset=utf-8");
print '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE rss SYSTEM "http://my.netscape.com/publish/formats/rss-0.91.dtd">
<rss version="0.91">
';

$res = db_query_params ('SELECT group_id,group_name,unix_group_name,homepage,short_description
	FROM groups
	WHERE status=$1
        ORDER BY group_id DESC',
			array('A'),
			$limit);

rss_dump_project_result_set($res,forge_get_config ('forge_name').' Full Project Listing');
?>
</rss>
