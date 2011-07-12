<?php

//$default_svn_server = forge_get_config('web_host') ;
//$default_svn_server = "svn." . forge_get_config('web_host') ;
$default_svn_server = forge_get_config('scm_host');
$use_ssh = false;
$use_dav = true;
$use_ssl = true;
// $svn_root = forge_get_config('chroot').'/scmrepos/svn' ;

$svn_bin = "/usr/bin/svn";

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
