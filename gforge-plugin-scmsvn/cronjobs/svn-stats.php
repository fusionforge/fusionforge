<?php
/**
* This file is part of GForge.
* 
* This is a translation if svn-stats.pl
*
* GForge is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* GForge is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with GForge; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
*/
require ('squal_pre.php');
require ('common/include/cron_utils.php');

class SVNGroup
{
	var $group_id;
	var $lastdate;
	var $lastrev;
	var $groupname;
	var $alreadyseen;
	 
	function SVNGroup($group_id, $lastdate, $lastrev, $groupname, $alreadyseen){
		$this->group_id		= $group_id;
		$this->lastdate		= $lastdate;
		$this->lastrev 		= $lastrev;
		$this->groupname 	= $groupname;
		$this->alreadyseen = $alreadyseen;
	 }
	 
	function getGroup_id(){
		return $group_id; 
	}
	function getlastdate(){
		return $lastdate;
	}
	function getlastrev(){
		return $lastrev;
	}
	function getgroupname(){
		return $groupname;
	}
	function getalreadyseen(){
		return $alreadyseen;
	}
	 
	function setGroup_id($group_id){
		$this->group_id = $group_id;
	}
	function setlastdate($lastdate){
		$this->lastdate = $lastdate;
	}
	function setlastrev($lastrev){
		$this->lastrev = $lastrev;
	}
	function setgroupname($groupname){
		$this->groupname = $groupname;
	}
	function setalreadyseen($alreadyseen){
		$this->alreadyseen = $alreadyseen;
	}
	 
};

$pluginname = "scmsvn" ;

db_begin();

$pluginid = get_plugin_id($pluginname);

$groups = array();
  
 
$res = db_query("SELECT group_plugin.group_id, groups.unix_group_name
			FROM group_plugin, groups
			WHERE group_plugin.plugin_id = $pluginid
			AND group_plugin.group_id = groups.group_id");

if (!$res) {
	$err .=  "Error! Database Query Failed: ".db_error();
	echo $err;
	db_rollback();
	exit;
}

while ( $row =& db_fetch_array($res) ) {
	$svn = new SVNGroup($row[0], 0, 0, $row[1], 0);
	$groups[$row[0]] = $svn;
}

$res = db_query("SELECT group_id, last_check_date, last_repo_version
				FROM plugin_scmsvn_stats");
             
if (!$res) {
	$err .=  "Error! Database Query Failed: ".db_error();
	echo $err;
	db_rollback();
	exit;
}

while ( $row =& db_fetch_array($res) ) {
	$groups[$row[0]]->setlastdate($row[1]);
	$groups[$row[0]]->setlastrev($row[2]);
	$groups[$row[0]]->setalreadyseen(1);
}
 
foreach ($groups as $group){
	$svnroot = "/var/lib/gforge/chroot/svnroot/" . $group->getgroupname();	
	$currev = shell_exec( "svnlook youngest ". $svnroot ) ;
	$adds = 0 ;
	$deletes = 0 ;
	$updates = 0 ;
	$commits = 0 ;
	$rev = $group->getlastrev() + 1 ;
		
	while ($rev <= $currev){
		$commits++;	
		$output = shell_exec("svnlook changed -r$rev $svnroot |");
		$lines = explode("\n", $output);
		foreach ($lines as $line) {
			if (!$line == "") {
				if(substr($line,0,1) == "A")
					$adds++;
				if(substr($line,0,1) == "D")
					$deletes++;
				if(substr($line,0,1) == "U")
					$updates++;
			}
		}
		$rev++;
	}
		
	$time = time();
	if ($group->getalreadyseen()) {
		$query = "UPDATE plugin_scmsvn_stats
			SET last_repo_version = " .$currev .",
			adds = " .$adds. ",
			deletes = " .$deletes . ",
			commits = " .$commits . ",
			changes = " .$updates . ",
			last_check_date = ". $time . "
			WHERE group_id = " .$group_id ;
	} else {
		$query = "INSERT INTO plugin_scmsvn_stats
			(last_repo_version, last_check_date, adds, deletes, commits, changes, group_id)
			VALUES (".$currev.", ".$time.", ".$adds.", ".$deletes.", ".$commits.", ".$updates.", ".$group_id.")";
	}
	  
	$res = db_query($query);
	  
	if (!$res) {
		$err .=  "Error! Database Query Failed: ".db_error();
		echo $err;
		db_rollback();
		exit;
	}
}

 
db_commit();

function get_plugin_id($pluginname){
	$res = db_query("SELECT plugin_id FROM plugins WHERE plugin_name = '".$pluginname."'");	
	if (!$res) {
		$err .=  "Error! Database Query Failed: ".db_error();
		echo $err;
		db_rollback();
		exit;
	}
	if ($row =& db_fetch_array($res)) {
		$plugin_id = $row[0];
	}
 
	return $plugin_id;
}
?>
