<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

/*
   SourceForge Search Engine
 */

/*
 *   Parameters:
 *   $type ($t)  = one of 'soft'[ware],'people','forums','bugs'
 *   $words ($q) = target words to search
 *   $exact      = 1 for search ing all words (AND), 0 - for any word (OR)
 *   $rss        = 1 to export RSS
 */


require ('pre.php');

function highlight_target_words($word_array,$text) {
        if (!$text) return '&nbsp;';
        $re=implode($word_array,'|');
	return eregi_replace("($re)",'<span style="background-color:pink">\1</span>',$text);
}

function error_while_in_rss($descr) {
	header("Content-Type: text/plain");
	print '<channel></channel>';
	exit;
}

if (!$rss) {
$HTML->header(array('title'=>'Search'));

echo "<P><CENTER>";

menu_show_search_box();
}

/*
	Force them to enter at least three characters
*/
if (!$words) $words=$q;
$words = trim($words);
if ($words && (strlen($words) < 3)) {
        if ($rss) error_while_in_rss('Search must be at least three characters');
	echo "<H2>Search must be at least three characters</H2>";
	$HTML->footer(array());
	exit;
}

if (!$words) {
        if ($rss) error_while_in_rss('Search criteria are not specified');
	echo "<BR>Enter Your Search Words Above</CENTER><P>";
	$HTML->footer(array());
	exit;
}

$no_rows = 0;

if ($exact) {
	$crit='AND';
} else {
	$crit='OR';
}

if (!$offset || $offset < 0) {
	$offset = 0;
}

if (!$type_of_search) $type_of_search=$type;
if (!$type_of_search) $type_of_search=$t;
if (!$type_of_search) $type_of_search='soft';

