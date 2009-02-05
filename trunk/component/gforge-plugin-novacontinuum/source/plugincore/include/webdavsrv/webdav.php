<?
// Can't user pre.php because there is a conflict with the System class which is also declare by the webDav API
require('common/include/constants.php');
require('local.inc');
require_once('common/include/database.php');

db_connect();

if (!$conn) {
	print "$sys_name Could Not Connect to Database: ".db_error();
	exit;
}
		
		
require_once(dirname(__FILE__).'/../services/ServicesManager.php');
$serviceManager =& ServicesManager::getInstance();

if($serviceManager->authDavUserWrite($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'], $_SERVER['PATH_INFO'])){
	set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/inc/');
	ini_set("default_charset", "UTF-8");
	require_once "HTTP/WebDAV/Server/Filesystem.php";
	$server = new HTTP_WebDAV_Server_Filesystem();
	$server->db_host = 'localhost';
	$server->db_name = 'gforge_plugin_novacontinuum_webdav';
	$server->db_user = 'novacontinuum';
	$server->db_passwd = 'SQjS5UO8';
	$server->ServeRequest($serviceManager->getContinuumDataDir());
}else{
	$serviceManager->unauthDavUser();
}
?>