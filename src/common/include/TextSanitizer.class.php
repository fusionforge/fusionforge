<?php
/**
 * FusionForge text sanitisation
 *
 * Copyright (C) 2005, Daniel Perez
 * Copyright (C) 2008-2009 Alcatel-Lucent
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

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The Style Sheet ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

require_once('HTMLPurifier.auto.php');

Class TextSanitizer extends Error {


	/**
	 *  convertExtendedCharsForEmail - Grabs some text with html special characters and converts them to the corresponding character.
	 *
	 *	@param   string		The input string
	 *	@return  string		The output string
	 */
	function convertExtendedCharsForEmail($text) {
		$text = str_replace("&acute;","'",$text); //it's better to see that char in the email than the html entity
		$text = str_replace("&amp;","&",$text);
		$text = str_replace("&quot;",'"',$text);
		$text = str_replace("&rsquo;","’",$text);
		$text = str_replace("&nbsp;",' ',$text);
		$text = str_replace("&lt;",'<',$text);
		$text = str_replace("&gt;",'>',$text);
		$text = str_replace("&deg;",'°',$text);
		$text = str_replace("&sup2;",'²',$text);
		$text = str_replace("&euro;",'€',$text);
		$text = str_replace("&uml;",'¨',$text);
		$text = str_replace("&pound;",'£',$text);
		$text = str_replace("&curren;",'¤',$text);
		$text = str_replace("&micro;",'µ',$text);
		$text = str_replace("&sect;",'§',$text);
		$text = str_replace("&oelig;",'œ',$text);
		$text = str_replace("&lt;br&gt;","\n",$text);
		$text = str_replace("&lt;br /&gt;","\n",$text);

		$text = str_replace("&eacute;","é",$text);
		$text = str_replace("&egrave;","è",$text);
		$text = str_replace("&ecirc;","ê",$text);
		$text = str_replace("&euml;","ë",$text);
		$text = str_replace("&agrave;","à",$text);
		$text = str_replace("&acirc;","â",$text);
		$text = str_replace("&ccedil;","ç",$text);
		$text = str_replace("&ugrave;","ù",$text);
		$text = str_replace("&ucirc;","û",$text);
		$text = str_replace("&uuml;","ü",$text);
		$text = str_replace("&ocirc;","ô",$text);
		$text = str_replace("&iuml;","ï",$text);

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
		$text = str_replace("<p>","\n",$text);
		$text = str_replace("</p>","\n",$text);
		$text = str_replace("<li>","\n - ",$text);
		$text = str_replace("</li>",'',$text);
		$text = str_replace("<ul>",'',$text);
		$text = str_replace("</ul>","\n",$text);
		$text = str_replace("\xc2\xa0",' ',$text);
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
		$input=str_replace('&lt;div&gt;','<div>',$input);
		$input=str_replace('&lt;div','<div',$input);
		$input=str_replace('&lt;/div&gt;','</div>',$input);
		$input=str_replace('&lt;u&gt;','<u>',$input);
		$input=str_replace('&lt;u ','<u ',$input); // rg
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
		$input=str_replace('&lt;a ','<a ',$input);
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
		$input=str_replace('&lt;address&gt;','<address>',$input);
		$input=str_replace('&lt;/address&gt;','</address>',$input);
		$input=str_replace('&lt;h1 ','<h1 ',$input);
		$input=str_replace('&lt;h2 ','<h2 ',$input);
		$input=str_replace('&lt;h3 ','<h3 ',$input);
		$input=str_replace('&lt;h4 ','<h4 ',$input);
		$input=str_replace('&lt;h5 ','<h5 ',$input);
		$input=str_replace('&lt;h6 ','<h6 ',$input);
		$input=str_replace('&rsquo;','\\\'',$input);
		$input=str_replace('&bull;','-',$input);

		// Allow embbeding video like youtube ones.
		$input=str_replace('&lt;object ','<object ',$input);
		$input=str_replace('&lt;/object&gt;','</object>',$input);
		$input=str_replace('&lt;param ','<param ',$input);
		$input=str_replace('&lt;/param&gt;','</param>',$input);
		$input=str_replace('&lt;embed ','<embed ',$input);
		$input=str_replace('&lt;/embed&gt;','</embed>',$input);

		return $input;
	}

	function stripTags ($text, $allowed='br,p,li,ul') {
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Cache.DefinitionImpl', NULL);
		$config->set('HTML.Allowed', $allowed);
		$purifier = new HTMLPurifier($config);
		$text = $purifier->purify($text);

		return $text;
	}

	static function purify ($text) {
		// Remove string like "<![if !supportLists]>" or "<![endif]>"
		$text = preg_replace('/<!\[.+?\]>/', '', $text);
		$config = HTMLPurifier_Config::createDefault();
		//$config->set('HTML.Allowed','a[href|title],strike,sub,span,font,hr,br,tbody,tr,td,table,div,u,p,ul,li,ol,blockquote,em,strong,sup,input,img,textarea,h1,h2,h3,h4,h5,h6,pre,address');
		$config->set('Cache.DefinitionImpl', NULL);
		$purifier = new HTMLPurifier($config);
		return $purifier->purify($text);
	}

	function summarize ($text, $nb_line=4, $truncate=true, $nb_char=145) {
		$text = $this->stripTags($text);
		$text = $this->convertNeededTagsForEmail($text);
		// Remove MS Windows extra char for CR
		$text = preg_replace('/\r/', '', $text);
		// Strip CR
		$text = preg_replace('/\n[\n\s]*/', "\n", $text);
		$text = trim($text);
		$arr = explode("\n", $text);
		$nb_max = count($arr);
		if ($nb_max > $nb_line) $nb_max = $nb_line;
		$summary = '';
		for ($l = 0; $l < $nb_max; $l++) {
			$summary .= '<br />';
			if ($truncate == true && $nb_max < $nb_line && $l == $nb_max - 1) {
				$nb_char = $nb_char * ($nb_line - $nb_max + 1);
			}
			$summary .= util_make_links((($truncate == true && strlen($arr[$l]) > $nb_char) ?
			preg_replace('/[^\s]*$/', ' <b>...</b>', substr($arr[$l], 0, $nb_char), 1) :
			$arr[$l]));
		}

		return $summary;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
