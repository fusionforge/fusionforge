<?php

require_once (forge_get_config('plugins_path').'/sysauthldap/common/SysAuthLDAPPlugin.class.php') ;

$SysAuthLDAPPluginObject = new SysAuthLDAPPlugin ;

register_plugin ($SysAuthLDAPPluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
