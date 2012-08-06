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


// export forum posts in RSS 2.0
// Author: Jutta Horstmann, data in transit <jh@dataintransit.com>
// Created: 14.01.08
// Based on: export/rss20_tracker.php (by JH), docman/index.php, export/forum.php, forum/forum.php
// Changes:
// Date         Author      Comment
// 31.01.08     JH          error handling & display for valid groups with no forums
//                          and invalid group_ids /forum_ids parameters (see mail CP 30.01.08)
//
// TO DO: Translations for error messages
// Notes:
// Keep in mind to write "&" in URLs as &amp; in RSS feeds

// Doc:
// Params in calling URL:
// group_ids (0 - x group ids, separated by "+"), optional, default: not set
// forum_ids (0 - x forum ids, separated by "+"), optional, default: not set
// number (no. of feed items), optional, default: 10
// item (feed variant, items should be thread headings or postings), optional, default:thread
// none: 10 last threads of evolvis as a whole
//

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'export/rss_utils.inc';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';
require_once $gfcommon.'forum/ForumMessageFactory.class.php';



//Initialize
$groups = array();
$forums = array();
$farr = array();
$group_ids = array();
$forum_ids = array();
$n_forums=0;
$n_groups=0;
$n_forum_ids=0;
$n_group_ids=0;

$where_group = "";
$where_forum = "";
$where_threads = "";
$where_start_or = "";
$where_end_or = "";

//Defaults
$number_items = 10;
$max_number_items = 100;
$show_threads = true;

//Debug?
$debug = false;

// ----------------------- check and extract params in GET ----------------------------

//--- filter by group_ids - resolve them to forums ---
if (isset($_GET['group_ids'])&&!empty($_GET['group_ids'])) {
    //explode: http changes "+" to " "
    $group_ids = array_unique(array_merge($group_ids, explode(" ",$_GET['group_ids'])));

    //loop through group_ids
    for ($i=0; $i<count($group_ids);$i++){
        if (is_numeric($group_ids[$i])) {
            $group =& group_get_object($group_ids[$i]);
            //does group exist? do we get an object? is group public? does it use forums?
            if ($group && is_object($group) && !$group->isError()
                        && $group->isPublic() && $group->usesForum()){
                $groups[] = $group;
                //valid forums from forum_ids param (needed for feed title)
                $n_group_ids++;

                //this groups' forums in array (code based on forum/index.php)
                $ff=new ForumFactory($group);
                if ($ff &&is_object($ff) && !$ff->isError()) {
                    $farr = array_merge($farr, $ff->getForums());
                    if (count($farr) < 1) {
                            error_log(_("Forum RSS: No forums found"),0);
                    }
                }
                else error_log("Forum RSS: ForumFactory error: ".$ff->getErrorMessage()." - No forums for group ".$group->getPublicName(),0);
            }
	    else error_log("Forum RSS: group object error",0);
        }
	else error_log("Forum RSS: invalid group_ids param: ".$group_ids[$i],0);
    }
}

// ----------- add forums called by forum_ids param, if any ------------
if (isset($_GET['forum_ids']) && !empty($_GET['forum_ids'])) {
    //explode: http changes "+" to " "
    $forum_ids = array_unique(explode(' ',$_GET['forum_ids']));

    foreach ($forum_ids as $fid){
        //we got strings from explode(), cast them to int (if possible)
        $fid= (int) $fid;

        if (is_numeric($fid)) {
            //based on code from forum/forum.php: Get the group_id based on this forum_id
		$result=db_query_params('SELECT group_id FROM forum_group_list
                              WHERE group_forum_id=$1',
					array ($fid));
            if ($result && db_numrows($result) >= 1) {
                $forum_group_id=db_result($result,0,'group_id');

                $g =& group_get_object($forum_group_id);
                if ($g && is_object($g) && !$g->isError() && $g->isPublic() && $g->usesForum()) {
                    $f=new Forum($g,$fid);

                    if ($f && is_object($f) && !$f->isError() && $f->isPublic()) {
                        //add group to group array, forum to forum array
                        $groups[] = $g;
                        $farr[] = $f;
                        //valid forums from forum_ids param (needed for feed title)
                        $n_forum_ids++;
                    }
		    else error_log("Forum RSS: forum object error",0);
                }
		else error_log("Forum RSS: forum group oject error",0);
            }//there is a db result
	    else error_log("Forum RSS: no forum group in DB",0);
        }//url param is valid (numeric)
	else error_log("Forum RSS: invalid forum_ids param",0);
    }//for loop

}

