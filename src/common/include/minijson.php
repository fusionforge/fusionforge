<?php
if (count(get_included_files()) <= 1 && !defined('__main__'))
	define('__main__', __FILE__);
/**
 * Minimal complete JSON generator and parser for FusionForge/Evolvis
 * and SimKolab, including for debugging output serialisation
 *
 * Copyright © 2010, 2011, 2012, 2014, 2016, 2017
 *	mirabilos <t.glaser@tarent.de>
 *
 * Provided that these terms and disclaimer and all copyright notices
 * are retained or reproduced in an accompanying document, permission
 * is granted to deal in this work without restriction, including un‐
 * limited rights to use, publicly perform, distribute, sell, modify,
 * merge, give away, or sublicence.
 *
 * This work is provided “AS IS” and WITHOUT WARRANTY of any kind, to
 * the utmost extent permitted by applicable law, neither express nor
 * implied; without malicious intent or gross negligence. In no event
 * may a licensor, author or contributor be held liable for indirect,
 * direct, other damage, loss, or other issues arising in any way out
 * of dealing in the work, even if advised of the possibility of such
 * damage or existence of a defect, except proven that it results out
 * of said person’s immediate fault when using the work as intended.
 *-
 * Do *not* use PHP’s json_encode because it is broken.
 * Note that JSON is case-sensitive and not binary-safe. My notes at:
 * http://www.mirbsd.org/cvs.cgi/contrib/hosted/tg/code/MirJSON/json.txt?rev=HEAD
 *
 * Call as CLI script to filter input as JSON pretty-printer. Options
 * are -c (compact output, no indentation or spaces), -d depth (parse
 * depth defaulting to 32), -r (pretty-print resources as string) and
 * -t truncsz (truncation size).
 */

/*-
 * I was really, really bad at writing parsers.
 * I still am really bad at writing parsers.
 *  -- Rasmus Lerdorf
 */

/**
 * Encodes an array (indexed or associative) or any value as JSON.
 * See minijson_encode_ob_string() for limitations on strings;
 * strings not encoded in UTF-8 and resources may not round-trip.
 *
 * in:	array	x (Value to be encoded)
 * in:	string	(optional) or bool false to skip beautification (default: '')
 * in:	integer	(optional) recursion depth (default: 32)
 * in:	integer	(optional) truncation size (default 0 to not truncate),
 *		makes output invalid JSON
 * in:	bool	(optional) whether to pretty-print resources (default: false)
 * out:	string	encoded
 */
function minijson_encode($x, $ri='', $depth=32, $truncsz=0, $dumprsrc=false) {
	ob_start();
	minijson_encode_ob($x, $ri, $depth, $truncsz, $dumprsrc);
	return ob_get_clean();
}

/**
 * Encodes a string as JSON. NUL terminates strings; strings
 * not comprised of only valid UTF-8 are interpreted as latin1.
 *
 * in:	string	x (Value to be encoded)
 * in:	integer	(optional) truncation size (default 0 to not truncate),
 *		makes output invalid JSON
 * out:	stdout	encoded
 */