if ($type_of_search == "soft") {
	/*
		Query to find software
	*/

	// If multiple words, separate them and put LIKE in between
	$array=explode(" ",$words);
	$words1=implode($array,"%' $crit group_name LIKE '%");
	$words2=implode($array,"%' $crit short_description LIKE '%");
	$words3=implode($array,"%' $crit unix_group_name LIKE '%");

        if (!$rss) {
		$sql = "SELECT group_name,unix_group_name,group_id,short_description "
		       ."FROM groups "
		       ."WHERE status='A' AND is_public='1' AND ((group_name LIKE '%$words1%') OR (short_description LIKE '%$words2%') OR (unix_group_name LIKE '%$words3%'))";
	} else {
        	// If it's RSS export, try to infer additional information, as
	        // shown by Freshmeat search. This means that only projects
	        // categorized under Trove will be exported - that's good, since
	        // cross-site search performed not to get junk results.
        	$sql = "SELECT group_name,unix_group_name,groups.group_id, "
	               ."short_description,license,register_time "
	               ."FROM groups "
	               ."WHERE status='A' AND is_public='1' "
	               ."AND groups.short_description<>'' "
	               ."AND ((group_name LIKE '%$words1%') OR (short_description LIKE '%$words2%') OR (unix_group_name LIKE '%$words3%'))";
	}

        if ($rss) $limit=200; else $limit=26;
	$result = db_query($sql,$limit+1,$offset);
	$rows = $rows_returned = db_numrows($result);

        /*
         *  Dump RSS rendering of search results, date registered, 
         *  include trove categories, license.
         */
        if ($rss) {
                include "../export/rss_utils.inc";
		function callback($data_row) {
		        $sql="SELECT trove_cat.fullpath "
			."FROM trove_group_link,trove_cat "
			."WHERE trove_group_link.trove_cat_root=18 " // Topic subtree
			."AND trove_group_link.trove_cat_id=trove_cat.trove_cat_id "
			."AND group_id=$data_row[group_id]";
			$result = db_query($sql);
                        $ret = ' | date registered: '.date('M jS Y',$data_row['register_time']);
                        $ret .= ' | category: '.str_replace(' ','',implode(util_result_column_to_array($result),','));
			return $ret.' | license: '.$data_row['license'];
		}
		header("Content-Type: text/plain");
        	rss_dump_project_result_set($result,
                	'SourceForge Search Results',
                        'SourceForge Search Results for "'
                        .htmlspecialchars($words).'"',
                        'callback');
                exit;
        }

        /*
         *  Else, render HTML
         */

        if (!$result || $rows < 1) {
		$no_rows = 1;
		echo "<H2>No matches found for $words</H2>";
		echo db_error();
//		echo $sql;
	} else {

		if ( $rows_returned > 25) {
			$rows = 25;
		}

		echo "<H3>Search results for $words</H3><P>\n\n";

		$title_arr = array();
		$title_arr[] = 'Group Name';
		$title_arr[] = 'Description';

		echo html_build_list_table_top($title_arr);

		echo "\n";

		for ( $i = 0; $i < $rows; $i++ ) {
			print	"<TR BGCOLOR=\"". html_get_alt_row_color($i)."\"><TD><A HREF=\"/projects/"
                                .db_result($result, $i, 'unix_group_name')."/\">"
				. html_image("images/msg.gif","10","12",array("BORDER"=>"0")) 
				. highlight_target_words($array,db_result($result, $i, 'group_name'))."</A></TD>"
				. "<TD>".highlight_target_words($array,db_result($result,$i,'short_description'))."</TD></TR>\n";
		}
		echo "</TABLE>\n";
	}

} else if ($type_of_search == "people") {
	/*
		Query to find users
	*/

	// If multiple words, separate them and put LIKE in between
	$array=explode(" ",$words);
	$words1=implode($array,"%' $crit user_name LIKE '%");
	$words2=implode($array,"%' $crit realname LIKE '%");

	$sql =	"SELECT user_name,user_id,realname "
		. "FROM users "
		. "WHERE ((user_name LIKE '%$words1%') OR (realname LIKE '%$words2%')) AND (status='A') ORDER BY user_name";
	$result = db_query($sql,26,$offset);
	$rows = $rows_returned = db_numrows($result);

	if (!$result || $rows < 1) {
		$no_rows = 1;
		echo "<H2>No matches found for $words</H2>";
		echo db_error();
//		echo $sql;
	} else {

		if ( $rows_returned > 25) {
			$rows = 25;
		}

		echo "<H3>Search results for $words</H3><P>\n\n";

		$title_arr = array();
		$title_arr[] = 'User Name';
		$title_arr[] = 'Real Name';

		echo html_build_list_table_top ($title_arr);

		echo "\n";

		for ( $i = 0; $i < $rows; $i++ ) {
			print	"<TR BGCOLOR=\"". html_get_alt_row_color($i) ."\"><TD><A HREF=\"/users/".db_result($result, $i, 'user_name')."/\">"
				. html_image("images/msg.gif","10","12",array("BORDER"=>"0")) . db_result($result, $i, 'user_name')."</A></TD>"
				. "<TD>".db_result($result,$i,'realname')."</TD></TR>\n";
		}
		echo "</TABLE>\n";
	}

} else if ($type_of_search == 'forums') {
	/*
		Query to search within forum messages
	*/

	$array=explode(" ",$words);
	$words1=implode($array,"%' $crit forum.body LIKE '%");
	$words2=implode($array,"%' $crit forum.subject LIKE '%");

	$sql =	"SELECT forum.msg_id,forum.subject,forum.date,users.user_name "
		. "FROM forum,users "
		. "WHERE users.user_id=forum.posted_by AND ((forum.body LIKE '%$words1%') "
		. "OR (forum.subject LIKE '%$words2%')) AND forum.group_forum_id='$forum_id' "
		. "GROUP BY msg_id,subject,date,user_name";
	$result = db_query($sql,26,$offset);
	$rows = $rows_returned = db_numrows($result);

	if (!$result || $rows < 1) {
		$no_rows = 1;
		echo "<H2>No matches found for $words</H2>";
		echo db_error();
//		echo $sql;
	} else {

		if ( $rows_returned > 25) {
			$rows = 25;
		}

		echo "<H3>Search results for $words</H3><P>\n\n";

		$title_arr = array();
		$title_arr[] = 'Thread';
		$title_arr[] = 'Author';
		$title_arr[] = 'Date';

		echo html_build_list_table_top ($title_arr);

		echo "\n";

		for ( $i = 0; $i < $rows; $i++ ) {
			print	"<TR BGCOLOR=\"". html_get_alt_row_color($i) ."\"><TD><A HREF=\"/forum/message.php?msg_id="
				. db_result($result, $i, "msg_id")."\">"
				. html_images("images/msg.gif","10","12",array("BORDER"=>"0"))
				. db_result($result, $i, "subject")."</A></TD>"
				. "<TD>".db_result($result, $i, "user_name")."</TD>"
				. "<TD>".date($sys_datefmt,db_result($result,$i,"date"))."</TD></TR>\n";
		}
		echo "</TABLE>\n";
	}

} else if ($type_of_search == 'bugs') {
	/*
		Query to search within project's bug reports
	*/

	$array=explode(" ",$words);
	$words1=implode($array,"%' $crit bug.details LIKE '%");
	$words2=implode($array,"%' $crit bug.summary LIKE '%");

	$sql =	"SELECT bug.bug_id,bug.summary,bug.date,users.user_name "
		. "FROM bug,users "
		. "WHERE users.user_id=bug.submitted_by AND ((bug.details LIKE '%$words1%') "
		. "OR (bug.summary LIKE '%$words2%') OR (users.user_name LIKE '%$words2%')) AND bug.group_id='$group_id' "
		.  "GROUP BY bug_id,summary,date,user_name";
	$result = db_query($sql,26,$offset);
	$rows = $rows_returned = db_numrows($result);

	if ( !$result || $rows < 1) {
		$no_rows = 1;
		echo "<H2>No matches found for $words</H2>";
		echo db_error();
//		echo $sql;
	} else {

		if ( $rows_returned > 25) {
			$rows = 25;
		}

		echo "<H3>Search results for $words</H3><P>\n";

		$title_arr = array();
		$title_arr[] = 'Bug Summary';
		$title_arr[] = 'Submitted By';
		$title_arr[] = 'Date';

		echo html_build_list_table_top ($title_arr);

		echo "\n";

		for ( $i = 0; $i < $rows; $i++ ) {
			print	"\n<TR BGCOLOR=\"". html_get_alt_row_color($i) ."\"><TD><A HREF=\"/bugs/?group_id=$group_id&func=detailbug&bug_id="
				. db_result($result, $i, "bug_id")."\"> "
				. html_image("images/msg.gif","10","12",array("BORDER"=>"0"))
				. db_result($result, $i, "summary")."</A></TD>"
				. "<TD>".db_result($result, $i, "user_name")."</TD>"
				. "<TD>".date($sys_datefmt,db_result($result,$i,"date"))."</TD></TR>";
		}
		echo "</TABLE>\n";
	}
} else {

	echo "<H1>Invalid Search - ERROR!!!!</H1>";

}

   // This code puts the nice next/prev.
