<?php
/**
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('common/include/account.php');
require_once('www/admin/admin_utils.php');
require_once('www/include/BaseLanguage.class');

session_require(array('group'=>'1','admin_flags'=>'A'));

//header('Content-Type: text/plain');

$result=db_query("SELECT * from tmp_lang WHERE language_id='".$lang."' AND tmpid!='-1' ORDER BY seq");
for ($i=0; $i<db_numrows($result) ; $i++) {
	$pagename=db_result($result, $i, 'pagename');
	$category=db_result($result, $i, 'category');
	$tstring=stripslashes(util_unconvert_htmlspecialchars(db_result($result, $i, 'tstring')));
	if ($pagename=='#') echo $tstring;
	else echo "$pagename	$category	$tstring";
}

?>
