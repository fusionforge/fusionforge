<?php
// export projects list in RSS 2.0
// Author: Scott Grayban <sgrayban@borgnet.us>
//

include "../env.inc.php";
include "pre.php";
include "rss_utils.inc";
header("Content-Type: text/xml");
print '<?xml version="1.0"?>
<rss version="2.0">
';
$res = db_query(
	 'SELECT group_id,group_name,unix_group_name,homepage,short_description,register_time '
	.'FROM groups '
	.'WHERE is_public=1 AND status=\'A\' '
        .'ORDER BY group_id',$limit);

rss20_dump_project_result_set($res,$GLOBALS[sys_default_name].' Full Project Listing');
?>
</rss>
