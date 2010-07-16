<?php
/**
  *
  * SourceForge Trove Software Map
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
if ($GLOBALS['sys_use_project_tags']) {
	header('Location: '.util_make_url('softwaremap/tag_cloud.php'));
}elseif (forge_get_config('use_trove')){
	header('Location: '.util_make_url('softwaremap/trove_list.php'));
}else{
header('Location: '.util_make_url('softwaremap/full_list.php'));
}?>
