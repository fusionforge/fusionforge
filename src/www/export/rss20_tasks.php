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


require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'export/rss_utils.inc';



//Default Vars
$number_items = 10;
$max_number_items = 100;
$show_threads = true;
$number=10;
$max_number=100;
$additive=' AND ';
$btwg='';
$btwp='';
$project='';
$us='';

//group and project and user, or group or project or user?
//take care: status means AND to all (user or group, but AND status)
if (isset($_GET['OR']))
{
	$additive=' OR ';
}

$user_arr=handle_getvar('user_ids');
if(isset($user_arr[0]))
{
	foreach($user_arr AS $single_user_id)
	{
		$user.=" OR (a.assigned_to_id = '".$single_user_id."')";
	}
	$user='('.substr($user,4).')';
}
//group_ids
$projects=array();
$groups = handle_getvar('group_ids');
if(isset($groups[0])) //die projekte der gruppen werden in $projects gespeichert
{
	foreach($groups AS $group)
	{
		$sql="SELECT group_project_id FROM project_group_list WHERE group_id='".$group."'";
		$res=pg_query($sql);
		while($row=db_fetch_array($res))
		{
			$projects[]=$row['group_project_id'];
		}
	}
}
$p=handle_getvar('group_project_ids');
$projects = array_unique(array_merge($projects,$p)); //die projekte der getvars kommen dazu
$project_sq = '' ;
if(isset($projects[0]))
{
	foreach($projects AS $project)
	{
		$project_sq.=" OR (group_project_id = '".$project."')";
		/*$sql="SELECT project_name,group_id FROM project_group_list WHERE group_project_id='".$project."'";
		$res=pg_query($sql);
		if(pg_num_rows($res)==0)
		{
			$project_c[$project]['project_name']='Wrong or deleted project';
			$project_c[$project]['group_id']='0';
		} else
		{
			$project_c[$project]=db_fetch_array($res);
		}
		if(!isset($group_c[$project_c[$project]['group_id']]))
		{
			$sql="SELECT group_name FROM groups WHERE group_id='".$project_c[$project]['group_id']."'";
			$res2=pg_query($sql);
			if(pg_num_rows($res2)==0)
			{
				$group_c[$project_c[$project]['group_id']]='Wrong or deleted group';
			} else
			{
				$a=db_fetch_array($res2);
				$group_c[$project_c[$project]['group_id']]=$a['group_name'];
			}
		}*/
		
	}
$project_sq='('.substr($project_sq,4).')';
}

foreach(handle_getvar('status_ids') AS $status_id)
{
	$status.=" OR (status_id = '".$status_id."')";
}
if(isset($status))
{
	$status='('.substr($status,4).')';
}

//important for correct sql-syntax
if(!empty($status))
{
	$status=' AND '.$status;
}
if(!empty($project_sq) OR !empty($user) OR !empty($status))
{
	$us="AND ";
}
if(!empty($project_sq) AND !empty($user))
{
	$btwp=$additive;
}

//calculates number of shown 
if (isset($_GET['number']) AND ctype_digit($_GET['number']))
{
	if($_GET['number']<=$max_number AND $_GET['number']>0)
	{
		$number=$_GET['number'];
	} elseif($_GET['number']>$max_number)
	{
		$number=$max_number;
	}
}

//creating, sending, and using the query

$sql="
	SELECT DISTINCT
		pt.*,u.realname AS user_realname
	FROM
		project_task pt,users u,project_assigned_to a
	WHERE
		".is_needed('(').$project_sq." ".$btwp."
		".$user." 
		".$status.is_needed(')')."
		".$us."u.user_id=pt.created_by
		AND pt.project_task_id=a.project_task_id
	ORDER BY
		last_modified_date
	LIMIT
		".$number.";";
$res=pg_query($sql);
$i=0;

