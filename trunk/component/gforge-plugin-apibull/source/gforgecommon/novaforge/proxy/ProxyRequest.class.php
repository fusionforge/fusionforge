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

require_once ("common/novaforge/log.php");
require_once ("common/novaforge/proxy/ProxyConfig.class.php");

/* 
 * Create the proxy request
 * Use Curl library
 */
class ProxyRequest
{

	var $httpHeader;    // http header
	var $httpHtml;      // http body ( the html page )
	var $confProxy;     // a ConfigProxy object

	// Constructor
	function ProxyRequest ($proxyConfig)
	{
		$this->confProxy = $proxyConfig;
	}

	// Get the http reponse on a page request on the server and set the httpHeader and httpHtml variable
	// 1- Build the request (cookie, post param, ...)
	// 2- Return the page
	function getServHttpReponse ($remotePath, $postVar = null, $getVar = null, $filesVar = null, $cookieVar = null)
	{
		$ok = true;
		if ($postVar == null)
		{
			$postVar = $_POST;
		}
		if ($getVar == null)
		{
			$getVar = $_GET;
		}
        	if ($filesVar == null)
		{
			$filesVar = $_FILES;
		}
        	if ($cookieVar == null)
		{
			$cookieVar = $_COOKIE;
		}
		$url = $this->confProxy->getRemoteUrl ();
		if (($url [strlen ($url) - 1] != "/") && ($remotePath [0] != "/"))
		{
			$url .= "/";
		}
		$url .= $remotePath;
		$ch = curl_init ($url);
		// Get the header of the response
		curl_setopt ($ch, CURLOPT_HEADER, true);
		// Get the response as a string
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		// Connection timeout
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
		// Timeout
		curl_setopt ($ch, CURLOPT_TIMEOUT, 30);
		// User agent string
		curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER ["HTTP_USER_AGENT"]);
		// Strip shlashes if magic quotes is on
		if (get_magic_quotes_gpc ())
		{
			$postVar = proxy_stripslashes_deep ($postVar);
			$getVar = proxy_stripslashes_deep ($getVar);
			$cookieVar = proxy_stripslashes_deep ($cookieVar);
		}
		if ($this->confProxy->getSkipEncoding ())
		{
		  $_post_var = $postVar;
		} else {
      $_post_var = mb_convert_encoding_deep ($postVar);
    }
		$needUpload = false;
		$filesUpload = array ();
		$repUpload = array ();
		// _FILES variable : transmit in POST field with CURL
		if (count ($filesVar) > 0)
		{
			foreach ($filesVar as $nameInputFile => $fileUpload)
			{
				if (($fileUpload ["name"] != "") && ($ok == true))
				{
					switch ($fileUpload ["error"])
					{
						case UPLOAD_ERR_OK :
							// No error 
							mkdir ($fileUpload ["tmp_name"] . "rep");
							move_uploaded_file ($fileUpload ["tmp_name"], $fileUpload ["tmp_name"] . "rep/" . $fileUpload ["name"]);
							$_post_var [$nameInputFile] = "@" . $fileUpload ["tmp_name"] . "rep/" . $fileUpload["name"];
							$filesUpload [] = $fileUpload ["tmp_name"] . "rep/" . $fileUpload ["name"];
							$repUpload [] = $fileUpload ["tmp_name"] . "rep";
							$needUpload = true;
							break;
						case UPLOAD_ERR_INI_SIZE :
						case UPLOAD_ERR_FORM_SIZE :
							// upload size > php.ini maximum
							log_error ("Upload error : file is too big (" . $fileUpload ["error"] . ")", __FILE__, __FUNCTION__, __CLASS__);
							$ok = false;
							break;
						// UPLOAD_ERR_PARTIAL
						// UPLOAD_ERR_NO_FILE
						// UPLOAD_ERR_NO_TMP_DIR
						default :
							log_error ("Upload error : internal error (" . $fileUpload ["error"] . ")", __FILE__, __FUNCTION__, __CLASS__);
							$ok = false;
					}
				}
			}
			if (($needUpload == true) && ($ok == true))
			{
//				curl_setopt ($ch, CURLOPT_UPLOAD, true);
			}
		}
		// _POST variable
		if ((count ($_post_var) > 0) && ($ok == true))
		{
			curl_setopt ($ch, CURLOPT_POST, true);
			$_post_var = flatPostArray ($_post_var);
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $_post_var);
		}
		// _COOKIE variable
		if ((count ($cookieVar) > 0) && ($ok == true))
		{
			$cookieString = "";
			$prefix = $this->confProxy->getCookiesPrefix ();
			$prefixLength = strlen ($prefix);
			foreach ($cookieVar as $name => $value)
			{
				// Send only the cookies beginning with the specified prefix
				if (strpos ($name, $prefix) === 0)
				{
					$cookieString .= substr ($name, $prefixLength) . "=" . $value . "; ";
				}
			}
			if (empty ($cookieString) == false)
			{
				curl_setopt($ch, CURLOPT_COOKIE, trim ($cookieString));
			}
		}
		if ($ok == true)
		{
			$gforge_base_url = "http";
			if ($this->confProxy->getLocalSsl () == true)
			{
				$gforge_base_url .= "s";
			}
			$gforge_base_url .= "://" . $this->confProxy->getLocalDomain ();
			curl_setopt ($ch, CURLOPT_HTTPHEADER, array ("GForgeBaseURL: " . $gforge_base_url, "GForgeRemoteURL: " . $this->confProxy->getRemoteUrl ()));
			$httpReponse = curl_exec ($ch);
			if ($httpReponse === false)
			{
				log_error ("CURL error: " . curl_error ($ch), __FILE__, __FUNCTION__, __CLASS__);
				$ok = false;
			}
			else
			{
				@list ($httpHeader, $httpHtml) = explode ("\r\n\r\n", $httpReponse, 2);
				if ((strpos ($httpHeader, "HTTP/1.1 1") !== false) && (strpos ($httpHtml, "HTTP/1.1") !== false))
				{
					@list ($httpHeader2, $httpHtml) = explode ("\r\n\r\n", $httpHtml, 2);
					$httpHeader .= "\r\n" . $httpHeader2;
				}
				$this->httpHeader = $httpHeader;
				$this->httpHtml = $httpHtml;
			}
		}
		curl_close ($ch);
		// delete temporary upload files
		for ($i = 0; $i < count ($filesUpload); $i++)
		{
			unlink ($filesUpload [$i]);
			rmdir ($repUpload [$i]);
		}
		return $ok;
	}

}