function minijson_encode_ob_string($x, $truncsz=0) {
	if (!is_string($x))
		$x = strval($x);

	$Sx = strlen($x);

	if ($truncsz && ($Sx > $truncsz)) {
		echo 'TOO_LONG_STRING_TRUNCATED:';
		$Sx = $truncsz;
	}
	echo '"';

	/* assume UTF-8 first, for sanity */
	ob_start();	/* in case a restart is needed */

	$Sp = 0;
 minijson_encode_string_utf8:
	if ($Sp >= $Sx) {
		ob_end_flush();
		echo '"';
		return;
	}

	/* read next octet */
	$c = ord(($ch = $x[$Sp++]));

	if ($c === 0x5C) {
		/* just backslash */
		echo "\\\\";
		goto minijson_encode_string_utf8;
	}

	if ($c > 0x22 && $c < 0x7F) {
		/* printable ASCII except space, !, " and backslash */
		echo $ch;
		goto minijson_encode_string_utf8;
	}

	if ($c < 0x80) {
		/* C0 control character, space, !, " or DEL */
		if (($c & 0x7E) === 0x20)
			echo $ch;
		elseif ($c === 0x22)
			echo '\"';
		elseif ($c === 0x08)
			echo '\b';
		elseif ($c === 0x09)
			echo '\t';
		elseif ($c === 0x0A)
			echo '\n';
		elseif ($c === 0x0C)
			echo '\f';
		elseif ($c === 0x0D)
			echo '\r';
		elseif (!$c)
			$Sp = $Sx;
		else
			printf('\u%04X', $c);
		goto minijson_encode_string_utf8;
	}

	/* UTF-8 lead byte */
	if ($c < 0xC2 || $c >= 0xF8) {
		goto minijson_encode_string_latin1;
	} elseif ($c < 0xE0) {
		$wc = ($c & 0x1F) << 6;
		$wmin = 0x80;
		$Ss = 1;
	} elseif ($c < 0xF0) {
		$wc = ($c & 0x0F) << 12;
		$wmin = 0x800;
		$Ss = 2;
	} else {
		$wc = ($c & 0x07) << 18;
		$wmin = 0x10000;
		$Ss = 3;
	}
	/* UTF-8 trail bytes */
	if ($Sp + $Ss > $Sx)
		goto minijson_encode_string_latin1;
	while ($Ss--)
		if (($c = ord($x[$Sp++]) ^ 0x80) <= 0x3F)
			$wc |= $c << (6 * $Ss);
		else
			goto minijson_encode_string_latin1;
	/* complete wide character */
	if ($wc < $wmin)
		goto minijson_encode_string_latin1;

	if ($wc < 0x00A0)
		printf('\u%04X', $wc);
	elseif ($wc < 0x0800)
		echo chr(0xC0 | ($wc >> 6)) .
		    chr(0x80 | ($wc & 0x3F));
	elseif ($wc > 0xFFFD || ($wc >= 0xD800 && $wc <= 0xDFFF) ||
	    ($wc >= 0x2028 && $wc <= 0x2029)) {
		if ($wc > 0xFFFF) {
			if ($wc > 0x10FFFF)
				goto minijson_encode_string_latin1;
			/* UTF-16 */
			$wc -= 0x10000;
			printf('\u%04X\u%04X',
			    0xD800 | ($wc >> 10),
			    0xDC00 | ($wc & 0x03FF));
		} else
			printf('\u%04X', $wc);
	} else
		echo chr(0xE0 | ($wc >> 12)) .
		    chr(0x80 | (($wc >> 6) & 0x3F)) .
		    chr(0x80 | ($wc & 0x3F));

	/* process next char */
	goto minijson_encode_string_utf8;

 minijson_encode_string_latin1:
	/* failed, interpret as sorta latin1 but display only ASCII */
	ob_end_clean();

	$Sp = 0;
	while ($Sp < $Sx && ($c = ord(($ch = $x[$Sp++])))) {
		/* similar logic as above, just not as golfed for speed */
		if ($c >= 0x20 && $c < 0x7F) {
			if ($c === 0x22 || $c === 0x5C)
				echo "\\" . $ch;
			else
				echo $ch;
		} else switch ($c) {
		case 0x08:
			echo '\b';
			break;
		case 0x09:
			echo '\t';
			break;
		case 0x0A:
			echo '\n';
			break;
		case 0x0C:
			echo '\f';
			break;
		case 0x0D:
			echo '\r';
			break;
		default:
			printf('\u%04X', $c);
			break;
		}
	}
	echo '"';
}

/**
 * Encodes a value as JSON to the currently active output buffer.
 * See minijson_encode() for details.
 *
 * in:	array	x (Value to be encoded)
 * in:	string	indent or bool false to skip beautification
 * in:	integer	recursion depth
 * in:	integer	truncation size (0 to not truncate), makes output not JSON
 * in:	bool	whether to pretty-print resources
 * out:	stdout	encoded
 */
