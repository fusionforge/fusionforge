<?php

require_once ('/usr/lib/gforge/plugins/ldapextauth/include/LdapExtAuthPlugin.class') ;

$LdapExtAuthPluginObject = new LdapExtAuthPlugin ;

register_plugin ($LdapExtAuthPluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