//if forum_ids specifies forums contained also in group_ids: drop duplicates
//does not filter groups by forums!

//merge $forums and $farr
$forums = array_merge($farr, $forums);
//unique for objects
$forums = object_array_unique($forums);
$groups = object_array_unique($groups);

$n_forums = count($forums);
$n_groups = count($groups);

// ----------------------- error cases ---------------------
$error_no_messages = false;
//there were filter parameters but they were invalid or
//there were valid group_ids, but they contained no forums

if (($n_groups == 0 && isset($_GET['group_ids']) && count($_GET['group_ids'])>0) ||
    ($n_forums == 0 && isset($_GET['forum_ids']) && count($_GET['forum_ids'])>0) ||
    ($n_groups > 0 && $n_forums == 0)){
    $error_no_messages = "No forum messages found. Please check for invalid parameters and if the project(s) contain public forums.";
}



//-------------------- other parameters --------------------
//number
if (isset($_GET['number']) && !empty($_GET['number']) &&
    is_numeric($_GET['number']) && $_GET['number']>0) {
    $number_items = $_GET['number'];
    if ($number_items > $max_number_items) $number_items = $max_number_items;
}

//item
if (isset($_GET['item']) && !empty($_GET['item']) && ($_GET['item'] == "posting")) {
    $show_threads = false;
}
else $where_threads = " AND is_followup_to=0";


// ------------- general settings and defaults for filtered and non-filtered feeds -------------
$feed_title_desc = $show_threads ? "Current threads" : "Recent postings";
$feed_title = forge_get_config('forge_name')." Forums: ".$feed_title_desc; //all site's forums
$feed_link = "http://".forge_get_config('web_host');
$feed_desc = forge_get_config('forge_name')." Forums";


// -------------for filtered feeds - set feed title, link and description-------------

//more than one group and/or multiple forums -> title is "Selected Evolvis Forums..."; link is default
if ($n_groups>1 || $n_forums>0) {
    $feed_title = "Selected ".$feed_title;
    if ($debug) error_log ("Forum RSS: g: ".$n_groups." f: ".$n_forums,0);
}
//one group and no forum param -> Groupname in feed title; link to group
if ($n_groups == 1 && $n_forum_ids == 0){
    $feed_title = $groups[0]->getPublicName()." Forums: ".$feed_title_desc;
    $feed_link = $feed_link . "/forum/?group_id=".$groups[0]->getID();
    $feed_desc = $groups[0]->getDescription(); //Feed desc = project desc
}
//one forum and no (valid) group param
//-> forum's group name and forum name in feed title; link to forum
if ($n_forum_ids == 1 && $n_group_ids == 0){
    $forum_group = $forums[0]->getGroup();

    $feed_title = $forum_group->getPublicName().' - "' .$forums[0]->getName().'" forum: '.
                $feed_title_desc;
    $feed_link = $feed_link . "/forum/forum.php?forum_id=".$forums[0]->getID();
    $feed_desc = $forums[0]->getDescription();
}

// --------------------------- build the feed -------------------------------

beginForumFeed($feed_title, $feed_link, $feed_desc);

// ----------------- collect the messages -----------

