<?php
/**
  *
  * SourceForge News Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/news/news_utils.php');
require_once('common/forum/Forum.class');

news_header(array('title'=>'News','pagename'=>'news','sectionvals'=>array(group_getname($group_id))));

echo $Language->getText('news', 'choose');

/*
	Put the result set (list of forums for this group) into a column with folders
*/
if ($group_id && ($group_id != $sys_news_group)) {
	$sql="SELECT * FROM news_bytes WHERE group_id='$group_id' AND is_approved <> '4' ORDER BY post_date DESC";
} else {
	$sql="SELECT * FROM news_bytes WHERE is_approved='1' ORDER BY post_date DESC";
}

if (!$limit || $limit>50) $limit=50;
$result=db_query($sql,$limit+1,$offset);
$rows=db_numrows($result);
$more=0;
if ($rows>$limit) {
	$rows=$limit;
        $more=1;
}

if ($rows < 1) {
	if ($group_id) {
		echo '<h2>'.$Language->getText('news', 'nonewsfor', group_getname($group_id)).'</h2>';
	} else {
		echo '<h2>'.$Language->getText('news', 'nonews').'</h2>';
	}
	echo '
		<p>' . $Language->getText('news', 'noitems') . '</p>';;
	echo db_error();
} else {
	echo '<table width="100%" border="0">
		<tr><td valign="top">';

	for ($j = 0; $j < $rows; $j++) { 
		echo '
		<a href="/forum/forum.php?forum_id='.db_result($result, $j, 'forum_id').'">'.
			html_image("ic/cfolder15.png","15","13",array("border"=>"0")) . ' &nbsp;'.
			stripslashes(db_result($result, $j, 'summary')).'</a> ';
		echo '
		<br />';
	}

        if ($more) {
        	echo '<br /><a href="'
                     .'?group_id='.$group_id.'&amp;limit='.$limit
                     .'&amp;offset='. (string)($offset+$limit) .'">[Older headlines]</a>';
        }

        echo '
	</td></tr></table>';
}

news_footer(array());

?>
