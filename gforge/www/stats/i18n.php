<?php
/**
  *
  * SourceForge Sitewide Statistics
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('site_stats_utils.php');

// require you to be a member of the sfstats group
session_require( array('group'=>$sys_stats_group) );

$HTML->header(array($Language->getText('stats_i18n','title',array($GLOBALS['sys_name']))));

//
// BEGIN PAGE CONTENT CODE
//

echo $GLOBALS['HTML']->listTableTop(array($Language->getText('stats_i18n','language')."",$Language->getText('stats_i18n','users')."","%"));
echo "<h1>".$Language->getText('stats_i18n','language_distribution',array($GLOBALS['sys_name']))." </h1>";

$sql='
SELECT count(user_name) AS total
FROM users
';
$total=db_result(db_query($sql),0,'total');

$sql='
SELECT supported_languages.name AS lang,count(user_name) AS cnt
FROM supported_languages LEFT JOIN users ON language_id=users.language
GROUP BY language_id,name
ORDER BY cnt DESC
';
$res=db_query($sql);
$non_english=0;
$i=0;
while ($lang_stat = db_fetch_array($res)) {
	echo '<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'><td>'.$lang_stat['lang'].'</td>'.
        '<td align="right">'.$lang_stat['cnt'].' </td>'.
        '<td align="right">'.sprintf("%.2f",$lang_stat['cnt']*100/$total)." </td></tr>\n";
        if ($lang_stat['lang']!='English') $non_english+=$lang_stat['cnt'];
}

echo '<tr><td><strong>'.$Language->getText('stats_i18n','total_non_english').'</strong></td>'.
'<td align="right"><strong>'.$non_english.' </strong></td>'.
'<td align="right"><strong>'.sprintf("%.2f",$non_english*100/$total).' </strong></td></tr>';

echo $GLOBALS['HTML']->listTableBottom();

echo "<p>".$Language->getText('stats_i18n','language_text');

//
// END PAGE CONTENT CODE
//

$HTML->footer( array() );
?>
