<?php
/*
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

require_once ("common/novaforge/auth.php");
require_once ("common/novaforge/log.php");
require_once ("plugins/mantis/include/ResponseParser.class");

function createMantisProject ($url,
                              &$id,
                              $name,
                              $description,
                              $status,
                              $visibility)
{
	$ok = false;
	$xml_in = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><REQUEST><CREATE_PROJECT NAME=\"" . xmlEncodeString ($name) . "\" DESCRIPTION=\"" . encodeLineFeeds (xmlEncodeString ($description)) . "\" STATUS=\"" . getProjectStatusName ($status) . "\" VISIBILITY=\"" . getProjectVisibilityName ($visibility) . "\"/></REQUEST></MANTIS>";
	if (callMantis ($url, $xml_in, $xml_out) == true)
	{
		$parser = new ResponseParser ();
		if ($parser->parse ($xml_out) == true)
		{
			if ((isset ($parser->array_attributes ["RESPONSE"]) == true)
			&&  (isset ($parser->array_attributes ["RESPONSE"] ["STATUS"]) == true)
			&&  ($parser->array_attributes ["RESPONSE"] ["STATUS"] == "success"))
			{
				if ((isset ($parser->array_attributes ["PROJECT"]) == true)
				&&  (isset ($parser->array_attributes ["PROJECT"] ["ID"]) == true)
				&&  ($parser->array_attributes ["PROJECT"] ["ID"] > 0))
				{
					$ok = true;
					$id = $parser->array_attributes ["PROJECT"] ["ID"];
				}
				else
				{
					log_error ("The Mantis project identifier is missing or incorrect", __FILE__, __FUNCTION__);
				}
			}
			else
			{
				log_error ("Processing of request failed on Mantis server '" . $url . "': " . $parser->array_attributes ["RESPONSE"] ["MESSAGE"], __FILE__, __FUNCTION__);
			}
		}
	}
	return $ok;
}

function getMantisProject ($url,
                           $id,
                           &$name,
                           &$description,
                           &$status,
                           &$visibility)
{
	$ok = false;
	$xml_in = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><REQUEST><GET_PROJECT ID=\"" . $id . "\"/></REQUEST></MANTIS>";
	if (callMantis ($url, $xml_in, $xml_out) == true)
	{
		$parser = new ResponseParser ();
		if ($parser->parse ($xml_out) == true)
		{
			if ((isset ($parser->array_attributes ["RESPONSE"]) == true)
			&&  (isset ($parser->array_attributes ["RESPONSE"] ["STATUS"]) == true)
			&&  ($parser->array_attributes ["RESPONSE"] ["STATUS"] == "success"))
			{
				$ok = true;
				$name = $parser->array_attributes ["PROJECT"] ["NAME"];
				$description = decodeLineFeeds ($parser->array_attributes ["PROJECT"] ["DESCRIPTION"]);
				$status = getProjectStatusCode ($parser->array_attributes ["PROJECT"] ["STATUS"]);
				$visibility = getProjectVisibilityCode ($parser->array_attributes ["PROJECT"] ["VISIBILITY"]);
			}
			else
			{
				log_error ("Processing of request failed on Mantis server '" . $url . "': " . $parser->array_attributes ["RESPONSE"] ["MESSAGE"], __FILE__, __FUNCTION__);
			}
		}
	}
	return $ok;
}

function updateMantisProject ($url,
                              $id,
                              $name,
                              $description,
                              $status,
                              $visibility)
{
	$ok=false;
	$xml_in = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><REQUEST><UPDATE_PROJECT ID=\"" . $id . "\" NAME=\"" . xmlEncodeString ($name) . "\" DESCRIPTION=\"" . encodeLineFeeds (xmlEncodeString ($description)) . "\" STATUS=\"" . getProjectStatusName ($status) . "\" VISIBILITY=\"" . getProjectVisibilityName ($visibility) . "\"/></REQUEST></MANTIS>";
	if (callMantis ($url, $xml_in, $xml_out) == true)
	{
		$parser = new ResponseParser ();
		if ($parser->parse($xml_out) == true)
		{
			if ((isset ($parser->array_attributes ["RESPONSE"]) == true)
			&&  (isset ($parser->array_attributes ["RESPONSE"] ["STATUS"]) == true)
			&&  ($parser->array_attributes ["RESPONSE"] ["STATUS"] == "success"))
			{
				$ok = true;
			}
			else
			{
				log_error ("Processing of request failed on Mantis server '" . $url . "': " . $parser->array_attributes ["RESPONSE"] ["MESSAGE"], __FILE__, __FUNCTION__);
			}
		}
	}
	return $ok;
}

function deleteMantisProject ($id,
                              $mantis_url)
{
	$ok=false;
	$xml_in = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><REQUEST><REMOVE_PROJECT ID=\"$id\" /></REQUEST></MANTIS>";
	if (callMantis ($mantis_url, $xml_in, $xml_out))
	{
		$parser = new ResponseParser();
		if ($parser->parse($xml_out) == true)
		{
			if ((isset ($parser->array_attributes ["RESPONSE"]) == true)
			&&  (isset ($parser->array_attributes ["RESPONSE"] ["STATUS"]) == true)
			&&  ($parser->array_attributes ["RESPONSE"] ["STATUS"] == "success"))
			{
				$ok = true;
			}
			else
			{
				log_error ("Processing of request failed on Mantis server '" . $url . "': " . $parser->array_attributes ["RESPONSE"] ["MESSAGE"], __FILE__, __FUNCTION__);
			}
		}
	}
	return $ok;
}

function getMantisUsers ($url,
                         &$array_ids,
                         &$array_names,
                         &$array_passwords,
                         &$array_realnames,
                         &$array_mails,
                         &$array_statuses)
{
	$ok=false;
	$xml_in = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><REQUEST><GET_USERS/></REQUEST></MANTIS>";
	if (callMantis ($url, $xml_in, $xml_out) == true)
	{
		$parser = new ResponseParser ();
		if ($parser->parse ($xml_out) == true)
		{
			if ((isset ($parser->array_attributes ["RESPONSE"]) == true)
			&&  (isset ($parser->array_attributes ["RESPONSE"] ["STATUS"]) == true)
			&&  ($parser->array_attributes ["RESPONSE"] ["STATUS"] == "success"))
			{
				$ok = true;
				$array_ids = array ();
				$array_names = array ();
				$array_passwords = array ();
				$array_realnames = array ();
				$array_mails = array ();
				$array_statuses = array ();
				foreach ($parser->array_users as $user)
				{
					$array_ids [] = $user ["ID"];
					$array_names [] = $user ["NAME"];
					$array_passwords [] = $user ["PASSWORD"];
					$array_realnames [] = $user ["REALNAME"];
					$array_mails [] = $user ["MAIL"];
					$array_statuses [] = $user ["STATUS"];
				}
			}
			else
			{
				log_error ("Processing of request failed on Mantis server '" . $url . "': " . $parser->array_attributes ["RESPONSE"] ["MESSAGE"], __FILE__, __FUNCTION__);
			}
		}
	}
	return $ok;
}

function createMantisUser ($url,
                           $name,
                           $password,
                           $realname,
                           $mail,
                           $status)
{
	$ok= false;
	$xml_in = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><REQUEST><CREATE_USER NAME=\"" . $name . "\" PASSWORD=\"" . xmlEncodeString ($password) . "\" REALNAME=\"" . xmlEncodeString ($realname) . "\" MAIL=\"" . $mail . "\" STATUS=\"" . $status . "\"/></REQUEST></MANTIS>";
	if (callMantis ($url, $xml_in, $xml_out) == true)
	{
		$parser = new ResponseParser ();
		if ($parser->parse ($xml_out) == true)
		{
			if ((isset ($parser->array_attributes ["RESPONSE"]) == true)
			&&  (isset ($parser->array_attributes ["RESPONSE"] ["STATUS"]) == true)
			&&  ($parser->array_attributes ["RESPONSE"] ["STATUS"] == "success"))
			{
				$ok = true;
			}
			else
			{
				log_error ("Processing of request failed on Mantis server '" . $url . "': " . $parser->array_attributes ["RESPONSE"] ["MESSAGE"], __FILE__, __FUNCTION__);
			}
		}
	}
	return $ok;
}

function updateMantisUser ($url,
                           $id,
                           $name,
                           $password,
                           $realname,
                           $mail,
                           $status)
{
	$ok= false;
	$xml_in = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><REQUEST><UPDATE_USER ID=\"" . $id . "\" NAME=\"" . $name . "\" PASSWORD=\"" . xmlEncodeString ($password) . "\" REALNAME=\"" . xmlEncodeString ($realname) . "\" MAIL=\"" . $mail . "\" STATUS=\"" . $status . "\"/></REQUEST></MANTIS>";
	if (callMantis ($url, $xml_in, $xml_out) == true)
	{
		$parser = new ResponseParser();
		if ($parser->parse($xml_out) == true)
		{
			if ((isset ($parser->array_attributes ["RESPONSE"]) == true)
			&&  (isset ($parser->array_attributes ["RESPONSE"] ["STATUS"]) == true)
			&&  ($parser->array_attributes ["RESPONSE"] ["STATUS"] == "success"))
			{
				$ok = true;
			}
			else
			{
				log_error ("Processing of request failed on Mantis server '" . $url . "': " . $parser->array_attributes ["RESPONSE"] ["MESSAGE"], __FILE__, __FUNCTION__);
			}
		}
	}
	return $ok;
}

function getMantisRoles ($url,
                         $mantis_id,
                         &$array_roles)
{
	$ok = false;
	$xml_in = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><REQUEST><GET_ROLES PROJECT_ID=\"" . $mantis_id . "\"/></REQUEST></MANTIS>";
	if (callMantis ($url, $xml_in, $xml_out) == true)
	{
		$parser = new ResponseParser ();
		if ($parser->parse ($xml_out) == true)
		{
			if ((isset ($parser->array_attributes ["RESPONSE"]) == true)
			&&  (isset ($parser->array_attributes ["RESPONSE"] ["STATUS"]) == true)
			&&  ($parser->array_attributes ["RESPONSE"] ["STATUS"] == "success"))
			{
				if ((isset ($parser->array_attributes ["ROLES"]) == true)
				&&  (isset ($parser->array_attributes ["ROLES"] ["VALUES"]) == true))
				{
					$ok = true;
					$array_pairs = explode (",", $parser->array_attributes ["ROLES"] ["VALUES"]);
					$array_roles = array ();
					foreach ($array_pairs as $pair)
					{
						$pos = strpos ($pair, ":");
						if (($pos !== false) && ($pos > 0))
						{
							$key = trim (substr ($pair, 0, $pos));
							$val = trim (substr ($pair, $pos + 1));
							$array_roles [$key] = $val;
						}
						else
						{
							$ok = false;
							log_error ("Format of role '" . $pair . "' is incorrect", __FILE__, __FUNCTION__);
							break;
						}
					}
					if (asort ($array_roles) == false)
					{
						$ok = false;
						log_error ("Sort of roles failed", __FILE__, __FUNCTION__);
					}
				}
				else
				{
					log_error ("Mantis roles are missing", __FILE__, __FUNCTION__);
				}
			}
			else
			{
				log_error ("Processing of request failed on Mantis server '" . $url . "': " . $parser->array_attributes ["RESPONSE"] ["MESSAGE"], __FILE__, __FUNCTION__);
			}
		}
	}
	return $ok;
}

function setMantisRoles ($url,
                         $id,
                         $array_user_names,
                         $array_role_ids)
{
	$ok = false;
	$xml_in ="<MANTIS><REQUEST><SET_ROLES PROJECT_ID=\"" . $id ."\">";
	for ($index = 0; $index < count ($array_user_names); $index++)
	{
		$xml_in .= "<PAIR USER_NAME=\"" . $array_user_names [$index] . "\" ROLE_ID=\"" . $array_role_ids [$index] . "\"/>";
	}
	$xml_in .= "</SET_ROLES></REQUEST></MANTIS>";
	if (callMantis ($url, $xml_in, $xml_out) == true)
	{
		$parser = new ResponseParser ();
		if ($parser->parse ($xml_out) == true)
		{
			if ((isset ($parser->array_attributes ["RESPONSE"]) == true)
			&&  (isset ($parser->array_attributes ["RESPONSE"] ["STATUS"]) == true)
			&&  ($parser->array_attributes ["RESPONSE"] ["STATUS"] == "success"))
			{
				$ok = true;
			}
			else
			{
				log_error ("Processing of request failed on Mantis server '" . $url . "': " . $parser->array_attributes ["RESPONSE"] ["MESSAGE"], __FILE__, __FUNCTION__);
			}
		}
	}
	return $ok;
}

function getMantisBugs ($url,
                        $id,
                        $user_name,
                        &$array_bug_ids,
                        &$array_bug_summaries)
{
	$ok = false;
	$xml_in = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><REQUEST><GET_BUGS PROJECT_ID=\"" . $id ."\" USER_NAME=\"" . $user_name . "\"/></REQUEST></MANTIS>";
	if (callMantis ($url, $xml_in, $xml_out) == true)
	{
		$parser = new ResponseParser ();
		if ($parser->parse ($xml_out) == true)
		{
			if ((isset ($parser->array_attributes ["RESPONSE"]) == true)
			&&  (isset ($parser->array_attributes ["RESPONSE"] ["STATUS"]) == true)
			&&  ($parser->array_attributes ["RESPONSE"] ["STATUS"] == "success"))
			{
				$ok = true;
				$array_bug_ids = array ();
				$array_bug_summaries = array ();
				foreach ($parser->array_bugs as $bug)
				{
					$array_bug_ids [] = $bug ["ID"];
					$array_bug_summaries [] = $bug ["SUMMARY"];
				}
			}
			else
			{
				log_error ("Processing of request failed on Mantis server '" . $url . "': " . $parser->array_attributes ["RESPONSE"] ["MESSAGE"], __FILE__, __FUNCTION__);
			}
		}
	}
	return $ok;
}

function checkinMantisBugs ($url,
                            $user_name,
                            $bug_ids,
                            $comment)
{
	$ok = false;
	$xml_in = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><REQUEST><CHECKIN_BUGS USER_NAME=\"" . $user_name . "\" BUG_IDS=\"" . $bug_ids . "\" COMMENT=\"" . encodeLineFeeds (xmlEncodeString ($comment)) . "\"/></REQUEST></MANTIS>";
	if (callMantis ($url, $xml_in, $xml_out) == true)
	{
		$parser = new ResponseParser ();
		if ($parser->parse ($xml_out) == true)
		{
			if ((isset ($parser->array_attributes ["RESPONSE"]) == true)
			&& (isset ($parser->array_attributes ["RESPONSE"] ["STATUS"]) == true)
			&&  ($parser->array_attributes ["RESPONSE"] ["STATUS"] == "success"))
			{
				$ok = true;
			}
			else
			{
				log_error ("Processing of request failed on Mantis server '" . $url . "': " . $parser->array_attributes ["RESPONSE"] ["MESSAGE"], __FILE__, __FUNCTION__);
			}
		}
	}
	return $ok;
}

function callMantis ($mantis_url, $xml_in, &$xml_out)
{
	$ok = false;
	$mantis_url = trim ($mantis_url);
	if ($mantis_url [strlen ($mantis_url) - 1] != "/")
	{
		$mantis_url .= "/";
	}
	if (authenticate ($mantis_url . "gforge/auth.php", $cookies, null) == true)
	{
		$ch = curl_init ();
		if ($ch !== false)
		{
			curl_setopt ($ch, CURLOPT_URL, $mantis_url . "gforge/command.php");
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
			curl_setopt ($ch, CURLOPT_TIMEOUT, 30);
			if ((isset ($_SERVER) == true) && (array_key_exists ("HTTP_USER_AGENT", $_SERVER) == true))
			{
				curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER ["HTTP_USER_AGENT"]);
			}
			else
			{
				curl_setopt ($ch, CURLOPT_USERAGENT, "GForge");
			}
			curl_setopt ($ch, CURLOPT_POSTFIELDS, "xml=" . urlencode ($xml_in));
			$cookie = "";
			foreach ($cookies as $name => $array_value_and_expires)
			{
				$cookie .= $name . "=" . $array_value_and_expires [0] . ";";
			}
			if (strlen ($cookie) > 0)
			{
				curl_setopt ($ch, CURLOPT_COOKIE, $cookie);
			}
			$xml_out = curl_exec ($ch);
			if (($xml_out !== false) && (curl_errno ($ch) == 0))
			{
				$ok = true;
			}
			else
			{
				log_error ("The curl_exec() function failed for URL '" . $url . "' with CURL error " . curl_errno ($ch) . ": " . curl_error ($ch), __FILE__, __FUNCTION__);
			}
			curl_close ($ch);
		}
		else
		{
			log_error ("The curl_init() function failed", __FILE__, __FUNCTION__);
		}
	}
	return $ok;
}

function getProjectStatusName ($code)
{
	$name = "development";
	switch ($code)
	{
		case "R":
			$name = "release";
			break;
		case "S" :
			$name = "stable";
			break;
		case "O" :
			$name = "obsolete";
			break;
		case "D" :
		default :
			$name = "development";
	}
	return $name;
}

function getProjectVisibilityName ($code)
{
	$name = "public";
	if ($code == 0)
	{
		$name = "private";
	}
	return $name;
}

function getProjectStatusCode ($name)
{
	$code = "D";
	switch ($name)
	{
		case "release":
			$code = "R";
			break;
		case "stable" :
			$code = "S";
			break;
		case "obsolete" :
			$code = "O";
			break;
		case "development" :
		default :
			$code = "D";
	}
	return $code;
}

function getProjectVisibilityCode ($name)
{
	$code = "1";
	if ($name == "private")
	{
		$code = "0";
	}
	return $code;
}

function encodeLineFeeds ($string)
{
	$value = $string;
	if (strchr ($string, "\n") !== false)
	{
		$value = str_replace ("\n", "[LF]", $value);
	}
	return $value;
}

function decodeLineFeeds ($string)
{
	$value = $string;
	if (strchr ($string, "[LF]") !== false)
	{
		$value = str_replace ("[LF]", "\n", $value);
	}
	return $value;
}

function xmlEncodeString ($string)
{
	$value = $string;
	if (strchr ($string, "&") !== false)
	{
		$value = str_replace ("&", "&amp;", $value);
	}
	if (strchr ($string, "<") !== false)
	{
		$value = str_replace ("<", "&lt;", $value);
	}
	if (strchr ($string, ">") !== false)
	{
		$value = str_replace (">", "&gt;", $value);
	}
	if (strchr ($string, "'") !== false)
	{
		$value = str_replace ("'", "&apos;", $value);
	}
	if (strchr ($string, "\"") !== false)
	{
		$value = str_replace ("\"", "&quot;", $value);
	}
	return $value;
}

?>
