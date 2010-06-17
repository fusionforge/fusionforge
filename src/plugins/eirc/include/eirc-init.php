<?php

global $gfplugins;
require_once $gfplugins.'eirc/include/EIRCPlugin.class.php' ;

$EIRCPluginObject = new EIRCPlugin ;

register_plugin ($EIRCPluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
