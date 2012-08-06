<?php
/**
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


// export a group's tracker bugs per artifact in RSS 2.0
// Author: Jutta Horstmann, data in transit <jh@dataintransit.com>
// Created: 01.10.07
// Based on: export/rss20_tracker.php (by JH), docman/index.php
// Changes:
// Date         Author      Comment
// 07.11.07     JH          show only public group feeds
//
//TO DO: Translations for error messages
//Notes:
// Keep in mind to write "&" in URLs as &amp; in RSS feeds


require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'export/rss_utils.inc';
require_once $gfcommon.'docman/DocumentFactory.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';


if (isset($_GET['group_id'])&&!empty($_GET['group_id'])&&is_numeric($_GET['group_id'])) {
	$group_id = $_GET['group_id'];

	$group =& group_get_object($group_id);


	//does group exist? do we get an object?
	if (!$group || !is_object($group)) {
		beginFeed();
		endOnError('Could not get the Group object');
	} elseif ($group->isError()) {
	        beginFeed();
		endOnError($group->getErrorMessage());
	}
    elseif (!session_check_perm ('project_read', $group_id)){
		beginFeed();
		endOnError('No RSS feed available as group status is set to private.');
	}
	$groupname = $group->getPublicName();
	$link = "/docman/index.php?group_id=$group_id";

    beginFeed($groupname,$link);

	//does documentation exist? do we get a factory?
	$df = new DocumentFactory($group);
	if ($df->isError()) {
		endOnError($df->getErrorMessage());
	}

	$dgf = new DocumentGroupFactory($group);
	if ($dgf->isError()) {
		endOnError($dgf->getErrorMessage());
	}
	// Get the document groups info
	$nested_groups =& $dgf->getNested();

	$d_arr =& $df->getDocuments();

	writeFeed($d_arr,$group_id, $nested_groups);
	endFeed();

}//no group_id in GET
else {
	beginFeed();
	displayError('Please supply a Group ID with the request.');
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
	print "  <link>http://".forge_get_config('web_host').$link."</link>\n";
	print "  <description>".forge_get_config('forge_name')." Documents of \"".$groupname."\"</description>\n";
	print "  <language>en-us</language>\n";
	print "  <copyright>Copyright 2000-".date("Y")." ".forge_get_config('forge_name')."</copyright>\n";
	print "  <webMaster>".forge_get_config('admin_email')."</webMaster>\n";
	print "  <lastBuildDate>".gmdate('D, d M Y G:i:s',time())." GMT</lastBuildDate>\n";
	print "  <docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";
	print "  <image>\n";
	print "    <url>http://".forge_get_config('web_host')."/images/bflogo-88.png</url>\n";
	print "    <title>".forge_get_config('forge_name')." Developer</title>\n";
	print "    <link>http://".forge_get_config('web_host')."/</link>\n";
	print "    <width>124</width>\n";
	print "    <heigth>32</heigth>\n";
	print "  </image>\n";
}

function writeFeed($d_arr, $group_id){

	// ## default limit
	//if (isset($limit) ||empty($limit)) $limit = 10;
	//if ($limit > 100) $limit = 100;

	/*
	if (!is_array($nested_groups["$parent_group"])) {
		return;
	}
	$child_count = count($nested_groups["$parent_group"]);
	*/
	if (!$d_arr || count($d_arr) < 1) {
		endOnError(_("No documents found in Document Manager"));

	} else {
		//	Put the result set (list of documents for this group) into feed items

		// ## item outputs
		//$outputtotal = 0;
		//loop through the documents
		for ($j = 0; $j < count($d_arr); $j++) {
			$link = (( $d_arr[$j]->isURL() ) ? $d_arr[$j]->getFileName() : "docman/view.php/".$d_arr[$j]->Group->getID()."/".$d_arr[$j]->getID()."/".$d_arr[$j]->getFileName() );

			print "  <item>\n";
			if (!is_object($d_arr[$j])) {
                        	//just skip it
			} elseif ($d_arr[$j]->isError()) {
				print " <title>Error</title>".
						"<description>".rss_description($d_arr[$j]->getErrorMessage())."</decription>";
			} else {
				print "   <title>".$d_arr[$j]->getName()."</title>\n";
				print "   <link>http://".forge_get_config('web_host')."/".$link."</link>\n";
				print "   <category>".$d_arr[$j]->getDocGroupName()."</category>\n";

				print "   <description>".
						rss_description($d_arr[$j]->getDescription()).
						" - Language: ". $d_arr[$j]->getLanguageName().
						"</description>\n";

				print "   <author>".$d_arr[$j]->getCreatorRealName()."</author>\n";
				//print "   <comment></comment>\n";
				//print "   <pubDate>".gmdate('D, d M Y G:i:s',time())." GMT</pubDate>\n";
				//print "   <guid></guid>\n";
			}//else (everything ok)
			print "  </item>\n";

			//$outputtotal++;
			//if ($outputtotal >= $limit) break;
		}//for loop
	}//else (there are documents)
}


function displayError($errorMessage) {
	print " <title>Error</title>".
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
?>
