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

require_once ("plugins/apibull/config.php");
require_once ("common/novaforge/log.php");

/*
 * Authenticate against an URL
 */
function authenticate ($url, &$array_http_cookies, $username)
{
	global $sys_default_domain;

	$ok = false;
	if (callUrl ($url,
	             array ("auth_step" => 1),
                     $http_error_code,
                     $http_content,
	             $array_http_headers,
	             $array_http_cookies) == true)
	{
		if ($http_error_code == 200)
		{
			$text = "";
			if (array_key_exists ("authtext", $array_http_headers) == true)
			{
				$text = trim ($array_http_headers ["authtext"] [0]);
			}
			if (strlen ($text) > 0)
			{
				if (encryptText ($text, $encrypted_text) == true)
				{
					$http_parameters = array ("auth_step" => 2,
					                          "auth_text" => base64_encode ($encrypted_text),
					                          "auth_server" => $sys_default_domain,
					                          "auth_username" => $username);
					if (callUrl ($url,
					             $http_parameters,
					             $http_error_code,
					             $http_content,
					             $array_http_headers,
					             $array_http_cookies) == true)
					{
						if ($http_error_code = 200)
						{
							if (array_key_exists ("authresult", $array_http_headers) == true)
							{
								$result = trim ($array_http_headers ["authresult"] [0]);
								if ($result == "success")
								{
									$ok = true;
								}
								else
								{
									if ($result == "failure")
									{
										log_error ("Authentication failed", __FILE__, __FUNCTION__);
									}
									else
									{
										log_error ("Unknown authentication result", __FILE__, __FUNCTION__);
									}
								}
							}
							else
							{
								log_error ("Authentication result is missing or empty", __FILE__, __FUNCTION__);
							}
						}
						else
						{
							log_error ("HTTP error: " . $http_error_code, __FILE__, __FUNCTION__);
						}
					}
				}
			}
			else
			{
				log_error ("Authentication text is missing or empty", __FILE__, __FUNCTION__);
			}
		}
		else
		{
			log_error ("HTTP error: " . $http_error_code, __FILE__, __FUNCTION__);
		}
	}
	return $ok;
}

/*
 * Encrypt a text with the private key
 */
function encryptText ($text, &$encrypted_text)
{
	global $sys_auth_private_key_file,
	       $sys_auth_private_key_passphrase_file,
	       $sys_auth_private_key_passphrase_header;

	$ok = false;
	$passphrase = "";
	if ((is_file ($sys_auth_private_key_passphrase_file) == true) && (is_readable ($sys_auth_private_key_passphrase_file) == true))
	{
		$passphrase = file_get_contents ($sys_auth_private_key_passphrase_file);
		if ($passphrase === false)
		{
			log_error ("Error while reading passphrase file '" . $sys_auth_private_key_passphrase_file . "'", __FILE__, __FUNCTION__);
			$passphrase = "";
		}
		else
		{
			$passphrase = trim ($passphrase);
			if (strlen ($passphrase) <= 0)
			{
				log_error ("Passphrase file '" . $sys_auth_private_key_passphrase_file . "' is empty", __FILE__, __FUNCTION__);
			}
		}
	}
	else
	{
		log_debug ("Passphrase file '" . $sys_auth_private_key_passphrase_file . "' is missing or unreadable", __FILE__, __FUNCTION__);
	}
	if (strlen ($passphrase) <= 0)
	{
		if (strlen ($sys_auth_private_key_passphrase_header) > 0)
		{
			$headers = apache_request_headers ();
			if (array_key_exists ($sys_auth_private_key_passphrase_header, $headers) == true)
			{
				$passphrase = trim ($headers [$sys_auth_private_key_passphrase_header]);
				if (strlen ($passphrase) <= 0)
				{
					log_error ("Value of passphrase header '" . $sys_auth_private_key_passphrase_header . "' is empty", __FILE__, __FUNCTION__);
				}
			}
			else
			{
				log_error ("Passphrase header '" . $sys_auth_private_key_passphrase_header . "' is missing", __FILE__, __FUNCTION__);
			}
		}
		else
		{
			log_error ("Name of passphrase header is empty", __FILE__, __FUNCTION__);
		}
	}
	if (strlen ($passphrase) > 0)
	{
		if ((is_file ($sys_auth_private_key_file) == true) && (is_readable ($sys_auth_private_key_file) == true))
		{
			$key_file_content = file_get_contents ($sys_auth_private_key_file);
			if ($key_file_content === false)
			{
				log_error ("Error while reading key file '" . $sys_auth_private_key_file . "'", __FILE__, __FUNCTION__);
			}
			else
			{
				if (strlen ($key_file_content) <= 0)
				{
					log_error ("Key file '" . $sys_auth_private_key_file . "' is empty", __FILE__, __FUNCTION__);
				}
				else
				{
					$ok = true;
				}
			}
		}
		else
		{
			log_error ("Key file '" . $sys_auth_private_key_file . "' is missing or unreadable", __FILE__, __FUNCTION__);
		}
	}
	if ($ok == true)
	{
		$ok = false;
		$key = openssl_pkey_get_private ($key_file_content, $passphrase);
		if ($key === false)
		{
			log_error ("Error while getting key", __FILE__, __FUNCTION__);
		}
		else
		{
			if (openssl_private_encrypt ($text, $encrypted_text, $key) == false)
			{
				log_error ("Error while encrypting data with key", __FILE__, __FUNCTION__);
			}
			else
			{
				$ok = true;
			}
		}
	}
	return $ok;
}

