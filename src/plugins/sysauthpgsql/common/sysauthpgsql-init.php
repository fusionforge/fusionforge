<?php

require_once (forge_get_config('plugins_path').'/sysauthpgsql/common/SysAuthPGSQLPlugin.class.php') ;

$SysAuthPGSQLPluginObject = new SysAuthPGSQLPlugin ;

register_plugin ($SysAuthPGSQLPluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
