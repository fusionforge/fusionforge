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
require_once('www/forum/forum_utils.php');

news_header(array('title'=>'News','pagename'=>'news','sectionvals'=>array(group_getname($group_id))));

echo $Language->getText('news', 'choose');

/*
	Put the result set (list of forums for this group) into a column with folders
*/
if ($group_id && ($group_id != $sys_news_group)) {
	$sql="SELECT * FROM news_bytes WHERE group_id='$group_id' AND is_approved <> '4' ORDER BY date DESC";
} else {
	$sql="SELECT * FROM news_bytes WHERE is_approved='1' ORDER BY date DESC";
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
		echo '<H2>'.$Language->getText('news', 'nonewsfor', group_getname($group_id)).'</H2>';
	} else {
		echo '<H2>'.$Language->getText('news', 'nonews').'</H2>';
	}
	echo '
		<P>' . $Language->getText('news', 'noitems');;
	echo db_error();
} else {
	echo '<table WIDTH="100%" border=0>
		<TR><TD VALIGN="TOP">'; 

	for ($j = 0; $j < $rows; $j++) { 
		echo '
		<A HREF="/forum/forum.php?forum_id='.db_result($result, $j, 'forum_id').'">'.
			html_image("images/ic/cfolder15.png","15","13",array("BORDER"=>"0")) . ' &nbsp;'.
			stripslashes(db_result($result, $j, 'summary')).'</A> ';
		echo '
		<BR>';
	}

        if ($more) {
        	echo '<br><a href="'
                     .'?group_id='.$group_id.'&limit='.$limit
                     .'&offset='. (string)($offset+$limit) .'">[Older headlines]</a>';
        }

        echo '
	</TD></TR></TABLE>';
}

news_footer(array());

?>
