<?php
/**
 * MS Project Integration Facility
 *
 * Copyright 2004 GForge, LLC
 * http://fusionforge.org
 *
 * Provides some fuctions for Ms Project Plugin.
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

require_once $gfwww.'pm/msproject/msp.php';

$send_task_email=false;

if (getStringFromRequest('showform')) {
?>
	<html>
	<title>XML Parser</title>
	<body>
	<h2>XML Parser</h2>
	<p>
	<form name="xmlparser" method="post" action="<?php echo getStringFromServer('PHP_SELF'); ?>">
	Text: <br />
	<textarea name="document" cols="50" rows="10"></textarea>
	<br />
	<input type="hidden" name="parser" value="yes">
	<input type="submit" value="Parser">
	</form>
	<?php
} elseif (getStringFromRequest("parser") == "yes") {

	$data = getStringFromRequest("document");
	//$data = str$data);

} else {

	$ph = fopen("php://input", "rb");
	while (!feof($ph)) {
		$data .= fread($ph, 4096);
	}
	//$data=addslashes($data);

}
if (!$data) {
	echo "No Data";
	exit;
}

//printr($data,'initial-data');

// SECTION 1. DEBUG XML
$data = str_replace("</ ","</",$data);
$data = str_replace("</ ","</",$data);
$data = str_replace("\r","",$data);
printr($data,'next-data');
printr(getenv('TZ'),'xmlparser1:: TZ');

//SECTION 2. 
//FUNCTIONS AND VARIABLES
$result = array();
global $principal_tag;
global $principal_action;
global $subproject_id;
global $tag_aux;

//TASKS VARIABLES
global $tasks;
global $atasks;
global $task_id;
global $project_id;
global $parent_task_id;
global $parent_project_id;
global $isnew;
global $task_count;
global $resourcename;
global $fdependenton;
global $iddependenton;
global $adependenton;
global $projectiddependenton;
global $linktypedependenton;
$task_count = 0;
$tasks = false;
$fdependenton = false;

function startElement($parser, $name, $attrib){
    global $principal_tag;
    global $principal_action;
    global $subproject_id;
    global $task_id;
    global $project_id;
    global $parent_task_id;
    global $parent_project_id;
    global $isnew;
    global $task_count;
    global $tasks;
    global $fdependenton;
    global $result;
    global $tag_aux;
    global $iddependenton;
    global $projectiddependenton;
    global $linktypedependenton;
   	
    $tag_aux = $name;
	if ($tasks == true){
		//ID TASK
		if ($name == "TASK") {
/*			$task_id = $attrib["ID"];
			$project_id = $attrib["PROJECTID"];
			$parent_task_id = $attrib["PARENT_TASK_ID"];
			$parent_project_id = $attrib["PARENT_PROJECT_ID"];
			$isnew = $attrib["ISNEW"];
*/
			if ($fdependenton == true){
				$iddependenton = $attrib["ID"];
				$projectiddependenton = $attrib["PROJECTID"];
				$linktypedependenton= $attrib["LINKTYPE"];
			}
		}
		if ($name == "DEPENDENTON") {
			$fdependenton = true;
		}
		return;
	}
	
    switch ($name){
	    case $name=="REQUEST" : {
		    switch ($attrib["HANDLE"]){
				case $attrib["HANDLE"] == "GetSubprojects": {
					$principal_tag = $attrib["HANDLE"];
					$result[$name] = $principal_tag;
					break;
				}
				case $attrib["HANDLE"] == "upload": {
					$principal_tag = $attrib["HANDLE"];
					$result[$name] = $principal_tag;
					$result["ACTION"] = $attrib["ACTION"];;
					break;
				}
				case $attrib["HANDLE"] == "download": {
					$principal_tag = $attrib["HANDLE"];
					$result[$name] = $principal_tag;
					$result["ACTION"] = $attrib["ACTION"];
					break;
				}
				case $attrib["HANDLE"] == "GetProjects": {
					$principal_tag = $attrib["HANDLE"];
					$result[$name] = $principal_tag;
					break;
				}
				case $attrib["HANDLE"] == "CreateProject": {
					$principal_tag = $attrib["HANDLE"];
					$result[$name] = $principal_tag;
					$result["GROUPID"] = $attrib["GROUPID"];
					break;
				}
			}
	    	break;
    	}
    
	   	case $name=="SUBPROJECT" : {
			$subproject_id = $attrib["ID"];
			break;
		}
	
	   	case $name=="TASKS" : {
		   	$tasks = true;
			break;
		}
	}
}


