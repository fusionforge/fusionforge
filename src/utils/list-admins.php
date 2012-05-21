#!/usr/bin/php -f
<?php
require "/usr/share/gforge/common/include/env.inc.php";
require_once $gfcommon."include/pre.php";
$admins = RBACEngine::getInstance()->getUsersByAllowedAction("forge_admin", -1);
$anames = array_map(create_function("\$x", "return \$x->getUnixName();"), $admins);
sort($anames); echo join(" ", $anames) . "\n";
