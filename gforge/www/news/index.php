<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require('pre.php');
require('../forum/forum_utils.php');

news_header(array('title'=>'News'));

echo '<H3>News</H3>
	<P>Choose a News item and you can browse, search, and post messages.<P>';

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
	echo '<H2>No News Found';
	if ($group_id) {
		echo ' For '.group_getname($group_id);
	}
	echo '</H2>';
	echo '
		<P>No items were found';
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
