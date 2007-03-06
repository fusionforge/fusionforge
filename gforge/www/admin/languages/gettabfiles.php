<?php
/**
  *
  * @version   $Id$
  *
  */


require_once('../../env.inc.php');
require_once('pre.php');
require_once('common/include/account.php');
require_once('www/admin/admin_utils.php');
require_once('www/include/BaseLanguage.class');

session_require(array('group'=>'1','admin_flags'=>'A'));

$lang = getStringFromRequest('lang');

header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment; filename=$lang.tab");

$result=db_query("SELECT * from tmp_lang WHERE language_id='".$lang."' AND tmpid!='-1' AND pagename='#' ORDER BY seq");
for ($i=0; $i<db_numrows($result) ; $i++) {
	$tstring=stripslashes(util_unconvert_htmlspecialchars(db_result($result, $i, 'tstring')));
	echo $tstring;
}
$result=db_query("SELECT * from tmp_lang WHERE language_id='".$lang."' AND tmpid!='-1' AND pagename!='#' ORDER BY pagename,category");
for ($i=0; $i<db_numrows($result) ; $i++) {
	$pagename=db_result($result, $i, 'pagename');
	$category=db_result($result, $i, 'category');
	$tstring=stripslashes(util_unconvert_htmlspecialchars(db_result($result, $i, 'tstring')));
	echo "$pagename	$category	$tstring";
}

?>
