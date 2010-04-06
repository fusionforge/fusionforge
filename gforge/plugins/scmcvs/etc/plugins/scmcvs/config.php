<?php
 
if (isset ($GLOBALS['sys_scm_host'])) {
	$default_cvs_server = "cvs." . forge_get_config('web_host');
} else {
	$default_cvs_server = 'scm';
}
$cvs_binary_version='1.12';
$use_ssl=false;
$GLOBALS['cvs_binary_version']=$cvs_binary_version;
// $cvs_root = $GLOBALS['sys_chroot'].'/scmrepos/cvs' ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
 
?>