function minijson_encode_ob($x, $ri, $depth, $truncsz, $dumprsrc) {
	if (!$depth-- || !isset($x) || is_null($x) || (is_float($x) &&
	    (is_nan($x) || is_infinite($x)))) {
		echo 'null';
		return;
	}

	if ($x === true) {
		echo 'true';
		return;
	}
	if ($x === false) {
		echo 'false';
		return;
	}

	if (is_int($x)) {
		$y = (int)$x;
		$z = strval($y);
		if (strval($x) === $z) {
			echo $z;
			return;
		}
		goto minijson_encode_number;
	}

	if (is_float($x)) {
 minijson_encode_number:
		$rs = sprintf('%.14e', $x);
		$v = explode('e', $rs);
		$rs = rtrim($v[0], '0');
		if (substr($rs, -1) === '.')
			$rs .= '0';
		if ($v[1] !== '-0' && $v[1] !== '+0')
			$rs .= 'E' . $v[1];
		echo $rs;
		return;
	}

	/* strings or unknown scalars */
	if (is_string($x) ||
	    (!is_array($x) && !is_object($x) && is_scalar($x))) {
		minijson_encode_ob_string($x, $truncsz);
		return;
	}

	/* arrays, objects, resources, unknown non-scalars */

	if ($ri === false) {
		$si = false;
		$xi = '';
		$xr = '';
		$Sd = ':';
	} else {
		$si = $ri . '  ';
		$xi = "\n" . $si;
		$xr = "\n" . $ri;
		$Sd = ': ';
	}
	$Si = ',' . $xi;

	/* arrays, potentially empty or non-associative */
	if (is_array($x)) {
		if (!($n = count($x))) {
			echo '[]';
			return;
		}
		ob_start();
		echo '[';
		for ($v = 0; $v < $n; ++$v) {
			if (!array_key_exists($v, $x)) {
				/* failed — sparse or associative */
				ob_end_clean();
				goto minijson_encode_object;
			}
			echo $xi;
			minijson_encode_ob($x[$v],
			    $si, $depth, $truncsz, $dumprsrc);
			$xi = $Si;
		}
		ob_end_flush();
		echo $xr . ']';
		return;
	}

	/* http://de2.php.net/manual/en/function.is-resource.php#103942 */
	if (!is_object($x) && !is_null($rsrctype = @get_resource_type($x))) {
		if (!$dumprsrc) {
			minijson_encode_ob_string($x, $truncsz);
			return;
		}
		$rs = array(
			'_strval' => strval($x),
			'_type' => $rsrctype,
		);
		switch ($rsrctype) {
		case 'stream':
			$rs['info'] = stream_get_meta_data($x);
			break;
		case 'curl':
			$rs['info'] = curl_getinfo($x);
			$rs['private'] = curl_getinfo($x, CURLINFO_PRIVATE);
			break;
		case 'GMP integer':
			$rs['value'] = gmp_strval($x);
			break;
		case 'OpenSSL key':
			$rs['info'] = openssl_pkey_get_details($x);
			break;
		case 'pgsql link':
		case 'pgsql link persistent':
			$rs['err'] = pg_last_error($x); // must be first
			$rs['db'] = pg_dbname($x);
			$rs['host'] = pg_host($x);
			$rs['status'] = pg_connection_status($x);
			$rs['txn'] = pg_transaction_status($x);
			break;
		case 'pgsql result':
			$rs['status'] = pg_result_status($x, PGSQL_STATUS_STRING);
			break;
		}
		echo '{' . $xi . '"\u0000resource"' . $Sd;
		minijson_encode_ob($rs, $si, $depth + 1, $truncsz, $dumprsrc);
		echo $xr . '}';
		return;
	}

	/* treat everything else as Object */

	/* PHP objects are mostly like associative arrays */
	if (!($x = (array)$x)) {
		echo '{}';
		return;
	}
 minijson_encode_object:
	$s = array();
	foreach (array_keys($x) as $k) {
		$v = $k;
		if (!is_string($v))
			$v = strval($v);
		/* protected and private members have NULs there */
		if (strpos($v, "\0") !== false)
			$v = str_replace("\0", "\\", $v);
		$s[$k] = $v;
	}
	asort($s, SORT_STRING);
	echo '{';
	foreach ($s as $k => $v) {
		echo $xi;
		minijson_encode_ob_string($v, $truncsz);
		echo $Sd;
		minijson_encode_ob($x[$k], $si, $depth, $truncsz, $dumprsrc);
		$xi = $Si;
	}
	echo $xr . '}';
}

