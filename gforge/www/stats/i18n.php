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

$HTML->header(array('title'=>$GLOBALS['sys_name']." I18n Statistics"));

//
// BEGIN PAGE CONTENT CODE
//

echo html_build_list_table_top(array("Language","Users","%"));
echo "<h1>".$GLOBALS['sys_name']." Languages Distribution</h1>";

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
	echo '<tr bgcolor="'.html_get_alt_row_color($i++).'"><td>'.$lang_stat['lang'].'</td>'.
        '<td align="right">'.$lang_stat['cnt'].' </td>'.
        '<td align="right">'.sprintf("%.2f",$lang_stat['cnt']*100/$total)." </td></tr>\n";
        if ($lang_stat['lang']!='English') $non_english+=$lang_stat['cnt'];
}

echo '<tr><td><b>Total Non-English</b></td>'.
'<td align="right"><b>'.$non_english.' </td>'.
'<td align="right"><b>'.sprintf("%.2f",$non_english*100/$total).' </td></tr>';

echo "</table>";

echo "<p>This is a list of the preferences that users have chosen in \n".
     "their sourceforge user preferences; it \n".
     "does not include languages which are selected via cookies or \n".
     "browser preferences.</p>";

//
// END PAGE CONTENT CODE
//

$HTML->footer( array() );
?>
