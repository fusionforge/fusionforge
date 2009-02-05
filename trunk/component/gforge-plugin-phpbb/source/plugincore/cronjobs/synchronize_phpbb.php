<?php
/*
* Plugin PhpBB
*/
#===================================
# Imports
#===================================
require_once ('squal_pre.php');
require_once ('plugins/phpbb/common/PluginPhpBB.class');
require_once ('common/include/cron_utils.php');
#===================================
# Traitement
#===================================
$out = " Synchronisation des données NovaForge vers PhpBB - " . date ("d/m/Y H:i:s") . "\n";
echo ">>>>> - $out \n";


PluginPhpBB::synchronize();

$out .= " END Synchronisation des projets NovaForge vers PhpBB - " . date ("d/m/Y H:i:s") . "\n";
echo "<<<<< out cron $out";
cron_entry(31,$out);
?>