/*
 * Util functions
 */


function proxy_stripslashes_deep ($value)
{
	if (is_array ($value) == true)
	{
		$value = array_map ("proxy_stripslashes_deep", $value);
	}
	else
	{
		$value = stripslashes ($value);
	}
	return $value;
}

function mb_convert_encoding_deep ($value)
{
	if (is_array ($value) == true)
	{
		$new_value = array ();
		foreach ($value as $k => $v)
		{
			$new_value [$k] = mb_convert_encoding_deep ($v);
		}
	}
	else
	{
		$new_value = mb_convert_encoding ($value, "ISO-8859-1", "UTF-8");
	}
	return $new_value;
}

/*
 * flatPostArray : transform 
 *
 *   array(
 *     'foo' => array(
 *          0 => 'A',
 *          1 => 'B'
 *     )
 *   )
 *
 *  to
 * 
 *   array(
 *      'foo[0]' => 'A',
 *      'foo[1]' => 'B'
 *   )
 *
 *
 */
function flatPostArray ($postArray)
{
	if ((is_array ($postArray) == true) && (count ($postArray) > 0))
	{
		$arr = array ();
		foreach ($postArray as $k => $v)
		{
			if (is_array ($v) == true)
			{
				flatPostArrayRecursive ($k, $v, $arr);
			}
			else
			{
				$arr [$k] = $v;
			}
		}
		return $arr;
	}
	else
	{
		return $postArray;
	}
}

function flatPostArrayRecursive ($stringIndex, $arr, &$newArr)
{
	if (is_array ($arr) == true)
	{
		foreach ($arr as $k => $v)
		{
			flatPostArrayRecursive ($stringIndex . "[" . $k . "]" , $v, $newArr);
		}
	}
	else
	{
		$newArr [$stringIndex] = $arr;
	}
}

?>
