#!/usr/local/bin/php
<?php

require ('squal_pre.php');

/*if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
            exit_permission_denied();
}*/

$rel = db_query("vacuum analyze;");
echo "Done: ".db_error();

?>
