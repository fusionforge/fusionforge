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
 * Manage the html proxy  reponse (change link, delete header, ...;)
 */
class ProxyReponse
{

	var $reponse;
    
	// Constructor
	function ProxyReponse ($confProxy, $httpHtml)
	{
		$this->confProxy = $confProxy;     
		$this->reponse = $httpHtml;
	}
    
    
	function hasHtlmHead ()
	{
		$has = false;
		if (strpos ($this->reponse, "<head>") !== false)
		{
			$has = true;
		}
		return $has;
	}

	function changeLink ()
	{
		$tags = array
		(
			"a"		=> array ("href"),
			"img"		=> array ("src", "longdesc"),
			"image"		=> array ("src", "longdesc"),
			"body"		=> array ("background"),
			"base"		=> array ("href"),
			"frame"		=> array ("src", "longdesc"),
			"iframe"	=> array ("src", "longdesc"),
			"head"		=> array ("profile"),
			"layer"		=> array ("src"),
			"input"		=> array ("src", "usemap"),
			"form"		=> array ("action"),
			"area"		=> array ("href"),
			"link"		=> array ("href", "src", "urn"),
			"meta"		=> array ("content"),
			"param"		=> array ("value"),
			"applet"	=> array ("codebase", "code", "object", "archive"),
			"object"	=> array ("usermap", "codebase", "classid", "archive", "data"),
			"script"	=> array ("src"),
			"select"	=> array ("src"),
			"hr"		=> array ("src"),
			"table"		=> array ("background"),
			"tr"		=> array ("background"),
			"th"		=> array ("background"),
			"td"		=> array ("background"),
			"bgsound"	=> array ("src"),
			"blockquote"	=> array ("cite"),
			"del"		=> array ("cite"),
			"embed"		=> array ("src"),
			"fig"		=> array ("src", "imagemap"),
			"ilayer"	=> array ("src"),
			"ins"		=> array ("cite"),
			"note"		=> array ("src"),
			"overlay"	=> array ("src", "imagemap"),
			"q"		=> array ("cite"),
			"ul"		=> array('src')
		);

		$_response_body = $this->reponse;
		preg_match_all ("#<\s*([a-zA-Z\?-]+)([^>]+)>#S", $_response_body, $matches);
		$count_matches = count ($matches [0]);
		for ($i = 0; $i < $count_matches; $i++)
		{
			$res = preg_match_all ("#([a-zA-Z\-\/]+)\s*(?:=\s*(?:\"([^\">]*)\"?|'([^'>]*)'?|([^'\"\s]*)))?#S", $matches [2] [$i], $m, PREG_SET_ORDER);
			if (($res === false) || ($res === 0))
			{
				continue;
			}
			$rebuild = false;
			$extra_html = $temp = "";
			$attrs = array ();
			for ($j = 0; $j < count ($m); $j++)
			{
				if (isset ($m [$j] [4]) == true)
				{
					$temp = $m [$j] [4];
				}
				else
				{
					if (isset ($m [$j] [3]) == true)
					{
						$temp = $m [$j] [3];
					}
					else
					{
						if (isset ($m [$j] [2]) == true)
						{
							$temp = $m [$j] [2];
						}
						else
						{
							$temp = false;
						}
					}
				}
				$attrs [strtolower ($m [$j] [1])] = $temp;
			}
			$tag = strtolower ($matches [1] [$i]);
			if (isset ($tags [$tag]) == true)
			{
				switch ($tag)
				{
					case "a" :
						if (isset ($attrs ["href"]) == true)
						{
                            
							$rebuild = true;
                            				$attrs ["href"] = $this->confProxy->translateUrl ($attrs ["href"]);
						}
						break;
					case "img" :
                        
						if (isset ($attrs ["src"]) == true)
						{
							$rebuild = true;
							$attrs ["src"] = $this->confProxy->translateUrl ($attrs ["src"]);
						}
						if (isset ($attrs ["longdesc"]) == true)
						{
							$rebuild = true;
							$attrs ["longdesc"] = $this->confProxy->translateUrl ($attrs ["longdesc"]);
						}
						break;
					case "form" :
						if (isset ($attrs ["action"]) == true)
						{
							$rebuild = true;
							$attrs ["action"] = $this->confProxy->translateUrl ($attrs ["action"]);
						}
						break;
					case "base" :
						if (isset ($attrs ["href"]) == true)
						{
							$rebuild = true;
							url_parse ($attrs ["href"], $_base);
							$attrs ["href"] = $this->confProxy->translateUrl ($attrs ["href"]);
						}
						break;
                    
					case "meta" :
						if (isset ($attrs ["name"]) == true)
						{
							$_response_body = str_replace ($matches [0] [$i], "", $_response_body);
						}
						if ((isset ($attrs ["http-equiv"], $attrs ["content"]) == true)
						&&   (preg_match ("#\s*refresh\s*#i", $attrs ["http-equiv"]) === 1))
						{
							if (preg_match ("#^(\s*[0-9]*\s*;\s*url=)(.*)#i", $attrs ["content"], $content) === 1)
							{
								$rebuild = true;
								$attrs ["content"] =  $content [1] . $this->confProxy->translateUrl (trim ($content [2], "\"'"));
							}
						}
						break;
                    
					case "applet" :
						if (isset ($attrs ["codebase"]) == true)
						{
							$rebuild = true;
							$temp = $_base;
							url_parse ($this->confProxy->translateUrl (rtrim ($attrs ["codebase"], "/") . "/", false), $_base);
							unset ($attrs ["codebase"]);
						}
						if ((isset ($attrs ["code"]) == true) && (strpos ($attrs ["code"], "/") !== false))
						{
							$rebuild = true;
							$attrs ["code"] = $this->confProxy->translateUrl ($attrs ["code"]);
						}
						if (isset ($attrs ["object"]) == true)
						{
							$rebuild = true;
							$attrs ["object"] = $this->confProxy->translateUrl ($attrs ["object"]);
						}
						if (empty ($temp) == false)
						{
							$_base = $temp;
						}
						break;
					case "object" :
						if (isset ($attrs ["usemap"]) == true)
						{
							$rebuild = true;
							$attrs ["usemap"] = $this->confProxy->translateUrl ($attrs ["usemap"]);
						}
						if (isset ($attrs ["codebase"]) == true)
						{
							$rebuild = true;
							$temp = $_base;
							url_parse ($this->confProxy->translateUrl (rtrim ($attrs ["codebase"], "/") . "/", false), $_base);
							unset ($attrs ["codebase"]);
						}
						if (isset ($attrs ["data"]) == true)
						{
							$rebuild = true;
							$attrs ["data"] = $this->confProxy->translateUrl ($attrs ["data"]);
						}
						if ((isset ($attrs ["classid"]) == true) && (preg_match ("#^clsid:#i", $attrs ["classid"]) !== 1))
						{
						$rebuild = true;
						$attrs ["classid"] = $this->confProxy->translateUrl ($attrs ["classid"]);
						}
						if (empty ($temp) == false)
						{
								$_base = $temp;
						}
						break;
					case "param" :
						if ((isset ($attrs ["valuetype"], $attrs ["value"]) == true)
						&&  (strtolower ($attrs ["valuetype"]) == "ref")
						&&  (preg_match ("#^[\w.+-]+://#", $attrs ["value"]) === 1))
						{
							$rebuild = true;
							$attrs ["value"] = $this->confProxy->translateUrl ($attrs ["value"]);
						}
						break;
					case "frame" :
					case "iframe" :
						if (isset ($attrs ["src"]) == true)
						{
							$rebuild = true;
							$attrs ["src"] = $this->confProxy->translateUrl ($attrs ["src"]) . "&nf=1";
						}
						if (isset ($attrs ["longdesc"]) == true)
						{
							$rebuild = true;
							$attrs ["longdesc"] = $this->confProxy->translateUrl ($attrs ["longdesc"]);
						}
						break;
					default :
						foreach ($tags [$tag] as $attr)
						{
							if (isset ($attrs [$attr]) == true)
							{
								$rebuild = true;
								$attrs [$attr] = $this->confProxy->translateUrl ($attrs [$attr]);
							}
						}
				}
			}
			if ($rebuild == true)
			{
				$new_tag = "<" . $tag;
				foreach ($attrs as $name => $value)
				{
					$new_tag .= " " . $name;
					if ($value !== false)
					{
						if ((strpos ($value, "\"") == true) && (strpos($value, "'") == false))
						{
							$delim = "'";
						}
						else
						{
							$delim = "\"";
						}
						$new_tag .= "=" . $delim . $value . $delim;
					}
				}
				$new_tag .= ">" . $extra_html;
				$_response_body = str_replace ($matches [0] [$i], $new_tag, $_response_body);
			}
		}
		$this->reponse = $_response_body;
	}

