<?php 
require_once('common/include/Error.class.php');


// length of the bbcode bbcode_uid that will be inserted in each bbcode tag
define("BBCODE_UID_LEN", 10);

// Need to initialize the random numbers only ONCE
mt_srand( (double) microtime() * 1000000);
	

class BBCodeSupport extends Error {

	var $bbcode_tpl = array();


	function BBCodeSupport(){		
		$this->Error();
		
		// some inner stuff to initialize
		$this->bbcodeStyleInitialize();		
	}
	
	
	function getTextReadyForDisplay($text, $bbcode_uid){
		$tmp = str_replace("\n", "\n<br />\n", $text);
		$tmp = $this->displayText($tmp,$bbcode_uid);
		$tmp = $this->makeClickable($tmp);
		//$tmp = $this->smiliesPass($tmp);
		
		
		return $tmp;
	}
	
	
	/**
	 * bbcodeStyleInitialize() - 
	 *
	 * Initialize all the bbcode tags and how they should be displayed
	 * @param	
	 *
	 */
	 function bbcodeStyleInitialize(){
	 	$this->bbcode_tpl = array('b_open' 				=> '<span style="font-weight:bold">',
								'b_close' 				=> '</span>',
								'i_open' 				=> '<span style="font-style:italic">',
								'i_close'				=> '</span>',
								'u_open' 				=> '<span style="text-decoration:underline">',
								'u_close' 				=> '</span>',
								'color_open' 			=> '<span style="color: \\1">',
								'color_close' 			=> '</span>',
								'size_open' 			=> '<span style="font-size: \\1px; line-height: normal">',
								'size_close' 			=> '</span>',
								
								'img' 					=> '<img src="\\1" border="0" />',
								
								'ulist_open' 			=> '<ul>',
								'ulist_close' 			=> '</ul>',
								'olist_open' 			=> '<ol type="\\1">',
								'olist_close' 			=> '</ol>',
								'listitem' 				=> '<li>',
								
								'code_open' 			=> '<table width="90%" cellspacing="1" cellpadding="3" border="0" align="center">
													<tr> 
												 		<td><span style="font-weight:bold"><b>{L_CODE}:</b></span></td>
													</tr>
													<tr>
	 													<td>',	
	  							'code_close' 			=> '		</td>
													</tr>
												</table>',
								
								
								'quote_open' 			=> '<table width="90%" cellspacing="1" cellpadding="3" border="0" align="center">
													<tr> 
														  <td><span style="font-weight:bold">{L_QUOTE}:</span></td>
														</tr>
														<tr>
														  <td>',
								'quote_close' 			=> '		</td>
													</tr>
												</table>',
												
								'quote_username_open' 	=> '<table width="90%" cellspacing="1" cellpadding="3" border="0" align="center">
															<tr> 
	  															<td><b>\\1 {L_WROTE}:</b></td>
															</tr>
															<tr>
																  <td>',
								
								'email' 				=> '<a href="mailto:\\1">\\1</A>',	
															
								'url1' 					=> '<a href="\\1\\2" target="_blank">\\1\\2</a>',
								'url2' 					=> '<a href="http://\\1" target="_blank">\\1</a>',
								'url3' 					=> '<a href="\\1\\2" target="_blank">\\3</a>',
								'url4' 					=> '<a href="http://\\1" target="_blank">\\2</a>',
								'urltask'				=> '<a href="/pm/task.php?func=detailtask&project_task_id=\\3&group_id=\\1&group_project_id=\\2">\\4</a>',
								'urlartifact'			=> '<a href="/tracker/index.php?func=detail&aid=\\1">Artifact\\1</a>'
										);
	 
	 } 
	
	
	/**
	 * Does second-pass bbencoding. This should be used before displaying the message in
	 * a thread. Assumes the message is already first-pass encoded, and we are given the 
	 * correct UID as used in first-pass encoding.
	 */
	function displayText($text, $bbcode_uid)
	{
		global $Language, $HTML;		
		
		// First: If there isn't a "[" and a "]" in the message, don't bother.
		if (!(strpos($text, "[") && strpos($text, "]"))){
			// IT PASS HERE WITH MY MAC SINCE GFORGE WAS WRITTEN WITH encoding="UTF-8"
			// IF SOMEONE COULD TEST IT WITH PC TO SEE WHAT HAPPENS
		//	return $text;
		}
		
		// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
		// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
		$text = " " . $text;
		
		// we translate the text (for quote and text)
		$this->bbcode_tpl['quote_open'] = str_replace("{L_QUOTE}", _('Quote'), $this->bbcode_tpl['quote_open']);
		$this->bbcode_tpl['code_open'] = str_replace("{L_CODE}", _('Code'), $this->bbcode_tpl['code_open']);
		$this->bbcode_tpl['quote_username_open']  = str_replace("{L_WROTE}", _('Wrote'), $this->bbcode_tpl['quote_username_open']);
		
	
		// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
		$text = $this->bbencodeSecondPassCode($text, $bbcode_uid, $bbcode_tpl);
	
		// [list] and [list=x] for (un)ordered lists.
		// unordered lists
		$text = str_replace("[list:$bbcode_uid]", $this->bbcode_tpl['ulist_open'], $text);
		// li tags
		$text = str_replace("[*:$bbcode_uid]", $this->bbcode_tpl['listitem'], $text);
		// ending tags
		$text = str_replace("[/list:u:$bbcode_uid]", $this->bbcode_tpl['ulist_close'], $text);
		$text = str_replace("[/list:o:$bbcode_uid]", $this->bbcode_tpl['olist_close'], $text);
		// Ordered lists
		$text = preg_replace("/\[list=([a1]):$bbcode_uid\]/si", $this->bbcode_tpl['olist_open'], $text);
	
		// colours
		$text = preg_replace("/\[color=(\#[0-9A-F]{6}|[a-z]+):$bbcode_uid\]/si", $this->bbcode_tpl['color_open'], $text);
		$text = str_replace("[/color:$bbcode_uid]", $this->bbcode_tpl['color_close'], $text);
		// if color set to default, then we get the default color of that theme
		$text = str_replace("[/color:default", $HTML->FONT_CONTENT, $text);
		
	
		// size
		$text = preg_replace("/\[size=([\-\+]?[1-2]?[0-9]):$bbcode_uid\]/si", $this->bbcode_tpl['size_open'], $text);
		$text = str_replace("[/size:$bbcode_uid]", $this->bbcode_tpl['size_close'], $text);
	
		// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.
		$text = str_replace("[quote:$bbcode_uid]", $this->bbcode_tpl['quote_open'], $text);
		$text = str_replace("[/quote:$bbcode_uid]", $this->bbcode_tpl['quote_close'], $text);
		
		// New one liner to deal with opening quotes with usernames...
		// replaces the two line version that I had here before..
		$text = preg_replace("/\[quote:$bbcode_uid=(?:\"?([^\"]*)\"?)\]/si", $this->bbcode_tpl['quote_username_open'], $text);

	
		// [b] and [/b] for bolding text.
		$text = str_replace("[b:$bbcode_uid]", $this->bbcode_tpl['b_open'], $text);
		$text = str_replace("[/b:$bbcode_uid]", $this->bbcode_tpl['b_close'], $text);
	
		// [u] and [/u] for underlining text.
		$text = str_replace("[u:$bbcode_uid]", $this->bbcode_tpl['u_open'], $text);
		$text = str_replace("[/u:$bbcode_uid]", $this->bbcode_tpl['u_close'], $text);
	
		// [i] and [/i] for italicizing text.
		$text = str_replace("[i:$bbcode_uid]", $this->bbcode_tpl['i_open'], $text);
		$text = str_replace("[/i:$bbcode_uid]", $this->bbcode_tpl['i_close'], $text);
	
		// Patterns and replacements for URL and email tags..
		$patterns = array();
		$replacements = array();
	
		// [img]image_url_here[/img] code..
		// This one gets first-passed..
		$patterns[0] = "#\[img:$bbcode_uid\](.*?)\[/img:$bbcode_uid\]#si";
		$replacements[0] = $this->bbcode_tpl['img'];
		
		// [url]xxxx://www.phpbb.com[/url] code..
		$patterns[1] = "#\[url\]([a-z]+?://){1}([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+\(\)]+)\[/url\]#si";
		$replacements[1] = $this->bbcode_tpl['url1'];
	
		// [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
		$patterns[2] = "#\[url\]([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+\(\)]+)\[/url\]#si";
		$replacements[2] = $this->bbcode_tpl['url2'];
	
		// [url=xxxx://www.phpbb.com]phpBB[/url] code..
		$patterns[3] = "#\[url=([a-z]+?://){1}([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+\(\)]+)\](.*?)\[/url\]#si";
		$replacements[3] = $this->bbcode_tpl['url3'];
	
		// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
		$patterns[4] = "#\[url=([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+\(\)]+)\](.*?)\[/url\]#si";
		$replacements[4] = $this->bbcode_tpl['url4'];
	
		// [email]user@domain.tld[/email] code..
		$patterns[5] = "#\[email\]([a-z0-9\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/email\]#si";
		$replacements[5] = $this->bbcode_tpl['email'];
		
		// [task]Forumid:Subproject:Taskid:TaskName[/task] code..
		$patterns[6] = "#\[task\]([0-9]+?):([0-9]+?):([0-9]+?):([a-z0-9\-_.]+?)\[/task\]#si";
		$replacements[6] = $this->bbcode_tpl['urltask'];
		
		// [artifact]artifactID[/artifact] code..
		$patterns[7] = "#\[artifact\]([0-9]+?)\[/artifact\]#si";
		$replacements[7] = $this->bbcode_tpl['urlartifact'];
		
		
		$text = preg_replace($patterns, $replacements, $text);
		
				
		// Remove our padding from the string..
		$text = substr($text, 1);
		
	
		return $text;
	} 
	
	
	function makeBBCodeUID(){
		// Unique ID for this message..
	
		$bbcode_uid = md5(mt_rand());
		$bbcode_uid = substr($bbcode_uid, 0, BBCODE_UID_LEN);
	
		return $bbcode_uid;
	}
	
