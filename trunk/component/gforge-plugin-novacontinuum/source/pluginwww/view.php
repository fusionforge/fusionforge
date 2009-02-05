<?php
require_once ("www/env.inc.php");

require_once ("include/pre.php");
require_once ("common/novaforge/log.php");
require_once('plugins/novacontinuum/include/services/ServicesManager.php');
$serviceManager =& ServicesManager::getInstance();

ini_set("default_charset", "UTF-8");

if($serviceManager->authDavUserRead($_SERVER['PATH_INFO'])){
	$groupName = $serviceManager->extractGroupName($_SERVER['PATH_INFO']);
	$path = $serviceManager->extractFileName($_SERVER['PATH_INFO']);
	$projectRoot = $serviceManager->getContinuumDataDir().$groupName;
	
	$src = $projectRoot.$path;
	
	if(!file_exists($src)){
		log_error ("File not found :".$src, __FILE__, __FUNCTION__);
		header("HTTP/1.0 404 Not Found");
		return;
	}
	if(( substr( $src, strlen( $src ) - 4 ) == '.css' )){
		header("Content-type: text/css;");
	}
	$html = implode ('', file ($src));
	
	echo $html;
}else{
	if (strtoupper($_SERVER['HTTPS']) == 'ON') {
		header ("Location: https://".$HTTP_HOST."/");
	}else{
		header ("Location: http://".$HTTP_HOST."/");
	}
}
?>