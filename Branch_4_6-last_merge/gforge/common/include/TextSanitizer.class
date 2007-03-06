<?php

/**
 * GForge Text Sanitizer Class
 *
 * 
 *
 * This file is part of GForge.
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/* Text Sanitizer Class
	by Daniel Perez (danielperez.arg@gmail.com) - 2005
*/


Class TextSanitizer extends Error {
	
	
			/**
	 *  convertExtendedCharsForEmail - Grabs some text with html special characters and converts them to the corresponding character. 
	 *
	 *	@param   string		The input string
	 *	@return  string		The output string
	 */
	function convertExtendedCharsForEmail($text) {
		$text = str_replace("&acute;","'",$text); //it´s better to see that char in the email than the html entity
		$text = str_replace("&amp;","&",$text);
		$text = str_replace("&quot;",'"',$text);
		$text = str_replace("&nbsp;",' ',$text);
		$text = str_replace("&lt;",'<',$text);
		$text = str_replace("&gt;",'>',$text);
		$text = str_replace("&deg;",'°',$text);
		$text = str_replace("&lt;br&gt;","\n",$text);
		$text = str_replace("&lt;br /&gt;","\n",$text);
		return $text;
	}
	
			/**
	 *  convertNeededTagsForEmail - Grabs some text with html tags and those which are important for display (<br>, <p>) convert accordingly
	 *
	 *	@param   string		The input string
	 *	@return  string		The output string
	 */	
	function convertNeededTagsForEmail($text) {
		$text = str_replace("<br>","\n",$text);
		$text = str_replace("<br />","\n",$text);
		$text = str_replace("<br/>","\n",$text);
		return $text;
	}
	
	function unhtmlentities ($string) {
		$trans_tbl = get_html_translation_table (HTML_SPECIALCHARS );
		$trans_tbl = array_flip ($trans_tbl );
		$res = strtr ($string ,$trans_tbl );
		$res = str_replace("&amp;quot;",'"',$res);
		return $res;
	}
	
		/**
	 *  SanitizeHtml - Grabs some text with all kinds of html code and parses it to make it safe
	 *
	 *	@param   string		The HTML Code
	 *	@return  string		The HTML output
	 */
	function SanitizeHtml($input) {
		
		$input = htmlspecialchars($input); // first strip all chars

				$input=str_replace('&amp;','&',$input);
                $input=str_replace('&quot;','"',$input);
                $input=str_replace('/&gt;','/>',$input);
                $input=str_replace('"&gt;','">',$input);
                $input=str_replace('&lt;/a&gt;','</a>',$input);
                $input=str_replace('&lt;strike&gt;','<strike>',$input);
                $input=str_replace('&lt;/strike&gt;','</strike>',$input);
                $input=str_replace('&lt;sub&gt;','<sub>',$input);
                $input=str_replace('&lt;/sub&gt;','</sub>',$input);
                $input=str_replace('&lt;span','<span',$input);
                $input=str_replace('&lt;/span&gt;','</span>',$input);
                $input=str_replace('&lt;font','<font',$input);
                $input=str_replace('&lt;/font&gt;','</font>',$input);
                $input=str_replace('&lt;hr&gt;','<hr>',$input);
                $input=str_replace('&lt;hr','<hr',$input);
                $input=str_replace('&lt;br&gt;','<br>',$input);
                $input=str_replace('&lt;br />','<br />',$input);
                $input=str_replace('&lt;tbody&gt;','<tbody>',$input);
                $input=str_replace('&lt;/tbody&gt;','</tbody>',$input);
                $input=str_replace('&lt;tr&gt;','<tr>',$input);
                $input=str_replace('&lt;/tr&gt;','</tr>',$input);
                $input=str_replace('&lt;td&gt;','<td>',$input);
                $input=str_replace('&lt;/td&gt;','</td>',$input);
                $input=str_replace('&lt;td','<td',$input);
                $input=str_replace('&lt;table&gt;','<table>',$input);
                $input=str_replace('&lt;table','<table',$input);
                $input=str_replace('&lt;/table&gt;','</table>',$input);
                $input=str_replace('&lt;div','<div',$input);
                $input=str_replace('&lt;/div&gt;','</div>',$input);
                $input=str_replace('&lt;u&gt;','<u>',$input);
                $input=str_replace('&lt;/u&gt;','</u>',$input);
                $input=str_replace('&lt;p&gt;','<p>',$input);
                $input=str_replace('&lt;/p&gt;','</p>',$input);
                $input=str_replace('&lt;p ','<p ',$input);
                $input=str_replace('&lt;li&gt;','<li>',$input);
                $input=str_replace('&lt;/li&gt;','</li>',$input);
				$input=str_replace('&lt;ul&gt;','<ul>',$input);
                $input=str_replace('&lt;/ul&gt;','</ul>',$input);
                $input=str_replace('&lt;ol&gt;','<ol>',$input);
                $input=str_replace('&lt;/ol&gt;','</ol>',$input);
                $input=str_replace('&lt;blockquote&gt;','<blockquote>',$input);
                $input=str_replace('&lt;blockquote','<blockquote',$input);
                $input=str_replace('&lt;/blockquote&gt;','</blockquote>',$input);
                $input=str_replace('&lt;em&gt;','<em>',$input);
                $input=str_replace('&lt;/em&gt;','</em>',$input);
                $input=str_replace('&lt;strong&gt;','<strong>',$input);
                $input=str_replace('&lt;/strong&gt;','</strong>',$input);
                $input=str_replace('&lt;sup&gt;','<sup>',$input);
                $input=str_replace('&lt;/sup&gt;','</sup>',$input);
                $input=str_replace('&lt;input ','<input ',$input);
                $input=str_replace('&lt;img ','<img ',$input);
                $input=str_replace('&lt;textarea ','<textarea ',$input);
                $input=str_replace('&lt;/textarea&gt;','</textarea>',$input);
                $input=str_replace('&lt;a href','<a href',$input);
                $input=str_replace('&lt;h1&gt;','<h1>',$input);
                $input=str_replace('&lt;/h1&gt;','</h1>',$input);
                $input=str_replace('&lt;h2&gt;','<h2>',$input);
                $input=str_replace('&lt;/h2&gt;','</h2>',$input);
                $input=str_replace('&lt;h3&gt;','<h3>',$input);
                $input=str_replace('&lt;/h3&gt;','</h3>',$input);
                $input=str_replace('&lt;h4&gt;','<h4>',$input);
                $input=str_replace('&lt;/h4&gt;','</h4>',$input);
                $input=str_replace('&lt;h5&gt;','<h5>',$input);
                $input=str_replace('&lt;/h5&gt;','</h5>',$input);
                $input=str_replace('&lt;h6&gt;','<h6>',$input);
                $input=str_replace('&lt;/h6&gt;','</h6>',$input);
                $input=str_replace('&lt;pre&gt;','<pre>',$input);
                $input=str_replace('&lt;/pre&gt;','</pre>',$input);
                $input=str_replace('&lt;h1 ','<h1 ',$input);
                $input=str_replace('&lt;h2 ','<h2 ',$input);
                $input=str_replace('&lt;h3 ','<h3 ',$input);
                $input=str_replace('&lt;h4 ','<h4 ',$input);
				$input=str_replace('&lt;h5 ','<h5 ',$input);
                $input=str_replace('&lt;h6 ','<h6 ',$input);
	
		return $input;
	}
}


?>
