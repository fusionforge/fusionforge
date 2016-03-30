<?php
/**
 * FusionForge Exports: RSS support module
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2015, Franck Villaume - TrivialDev
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';    // Initial db and session library, opens session

function rss_setscheme() {
	if ($GLOBALS['scheme'] == "netscape") {
		$GLOBALS['description_maxlen'] = 500;
		$GLOBALS['description_notnull'] = 1;
	}
}

function rss_date($date) {
	return gmdate('D, d M Y H:i:s', $date).' GMT';
}

function rss_description ($in) {
	// if description_maxlen is defined, then truncate appropriately
	// added for netscape rss schema compat
	if (isset($GLOBALS['description_maxlen']) && $GLOBALS['description_maxlen'] &&
		(strlen($in) > $GLOBALS['description_maxlen'])) {
		$in = substr($in,0,$GLOBALS['description_maxlen']);
	}
	// according to netscape, this cannot be blank
	if ((strlen($in)==0) && isset($GLOBALS['description_notnull']) && $GLOBALS['description_notnull']) {
		$in = "No description available.";
	}
	return htmlspecialchars(nl2br($in));
}

// callback is optional function name which should be called for each project
// row and which result will be appended to description element
function rss_dump_project_result_set ($res,$title,$desription='',$callback=0) {
        if (!$desription) $desription=$title;
	// one time output
	print " <channel>\n";
	print "  <copyright>Copyright ".date("Y")." ".forge_get_config ('forge_name')."</copyright>\n";
	print "  <pubDate>".rss_date(time())."</pubDate>\n";
	print "  <description>$desription</description>\n";
	print "  <link>http://".forge_get_config ('web_host')."/</link>\n";
	print "  <title>$title</title>\n";
	print "  <webMaster>".forge_get_config ('admin_email')."</webMaster>\n";
	print "  <language>en-us</language>\n";

	// item outputs
	$addendum = '' ;
	while ($row = db_fetch_array($res)) {
		if (!forge_check_perm ('project_read', $row['group_id'])) {
			continue ;
		}
		if ($callback) {
			$addendum=$callback($row);
		}
		print "  <item>\n";
		print "   <title>".htmlspecialchars($row['group_name'])."</title>\n";
		print "   <link>".util_make_base_url().util_make_url_g($row['unix_group_name'],$row['group_id'])."</link>\n";
		print "   <description>";
		print preg_replace('[\n\r]',' ',rss_description($row['short_description']));
		print $addendum;
		print "</description>\n";
		print "  </item>\n";
	}
	// end output
	print " </channel>\n";
}
