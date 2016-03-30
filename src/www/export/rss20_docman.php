<?php
/**
 * FusionForge Documents RSS Feed
 * Copyright 2012,2015 Franck Villaume - TrivialDev
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

// Keep in mind to write "&" in URLs as &amp; in RSS feeds


require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'export/rss_utils.inc';
require_once $gfcommon.'docman/DocumentFactory.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';

$sysdebug_enable = false;

$group_id = getIntFromRequest('group_id');
$limit = getIntFromRequest('limit', 10);
if ($limit > 100) $limit = 100;

if (isset($group_id) && !empty($group_id) && is_numeric($group_id)) {
	session_require_perm('project_read', $group_id);
	$group = group_get_object($group_id);

	//does group exist? do we get an object?
	if (!$group || !is_object($group)) {
		beginFeed();
		endOnError('Could not get the Group object');
	} elseif ($group->isError()) {
	        beginFeed();
		endOnError($group->getErrorMessage());
	}

	$groupname = $group->getPublicName();
	$link = "/docman/index.php?group_id=".$group_id;

	beginFeed($groupname, $link);

	//does documentation exist? do we get a factory?
	$df = new DocumentFactory($group);
	if ($df->isError()) {
		endOnError($df->getErrorMessage());
	}

	$d_arr =& $df->getDocuments();

	writeFeed($d_arr, $limit);
	endFeed();

} else {
	beginFeed();
	displayError(_('Please supply a Group ID with the request.'));
	endFeed();
}

//**************************************************************++
function beginFeed($groupname = "", $link = "") {

	header("Content-Type: text/xml");
	print '<?xml version="1.0" encoding="UTF-8"?>
			<rss version="2.0">
			';
	print " <channel>\n";
	print "  <title>".forge_get_config('forge_name')." Project \"".$groupname."\" Documents</title>\n";
	print "  <link>".util_make_url($link)."</link>\n";
	print "  <description>".forge_get_config('forge_name')." Documents of \"".$groupname."\"</description>\n";
	print "  <language>en-us</language>\n";
	print "  <copyright>Copyright ".date("Y")." ".forge_get_config('forge_name')."</copyright>\n";
	print "  <webMaster>".forge_get_config('admin_email')."</webMaster>\n";
	print "  <lastBuildDate>".rss_date(time())."</lastBuildDate>\n";
	print "  <docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";
	print "  <generator>".forge_get_config ('forge_name')." RSS generator</generator>\n";
}

function writeFeed($d_arr, $limit){

	/*
	if (!is_array($nested_groups["$parent_group"])) {
		return;
	}
	$child_count = count($nested_groups["$parent_group"]);
	*/
	if ($d_arr && count($d_arr) > 0) {
		//	Put the result set (list of documents for this group) into feed items

		// ## item outputs
		//$outputtotal = 0;
		//loop through the documents
		for ($j = 0; $j < count($d_arr); $j++) {
			$link = (( $d_arr[$j]->isURL() ) ? $d_arr[$j]->getFileName() : util_make_url('/docman/view.php/'.$d_arr[$j]->Group->getID().'/'.$d_arr[$j]->getID().'/'.urlencode($d_arr[$j]->getFileName())));

			print "  <item>\n";
			if (!is_object($d_arr[$j])) {
                        	//just skip it
			} elseif ($d_arr[$j]->isError()) {
				print " <title>Error</title>".
						"<description>".rss_description($d_arr[$j]->getErrorMessage())."</decription>";
			} else {
				print "   <title>".$d_arr[$j]->getName()."</title>\n";
				print "   <link>".$link."</link>\n";
				//print "   <category>".$d_arr[$j]->getDocGroupName()."</category>\n";
				print "   <description>".rss_description($d_arr[$j]->getDescription())."</description>\n";
				print "   <author>".trim($d_arr[$j]->getCreatorRealName())."</author>\n";
				if ( $d_arr[$j]->getUpdated() ) {
					$pubdate = date(_('Y-m-d H:i'), $d_arr[$j]->getUpdated());
				} else {
					$pubdate = date(_('Y-m-d H:i'), $d_arr[$j]->getCreated());
				}
				print "   <pubDate>".$pubdate."</pubDate>\n";
				//print "   <guid></guid>\n";
			}//else (everything ok)
			print "  </item>\n";

			if ($j >= $limit) break;
		}//for loop
	}//else (there are documents)
}


function displayError($errorMessage) {
	print " <title>"._('Error')."</title>".
		"<description>".rss_description($errorMessage)."</description>";
}

function endFeed() {
	print '</channel></rss>';
	exit();
}

function endOnError($errorMessage) {
	displayError($errorMessage);
	endFeed();
}
