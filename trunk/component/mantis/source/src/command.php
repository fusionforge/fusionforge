<?php
/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
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
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once ("RequestParser.class");
require_once ("functions.php");

session_start ();
error_reporting (E_ALL);
openlog ("mantis", LOG_PID | LOG_PERROR, LOG_LOCAL5);
$ok = true;
$message = "";
if (array_key_exists ("gforge_authenticated", $_SESSION) == true)
{
	if ($_SESSION ["gforge_authenticated"] != 1)
	{
		$ok = false;
		$message = "Not authenticated";
	}
}
else
{
	$ok = false;
	$message = "Authentication has not been called";
}
if ($ok == true)
{
	if (array_key_exists ("xml", $_POST) == false)
	{
		$ok = false;
		$message = "The request does not contain the 'xml' parameter";
	}
}
if ($ok == true)
{
	if (get_magic_quotes_gpc () == 0)
	{
		$xml_in = $_POST ["xml"];
	}
	else
	{
		$xml_in = stripslashes ($_POST ["xml"]);
	}
	$encoding = mb_detect_encoding ($xml_in);
	if ($encoding != lang_get ("charset"))
	{
		$xml_in = mb_convert_encoding ($xml_in, lang_get ("charset"), $encoding);
	} 
	$parser = new RequestParser ();
	if ($parser->parse ($xml_in) == true)
	{
		switch ($parser->command)
		{
			case "CREATE_PROJECT" :
				$xml_out = createProject ($parser->array_attributes ["CREATE_PROJECT"] ["NAME"],
				                          $parser->array_attributes ["CREATE_PROJECT"] ["DESCRIPTION"],
				                          $parser->array_attributes ["CREATE_PROJECT"] ["STATUS"],
				                          $parser->array_attributes ["CREATE_PROJECT"] ["VISIBILITY"]);
				break;
			case "GET_ROLES" :
				$xml_out = getRoles ();
				break;
			case "GET_BUGS" :
				$xml_out = getBugs ($parser->array_attributes ["GET_BUGS"] ["PROJECT_ID"],
				                    $parser->array_attributes ["GET_BUGS"] ["USER_NAME"]);
				break;
			case "GET_PROJECT" :
				$xml_out = getProject ($parser->array_attributes ["GET_PROJECT"] ["ID"]);
				break;
			case "UPDATE_PROJECT" :
				$xml_out = updateProject ($parser->array_attributes ["UPDATE_PROJECT"] ["ID"],
				                          $parser->array_attributes ["UPDATE_PROJECT"] ["NAME"],
				                          $parser->array_attributes ["UPDATE_PROJECT"] ["DESCRIPTION"],
				                          $parser->array_attributes ["UPDATE_PROJECT"] ["STATUS"],
				                          $parser->array_attributes ["UPDATE_PROJECT"] ["VISIBILITY"]);
				break;
			case "REMOVE_PROJECT" :
				$xml_out = removeProject ($parser->array_attributes ["REMOVE_PROJECT"] ["ID"]);
				break;
			case "GET_USERS" :
				$xml_out = getUsers ();
				break;
			case "CREATE_USER" :
				$xml_out = createUser ($parser->array_attributes ["CREATE_USER"] ["NAME"], 
				                       $parser->array_attributes ["CREATE_USER"] ["PASSWORD"],
				                       $parser->array_attributes ["CREATE_USER"] ["REALNAME"],
				                       $parser->array_attributes ["CREATE_USER"] ["MAIL"],
				                       $parser->array_attributes ["CREATE_USER"] ["STATUS"]);
				break;
			case "UPDATE_USER" :
				$xml_out = updateUser ($parser->array_attributes ["UPDATE_USER"] ["ID"],
				                       $parser->array_attributes ["UPDATE_USER"] ["NAME"], 
				                       $parser->array_attributes ["UPDATE_USER"] ["PASSWORD"],
				                       $parser->array_attributes ["UPDATE_USER"] ["REALNAME"],
				                       $parser->array_attributes ["UPDATE_USER"] ["MAIL"],
				                       $parser->array_attributes ["UPDATE_USER"] ["STATUS"]);
				break;
			case "SET_ROLES" :
				$xml_out = setRoles ($parser->array_attributes ["SET_ROLES"] ["PROJECT_ID"],
				                     $parser->array_roles);
				break;
			case "CHECKIN_BUGS" :
				$xml_out = checkinBugs ($parser->array_attributes ["CHECKIN_BUGS"] ["USER_NAME"],
				                        $parser->array_attributes ["CHECKIN_BUGS"] ["BUG_IDS"],
				                        $parser->array_attributes ["CHECKIN_BUGS"] ["COMMENT"]);
				break;
			default :
				$ok = false;
				$message = "The '" . $parser->command . "' command is not implemented";
		}
	}
	else
	{
		$ok = false;
		$message = "An error occured while parsing the request";
	}
}
if ($ok == false)
{
	syslog (LOG_ERR, __FILE__ . " - " . $message);
	$xml_out = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><RESPONSE STATUS=\"failure\" MESSAGE=\"" . xmlEncodeString ($message) . "\"/></MANTIS>";
}
if (lang_get ("charset") != "UTF-8")
{
	$xml_out = mb_convert_encoding ($xml_out, "UTF-8", lang_get ("charset"));
}
header ("Content-Type: text/xml; charset=UTF-8");
echo $xml_out;
?>
