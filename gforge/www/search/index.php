<?php
/**
  *
  * SourceForge Search Engine
  *
  * Parameters:
  *   $type ($t)  = one of 'soft'[ware],'people','forums','bugs'
  *   $words ($q) = target words to search
  *   $exact	  = 1 for search ing all words (AND), 0 - for any word (OR)
  *   $rss	  = 1 to export RSS
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

// Support for short aliases
if (!$words) {
	$words=$q;
}

if (!$type_of_search) {
	$type_of_search=$type;
}
if (!$type_of_search) {
	$type_of_search=$t;
}
if (!$type_of_search) {
	$type_of_search='soft';
}

require_once('pre.php');
require_once('www/tracker/include/ArtifactTypeHtml.class');

function highlight_target_words($word_array,$text) {
	if (!$text) {
		return '&nbsp;';
	}
	$re=implode($word_array,'|');
	return eregi_replace("($re)",'<span style="background-color:pink">\1</span>',$text);
}

function error_while_in_rss($descr) {
	header("Content-Type: text/plain");
	print '<channel></channel>';
	exit;
}

if (!$rss) {
	// If search context is a project, show its toolbar
	if ($type_of_search == "forums" || $type_of_search == "artifact") {
		site_project_header(array('title'=>'Project Search','group'=>$group_id,'pagename'=>'search'));
	} else {
		$HTML->header(array('title'=>'Search','pagename'=>'search'));
	}

	echo "<P><CENTER>";

	// show search box which will return results on
	// this very page (default is to open new window)
	echo $HTML->searchBox();
}

/*
	Force them to enter at least three characters
*/

$words = htmlspecialchars(trim($words));
$words = ereg_replace("[ \t]+", ' ', $words);

if ($words && (strlen($words) < 3)) {
	if ($rss) {
		error_while_in_rss('Search must be at least three characters');
	} else {
		echo "<H2>Search must be at least three characters</H2>";
		$HTML->footer(array());
		exit;
	}
}

