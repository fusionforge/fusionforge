<?php

require_once ('/usr/lib/sourceforge/plugins/helloworld/include/HelloWorldPlugin.class') ;

$HelloWorldPluginObject = new HelloWorldPlugin () ;

plugin_manager_get_object ()->RegisterPlugin ("helloworld", $HelloWorldPluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>