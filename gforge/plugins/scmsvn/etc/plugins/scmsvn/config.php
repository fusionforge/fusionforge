<?php
 
//$default_svn_server = $GLOBALS['sys_default_domain'] ;
//$default_svn_server = "svn." . $GLOBALS['sys_default_domain'] ;
if (isset ($GLOBALS['sys_scm_host'])) {
	$default_svn_server = $GLOBALS['sys_scm_host'];
} else {
	$default_svn_server = 'scm';
}
$use_ssh = false;
$use_dav = true;
$use_ssl = true;
// $svn_root = $GLOBALS['sys_chroot'].'/scmrepos/svn' ;

$svn_bin = "/usr/bin/svn";

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
 
?>