if (!$words) {
	if ($rss) {
		error_while_in_rss('Search criteria are not specified');
	} else {
		echo "<BR>Enter Your Search Words Above</CENTER><P>";
		$HTML->footer(array());
		exit;
	}
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

if ($type_of_search == "soft") {
	/*
		Query to find software
	*/

	// If multiple words, separate them and put ILIKE (pgsql's 
	// case-insensitive LIKE) in between
	// XXX:SQL: this assumes db understands backslash-quoting

	$array=explode(" ",quotemeta($words));
	// we need to use double-backslashes in SQL
	$array_re=explode(" ",addslashes(quotemeta($words)));

	$words1="group_name ILIKE '%" . implode($array,"%' $crit group_name ILIKE '%") ."%'";
	$words2="short_description ILIKE '%" . implode($array,"%' $crit short_description ILIKE '%") ."%'";
	$words3="unix_group_name ILIKE '%" . implode($array,"%' $crit unix_group_name ILIKE '%") . "%'";

	if (!$rss) {
		$sql = "SELECT group_name,unix_group_name,type,group_id,short_description "
			   ."FROM groups "
			   ."WHERE status IN ('A','H') "
			   ."AND is_public='1' "
			   ."AND (($words1) OR ($words2) OR ($words3))";
	} else {
			// If it's RSS export, try to infer additional information, as
			// shown by Freshmeat search. This means that only projects
			// categorized under Trove will be exported - that's good, since
			// cross-site search performed not to get junk results.
			$sql = "SELECT group_name,unix_group_name,type,groups.group_id, "
				   ."short_description,license,register_time "
				   ."FROM groups "
				   ."WHERE status IN ('A','H') "
					   ."AND is_public='1' "
				   ."AND groups.short_description<>'' "
				   ."AND (($words1) OR ($words2) OR ($words3))";
	}

	if ($rss) {
		$limit=200; 
	} else {
		$limit=25;
	}
	$result = db_query($sql, $limit+1, $offset, SYS_DB_SEARCH);
	$rows = $rows_returned = db_numrows($result);

	/*
	 *  Dump RSS rendering of search results, date registered, 
	 *  include trove categories, license.
	 */
	if ($rss) {
		include_once('www/export/rss_utils.inc');
		function callback($data_row) {
                        // trove_cat_root=18 - Topic subtree
			// [CB] now $default_trove_cat defined in local.inc
			$res = db_query("
				SELECT trove_cat.fullpath 
				FROM trove_group_link,trove_cat 
				WHERE trove_group_link.trove_cat_root=$default_trove_cat 
				AND trove_group_link.trove_cat_id=trove_cat.trove_cat_id 
				AND group_id='".$data_row['group_id']."'");
			$ret = ' | date registered: '.date('M jS Y',$data_row['register_time']);
			$ret .= ' | category: '.str_replace(' ','',implode(util_result_column_to_array($res),','));
			return $ret.' | license: '.$data_row['license'];
		}
		header("Content-Type: text/plain");
		rss_dump_project_result_set($result, 'SourceForge Search Results', 
			'SourceForge Search Results for "' .htmlspecialchars($words).'"', 'callback');
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

		echo $GLOBALS['HTML']->listTableTop($title_arr);

		for ( $i = 0; $i < $rows; $i++ ) {
			if (db_result($result, $i, 'type') == 2) {
				$what = 'foundry';
			} else {
				$what = 'projects';
			}
			
			print	"<TR BGCOLOR=\"". html_get_alt_row_color($i)."\"><TD><A HREF=\"/$what/"
				. db_result($result, $i, 'unix_group_name')."/\">"
				. html_image("images/msg.png","10","12",array("BORDER"=>"0")) 
				. highlight_target_words($array,db_result($result, $i, 'group_name'))."</A></TD>"
				. "<TD>".highlight_target_words($array,db_result($result,$i,'short_description'))."</TD></TR>\n";
		}

		echo $GLOBALS['HTML']->listTableBottom();

	}

} else if ($type_of_search == "people") {
	/*
		Query to find users
	*/

	// If multiple words, separate them and put ILIKE in between
	$array=explode(" ",$words);
	$words1=implode($array,"%' $crit user_name ILIKE '%");
	$words2=implode($array,"%' $crit realname ILIKE '%");

	$sql =	"SELECT user_name,user_id,realname 
		FROM users 
		WHERE ((user_name ILIKE '%$words1%') 
		OR (realname ILIKE '%$words2%')) 
		AND (status='A') 
		ORDER BY user_name";

	$result = db_query($sql, 26, $offset, SYS_DB_SEARCH);

	$rows = $rows_returned = db_numrows($result);

	if (!$result || $rows < 1) {
		$no_rows = 1;
		echo "<H2>No matches found for $words</H2>";
		echo db_error();
	} else {

		if ( $rows_returned > 25) {
			$rows = 25;
		}

		echo "<H3>Search results for $words</H3><P>\n\n";

		$title_arr = array();
		$title_arr[] = 'User Name';
		$title_arr[] = 'Real Name';

		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		for ( $i = 0; $i < $rows; $i++ ) {
			print	"<TR BGCOLOR=\"". html_get_alt_row_color($i) ."\"><TD><A HREF=\"/users/".db_result($result, $i, 'user_name')."/\">"
				. html_image("images/msg.png","10","12",array("BORDER"=>"0")) . db_result($result, $i, 'user_name')."</A></TD>"
				. "<TD>".db_result($result,$i,'realname')."</TD></TR>\n";
		}

		echo $GLOBALS['HTML']->listTableBottom();

	}

} else if ($type_of_search == 'forums' && $forum_id && $group_id) {
	/*
		Query to search within forum messages
	*/

	$array=explode(" ",$words);
	$words1=implode($array,"%' $crit forum.body ILIKE '%");
	$words2=implode($array,"%' $crit forum.subject ILIKE '%");

	$sql =	"SELECT forum.msg_id,forum.subject,forum.date,users.user_name 
		FROM forum,users 
		WHERE users.user_id=forum.posted_by AND ((forum.body ILIKE '%$words1%') 
		OR (forum.subject ILIKE '%$words2%')) AND forum.group_forum_id='$forum_id' 
		GROUP BY msg_id,subject,date,user_name";
	$result = db_query($sql,26,$offset);
	$rows = $rows_returned = db_numrows($result);

	if (!$result || $rows < 1) {
		$no_rows = 1;
		echo "<H2>No matches found for $words</H2>";
		echo db_error();
	} else {

		if ( $rows_returned > 25) {
			$rows = 25;
		}

		echo "<H3>Search results for $words</H3><P>\n\n";

		$title_arr = array();
		$title_arr[] = 'Thread';
		$title_arr[] = 'Author';
		$title_arr[] = 'Date';

		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		for ( $i = 0; $i < $rows; $i++ ) {
			print	"<TR BGCOLOR=\"". html_get_alt_row_color($i) ."\"><TD><A HREF=\"/forum/message.php?msg_id="
				. db_result($result, $i, "msg_id")."\">"
				. html_image("images/msg.png","10","12",array("BORDER"=>"0"))
				. db_result($result, $i, "subject")."</A></TD>"
				. "<TD>".db_result($result, $i, "user_name")."</TD>"
				. "<TD>".date($sys_datefmt,db_result($result,$i,"date"))."</TD></TR>\n";
		}

		echo $GLOBALS['HTML']->listTableBottom();

	}

} else if ($type_of_search == 'artifact' && $atid && $group_id) {
	/*
		Query to search within a specific ArtifactType
	*/

	$array=explode(" ",$words);
	$words1=implode($array,"%' $crit a.details ILIKE '%");
	$words2=implode($array,"%' $crit a.summary ILIKE '%");
	
	if (ereg('^#?[0-9]+$', $words)) {
		$no = ereg_replace('^#?([0-9]+)$', '\\1', $words);
		$by_no_sql = "OR artifact_id=$no ";
	}

	$sql =	"SELECT DISTINCT ON (a.group_artifact_id,a.artifact_id) a.group_artifact_id,a.artifact_id,a.summary,a.open_date,users.user_name
		FROM artifact a,users 
		WHERE 
		a.group_artifact_id='$atid'
		AND users.user_id=a.submitted_by 
		AND ((a.details ILIKE '%$words1%') 
			OR (a.summary ILIKE '%$words2%')
			$by_no_sql) 
		ORDER BY group_artifact_id ASC,artifact_id ASC";

		//GROUP BY group_artifact_id,artifact_id,summary,open_date,user_name
/*

create index art_groupartid_artifactid on artifact (group_artifact_id,artifact_id);

*/

	$result = db_query($sql,26,$offset);
	$rows = $rows_returned = db_numrows($result);

	if ( !$result || $rows < 1) {
		$no_rows = 1;
		echo "<H2>No matches found for $words</H2>";
		echo db_error();
	} else {

		if ( $rows_returned > 25) {
			$rows = 25;
		}

		echo "<H3>Search results for $words</H3><P>\n";

		$title_arr = array();
		$title_arr[] = '#';
		$title_arr[] = 'Bug Summary';
		$title_arr[] = 'Submitted By';
		$title_arr[] = 'Date';

		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		for ( $i = 0; $i < $rows; $i++ ) {
			print	"\n<TR BGCOLOR=\"". html_get_alt_row_color($i) ."\">
				<td>".db_result($result, $i, "artifact_id")."</td>
				<TD><A HREF=\"/tracker/?group_id=$group_id&atid="
				. db_result($result, $i, "group_artifact_id") 
				. "&func=detail&aid="
				. db_result($result, $i, "artifact_id")."\"> "
				. html_image("images/msg.png","10","12",array("BORDER"=>"0"))
				. db_result($result, $i, "summary")."</A></TD>"
				. "<TD>".db_result($result, $i, "user_name")."</TD>"
				. "<TD>". date($sys_datefmt,db_result($result,$i,"open_date"))."</TD></TR>";
		}

		echo $GLOBALS['HTML']->listTableBottom();

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
			. html_image("images/t2.png","15","15",array("BORDER"=>"0","ALIGN"=>"MIDDLE")) 
			. " Previous Results </A></B></FONT>";
	} else {
		echo "&nbsp;";
	}
	echo "</TD>\n\t<TD ALIGN=\"right\">";
	if ( $rows_returned > $rows) {
		echo "<FONT face=\"Arial, Helvetica\" SIZE=3 STYLE=\"text-decoration: none\"><B>";
		echo "<A HREF=\"/search/?type=$type_of_search&exact=$exact&q=".urlencode($words)."&offset=".($offset+25);
		if ( $type_of_search == 'artifact' ) {
			echo "&group_id=$group_id&atid=$atid";
		} 
		if ( $type_of_search == 'forums' ) {
			echo "&group_id=$group_id&forum_id=$forum_id";
		}
		echo "\"><B>Next Results " . html_image("images/t.png","15","15",array("BORDER"=>"0","ALIGN"=>"MIDDLE")) . "</A></B></FONT>";
	} else {
		echo "&nbsp;";
	}
	echo "</TD>\n</TR>\n";
	echo "</TABLE>\n";
}

$HTML->footer(array());
?>