	// Keep links, metas, ... and the body content
	function deleteHtmlHeader ()
	{
		//
		// WARNING: the strpos is case sensitive... it won't found BODY, LINK, META,...
		//
        	// Extract header and body
	        $tagStartBody = "<body";
        	$posStartBody = strpos ($this->reponse, $tagStartBody);
		if ($posStartBody !== false)
		{
			$headerHtml = substr ($this->reponse, 0, $posStartBody);
			$posStartBody = strpos ($this->reponse, ">", $posStartBody);
		}
        	if ($posStartBody === false)
		{
			return;
		}
        	$bodyHtml = substr ($this->reponse, $posStartBody + 1);
		// Extract link tags
        	preg_match_all ("`(<\s*link[^>]*>)`i", $headerHtml, $linksArray);
	        $linkString = "";
        	foreach ($linksArray [0] as $link)
		{
			// Do not add the shortcut icon link
			if (strpos ($link, "shortcut icon") === false)
			{
	        		$linkString .= $link . "\n";
			}
        	}
		// Extract meta tags
        	preg_match_all ("`(<meta[^>]*>)`i", $headerHtml, $metasArray);
	        $metaString = "";
        	foreach ($metasArray [0] as $meta)
		{
			// Do not add the charset meta tags
			if (strpos ($meta, "charset") === false)
			{
				$metaString .= $meta . "\n";
			}
		} 
		// Extract script tags
	        preg_match_all ("`(<script[^>]*src=[^>]*)>`i", $headerHtml, $scriptsArray);
        	$scriptsString = "";
	        foreach ($scriptsArray [1] as $script)
		{
        		$scriptsString .= $script . "></script>\n";
	        }       
	        // Extract inline scripts
	        preg_match_all ("`(<script[^>]*language=[^>]*)>.*</script>`isU", $headerHtml, $scriptInlineArray);
        	$scriptInlineString = "";
		foreach ($scriptInlineArray [0] as $script)
		{
			$scriptInlineString .= $script . "\n";
	        }               
		// Extract inline styles
		preg_match_all ("`(<style\s*type=\"text/css\"\s*>.*</style>)`isU", $headerHtml, $styleInlineArray);
		$styleInlineString = "";
		foreach ($styleInlineArray [0] as $style)
		{
			$styleInlineString .= $script . "\n";
		}
		// Add links, metas ... before body content
		$bodyHtml = $metaString . $linkString . $styleInlineString . $scriptsString . $scriptInlineString . $bodyHtml;
		// Remove end of body and html tags
		$this->reponse = preg_replace (array ("`<\/body>`i", "`<\/html>`i"), "", $bodyHtml);
	}
  
	function replacePatterns ($array_patterns, $array_replacements)
	{
		$this->reponse = preg_replace ($array_patterns, $array_replacements, $this->reponse);
	}

}

?>
