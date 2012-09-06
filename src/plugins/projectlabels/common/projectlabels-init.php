<?php

require_once (forge_get_config('plugins_path').'/projectlabels/common/ProjectLabelsPlugin.class.php') ;

$ProjectLabelsPluginObject = new ProjectLabelsPlugin ;

register_plugin ($ProjectLabelsPluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