/**
 * Decodes a UTF-8 string from JSON (ECMA 262).
 * Empty Objects are returned as empty PHP arrays and thus
 * trip around as empty Arrays.
 *
 * in:	string	JSON text to decode
 * in:	reference output Value (or error string)
 * in:	integer	(optional) recursion depth (default: 32)
 * out:	boolean	false if an error occured, true if the output is valid
 */
function minijson_decode($s, &$ov, $depth=32) {
	if (!isset($s))
		$s = '';
	elseif (!is_string($s))
		$s = strval($s);

	$Sp = 0;
	$Sx = strlen($s);
	$rv = false;

	/* skip Byte Order Mark if present */
	if (substr($s, 0, 3) === "\xEF\xBB\xBF")
		$Sp = 3;

	/* skip leading whitespace */
	minijson_skip_wsp($s, $Sp, $Sx);

	/* recursively parse input */
	if ($Sp < $Sx)
		$rv = minijson_decode_value($s, $Sp, $Sx, $ov, $depth);
	else
		$ov = 'empty input';

	/* skip trailing whitespace */
	if ($rv) {
		minijson_skip_wsp($s, $Sp, $Sx);
		/* end of string? */
		if ($Sp < $Sx) {
			$ov = 'expected EOS';
			$rv = false;
		}
	}

	/* amend errors by erroring offset */
	if (!$rv)
		$ov = sprintf('%s at offset 0x%0' . strlen(dechex($Sx)) . 'X',
		    $ov, $Sp);
	return $rv;
}

/* skip all characters that are JSON whitespace */
function minijson_skip_wsp($s, &$Sp, $Sx) {
	while ($Sp < $Sx)
		switch (ord($s[$Sp])) {
		default:
			return;
		case 0x09:
		case 0x0A:
		case 0x0D:
		case 0x20:
			++$Sp;
		}
}

function minijson_decode_array($s, &$Sp, $Sx, &$ov, $depth) {
	$ov = array();

	/* skip optional whitespace between tokens */
	minijson_skip_wsp($s, $Sp, $Sx);

	/* check for end of array or first member */
	if ($Sp >= $Sx) {
 minijson_decode_array_eos:
		$ov = 'unexpected EOS in Array';
		return false;
	}
	switch ($s[$Sp]) {
	case ',':
		$ov = 'unexpected leading comma in Array';
		return false;
	case ']':
		++$Sp;
		return true;
	}

	goto minijson_decode_array_member;

 minijson_decode_array_loop:
	/* skip optional whitespace between tokens */
	minijson_skip_wsp($s, $Sp, $Sx);

	/* check for end of array or next member */
	if ($Sp >= $Sx)
		goto minijson_decode_array_eos;
	switch ($s[$Sp++]) {
	case ']':
		return true;
	case ',':
		break;
	default:
		--$Sp;
		$ov = 'missing comma in Array';
		return false;
	}

 minijson_decode_array_member:
	/* parse the member value */
	$v = NULL;
	if (!minijson_decode_value($s, $Sp, $Sx, $v, $depth)) {
		/* pass through error code */
		$ov = $v;
		return false;
	}
	/* consume, rinse, repeat */
	$ov[] = $v;
	goto minijson_decode_array_loop;
}

