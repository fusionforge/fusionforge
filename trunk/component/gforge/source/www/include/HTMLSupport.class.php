<?php 
require_once $gfcommon.'include/Error.class.php';


class HTMLSupport extends Error {
 
	var $allowed_html_tags = array(); 
	
	// must be called after HTML was initialized
	function HTMLSupport(){
		$this->Error();
	}
	
	/*  Replace allowed tags back to original
	
	*/
	function replaceallowed($text) {
		$allowed = array ("&lt;b&gt;" => "<b>","&lt;/b&gt;" => "</b>","&lt;i&gt;" => "<i>","&lt;/i&gt;" => "</i>","&lt;u&gt;" => "<u>","&lt;/u&gt;" => "</u>",
							"&lt;ul&gt;" => "<ul>","&lt;/ul&gt;" => "</ul>","&lt;li&gt;" => "<li>","&lt;/li&gt;" => "</li>",
							"&lt;ol&gt;" => "<ol>","&lt;/ol&gt;" => "</ol>");
		//for img tag it isn�t as simple as a conversion table
		preg_match_all('/&lt;img src=.*&gt;/', $text, $matches);
		foreach ($matches[0] as $one) {
			$one = html_entity_decode($one,ENT_QUOTES);
			$one = stripslashes($one);
			$text = preg_replace('/(&lt;img src=)(.*)(&gt;)/',$one,$text,1);
		}
		
		
		
		return (strtr($text,$allowed));
	}	
	
	function prepareText($text, $strip_html) {
		if ($strip_html){
			$trans_tbl = get_html_translation_table (HTML_ENTITIES,ENT_QUOTES); // more restrictive
		}else{
			$trans_tbl = get_html_translation_table (HTML_SPECIALCHARS); // translate all
		}	
		
		// MS Word strangeness..
		// smart single/ double quotes:
		$trans_tbl[chr(145)] = '\'';
		$trans_tbl[chr(146)] = '\'';
		$trans_tbl[chr(147)] = '&';
		$trans_tbl[chr(148)] = '&';
		// � :
		$trans_tbl[chr(142)] = '�';

		$text = strtr ($text, $trans_tbl);	
		//re-replace the allowed tags
		//if (!$strip_html) {
		$text = $this->replaceallowed($text);
		//}
				
		return $text;
	}
	
	


}
?>