/*
 * Call an URL
 */
function callUrl ($url,
                  $http_parameters,
                  &$http_error_code,
                  &$http_content,
                  &$array_http_headers,
                  &$array_http_cookies)
{
	$ok = false;
	$ch = curl_init ();
	if ($ch !== false)
	{
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_HEADER, true);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $http_parameters);
		if (count ($array_http_cookies) > 0)
		{
			$cookie = "";
			foreach ($array_http_cookies as $name => $value_and_expires)
			{
				$cookie .= $name . "=" . $value_and_expires [0] . ";";
			}
			curl_setopt ($ch, CURLOPT_COOKIE, $cookie);
		}
		$result = curl_exec ($ch);
		if (($result !== false) &&  (curl_errno ($ch) == 0))
		{
			$ok = true;
			$http_error_code = curl_getinfo ($ch, CURLINFO_HTTP_CODE);
			$headers_size = curl_getinfo ($ch, CURLINFO_HEADER_SIZE);
			$headers = substr ($result, 0, $headers_size);
			$http_content = substr ($result, $headers_size);
			$lines = explode ("\n", $headers);
			$array_http_headers = array ();
			foreach ($lines as $line)
			{
				$result = strpos ($line, ":");
				if (($result !== false) && ($result > 0))
				{
					$name = strtolower (trim (substr ($line, 0, $result)));
					$value = trim (substr ($line, $result + 1));
					if (array_key_exists ($name, $array_http_headers) == false)
					{
						$array_http_headers [$name] = array ();
					}
					$array_http_headers [$name] [] = $value;
				}
			}
			if (array_key_exists ("set-cookie", $array_http_headers) == true)
			{
				foreach ($array_http_headers ["set-cookie"] as $cookie)
				{
					$code = preg_match_all ("`\s*([^=]+)=([^;]+)[;\n\r]+`", $cookie, $array_cookie, PREG_SET_ORDER);
					if ($code !== false)
					{
						if ($code > 0)
						{
							$name = $array_cookie [0] [1];
							$value = $array_cookie [0] [2];
							if ((isset ($name) == true)
							&&  (strlen ($name) > 0)
							&&  (isset ($value) == true))
							{
								$expires = 0;
								for ($i = 1; $i < count ($array_cookie); $i++)
								{
									if (strtolower ($array_cookie [$i] [1]) == "expires")
									{
										$expires = strtotime ($array_cookie [$i] [2]);
										break;
									}
								}
								$array_http_cookies [$name] = array ($value, $expires);
							}
							else
							{
								log_error ("Name of cookie '" . $cookie . "' is empty", __FILE__, __FUNCTION__);
								$ok = false;
								break;
							}
						}
						else
						{
							log_error ("The preg_match_all() function did not found a match in cookie '" . $cookie . "'", __FILE__, __FUNCTION__);
							$ok = false;
							break;
						}
					}
					else
					{
						log_error ("The preg_match_all() function failed while parsing cookie '" . $cookie . "'", __FILE__, __FUNCTION__);
						$ok = false;
						break;
					}
				}
			}
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
	return $ok;
}

?>
