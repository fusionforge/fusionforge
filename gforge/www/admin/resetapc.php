<?php

require_once('pre.php');
require_once('www/admin/admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

site_admin_header(array('title'=>"Site Admin"));

echo '<P>'.$sys_name.'<P>';

apc_reset_cache();

site_admin_footer(array());

?>
