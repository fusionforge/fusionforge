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

	        global $PHP_SELF;

       		echo '<tr bgcolor="'.html_get_alt_row_color($i).'"><td>';
       		if ($selectable) {
       			echo '<input type="checkbox" '
       			     .'name="news_id[]" value="'
       			     .db_result($result, $i, 'id').'">';
       		}
       		echo date('Y-m-d', db_result($result,$i,'date')).'</td>
       		<td>';
       		echo '
       		<a href="'.$PHP_SELF.'?approve=1&id='.db_result($result,$i,'id').'">'.db_result($result,$i,'summary').'</A>
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

       	echo '<form ACTION="'. $PHP_SELF .'" METHOD="POST">';
       	echo '<input type="hidden" name="mass_reject" value="1">';
       	echo '<input type="hidden" name="post_changes" value="y">';

       	if ($rows < 1) {
       		echo '
       			<H4>No Queued Items Found</H4>';
       	} else {
       		echo '<H4>These items need to be approved (total: '.$rows.')</H4>';
       		echo html_build_list_table_top($title_arr);
       		for ($i=0; $i<$rows; $i++) {
       			show_news_item($result,$i,false,true);
       		}
       		echo '</table>';
       		echo '<br><input type="submit" name="submit" value="Reject Selected">';
       	}
       	echo '</form>';

       	/*
       		Show list of rejected news items for this week
       	*/

       	$result=db_query($sql_rejected);
       	$rows=db_numrows($result);
       	if ($rows < 1) {
       		echo '
       			<H4>No rejected items found for this week</H4>';
       	} else {
       		echo '<H4>These items were rejected this past week (total: '.$rows.')</H4>';
       		echo html_build_list_table_top($title_arr);
       		for ($i=0; $i<$rows; $i++) {
       			show_news_item($result,$i,false,false);
       		}
       		echo '</table>';
       	}

       	/*
       		Show list of approved news items for this week
       	*/

       	$result=db_query($sql_approved);
       	$rows=db_numrows($result);
       	if ($rows < 1) {
       		echo '
       			<H4>No approved items found for this week</H4>';
       	} else {
       		echo '<H4>These items were approved this past week (total: '.$rows.')</H4>';
       		echo html_build_list_table_top($title_arr);
       		for ($i=0; $i<$rows; $i++) {
       			show_news_item($result,$i,true,false);
       		}
       		echo '</table>';
       	}

}

?>