function endElement($parser, $name) {
	global $task_count;
	global $iddependenton;
	global $fdependenton;
	global $result;
	global $atasks;
	global $resourcename;
	global $principal_tag;
	global $adependenton;
	global $result;

	if ($name == "TASK" && $principal_tag=="upload" && ($fdependenton == false)){
		$task_count++;
		$atasks["resources"] = $resourcename;
		$resourcename = array();
	}
	if ($name == "TASK" && $principal_tag=="upload" &&($fdependenton == false)) {
		$result["tasks"][] = $atasks;
		$atasks=array();
	}
	if ($name == "DEPENDENTON" && $principal_tag=="upload"){
		$fdependenton = false;
		$atasks["dependenton"] = $adependenton;
		$adependenton = array();
	}

}

function characterDataHandler ($parser, $data) {
	global $principal_tag;
	global $subproject_id;
	global $result;
	global $tag_aux;
	global $task_count;
	global $task_id;
	global $project_id;
	global $parent_task_id;
	global $parent_project_id;
	global $isnew;
	global $atasks;
	global $fdependenton;
	global $iddependenton;
	global $adependenton;
   	global $projectiddependenton;
   	global $linktypedependenton;
	global $resourcename;

	if (!$principal_tag) {return;}
	if (!$tag_aux) {return;}

	switch ($principal_tag){
		case $principal_tag == "GetSubprojects": {
			switch ($tag_aux){
				case $tag_aux == "LOGINID": {
					$result["loginid"] = $data;
					break;
				}
				case $tag_aux == "PASSWORD": {
					$result["password"] = $data;
					break;
				}
			}
			$tag_aux = "";
			break;
		}
		
		case $principal_tag == "download": {
			switch ($tag_aux) {
				case $tag_aux == "SESSION_ID": {
					$result["session_id"] = $data;
					break;
				}
				case $tag_aux == "SUBPROJECT": {
					$result["subproject"][] = array("id"=>$subproject_id,"name"=>$data);
					break;
				}
			}
			$tag_aux = "";
			break;
		}
		
		case  $principal_tag == "GetProjects": {
                        switch ($tag_aux) {
				case $tag_aux == "SESSION_ID": {
					$result["session_id"] = $data;
					break;
				}
			}
		}

		case  $principal_tag == "CreateProject": {
			switch ($tag_aux) {
				case $tag_aux == "SESSION_ID": {
					$result["session_id"] = $data;
					break;
				}
				case $tag_aux == "NAME": {
					$result["name"] = $data;
					break;
				}
				case $tag_aux == "ISPUBLIC": {
					$result["ispublic"] = $data;
					break;
				}
				case $tag_aux == "DESCRIPTION": {
					$result["description"] = $data;
					break;
				}
			}
		}

		case $principal_tag == "upload": {
			switch ($tag_aux) {
				case $tag_aux == "SESSION_ID": {
					$result["session_id"] = $data;
					break;
				}
				case $tag_aux == "SUBPROJECT": {
					$result["subproject"][] = array("id"=>$subproject_id,"name"=>$data);
					break;
				}
				case $tag_aux == "TASK": {
					if ($fdependenton == true) {
						$adependenton[] = array("task_id"=>$iddependenton,"msproj_id"=>$projectiddependenton,"link_type"=>$linktypedependenton,"task_name"=>$data);
					} else {
						$atasks = Array();
/*
						$atasks["id"] = $task_id;
						$atasks["msproj_id"] = $project_id;
						$atasks["parent_id"] = $project_task_id;
						$atasks["parent_msproj_id"] = $parent_project_id;
						$atasks["isnew"] = $isnew;
*/
					}
					break;
				}
				case $tag_aux == "ID": {
					$atasks["id"] = trim($data);
					break;
				}
				case $tag_aux == "PARENT_TASK_ID": {
					$atasks["parent_id"] = trim($data);
					break;
				}
				case $tag_aux == "PROJECTID": {
					$atasks["msproj_id"] = trim($data);
					break;
				}
				case $tag_aux == "PARENT_PROJECT_ID": {
					$atasks["parent_msproj_id"] = trim($data);
					break;
				}
				case $tag_aux == "ISNEW": {
					$atasks["isnew"] = trim($data);
					break;
				}
				case $tag_aux == "NAME": {
					$atasks["name"] = trim($data);
					break;
				}
				case $tag_aux == "WORK": {
					$atasks["work"] = trim($data);
					break;
				}
				case $tag_aux == "START_DATE": {
					$atasks["start_date"] = trim($data);
					break;
				}
				case $tag_aux == "END_DATE": {
					$atasks["end_date"] = trim($data);
					break;
				}
				case $tag_aux == "PERCENT_COMPLETE": {
					$atasks["percent_complete"] = trim($data);
					break;
				}
				case $tag_aux == "DURATION": {
					$atasks["duration"] = trim($data);
					break;
				}
				case $tag_aux == "PRIORITY": {
					$atasks["priority"] = trim($data);
					break;
				}
				case $tag_aux == "RESOURCENAME": {
					$resourcename[] = array("user_name"=>$data);
					break;
				}
				case $tag_aux == "NOTES": {
					$atasks["notes"] = trim($data);
					break;
				}
			}
			$tag_aux = "";
			break;
		}
	}
}
printr($foo2,'Starting XMLParse 1');
printr(getenv('TZ'),'xmlparser2:: TZ');
//SECTION 3. MAIN
$xml_parser = xml_parser_create();
xml_set_element_handler($xml_parser, "startElement", "endElement");
printr($foo2,'Starting XMLParse 2');
xml_set_character_data_handler( $xml_parser, "characterDataHandler");
printr($foo2,'Starting XMLParse 3');
if (!xml_parse($xml_parser, $data,true)) {
	printr($foo2,'Starting XMLParse 4');
	$err=sprintf("XML error: %s at line %d",
	xml_error_string(xml_get_error_code($xml_parser)),
	xml_get_current_line_number($xml_parser));
	printr($err,'Fatal Error');
	die($err);
}
xml_parser_free($xml_parser);

