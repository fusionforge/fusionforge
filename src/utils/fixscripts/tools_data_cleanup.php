<?php

/*
 *  Due to bug in code, patch and support req close date wasn't
 *  set properly before 2000-11-16. This script is to fix it (kinda).
 *
 */

require_once $gfcommon.'include/pre.php';

if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
        exit_permission_denied();
}

echo "Patching patches<br />";

# If some patch is not opened, set its close date one month after open
$res=db_query_params ('
UPDATE patch
SET close_date=open_date+60*60*24*30
WHERE close_date=0 AND patch_status_id NOT IN (0,1)
',
			array());

if (!$res) print "error<br />";

echo "Affected rows: ",db_affected_rows($res),"<br />";




echo "Patching sup reqs<br />";

$res=db_query_params ('
UPDATE support
SET close_date=open_date+60*60*24*30
WHERE close_date=0 AND support_status_id NOT IN (0,1)
',
			array());

if (!$res) print "error<br />";

echo "Affected rows: ",db_affected_rows($res),"<br />";
