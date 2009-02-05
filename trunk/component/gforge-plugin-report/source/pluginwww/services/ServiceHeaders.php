<?php
// Can't user pre.php because there is no logged user by cookie
require('common/include/constants.php');
require('local.inc');
require_once('common/include/database.php');

db_connect();

if (!$conn) {
	print "$sys_name Could Not Connect to Database: ".db_error();
	exit;
}

require_once('plugins/report/include/libs/nusoap.php');
require_once("plugins/report/config.php");
require_once('plugins/report/include/facade/GroupFacade.php');
require_once('plugins/report/include/facade/TransactionFacade.php');

ini_set('memory_limit', '256M');
?>