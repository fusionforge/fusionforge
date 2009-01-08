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

function authenticate ($public_keys_dir, &$username)
{
	$authenticated = false;
	header ("AuthResult: failure", true);
	unset ($auth_step);
	if (array_key_exists ("auth_step", $_POST) == true)
	{
		$auth_step = $_POST ["auth_step"];
	}
	if (isset ($auth_step) == true)
	{
		switch ($auth_step)
		{
			case 1 :
				$text = generateText (64);
				header ("AuthText: " . $text, true);
				break;
			case 2 :
				if (checkText ($public_keys_dir) == true)
				{
					$authenticated = true;
					header ("AuthResult: success", true);
					if (array_key_exists ("auth_username", $_POST) == true)
					{
						$username = $_POST ["auth_username"];
					}
					else
					{
						$username = "";
					}
				}
				break;
			default :
				syslog (LOG_ERR, __FILE__ . " - " .__FUNCTION__ .  " - Step value is unknown");
		}
	}
	else
	{
		syslog (LOG_ERR, __FILE__ . " - " .__FUNCTION__ .  " - Step is missing");
	}
	return $authenticated;
}

function generateText ($length)
{
	$text = "";
	$i = 0;
	while ($i < $length)
	{
		$index = mt_rand (0, 2);
		switch ($index)
		{
			// 0 -> 9
			case 0 :
				$min = 48;
				$max = 57;
				break;
			// A -> Z
			case 1 :
				$min = 65;
				$max = 90;
				break;
			// a -> z
			default :
				$min = 97;
				$max = 122;
		}
		$text .= chr (mt_rand ($min, $max));
		$i++;
	}
	$_SESSION ["auth_text"] = $text;
	return $text;
}

function checkText ($public_keys_dir)
{
	$ok = false;
	if (array_key_exists ("auth_text", $_SESSION) == true)
	{
		$auth_text_original = $_SESSION ["auth_text"];
		if (array_key_exists ("auth_text", $_POST) == true)
		{
			$auth_text_crypted = base64_decode ($_POST ["auth_text"]);
			if ($auth_text_crypted !== false)
			{
				if (array_key_exists ("auth_server", $_POST) == true)
				{
					$auth_server = $_POST ["auth_server"];
					$key_file = $public_keys_dir;
					if ($key_file [strlen ($key_file) - 1] != "/")
					{
						$key_file .= "/";
					}
					$key_file .= $auth_server;
					if ((is_file ($key_file) == true) && (is_readable ($key_file) == true))
					{
						$key = file_get_contents ($key_file);
						if ($key !== false)
						{
							if (openssl_public_decrypt ($auth_text_crypted, $auth_text_decrypted, $key) == true)
							{
								if (strcmp ($auth_text_original, $auth_text_decrypted) == 0)
								{
									$ok = true;
								}
								else
								{
									syslog (LOG_ERR, __FILE__ . " - " .__FUNCTION__ .  " - Decrypted text differs from original");
								}
							}
							else
							{
								syslog (LOG_ERR, __FILE__ . " - " .__FUNCTION__ .  " - Error while decrypting text");
							}
						}
						else
						{
							syslog (LOG_ERR, __FILE__ . " - " .__FUNCTION__ .  " - Error while reading key file '" . $key_file . "'");
						}
					}
					else
					{
						syslog (LOG_ERR, __FILE__ . " - " .__FUNCTION__ .  " - Key file '" . $key_file . "' is missing or unreadable");
					}
				}
				else
				{
					syslog (LOG_ERR, __FILE__ . " - " .__FUNCTION__ .  " - Server name is missing");
				}
			}
			else
			{
				syslog (LOG_ERR, __FILE__ . " - " .__FUNCTION__ .  " - Error while decoding crypted text");
			}
		}
		else
		{
			syslog (LOG_ERR, __FILE__ . " - " .__FUNCTION__ .  " - Crypted text is missing");
		}
	}
	else
	{
		syslog (LOG_ERR, __FILE__ . " - " .__FUNCTION__ .  " - Original text is missing");
	}
	return $ok;
}

?>