beginTaskFeed('evolvis: Current Tasks',forge_get_config('web_host'),'See all the tasks you want to see!');
if(0<pg_num_rows($res))
{
	while($i<pg_num_rows($res))
	{
		$sql1="SELECT group_id,project_name FROM project_group_list WHERE group_project_id='".pg_fetch_result($res,$i,'group_project_id')."'";
		$res1=pg_query($sql1);
		if(pg_num_rows($res1)==1)
		{
			$row1=db_fetch_array($res1);
			$project_c[pg_fetch_result($res,$i,'group_project_id')]['group_id']=$row1['group_id'];
			if(isset($row1['project_name']))
			{
				$project_c[pg_fetch_result($res,$i,'group_project_id')]['project_name']=$row1['project_name'];
			} else
			{
				$project_c[pg_fetch_result($res,$i,'group_project_id')]['project_name']='Wrong or deleted project';
			}
			$sql2="SELECT group_name FROM groups WHERE group_id='".$row1['group_id']."'";
			$res2=pg_query($sql2);
			$row2=db_fetch_array($res2);
			if(isset($row2['group_name']))
			{
				$group_c[$row1['group_id']]=$row2['group_name'];
			} else
			{
				$group_c[$row1['group_id']]='Wrong or deleted group';
			}
	
	
	
			$item_cat = $group_c[$project_c[pg_fetch_result($res,$i,'group_project_id')]['group_id']]." - ".$project_c[pg_fetch_result($res,$i,'group_project_id')]['project_name']." -- ".pg_fetch_result($res,$i,'summary');
			$ar['project_task_id']=pg_fetch_result($res,$i,'project_task_id');
			$ar['group_project_id']=pg_fetch_result($res,$i,'group_project_id');
			$ar['group_id']=$project_c[pg_fetch_result($res,$i,'group_project_id')]['group_id'];
			$ar['most_recent_date']=pg_fetch_result($res,$i,'last_modified_date');
			$ar['subject']=pg_fetch_result($res,$i,'summary');
			$ar['user_realname']=pg_fetch_result($res,$i,'user_realname');
			$ar['details']=pg_fetch_result($res,$i,'details');
			writeTaskFeed($ar,$item_cat);
		}
		$i++;
	}
} else
{
	displayError('No tasks found! Please check for invalid params.');
}
endFeed();




//*********************** HELPER FUNCTIONS ***************************************

function is_needed($str)
{
	global $project_sq,$user,$status;
	if(!empty($project_sq) OR !empty($user) OR !empty($status))
	{
		return $str;
	} else
	{
		return '';
	}
}
function handle_getvar($name)
{
	$return = array();
	if(isset($_GET[$name]))
	{
		$vars = array_unique(explode(" ",$_GET[$name]));
		foreach ($vars as $var)
		{
			if(ctype_digit($var))
			{
				$return[]=$var;
			}
		}
	}
	return $return;
}


function beginTaskFeed($feed_title, $feed_link, $feed_desc) {

	header("Content-Type: text/xml");
	print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	print "<rss version=\"2.0\">\n";
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

function writeTaskFeed($msg, $item_cat){
    global  $show_threads;
            
    //------------ build one feed item ------------
    print "  <item>\n";					
        print "   <title>".$msg['subject']."</title>\n"; 
        print "   <link>" . util_make_url("/pm/t_follow.php/" . $msg['project_task_id']) . "</link>\n";
        print "   <category>".$item_cat."</category>\n";
        print "   <description>".$msg['details']."</description>\n";
        print "   <author>".$msg['user_realname']."</author>\n";
                //print "   <comment></comment>\n";
        print "   <pubDate>".gmdate('D, d M Y G:i:s',$msg['most_recent_date'])." GMT</pubDate>\n";
	print "   <guid>" . util_make_url("/pm/t_lookup.php?tid=" . $msg['project_task_id']) . "</guid>\n";
    print "  </item>\n";

}


function displayError($errorMessage) {
	print "  <title>Error</title>\n".
		"  <description>".$errorMessage."</description>";
}

function endFeed() {
    print "\n </channel>\n</rss>";
    exit();
}

function endOnError($errorMessage) {
	displayError($errorMessage);
	endFeed();
}
?>
