<?php
/**
 * FusionForge mail parser
 *
 * Copyright 2004, GForge, LLC
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

require_once $gfcommon.'include/Error.class.php';

class MailParser extends Error {

	var $max_file_size=2000000;
	var $headers;
	var $body;

	function MailParser($input_file) {
		$this->Error();
		$size = filesize($input_file);
		if ($size > $this->max_file_size) {
			$this->setError("Error - file too large");
			return false;
		}
		$fo = fopen($input_file, 'r');
		$input_data = fread($fo, $size);
		fclose($fo);

		$lines=explode("\n",$input_data);
		$linecount=count($lines);
		unset($input_data);

//system("echo \"mp: headers".implode("***\n",$lines)."\n\" >> /tmp/forum.log");
		//
		//	Read the message line-by-line
		//
		for ($i=0; $i<($linecount-1); $i++) {
//system("echo \"mp: line $i of $linecount length: ".strlen($lines[$i])." ".$lines[$i]."\n\" >> /tmp/forum.log");
			//
			//	Still reading headers
			//
			if (!$got_headers) {
				//
				//	If we hit a blank line, end of headers
				//
				if (strlen($lines[$i]) < 2) {
					$got_headers=true;
				} else {
					//
					//	See if line starts with tab, if so ignore it for now
					//
					if (!preg_match('/^[A-z]/',$lines[$i])) {
						$header[$lastheader] = $header[$lastheader]."\n".$lines[$i];
					} else {
						$pos = (strpos($lines[$i],':'));
						$header[substr($lines[$i],0,$pos)] = trim(substr($lines[$i],$pos+2,(strlen($lines[$i])-$pos-2)));
						$lastheader=substr($lines[$i],0,$pos);
					}
				}
			} else {
				$body .= $lines[$i]."\r\n";
			}


		}
		$this->body =& $body;
		$this->headers =& $header;

		if ($header['Content-Type']) {
			$hdr = strtolower($header['Content-Type']);
			if (strpos($hdr,'text/plain') !== false) {

			} else {
				$this->setError('Error - only text/plain supported at this time');
				return false;
			}
		}
//echo "\n\n**".$header['Content-Type']."**\n\n";



		unset ($lines);
//system("echo \"mp: headers".implode("***\n",$header)."\n\" >> /tmp/forum.log");
//system("echo \"mp: body".$body."\n\" >> /tmp/forum.log");
		return true;
	}

	function &getBody() {
		return $this->body;
	}

	function &getHeader($header) {
		return $this->headers[$header];
	}

	function getSubject() {
		return $this->getHeader('Subject');
	}

	function getFromEmail() {
		$mail = $this->getHeader('From');
		if (strpos($mail,'(') !== false) {
			$email = substr($mail,0,strpos($mail,' '));
		} elseif (strpos($mail,'<') !== false) {
			$begin=(strpos($mail,'<')+1);
			$end = strpos($mail,'>');
			$email = substr($mail,$begin,($end-$begin));
		} else {
			$email = $mail;
		}
		$email = str_replace('"','',$email);

//echo "***$mail*$begin*$end**".$email."*****";
//system("echo \"mp: email".$email."\n\" >> /tmp/forum.log");
		return trim($email);
	}

	/*------------------------------------------------------------------------
	 *  MIME decoding functions
	 *-----------------------------------------------------------------------*/
	/*
	 * Subject and From decode implementation of RFC 2047
	 *
	 * @param String one or more encoded strings
	 * @return String strcat of all texts. Ignore all charsets
	 */
	function mime_header_decode_string($string) {

		$decoded_arr = $this->mime_header_decode($string);

		$return_string = $decoded_arr[0]['text'];

		/* Need a space? */
		for ($i=1; $i<count($decoded_arr); $i++) {
			$return_string.=$decoded_arr[$i]['text'];
		}

		DBG("mime_header: $string -> $return_string \n");

		return $return_string;
	}

	/**
	 * Mime header decoding
	 *
	 * @param String to decode
	 * @return Decoded String Array. return['charset'] and retutn['text']
	 *
	 *# FIXME: Should we use imap_mime_headres_decode? It's too havey to install
	 *  See http://us2.php.net/manual/en/function.imap-mime-header-decode.php
	 *
	 */
	function mime_header_decode($string) {
		/* We expecting series of encoded-word:
		 * encoded-word = "=?" charset "?" encoding "?" encoded-text "?="
		 * See more detail in RFC 2407
		 */
		$count=0;
		$strlen = strlen($string);

		for ($i=0; $i < $strlen; $i++) {
			/* Start seperation */
			if (!strcmp($string{$i} . $string{$i+1}, "=?")) {
				$count++;
			}

			/* End seperation */
			if( !strcmp($string{$i} . $string{$i+1}, "?=")) {
				$encoded_word_arr[$count].=$string{$i};
				$encoded_word_arr[$count].=$string{++$i};
				$count++; /* Null array should be OK */
				continue;
			}

			$encoded_word_arr[$count].=$string{$i};
		}

		for ($i=0; $i<count($encoded_word_arr); $i++) {
			$return_arr[$i] = $this->mime_header_one_word_decode($encoded_word_arr[$i]);
		}

		return $return_arr;
	}

	/**
	 * one word decode implementation of RFC 2047
	 */
	function mime_header_one_word_decode($string) {
		/* Default charset */
		$charset = "ASCII";

		/* We expecting : encoded-word = "=?" charset "?" encoding "?" encoded-text "?="
		 * See more detail in RFC 2407
		 */

		/* No encoded-word, return default */
		if (strncmp($string, "=?", 2)) {
			return array("charset"=>$charset, "text" => $string);
		}

		/*
		 * Expecting [0]='=', [1]=charset, [2]=B|Q, [3]=encoded-text
		 */
		$string_arr = explode('?', $string);

		if (!strcasecmp($string_arr[2], "B") && $string_arr[3]) {
			$string = base64_decode($string_arr[3]);
			$charset = $string_arr[1];
		} else if (!strcasecmp($string_arr[2], "Q") && $string_arr[3]) {
			$string = quoted_printable_decode($string_arr[3]);
			$charset = $string_arr[1];
		}

		/* Return what we have */
		$ret_arr = array("charset"=>$charset, "text" => $string);
		return $ret_arr;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