function minijson_decode_object($s, &$Sp, $Sx, &$ov, $depth) {
	$ov = array();
	/* skip optional whitespace between tokens */
	minijson_skip_wsp($s, $Sp, $Sx);

	/* check for end of object or first member */
	if ($Sp >= $Sx) {
 minijson_decode_object_eos:
		$ov = 'unexpected EOS in Object';
		return false;
	}
	switch ($s[$Sp]) {
	case ',':
		$ov = 'unexpected leading comma in Object';
		return false;
	case '}':
		++$Sp;
		return true;
	}

	goto minijson_decode_object_member;

 minijson_decode_object_loop:
	/* skip optional whitespace between tokens */
	minijson_skip_wsp($s, $Sp, $Sx);

	/* check for end of object or next member */
	if ($Sp >= $Sx)
		goto minijson_decode_object_eos;
	switch ($s[$Sp++]) {
	case '}':
		return true;
	case ',':
		break;
	default:
		--$Sp;
		$ov = 'missing comma in Object';
		return false;
	}

 minijson_decode_object_member:
	/* skip optional whitespace between tokens */
	minijson_skip_wsp($s, $Sp, $Sx);

	/* look for the member key */
	if ($Sp >= $Sx)
		goto minijson_decode_object_eos;
	if ($s[$Sp++] !== '"') {
		--$Sp;
		$ov = 'expected key string for Object member';
		return false;
	}
	if (($k = minijson_decode_string($s, $Sp, $Sx)) !== true) {
		ob_end_clean();
		/* pass through error code */
		$ov = $k;
		return false;
	}
	$k = ob_get_clean();

	/* skip optional whitespace between tokens */
	minijson_skip_wsp($s, $Sp, $Sx);

	/* check for separator between key and value */
	if ($Sp >= $Sx)
		goto minijson_decode_object_eos;
	if ($s[$Sp++] !== ':') {
		--$Sp;
		$ov = 'expected colon in Object member';
		return false;
	}

	/* parse the member value */
	$v = NULL;
	if (!minijson_decode_value($s, $Sp, $Sx, $v, $depth)) {
		/* pass through error code */
		$ov = $v;
		return false;
	}
	/* consume, rinse, repeat */
	$ov[$k] = $v;
	goto minijson_decode_object_loop;
}

function minijson_decode_value($s, &$Sp, $Sx, &$ov, $depth) {
	/* skip optional whitespace between tokens */
	minijson_skip_wsp($s, $Sp, $Sx);

	/* parse begin of Value token */
	if ($Sp >= $Sx) {
		$ov = 'unexpected EOS, Value expected';
		return false;
	}
	$c = $s[$Sp++];

	/* style: falling through exits with false */
	if ($c === 'n') {
		/* literal null? */
		if (substr($s, $Sp, 3) === 'ull') {
			$Sp += 3;
			$ov = NULL;
			return true;
		}
		--$Sp;
		$ov = 'expected “ull” after “n”';
	} elseif ($c === 't') {
		/* literal true? */
		if (substr($s, $Sp, 3) === 'rue') {
			$Sp += 3;
			$ov = true;
			return true;
		}
		--$Sp;
		$ov = 'expected “rue” after “t”';
	} elseif ($c === 'f') {
		/* literal false? */
		if (substr($s, $Sp, 4) === 'alse') {
			$Sp += 4;
			$ov = false;
			return true;
		}
		--$Sp;
		$ov = 'expected “alse” after “f”';
	} elseif ($c === '[') {
		if (--$depth > 0)
			return minijson_decode_array($s, $Sp, $Sx, $ov, $depth);
		--$Sp;
		$ov = 'recursion limit exceeded by Array';
	} elseif ($c === '{') {
		if (--$depth > 0)
			return minijson_decode_object($s, $Sp, $Sx, $ov, $depth);
		--$Sp;
		$ov = 'recursion limit exceeded by Object';
	} elseif ($c === '"') {
		if (($ov = minijson_decode_string($s, $Sp, $Sx)) !== true) {
			ob_end_clean();
			return false;
		}
		$ov = ob_get_clean();
		return true;
	} elseif ($c === '-' || (ord($c) >= 0x30 && ord($c) <= 0x39)) {
		--$Sp;
		return minijson_decode_number($s, $Sp, $Sx, $ov);
	} elseif (ord($c) >= 0x20 && ord($c) <= 0x7E) {
		--$Sp;
		$ov = "unexpected “{$c}”, Value expected";
	} else {
		--$Sp;
		$ov = sprintf('unexpected 0x%02X, Value expected', ord($c));
	}
	return false;
}