//only if no $error_no_messages
if (!$error_no_messages){
    //messages to be displayed
    $rss_messages = array();

    //get forum messages
    $qpa = db_construct_qpa () ;
    $qpa = db_construct_qpa ($qpa, 'SELECT f.group_forum_id AS group_forum_id,
                f.msg_id AS msg_id, f.subject AS subject, f.most_recent_date AS most_recent_date,
                f.has_followups, f.thread_id,
                u.realname AS user_realname,
                g.group_id AS group_id, g.group_name as group_name,
                fg.forum_name as forum_name, fg.description AS forum_desc
        FROM forum f,users u, groups g,forum_group_list fg
        WHERE f.posted_by=u.user_id
        AND g.group_id = fg.group_id
        AND f.group_forum_id = fg.group_forum_id
        AND g.status=$1
        AND g.use_forum=1 ',
			     array ('A')) ;
    $cnt = 0;
    if ($n_forums > 0) {
	    $qpa = db_construct_qpa ($qpa, 'AND (') ;
	    foreach ($forums as $f){
		    $qpa = db_construct_qpa ($qpa, 'f.group_forum_id = $1',
					     array ($f->getID())) ;
		    $cnt++ ;
		    if ($cnt < $n_forums) {
			    $qpa = db_construct_qpa ($qpa, ' OR ') ;
		    }
	    }
	    $qpa = db_construct_qpa ($qpa, ') ') ;
    }

    $qpa = db_construct_qpa ($qpa, 'ORDER BY f.most_recent_date DESC LIMIT $1',
			     array ($number_items)) ;

    $res_msg = db_query_qpa($qpa);
    if (!$res_msg) {
            error_log(_("Forum RSS: Forum not found: ").' '.db_error(),0);
    }

    while ($row_msg = db_fetch_array($res_msg)) {
	    if (!forge_check_perm('forum',$row_msg['group_forum_id'],'read')) {
		    continue;
	    }
        //get thread name for posting
        $res_thread = db_query_params('SELECT subject FROM forum WHERE is_followup_to=0 AND thread_id = $1',
				      array ($row_msg['thread_id']));
        $row_thread = db_fetch_array($res_thread);
        if (!$res_thread || db_numrows($res_thread) != 1) {
                error_log("Forum RSS: Could not get thread subject to thread-ID ".$row_msg['thread_id'],0);
        }
        //category: Project name - Forum Name - Thread Name
        $item_cat = $row_msg['group_name']." - ".$row_msg['forum_name']." -- ".$row_thread['subject'];
        writeForumFeed($row_msg, $item_cat);
    }
}//end no $error_no_messages
else {
    displayError($error_no_messages);
}
endFeed();



//*********************** HELPER FUNCTIONS ***************************************
function beginForumFeed($feed_title, $feed_link, $feed_desc) {

	header("Content-Type: text/xml");
	print '<?xml version="1.0" encoding="UTF-8"?>
			<rss version="2.0">
			';
	print " <channel>\n";
	print "  <title>".$feed_title."</title>\n";
	print "  <link>".$feed_link."</link>\n";
	print "  <description>".$feed_desc."</description>\n";
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

function writeForumFeed($msg, $item_cat){
    global  $show_threads;

    $link = "forum/message.php?msg_id=".$msg['msg_id'];

    //------------ build one feed item ------------
    print "  <item>\n";
        print "   <title>".$msg['subject']."</title>\n";
        print "   <link>http://".forge_get_config('web_host')."/".$link."</link>\n";
        print "   <category>".$item_cat."</category>\n";
                //print "   <description>".rss_description($item_desc)."</description>\n";
        print "   <author>".$msg['user_realname']."</author>\n";
                //print "   <comment></comment>\n";
        print "   <pubDate>".gmdate('D, d M Y G:i:s',$msg['most_recent_date'])." GMT</pubDate>\n";
                //print "   <guid></guid>\n";
    print "  </item>\n";

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

//function taken from here http://de3.php.net/manual/de/function.array-unique.php#75307
function object_array_unique($array, $keep_key_assoc = false)
{
    $duplicate_keys = array();
    $tmp         = array();

    foreach ($array as $key=>$val)
    {
        // convert objects to arrays, in_array() does not support objects
        if (is_object($val))
            $val = (array)$val;

        if (!in_array($val, $tmp))
            $tmp[] = $val;
        else
            $duplicate_keys[] = $key;
    }

    foreach ($duplicate_keys as $key)
        unset($array[$key]);

    return $keep_key_assoc ? $array : array_values($array);
}
?>
