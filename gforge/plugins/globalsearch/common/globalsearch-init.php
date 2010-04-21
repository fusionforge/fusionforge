<?php

require_once (forge_get_config('plugins_path').'/globalsearch/common/GlobalsearchPlugin.class.php') ;

$globalSearchPluginObject = new globalSearchPlugin ;

register_plugin ($globalSearchPluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
