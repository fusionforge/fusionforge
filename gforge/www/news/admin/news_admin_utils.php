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


function show_news_approve_form($sql_pending, $sql_rejected, $sql_approved) {

        /*
       		Show list of waiting news items
       	*/

       	// function to show single news item
       	// factored out because called 3 time below
       	function show_news_item($result,$i,$approved,$selectable) {

	        global $PHP_SELF,$HTML;

		echo '<tr '. $HTML->boxGetAltRowStyle($i) . '><td>';
       		if ($selectable) {
       			echo '<input type="checkbox" '
       			     .'name="news_id[]" value="'
       			     .db_result($result, $i, 'id').'" />';
       		}
       		echo date('Y-m-d', db_result($result,$i,'date')).'</td>
       		<td>';
       		echo '
       		<a href="'.$PHP_SELF.'?approve=1&amp;id='.db_result($result,$i,'id').'">'.db_result($result,$i,'summary').'</a>
       		</td>

       		<td>
       		<a href="/projects/'
       		.db_result($result,$i,'unix_group_name').'/">'
       		.db_result($result,$i,'group_name')
       		.' ('.db_result($result,$i,'unix_group_name').')'
       		.'</a>
       		</td>
       		</tr>'
       		;
       	}

       	$title_arr=array();
       	$title_arr[]='Date';
       	$title_arr[]='Title';
       	$title_arr[]='Project';

       	$result=db_query($sql_pending);
       	$rows=db_numrows($result);

       	echo '<form action="'. $PHP_SELF .'" method="post">';
       	echo '<input type="hidden" name="mass_reject" value="1" />';
       	echo '<input type="hidden" name="post_changes" value="y" />';

       	if ($rows < 1) {
       		echo '
       			<h4>No Queued Items Found</h4>';
       	} else {
       		echo '<h4>These items need to be approved (total: '.$rows.')</h4>';
       		echo $GLOBALS['HTML']->listTableTop($title_arr);
       		for ($i=0; $i<$rows; $i++) {
       			show_news_item($result,$i,false,true);
       		}
       		echo $GLOBALS['HTML']->listTableBottom();
       		echo '<br /><input type="submit" name="submit" value="Reject Selected" />';
       	}
       	echo '</form>';

       	/*
       		Show list of rejected news items for this week
       	*/

       	$result=db_query($sql_rejected);
       	$rows=db_numrows($result);
       	if ($rows < 1) {
       		echo '
       			<h4>No rejected items found for this week</h4>';
       	} else {
       		echo '<h4>These items were rejected this past week (total: '.$rows.')</h4>';
       		echo $GLOBALS['HTML']->listTableTop($title_arr);
       		for ($i=0; $i<$rows; $i++) {
       			show_news_item($result,$i,false,false);
       		}
       		echo $GLOBALS['HTML']->listTableBottom();
       	}

       	/*
       		Show list of approved news items for this week
       	*/

       	$result=db_query($sql_approved);
       	$rows=db_numrows($result);
       	if ($rows < 1) {
       		echo '
       			<h4>No approved items found for this week</h4>';
       	} else {
       		echo '<h4>These items were approved this past week (total: '.$rows.')</h4>';
       		echo $GLOBALS['HTML']->listTableTop($title_arr);
       		for ($i=0; $i<$rows; $i++) {
       			show_news_item($result,$i,true,false);
       		}
       		echo $GLOBALS['HTML']->listTableBottom();
       	}

}

?>