	function prepareText($text, $bbcode_uid)	{
		// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
		// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
		$text = " " . $text;
	
		// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
		$text = $this->bbencodeFirstPassPDA($text, $bbcode_uid, '[code]', '[/code]', '', true, '');
	
		// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.
		$text = $this->bbencodeFirstPassPDA($text, $bbcode_uid, '[quote]', '[/quote]', '', false, '');
		
		$text = $this->bbencodeFirstPassPDA($text, $bbcode_uid, '/\[quote=(\\\\"[^"]*?\\\\")\]/is', '[/quote]', '', false, '', "[quote:$bbcode_uid=\\1]");
	
		// [list] and [list=x] for (un)ordered lists.
		$open_tag = array();
		$open_tag[0] = "[list]";
	
		// unordered..
		$text = $this->bbencodeFirstPassPDA($text, $bbcode_uid, $open_tag, "[/list]", "[/list:u]", false, 'replace_list_items');
	
		$open_tag[0] = "[list=1]";
		$open_tag[1] = "[list=a]";
	
		// ordered.
		$text = $this->bbencodeFirstPassPDA($text, $bbcode_uid, $open_tag, "[/list]", "[/list:o]",  false, 'replace_list_items');
	
		// [color] and [/color] for setting text color
		$text = preg_replace("#\[color=(\#[0-9A-F]{6}|[a-z\-]+)\](.*?)\[/color\]#si", "[color=\\1:$bbcode_uid]\\2[/color:$bbcode_uid]", $text);
	
		// [size] and [/size] for setting text size
		$text = preg_replace("#\[size=([\-\+]?[1-2]?[0-9])\](.*?)\[/size\]#si", "[size=\\1:$bbcode_uid]\\2[/size:$bbcode_uid]", $text);
	
		// [b] and [/b] for bolding text.
		$text = preg_replace("#\[b\](.*?)\[/b\]#si", "[b:$bbcode_uid]\\1[/b:$bbcode_uid]", $text);
	
		// [u] and [/u] for underlining text.
		$text = preg_replace("#\[u\](.*?)\[/u\]#si", "[u:$bbcode_uid]\\1[/u:$bbcode_uid]", $text);
	
		// [i] and [/i] for italicizing text.
		$text = preg_replace("#\[i\](.*?)\[/i\]#si", "[i:$bbcode_uid]\\1[/i:$bbcode_uid]", $text);
	
		// [img]image_url_here[/img] code..
		$text = preg_replace("#\[img\](http(s)?://)([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+)\[/img\]#si", "[img:$bbcode_uid]\\1\\3[/img:$bbcode_uid]", $text);
	
		// Remove our padding from the string..
		$text = substr($text, 1);
	
	
		return $text;
	
	} 
	
