<?php

/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 */

global $sys_plugins_path;
require_once ($sys_plugins_path.'/novamonitor/include/NovaMonitorPlugin.class.php') ;

$NovaMonitorPlugin = new NovaMonitorPlugin() ;

register_plugin ($NovaMonitorPlugin) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
