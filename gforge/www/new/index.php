<?php
/**
  *
  * SourceForge New Releases Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');
require_once('vote_function.php');

$HTML->header(array("title"=>$Language->getText('new','title'),'pagename'=>'new'));

if ( !$offset || $offset < 0 ) {
	$offset = 0;
}

// For expediancy, list only the filereleases in the past three days.
$start_time = time() - (30 * 86400);

$query	= "SELECT groups.group_name,"
	. "groups.group_id,"
	. "groups.unix_group_name,"
	. "groups.short_description,"
	. "users.user_name,"
	. "users.user_id,"
	. "frs_release.release_id,"
	. "frs_release.name AS release_version,"
	. "frs_release.release_date,"
	. "frs_release.released_by,"
	. "frs_package.name AS module_name, "
	. "frs_dlstats_grouptotal_vw.downloads "
	. "FROM groups,users,frs_package,frs_release,frs_dlstats_grouptotal_vw "
	. "WHERE ( frs_release.release_date > '$start_time' "
	. "AND frs_release.package_id = frs_package.package_id "
	. "AND frs_package.group_id = groups.group_id "
	. "AND frs_release.released_by = users.user_id "
	. "AND frs_package.group_id = frs_dlstats_grouptotal_vw.group_id "
	. "AND frs_release.status_id=1 ) "
	. "ORDER BY frs_release.release_date DESC";
$res_new = db_query($query, 21, $offset, SYS_DB_STATS);

if (!$res_new || db_numrows($res_new) < 1) {
	// echo $query . "<br /><br />";
	echo db_error();
	echo "<h1>".$Language->getText('new','no_new_release_found')."</h1>";
} else {

	if ( db_numrows($res_new) > 20 ) {
		$rows = 20;
	} else {
		$rows = db_numrows($res_new);
	}

	echo "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
	for ($i=0; $i<$rows; $i++) {
		$row_new = db_fetch_array($res_new);
		// avoid dupulicates of different file types
		if (!($G_RELEASE["$row_new[group_id]"])) {
			print "<tr valign=\"top\">";
			print "<td colspan=\"2\">";
			print "<a href=\"/projects/$row_new[unix_group_name]/\"><strong>$row_new[group_name]</strong></a>"
				. "\n</td><td nowrap=\"nowrap\"><em>".$Language->getText('new','released_by')." <a href=\"/users/$row_new[user_name]/\">"
				. "$row_new[user_name]</a></em></td></tr>\n";

			print "<tr><td>".$Language->getText('new','module')." "."$row_new[module_name]</td>\n";
			print "<td>".$Language->getText('new','version')." "."$row_new[release_version]</td>\n";
			print "<td>" . date("M d, h:iA",$row_new[release_date]) . "</td>\n";
			print "</tr>\n";

			print "<tr valign=\"top\">";
			print "<td colspan=\"2\">&nbsp;<br />";
			if ($row_new['short_description']) {
				print "<em>$row_new[short_description]</em>";
			} else {
				print "<em>".$Language->getText('new','this_project_has_not')."</em>";
			}
			// print "<p>Release rating: ";
			// print vote_show_thumbs($row_new[filerelease_id],2);
			print "</td>";
			print '<td align="center" nowrap="nowrap">';
			// print '&nbsp;<br />Rate this Release!<br />';
			// print vote_show_release_radios($row_new[filerelease_id],2);
			print "&nbsp;</td>";
			print "</tr>\n";

			print '<tr><td colspan="3">';
			// link to whole file list for downloads
			print "&nbsp;<br /><a href=\"/project/showfiles.php?group_id=$row_new[group_id]&amp;release_id=$row_new[release_id]\">";
			print $Language->getText('new','download'). "</a> ";
			print '('.$Language->getText('new','projects_total') .$row_new['downloads'].') | ';
			// notes for this release
			print "<a href=\"/project/shownotes.php?release_id=".$row_new[release_id]."\">";
			print $Language->getText('new','notes_changes'). "</a>";
			print "<hr /></td></tr>\n";

			$G_RELEASE["$row_new[group_id]"] = 1;
		}
	}

	echo "<tr style=\"background-color:#eeeeee\"><td>";
        if ($offset != 0) {
		echo "<span style=\"text-decoration: none;font-family: arial, helvetica\">";
        	echo "<a href=\"/new/?offset=".($offset-20)."\">" .
			html_image("t2.png","15","15",array("border"=>"0","align"=>"middle")) .
			" <strong>".$Language->getText('new','newer_releases')."</strong></a></span>";
        } else {
        	echo "&nbsp;";
        }

	echo "</td><td colspan=\"2\" align=\"right\">";
	if (db_numrows($res_new)>$rows) {
		echo "<span style=\"text-decoration: none;font-family: arial, helvetica\">";
		echo "<a href=\"/new/?offset=".($offset+20)."\"><strong>".$Language->getText('new','older_releases')."</strong> " .
		html_image("t.png","15","15",array("border"=>"0","align"=>"middle")) .
		"</a></span>";
	} else {
		echo "&nbsp;";
	}
	echo "</td></tr>\n</table>";

}

$HTML->footer(array());

?>
