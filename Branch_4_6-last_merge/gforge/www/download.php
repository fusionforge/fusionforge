<?php
/*
	Temporary redirect so we don't break existing installs/links
*/

require_once('pre.php');
Header("Location: /frs" . getStringFromServer('REQUEST_URI'));

?>
