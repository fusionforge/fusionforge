<?php
/**
 * Copyright 2005-2012, Codendi Team
 * Copyright 2013, Franck Villaume - TrivialDev
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

require_once $gfcommon.'include/utils.php';

class Mail {

	var $_headerCharset;
	var $_bodyCharset;
	var $_subject;
	var $_body;
	var $_from;
	var $_to;
	var $_bcc;
	var $_cc;
	var $_mimeType;
	var $_additionalHeaders;

	function Mail() {
		$charset = _('UTF-8');
		if (!$charset) {
			$charset = 'UTF-8';
		}
		$this->setHeaderCharset($charset);
		$this->setBodyCharset($charset);
		$this->setMimeType('text/plain');
		$this->setTo('', true);
		$this->setBcc('', true);
		$this->setCc('', true);
		$this->setBody('', true);
		$this->clearAdditionalHeaders();
	}

	function setHeaderCharset($charset) {
		$this->_headerCharset = $charset;
	}

	function getHeaderCharset() {
		return $this->_headerCharset;
	}


	function setBodyCharset($charset) {
		$this->_bodyCharset = $charset;
	}

	function getBodyCharset() {
		return $this->_bodyCharset;
	}

	function setSubject($subject) {
		$this->_subject = $subject;
	}

	function getSubject() {
		return $this->_subject;
	}

	function getEncodedSubject() {
		return $this->_encodeHeader($this->_subject, $this->getHeaderCharset());
	}

	/**
	* Function to encode a header if necessary
	* according to RFC2047
	* Filename.......: class.html.mime.mail.inc
	* Project........: HTML Mime mail class
	* Last Modified..: Date: 2002/07/24 13:14:10
	* CVS Revision...: Revision: 1.4
	* Copyright......: 2001, 2002 Richard Heyes
	*/
	function _encodeHeader($input, $charset) {
		preg_match_all('/(\s?\w*[\x80-\xFF]+\w*\s?)/', $input, $matches);
		foreach ($matches[1] as $value) {
			$replacement = preg_replace('/([\x80-\xFF])/e', '"=" . strtoupper(dechex(ord("\1")))', $value);
			$input = str_replace($value, '=?' . $charset . '?Q?' . $replacement . '?=', $input);
		}
		return $input;
	}

	function setBody($body) {
		$this->_body = $body;
	}

	function getBody() {
		return $this->_body;
	}

	function getEncodedBody() {
		return util_convert_body($this->getBody(), $this->getBodyCharset());
	}

	function setFrom($from) {
		$this->_from = $this->_validateRecipient($from);
	}

	function getFrom() {
		if (!$this->_from) {
			return 'noreply@'.forge_get_config('web_host');
		}
		return $this->_from;
	}

	/**
	* Check if given mail is a valid (Ie. Active or Restricted) user.
	*
	* The given mail can by both user_name or email. Return form is always the
	* user email.
	*
	* @param	$list	(IN) list of email addresses separated by , or ;
	* @return	list of email separated by ,
	*/
	function _validateRecipient($list) {
		$recipArray = preg_split('/[;,]/', $list);
		$retArray = array();
		foreach($recipArray as $email) {
			$email = trim($email);
			if(!empty($email)) {
				$user = UserManager::instance()->getUserByEmail($email);
				if ($user) {
					$allowed_status = array('A', 'R', 'P', 'V', 'W');
					$one_with_status_allowed_found = false;
					while ( !$one_with_status_allowed_found) {
						if (in_array($user->getStatus(), $allowed_status)) {
							$retArray[] = '"'.$this->_encodeHeader($user->getRealName(), $this->getHeaderCharset()).'" <'.$user->getEmail().'>';
							$one_with_status_allowed_found = true;
						}
					}
				} else {
					if (validate_email($email)) {
						$retArray[] = $email;
					}
				}
			}
		}
		return implode(', ', $retArray);
	}


	function setTo($to, $raw = false) {
		if ($raw)
			$this->_to = $to;
		else
			$this->_to = $this->_validateRecipient($to);
	}

	function getTo()  {
		if (!$this->_to) {
			return 'noreply@'.forge_get_config('web_host');
		}
		return $this->_to;
	}

	function setBcc($bcc, $raw = false) {
		if ($raw)
			$this->_bcc = $bcc;
		else
			$this->_bcc = $this->_validateRecipient($bcc);

		if (forge_get_config('bcc_all_emails') != '') {
			$this->addBcc(forge_get_config('bcc_all_emails'));
		}
	}

	function addBcc($addbcc, $raw = false) {
		if ($raw) {
			$this->_bcc .= ', '.$addbcc;
		} else {
			$_addbcc = $this->_validateRecipient($addbcc);
			if (strlen($_addbcc)) {
				$this->_bcc .= ', '.$_addbcc;
			}
		}
	}

	function getBcc()  {
		return $this->_bcc;
	}

	function setCc($cc, $raw = false) {
		if($raw)
			$this->_cc = $cc;
		else
			$this->_cc = $this->_validateRecipient($cc);
	}

	function getCc()  {
		return $this->_cc;
	}

	function setMimeType($mimeType) {
		$this->_mimeType = $mimeType;
	}

	function getMimeType() {
		return $this->_mimeType;
	}

	function clearAdditionalHeaders() {
		$this->_additionalHeaders = array();
	}

	function addAdditionalHeader($name, $value) {
		$this->_additionalHeaders[$name] = $value;
	}

	function removeAdditionalHeader($name) {
		if (isset($this->_additionalHeaders[$name])) {
			unset($this->_additionalHeaders[$name]);
		}
	}

	/**
	* @returns TRUE if the mail was successfully accepted for delivery, FALSE otherwise.
	*          It is important to note that just because the mail was accepted for delivery,
	*          it does NOT mean the mail will actually reach the intended destination.
	**/
	function send() {
		$sys_lf="\n";

		$mail = "To: ".$this->getTo().$sys_lf;
		$mail .= "From: ".$this->getFrom().$sys_lf;
		$mail .= "Content-type: ".$this->getMimeType()."; charset=".$this->getBodyCharset().$sys_lf;
		$cc = $this->getCc();
		if (strlen($cc) > 0) {
			$mail .= "Cc: ".$cc.$sys_lf;
		}
		$bcc = $this->getBcc();
		if (strlen($bcc) > 0) {
			$mail .= "Bcc: ".$bcc.$sys_lf;
		}
		foreach($this->_additionalHeaders as $name => $value) {
			$mail .= $name.": ".$value.$sys_lf;
		}
		$mail .= $this->getEncodedSubject().$sys_lf;
		$mail .= $this->getEncodedBody().$sys_lf;
		return $this->_sendmail($mail);
	}

	/**
	* Perform effective email send.
	* @access	protected
	*/
	function _sendmail($mail) {
		$from = $this->getFrom();
		$handle = popen(forge_get_config('sendmail_path')." -f'$from' -t -i", 'w');
		fwrite($handle, $mail);
		pclose($handle);
		return true;
	}
}