function minijson_decode_string($s, &$Sp, $Sx) {
	ob_start();
 minijson_decode_string_loop:
	if ($Sp >= $Sx)
		return 'unexpected EOS in String';
	/* get next octet; switch on what to do with it */
	if (($ch = $s[$Sp++]) === '"') {
		/* regular exit point for the loop */
		return true;
	}
	/* backslash escape? */
	if ($ch === "\\") {
		if ($Sp >= $Sx)
			return 'unexpected EOS after backslash in String';
		$ch = $s[$Sp++];
		if ($ch === '"' || $ch === '/' || $ch === "\\")
			echo $ch;
		elseif ($ch === 't')
			echo "\x09";
		elseif ($ch === 'n')
			echo "\x0A";
		elseif ($ch === 'r')
			echo "\x0D";
		elseif ($ch === 'b')
			echo "\x08";
		elseif ($ch === 'f')
			echo "\x0C";
		elseif ($ch !== 'u') {
			$Sp -= 2;
			return "invalid escape '\\$ch' in String";
		} else {
			$surrogate = 0;
 minijson_decode_string_unicode_escape:
			$wc = 0;
			if ($Sp + 4 > $Sx) {
				$Sp -= 2;
				return 'unexpected EOS in Unicode escape sequence';
			}
			for ($tmp = 1; $tmp <= 4; $tmp++) {
				$wc <<= 4;
				switch (ord($s[$Sp++])) {
				case 0x30:			   break;
				case 0x31:		$wc +=  1; break;
				case 0x32:		$wc +=  2; break;
				case 0x33:		$wc +=  3; break;
				case 0x34:		$wc +=  4; break;
				case 0x35:		$wc +=  5; break;
				case 0x36:		$wc +=  6; break;
				case 0x37:		$wc +=  7; break;
				case 0x38:		$wc +=  8; break;
				case 0x39: 		$wc +=  9; break;
				case 0x41: case 0x61:	$wc += 10; break;
				case 0x42: case 0x62:	$wc += 11; break;
				case 0x43: case 0x63:	$wc += 12; break;
				case 0x44: case 0x64:	$wc += 13; break;
				case 0x45: case 0x65:	$wc += 14; break;
				case 0x46: case 0x66:	$wc += 15; break;
				default:
					--$Sp;
					return "invalid hex digit #$tmp/4 in Unicode escape sequence";
				}
			}
			if ($surrogate) {
				if ($wc < 0xDC00 || $wc > 0xDFFF) {
					$Sp -= 6;
					return sprintf('expected low surrogate, not %04X, after high surrogate %04X', $wc, $surrogate);
				}
				$wc = 0x10000 + (($surrogate & 0x03FF) << 10) + ($wc & 0x03FF);
			} elseif ($wc >= 0xD800 && $wc <= 0xDBFF) {
				$surrogate = $wc;
				/* UTF-16 expects the low surrogate */
				if (substr($s, $Sp, 2) !== '\u')
					return 'expected Unicode escape after high surrogate';
				$Sp += 2;
				goto minijson_decode_string_unicode_escape;
			} elseif ($wc >= 0xDC00 && $wc <= 0xDFFF) {
				$Sp -= 6;
				return sprintf('loose low surrogate %04X', $wc);
			} elseif ($wc < 1 || $wc > 0xFFFD) {
				$Sp -= 6;
				return sprintf('non-Unicode escape %04X', $wc);
			}
			if ($wc < 0x80) {
				echo chr($wc);
				goto minijson_decode_string_loop;
			}
 minijson_decode_string_unicode_char:
			if ($wc < 0x0800)
				echo chr(0xC0 | ($wc >> 6)) .
				    chr(0x80 | ($wc & 0x3F));
			elseif ($wc <= 0xFFFF)
				echo chr(0xE0 | ($wc >> 12)) .
				    chr(0x80 | (($wc >> 6) & 0x3F)) .
				    chr(0x80 | ($wc & 0x3F));
			else
				echo chr(0xF0 | ($wc >> 18)) .
				    chr(0x80 | (($wc >> 12) & 0x3F)) .
				    chr(0x80 | (($wc >> 6) & 0x3F)) .
				    chr(0x80 | ($wc & 0x3F));
		}
		goto minijson_decode_string_loop;
	}
	if (($c = ord($ch)) < 0x20) {
		--$Sp;
		return sprintf('unescaped control character 0x%02X in String', $c);
	}
	if ($c < 0x80) {
		echo $ch;
		goto minijson_decode_string_loop;
	}
	/* decode UTF-8 */
	if ($c < 0xC2 || $c >= 0xF0) {
		--$Sp;
		return sprintf('invalid UTF-8 lead octet 0x%02X in String', $c);
	}
	if ($c < 0xE0) {
		$wc = ($c & 0x1F) << 6;
		$wmin = 0x80; /* redundant */
		$Ss = 1;
	} else {
		$wc = ($c & 0x0F) << 12;
		$wmin = 0x800;
		$Ss = 2;
	}
	if ($Sp + $Ss > $Sx) {
		--$Sp;
		return 'unexpected EOS after UTF-8 lead byte in String';
	}
	while ($Ss--)
		if (($c = ord($s[$Sp++]) ^ 0x80) <= 0x3F)
			$wc |= $c << (6 * $Ss);
		else {
			--$Sp;
			return sprintf('invalid UTF-8 trail octet 0x%02X in String', $c ^ 0x80);
		}
	if ($wc < $wmin) {
		$Sp -= 3; /* only for E0‥EF-led sequence */
		return sprintf('non-minimalistic encoding for Unicode char %04X in String', $wc);
	}

	if ($wc >= 0xD800 && $wc <= 0xDFFF) {
		$Sp -= 3;
		return sprintf('unescaped surrogate %04X in String', $wc);
	}
	if ($wc <= 0xFFFD)
		goto minijson_decode_string_unicode_char;
	$Sp -= 3;
	return sprintf('non-Unicode char %04X in String', $wc);
}