if ( !$no_rows && ( ($rows_returned > $rows) || ($offset != 0) ) ) {

	echo "<BR>\n";

	echo "<TABLE BGCOLOR=\"#EEEEEE\" WIDTH=\"100%\" CELLPADDING=\"5\" CELLSPACING=\"0\">\n";
	echo "<TR>\n";
	echo "\t<TD ALIGN=\"left\">";
	if ($offset != 0) {
		echo "<FONT face=\"Arial, Helvetica\" SIZE=3 STYLE=\"text-decoration: none\"><B>";
		echo "<A HREF=\"javascript:history.back()\"><B>" 
				. html_image("images/t2.gif","15","15",array("BORDER"=>"0","ALIGN"=>"MIDDLE")) 
				. " Previous Results </A></B></FONT>";
	} else {
		echo "&nbsp;";
	}
	echo "</TD>\n\t<TD ALIGN=\"right\">";
	if ( $rows_returned > $rows) {
		echo "<FONT face=\"Arial, Helvetica\" SIZE=3 STYLE=\"text-decoration: none\"><B>";
		echo "<A HREF=\"/search/?type_of_search=$type_of_search&words=".urlencode($words)."&offset=".($offset+25);
		if ( $type_of_search == 'bugs' ) {
			echo "&group_id=$group_id&is_bug_page=1";
		} 
		if ( $type_of_search == 'forums' ) {
			echo "&forum_id=$forum_id&is_forum_page=1";
		}
		echo "\"><B>Next Results " . html_image("images/t.gif","15","15",array("BORDER"=>"0","ALIGN"=>"MIDDLE")) . "</A></B></FONT>";
	} else {
		echo "&nbsp;";
	}
	echo "</TD>\n</TR>\n";
	echo "</TABLE>\n";
}



$HTML->footer(array());
?>