	/**
	 * $text - The text to operate on.
	 * $bbcode_uid - The UID to add to matching tags.
	 * $open_tag - The opening tag to match. Can be an array of opening tags.
	 * $close_tag - The closing tag to match.
	 * $close_tag_new - The closing tag to replace with.
	 * $mark_lowest_level - boolean - should we specially mark the tags that occur
	 * 					at the lowest level of nesting? (useful for [code], because
	 *						we need to match these tags first and transform HTML tags
	 *						in their contents..
	 * $func - This variable should contain a string that is the name of a function.
	 *				That function will be called when a match is found, and passed 2
	 *				parameters: ($text, $bbcode_uid). The function should return a string.
	 *				This is used when some transformation needs to be applied to the
	 *				text INSIDE a pair of matching tags. If this variable is FALSE or the
	 *				empty string, it will not be executed.
	 * If open_tag is an array, then the pda will try to match pairs consisting of
	 * any element of open_tag followed by close_tag. This allows us to match things
	 * like [list=A]...[/list] and [list=1]...[/list] in one pass of the PDA.
	 *
	 * NOTES:	- this function assumes the first character of $text is a space.
	 *				- every opening tag and closing tag must be of the [...] format.
	 */
	function bbencodeFirstPassPDA($text, $bbcode_uid, $open_tag, $close_tag, $close_tag_new, $mark_lowest_level, $func, $open_regexp_replace = false){
		$open_tag_count = 0;
	
		if (!$close_tag_new || ($close_tag_new == ''))
		{
			$close_tag_new = $close_tag;
		}
	
		$close_tag_length = strlen($close_tag);
		$close_tag_new_length = strlen($close_tag_new);
		$uid_length = strlen($bbcode_uid);
	
		$use_function_pointer = ($func && ($func != ''));
	
		$stack = array();
	
		if (is_array($open_tag)) {
			if (0 == count($open_tag)) {
				// No opening tags to match, so return.
				return $text;
			}
			$open_tag_count = count($open_tag);
		}
		else {
			// only one opening tag. make it into a 1-element array.
			$open_tag_temp = $open_tag;
			$open_tag = array();
			$open_tag[0] = $open_tag_temp;
			$open_tag_count = 1;
		}
		
		$open_is_regexp = false;
		
		if ($open_regexp_replace) { 
			$open_is_regexp = true;
			if (!is_array($open_regexp_replace)) {
				$open_regexp_temp = $open_regexp_replace;
				$open_regexp_replace = array();
				$open_regexp_replace[0] = $open_regexp_temp;
			}
		}
		
		if ($mark_lowest_level && $open_is_regexp) {
			$this->setError("Unsupported operation for bbcode_first_pass_pda().");
		}
		
	
		// Start at the 2nd char of the string, looking for opening tags.
		$curr_pos = 1;
		while ($curr_pos && ($curr_pos < strlen($text))) {
			$curr_pos = strpos($text, "[", $curr_pos);
	
			// If not found, $curr_pos will be 0, and the loop will end.
			if ($curr_pos) {
				// We found a [. It starts at $curr_pos.
				// check if it's a starting or ending tag.
				$found_start = false;
				$which_start_tag = "";
				$start_tag_index = -1;
				for ($i = 0; $i < $open_tag_count; $i++) {
					// Grab everything until the first "]"...
					$possible_start = substr($text, $curr_pos, strpos($text, "]", $curr_pos + 1) - $curr_pos + 1);
	
					//
					// We're going to try and catch usernames with "[' characters.
					//
					if( preg_match('/\[quote\=\\\\"/si', $possible_start) && !preg_match('/\[quote=\\\\"[^"]*\\\\"\]/si', $possible_start) ) {
						//
						// OK we are in a quote tag that probably contains a ] bracket.
						// Grab a bit more of the string to hopefully get all of it..
						// 
						$possible_start = substr($text, $curr_pos, strpos($text, "\"]", $curr_pos + 1) - $curr_pos + 2);
					}
					//
					// Now compare, either using regexp or not.
					
					if ($open_is_regexp) {
						$match_result = array();
						// PREG regexp comparison.
						if (preg_match($open_tag[$i], $possible_start, $match_result)) {
							$found_start = true;
							$which_start_tag = $match_result[0];
							$start_tag_index = $i;
							break;
						}
					}
					else{
						// straightforward string comparison.
						if (0 == strcasecmp($open_tag[$i], $possible_start)){
							$found_start = true;
							$which_start_tag = $open_tag[$i];
							$start_tag_index = $i;
							break;
						}
					}
				}
	
				if ($found_start){
					// We have an opening tag.
					// Push its position, the text we matched, and its index in the open_tag array on to the stack, and then keep going to the right.
					$match = array("pos" => $curr_pos, "tag" => $which_start_tag, "index" => $start_tag_index);
					$this->bbcodeArrayPush($stack, $match);
					//
					// Rather than just increment $curr_pos
					// Set it to the ending of the tag we just found
					// Keeps error in nested tag from breaking out
					// of table structure..
					//
					$curr_pos = $curr_pos + strlen($possible_start);	
				}
				else
				{
					// check for a closing tag..
					$possible_end = substr($text, $curr_pos, $close_tag_length);
					if (0 == strcasecmp($close_tag, $possible_end)){
						// We have an ending tag.
						// Check if we've already found a matching starting tag.
						if (sizeof($stack) > 0) {
							// There exists a starting tag.
							$curr_nesting_depth = sizeof($stack);
							// We need to do 2 replacements now.
							$match = $this->bbcodeArrayPop($stack);
							$start_index = $match['pos'];
							$start_tag = $match['tag'];
							$start_length = strlen($start_tag);
							$start_tag_index = $match['index'];
	
							if ($open_is_regexp)
							{
								$start_tag = preg_replace($open_tag[$start_tag_index], $open_regexp_replace[$start_tag_index], $start_tag);
							}
	
							// everything before the opening tag.
							$before_start_tag = substr($text, 0, $start_index);
	
							// everything after the opening tag, but before the closing tag.
							$between_tags = substr($text, $start_index + $start_length, $curr_pos - $start_index - $start_length);
	
							// Run the given function on the text between the tags..
							if ($use_function_pointer)
							{
								$between_tags = $func($between_tags, $bbcode_uid);
							}
	
							// everything after the closing tag.
							$after_end_tag = substr($text, $curr_pos + $close_tag_length);
	
							// Mark the lowest nesting level if needed.
							if ($mark_lowest_level && ($curr_nesting_depth == 1))
							{
								if ($open_tag[0] == '[code]')
								{
									$code_entities_match = array('#<#', '#>#', '#"#', '#:#', '#\[#', '#\]#', '#\(#', '#\)#', '#\{#', '#\}#');
									$code_entities_replace = array('&lt;', '&gt;', '&quot;', '&#58;', '&#91;', '&#93;', '&#40;', '&#41;', '&#123;', '&#125;');
									$between_tags = preg_replace($code_entities_match, $code_entities_replace, $between_tags);
								}
								$text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$curr_nesting_depth:$bbcode_uid]";
								$text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$curr_nesting_depth:$bbcode_uid]";
							}
							else
							{
								if ($open_tag[0] == '[code]')
								{
									$text = $before_start_tag . '&#91;code&#93;';
									$text .= $between_tags . '&#91;/code&#93;';
								}
								else
								{
									if ($open_is_regexp)
									{
										$text = $before_start_tag . $start_tag;
									}
									else
									{
										$text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$bbcode_uid]";
									}
									$text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$bbcode_uid]";
								}
							}
	
							$text .= $after_end_tag;
	
							// Now.. we've screwed up the indices by changing the length of the string.
							// So, if there's anything in the stack, we want to resume searching just after it.
							// otherwise, we go back to the start.
							if (sizeof($stack) > 0){
								$match = bbcodeArrayPop($stack);
								$curr_pos = $match['pos'];
								bbcodeArrayPush($stack, $match);
								++$curr_pos;
							}
							else {
								$curr_pos = 1;
							}
						}
						else {
							// No matching start tag found. Increment pos, keep going.
							++$curr_pos;
						}
					}
					else {
						// No starting tag or ending tag.. Increment pos, keep looping.,
						++$curr_pos;
					}
				}
			}
		} // while
	
		return $text;
	
	} 
	
	/**
	 * Does second-pass bbencoding of the [code] tags. This includes
	 * running htmlspecialchars() over the text contained between
	 * any pair of [code] tags that are at the first level of
	 * nesting. Tags at the first level of nesting are indicated
	 * by this format: [code:1:$bbcode_uid] ... [/code:1:$bbcode_uid]
	 * Other tags are in this format: [code:$bbcode_uid] ... [/code:$bbcode_uid]
	 */
	function bbencodeSecondPassCode($text, $bbcode_uid) {
		global $lang;
	
		$code_start_html = $this->bbcode_tpl['code_open'];
		$code_end_html =  $this->bbcode_tpl['code_close'];
	
		// First, do all the 1st-level matches. These need an htmlspecialchars() run,
		// so they have to be handled differently.
		$match_count = preg_match_all("#\[code:1:$bbcode_uid\](.*?)\[/code:1:$bbcode_uid\]#si", $text, $matches);
	
		for ($i = 0; $i < $match_count; $i++){
			$before_replace = $matches[1][$i];
			$after_replace = $matches[1][$i];
			
			// Replace 2 spaces with "&nbsp; " so non-tabbed code indents without making huge long lines.
			$after_replace = str_replace("  ", "&nbsp; ", $after_replace);
			// now Replace 2 spaces with " &nbsp;" to catch odd #s of spaces.
			$after_replace = str_replace("  ", " &nbsp;", $after_replace);
			
			// Replace tabs with "&nbsp; &nbsp;" so tabbed code indents sorta right without making huge long lines.
			$after_replace = str_replace("\t", "&nbsp; &nbsp;", $after_replace);
	
			$str_to_match = "[code:1:$bbcode_uid]" . $before_replace . "[/code:1:$bbcode_uid]";
	
			$replacement = $code_start_html;
			$replacement .= $after_replace;
			$replacement .= $code_end_html;
	
			$text = str_replace($str_to_match, $replacement, $text);
		}
	
		// Now, do all the non-first-level matches. These are simple.
		$text = str_replace("[code:$bbcode_uid]", $code_start_html, $text);
		$text = str_replace("[/code:$bbcode_uid]", $code_end_html, $text);
	
		return $text;
	
	} 
	
	
	
	/**
	 * Escapes the "/" character with "\/". This is useful when you need
	 * to stick a runtime string into a PREG regexp that is being delimited
	 * with slashes.
	 */
	function escapeSlashes($input){
		$output = str_replace('/', '\/', $input);
		return $output;
	}
	
	/**
	 * This function does exactly what the PHP4 function array_push() does
	 * however, to keep phpBB compatable with PHP 3 we had to come up with our own
	 * method of doing it.
	 */
	function bbcodeArrayPush(&$stack, $value){
	   $stack[] = $value;
	   return(sizeof($stack));
	}
	
	/**
	 * This function does exactly what the PHP4 function array_pop() does
	 * however, to keep phpBB compatable with PHP 3 we had to come up with our own
	 * method of doing it.
	 */
	function bbcodeArrayPop(&$stack){
	   $arrSize = count($stack);
	   $x = 1;
	
	   while(list($key, $val) = each($stack)){
		  if($x < count($stack))
		  {
				$tmpArr[] = $val;
		  }
		  else
		  {
				$return_val = $val;
		  }
		  $x++;
	   }
	   $stack = $tmpArr;
	
	   return($return_val);
	}

}

/**
 * I KNOW THIS IS UGLY, BUT I HAVEN'T FOUND A WAY TO PUT THAT FONCTION IN THE CLASS AND
 * CALL IT AS A VARIABLE FUNCTION
 * This is used to change a [*] tag into a [*:$bbcode_uid] tag as part
 * of the first-pass bbencoding of [list] tags. It fits the
 * standard required in order to be passed as a variable
 * function into $this->bbencodeFirstPassPDA().
 */
function replace_list_items($text, $bbcode_uid)
{
	$text = str_replace("[*]", "[*:$bbcode_uid]", $text);

	return $text;
}

?>