printr($result["REQUEST"],'request');
printr(getenv('TZ'),'xmlparser3:: TZ');
//SECTION 4. CALL GFORGE FUNCTIONS
switch ($result["REQUEST"]) {
	//MSPLogin
	case $result["REQUEST"] == "GetSubprojects": {
		$gforgeresult = MSPLogin($result["loginid"],$result["password"]);
		print('<?xml version="1.0"?>');
		print('<xml>');
		if ($gforgeresult["success"] == true) {
			print('<response handle="result">');
			print('<session_id>');
			print($gforgeresult["session_hash"]);
			print('</session_id>');
			$subprojects = $gforgeresult["subprojects"];
			print('<subprojects>');
			if (count($subprojects) > 0 ) {
				foreach($subprojects as $k => $v) {
					print('<subproject id ="'.$k.'">'.$v.'</subproject>');
				}
			}
			print('</subprojects>');
		} else {
			print('<response handle="error">');
			print('<error><description>'.$gforgeresult["errormessage"].'</description></error>');
		}
		print('</response>');
		print('</xml>');
		break;
	}
	//MSPDownload
	case $result["REQUEST"] == "download": {
		if ((trim($result["ACTION"]) == "GetLatestVersion") || (trim($result["ACTION"]) == "Checkout")) {
			$gforgeresult =& MSPDownload($result["session_id"],$result["subproject"][0]["id"]);
			printr($gforgeresult,'gforgeresult');
			$return = ('<?xml version="1.0"?>');
			$return .= ('<xml>');
			if ($gforgeresult["success"] == true) {
				$return .= ('<response handle="result">');
				$tasks =& $gforgeresult["tasks"];
				$return .= ('<sync_time>'.date('Y-m-d H:i:s').'</sync_time>');
				$return .= ('<tasks>');
				for ($tc=0; $tc<count($tasks); $tc++) {
					$task = $tasks[$tc];
					$return .= ('<task id ="'.$task->getID().'">');
					$return .= ('<projectid>'.$task->getExternalID().'</projectid>');
					$return .= ('<name>'.$task->getSummary().'</name>');
					$return .= ('<start_date>'.date('Y-m-d H:i:s',$task->getStartDate()).'</start_date>');
					$return .= ('<end_date>'.date('Y-m-d H:i:s',$task->getEndDate()).'</end_date>');
					$return .= ('<work>'.$task->getHours().'</work>');
					$return .= ('<duration>'.$task->getDuration().'</duration>');
					$return .= ('<percent_complete>'.$task->getPercentComplete().'</percent_complete>');
					$return .= ('<priority>'.$task->getPriority().'</priority>');
					$return .= ('<lastmodified>');
					if ($task->getLastModifiedDate() != "") {
						$return .= (date('Y-m-d H:i:s',$task->getLastModifiedDate()));
					}
					$return .= ('</lastmodified>');
					$users =& user_get_objects($task->getAssignedTo());
					if (count($users) == 1 && $users[0]->getID()==100) {
						//skip if only one user - the 100 user
					} else {
						$return .= ('<resources>');
						for ($i=0; $i<count($users); $i++) {
							$return .= ('<resourcename>'.$users[$i]->getUnixName().'</resourcename>');
						}
						$return .= ('</resources>');
					}
					$dependenton =& $task->getDependentOn();
					if (count($dependenton) == 1 && $dependenton[100]) {
						//skip if only one user - the 100 user
					} else {
						$return .= ('<dependenton>');
						reset($dependenton);
						while (list ($id, $link_type) = each ($dependenton)) {
							$return .= ('<task id="'.$id.'" linktype="'.$link_type.'"></task>');
						}
						$return .= ('</dependenton>');
					}
					$return .= ('<notes>'.$task->getDetails().'</notes>');
					$return .= ('</task>');
				}
				$return .= ('</tasks>');
				printr($return,'download XML');
				print $return;
			} else {
				print($return.'<response handle="error">');
				print('<error><description>'.$gforgeresult["errormessage"].'</description></error>');
			}
			print('</response>');
			print('</xml>');
		}
		break;
	}
	//MSPCheckin
	case $result["REQUEST"] == "upload": {
		if (trim($result["ACTION"]) == "Checkin") {
			$gforgeresult = MSPCheckin($result["session_id"],$result["subproject"][0]["id"],$result["tasks"]);
			print('<?xml version="1.0"?>');
			print('<xml>');
			if (!isset($gforgeresult["success"]) || ($gforgeresult["success"] == false)) {
				if ($gforgeresult["errormessage"] == "Invalid Resource Name") {
					print('<response handle="mapuser">');
					$resourcenames = $gforgeresult["resourcename"];
					print('<resourcenames>');
					if (count($resourcenames) > 0 ) {
						foreach($resourcenames as $k => $resourcename) {
							print('<resourcename>'.$resourcename.'</resourcename>');
						}
					}
					print('</resourcenames>');
					print('<usernames>');
					$usernames = $gforgeresult["usernames"];
					if (count($usernames) > 0 ) {					
						foreach($usernames as $k => $username) {
							print('<user id ="'.$k.'">'.$username.'</user>');
						}
					}
					print('</usernames>');
				} else {
					print('<response handle="error">');
					print('<error>');
					print('<description>'.$gforgeresult["errormessage"].'</description>');
					print('</error>');
				}
				print('</response>');
			} else {
				print ('<response handle="result">success</response>');
			}
			print('</xml>');
		}
		if (trim($result["ACTION"]) == "Undo") {
			print('<?xml version="1.0"?>');
			print('<xml>');
				print ('<response handle="result">success</response>');
			print('</xml>');
		//call MSPUNDO($result["session_id"],$result["subproject"]["id"]);
		}
		break;
	}

//GetProjects
case $result["REQUEST"] == "GetProjects": {
	//Call GetProjects
	$gforgeresult = MSPGetProjects($result["session_id"]);
	print('<?xml version="1.0"?>');
	print('<xml>');
	if ($gforgeresult) {
		print('<response handle="result">');
		print('<projects>');
		for ($c=0;$c<count($gforgeresult);$c++) {
			$cgroup=$gforgeresult[$c];
			print('<project id ="'.$cgroup->getID().'">'.$cgroup->getPublicName().'</project>');
		}
		print('</projects>');
	} else {
		print('<response handle="error">');
		print('<error>');
		print('<description>Not a member of any projects</description>');
		print('</error>');
	}
	print('</response>');
	print('</xml>');
	break;
}		

case $result["REQUEST"] == "CreateProject": {
	if($result["ispublic"]==1 || $result["ispublic"]==true) {
		$result["ispublic"]=1;
	} else { 
		$result["ispublic"]=0;
	}
	$gforgeresult = MSPCreateProject($result["GROUPID"],$result["session_id"],$result["name"],$result["ispublic"],$result["description"]);
	print('<?xml version="1.0"?>');
	print('<xml>');
	if (!is_object($gforgeresult) && $gforgeresult['code'] == "error") {
		print('<response handle="error">');
		print('<error>');
		print('<description>'.$gforgeresult['description'].'</description>');
		print('</error>');
	} else { 
		print('<response handle="result">');
		print('<Group_ID>'.$result["GROUPID"].'</Group_ID>');
		print('<Group_Project_ID>'.$gforgeresult->getID().'</Group_Project_ID>');
	}
	print('</response>');
	print('</xml>');
	break;
}
}

printrcomplete();


?>