function minijson_decode_number($s, &$Sp, $Sx, &$ov) {
	$matches = array('');
	if (!preg_match('/-?(?:0|[1-9][0-9]*)(?:\.[0-9]+)?(?:[Ee][+-]?[0-9]+)?/A',
	    $s, $matches, 0, $Sp) || strlen($matches[0]) < 1) {
		$ov = 'expected Number';
		return false;
	}
	$Sp += strlen($matches[0]);
	if (strpos($matches[0], '.') === false) {
		/* possible integer */
		$ov = (int)$matches[0];
		if (strval($ov) === $matches[0])
			return true;
	}
	$ov = (float)$matches[0];
	return true;
}

if (defined('__main__') && constant('__main__') === __FILE__) {
	function usage($rc=1) {
		fwrite(STDERR,
		    "Syntax: minijson.php [-cr] [-d depth] [-t truncsz]\n");
		exit($rc);
	}

	$indent = '';
	$depth = 32;
	$truncsz = 0;
	$rsrc = false;
	array_shift($argv);	/* argv[0] */
	while (count($argv)) {
		$arg = array_shift($argv);
		/* only options, no arguments (Unix filter) */
		if ($arg[0] !== '-')
			usage();
		if ($arg === '--' && count($argv))
			usage();
		if ($arg === '-')
			usage();
		$arg = str_split($arg);
		array_shift($arg);	/* initial ‘-’ */
		/* parse select arguments */
		while (count($arg)) {
			switch ($arg[0]) {
			case 'c':
				$indent = false;
				break;
			case 'd':
				if (!count($argv))
					usage();
				$depth = array_shift($argv);
				if (!preg_match('/^[1-9][0-9]*$/', $depth))
					usage();
				if (strval((int)$depth) !== $depth)
					usage();
				$depth = (int)$depth;
				break;
			case 'h':
			case '?':
				usage(0);
			case 'r':
				$rsrc = true;
				break;
			case 't':
				if (!count($argv))
					usage();
				$truncsz = array_shift($argv);
				if (!preg_match('/^[1-9][0-9]*$/', $truncsz))
					usage();
				if (strval((int)$truncsz) !== $truncsz)
					usage();
				$truncsz = (int)$truncsz;
				break;
			default:
				usage();
			}
			array_shift($arg);
		}
	}

	$idat = file_get_contents('php://stdin');
	$odat = '';
	if (!minijson_decode($idat, $odat, $depth)) {
		fwrite(STDERR, 'JSON decoding of input failed: ' .
		    minijson_encode(array(
			'input' => $idat,
			'message' => $odat,
		    )) . "\n");
		exit(1);
	}
	fwrite(STDOUT, minijson_encode($odat, $indent, $depth,
	    $truncsz, $rsrc) . "\n");
	exit(0);
}
