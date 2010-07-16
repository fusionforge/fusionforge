<?php
/*
	Temporary redirect so we don't break existing installs/links
*/

require_once('env.inc.php');
require_once $gfcommon.'include/pre.php';
Header("Location: /frs" . getStringFromServer('REQUEST_URI'));

?>
