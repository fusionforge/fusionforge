<?php

require_once ('/usr/lib/sourceforge/plugins/helloworld/include/HelloWorldPlugin.class') ;

$HelloWorldPluginObject = new HelloWorldPlugin () ;

$pm = plugin_manager_get_object() ;

$pm->RegisterPlugin ("helloworld", $HelloWorldPluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
