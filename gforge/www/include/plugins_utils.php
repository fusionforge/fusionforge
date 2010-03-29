<?php
/**
 * Portions Copyright 2010 (c) Mélanie Le Bail
 */



 
$GLOBALS['mailman_bin_dir'] =  $GLOBALS['sys_path_to_mailman'].'/bin';
$GLOBALS['mailman_list_dir'] = '/var/lib/mailman/lists';
global $html;
require_once 'common/include/Plugin.class.php';

function isLogged(){
	
	return session_loggedin();
}

function htmlRedirect($url) {
	session_redirect('plugins/mailman/'.$url);
}
function htmlIframe($url,$poub) {
	echo ('<iframe src= "'.$url.'" width=100% height=500px></iframe>');
}


function helpButton($help) {
	
}
function getIcon() {
	echo html_image("ic/mail16b.png","20","20",array("border"=>"0"));
}
?>
