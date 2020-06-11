<?php
if (!defined('__main__') && count(get_included_files()) <= 1 && count(debug_backtrace()) < 1)
	define('__main__', __FILE__);
/**
 * Minimal complete JSON generator and parser for FusionForge/Evolvis
 * and SimKolab, including for debugging output serialisation
 *
 * Copyright © 2020
 *	mirabilos <m@mirbsd.org>
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
 * depth defaulting to 32), -r (pretty-print resources as object) and
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
 * strings not encoded in UTF-8 and resources do not round-trip.
 *
 * Optional arguments use the default value if NULL is passed for each.
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
	minijson_encode_ob($x, !is_null($ri) ? $ri : '', $depth ? $depth : 32,
	    $truncsz ? $truncsz : 0, $dumprsrc ? $dumprsrc : false);
	return ob_get_clean();
}

/**
 * Encodes a string as JSON. NUL terminates strings; strings
 * not comprised of only valid UTF-8 are interpreted as latin1.
 *
 * in:	string	x (Value to be encoded)
 * in:	integer	(optional) truncation size (default 0 to not truncate),
 *		makes output invalid JSON
 * in:  string  (optional) always '"'
 * out:	stdout	encoded
 */
function minijson_encode_ob_string($x, $truncsz=0, $leader='"') {
	if (!is_string($x))
		$x = strval($x);

	$Sx = strlen($x);

	if ($truncsz && ($Sx > $truncsz)) {
		echo 'TOO_LONG_STRING_TRUNCATED:';
		$Sx = $truncsz;
	}
	echo $leader;

	/* assume UTF-8 first, for sanity */
	ob_start();	/* in case a restart is needed */

	$Sp = 0;
	while (true) {
		if ($Sp >= $Sx) {
			echo '"';
			ob_end_flush();
			return;
		}

		/* read next octet */
		$c = ord(($ch = $x[$Sp++]));

		if ($c > 0x22 && $c < 0x7F) {
			/* printable ASCII except space, !, " */
			if ($c === 0x5C)
				echo $ch;
			echo $ch;
			continue;
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
			continue;
		}

		/* UTF-8 lead byte */
		if ($c < 0xE0) {
			if ($c < 0xC2)
				break;
			$wc = ($c & 0x1F) << 6;
			$wmin = 0x80;
			$Ss = 1;
		} elseif ($c < 0xF0) {
			$wc = ($c & 0x0F) << 12;
			$wmin = 0x800;
			$Ss = 2;
		} elseif ($c < 0xF8) {
			$wc = ($c & 0x07) << 18;
			$wmin = 0x10000;
			$Ss = 3;
		} else {
			break;
		}
		$u = $ch;
		/* UTF-8 trail bytes */
		if ($Sp + $Ss > $Sx)
			break;
		while ($Ss--)
			if (($c = ord(($ch = $x[$Sp++])) ^ 0x80) <= 0x3F) {
				$wc |= $c << (6 * $Ss);
				$u .= $ch;
			} else
				break 2;
		/* complete wide character */
		if ($wc < $wmin)
			break;

		if (($wc >= 0x00A0 && $wc < 0x2028) ||
		    ($wc > 0x2029 && $wc < 0xD800) ||
		    ($wc > 0xDFFF && $wc <= 0xFFFD))
			echo $u;
		elseif ($wc > 0xFFFF) {
			if ($wc > 0x10FFFF)
				break;
			/* UTF-16 */
			$wc -= 0x10000;
			printf('\u%04X\u%04X',
			    0xD800 | ($wc >> 10),
			    0xDC00 | ($wc & 0x03FF));
		} else
			printf('\u%04X', $wc);

		/* process next char */
	}

	/* failed, interpret as sorta latin1 but display only ASCII */
	ob_end_clean();

	$Sp = 0;
	while ($Sp < $Sx && ($c = ord(($ch = $x[$Sp++])))) {
		/* similar logic as above, just not as golfed for speed */
		if ($c >= 0x20 && $c < 0x7F) {
			if ($c === 0x22 || $c === 0x5C)
				echo "\\";
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
	if (!$depth-- || !isset($x) || is_null($x)) {
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

	if (is_int($x) || is_float($x)) {
		if (is_int($x)) {
			$y = (int)$x;
			$z = strval($y);
			if (strval($x) === $z) {
				echo $z;
				return;
			}
		} else if (is_nan($x) || is_infinite($x)) {
			echo 'null';
			return;
		}
		$rs = sprintf('%.14e', $x);
		$v = explode('e', $rs);
		$rs = rtrim($v[0], '0');
		echo $rs;
		if ($rs[strlen($rs) - 1] === '.')
			echo '0';
		if ($v[1] !== '-0' && $v[1] !== '+0')
			echo 'E' . $v[1];
		return;
	}

	/* strings */
	if (is_string($x)) {
		minijson_encode_ob_string($x, $truncsz);
		return;
	}

	/* arrays, objects, resources, unknown scalars and nōn-scalars */

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
		echo '['/*]*/;
		$isarr = true;
		for ($v = 0; $v < $n; ++$v) {
			if (!array_key_exists($v, $x)) {
				/* failed — sparse or associative */
				$isarr = false;
				break;
			}
			echo $xi;
			minijson_encode_ob($x[$v],
			    $si, $depth, $truncsz, $dumprsrc);
			$xi = $Si;
		}
		if ($isarr) {
			ob_end_flush();
			echo $xr . /*[*/']';
			return;
		}
		ob_end_clean();
		/* sparse or associative array */
	} elseif (is_object($x)) {
		/* PHP objects are mostly like associative arrays */
		if (!($x = (array)$x)) {
			echo '{}';
			return;
		}
		/* converted into nōn-empty associative array */
	/* https://www.php.net/manual/en/function.is-resource.php#103942 */
	} elseif (!is_null($rsrctype = @get_resource_type($x))) {
		if (!$dumprsrc) {
			$rs = (int)$x;
			$x = strval($x);
			$rsrctype = 'resource('/*)*/ . $rs . ($rsrctype ?
			    (/*(*/')<' . $rsrctype . '>') : /*(*/'?)');
			if ($x === ('Resource id #' . $rs))
				$x = $rsrctype . ';';
			elseif (strncmp($x, 'Resource ', 9) === 0)
				$x = $rsrctype . substr($x, 8);
			else
				$x = $rsrctype . '{' . $x . '}';
			minijson_encode_ob_string($x, $truncsz, '"\u0000');
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
		echo '{'/*}*/ . $xi . '"\u0000resource:"' . $Sd;
		minijson_encode_ob($rs, $si, $depth + 1, $truncsz, $dumprsrc);
		echo $xr . /*{*/'}';
		return;
	} elseif (is_scalar($x)) {
		/* unknown scalar (treat as String) */
		minijson_encode_ob_string($x, $truncsz);
		return;
	} else {
		/* unknown nōn-scalar (treat as Object and cast, see above) */
		if (!($x = (array)$x)) {
			echo '{}';
			return;
		}
	}

	/* array, object or unknown nōn-scalar, cast as associative array */

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
	echo '{'/*}*/;
	foreach ($s as $k => $v) {
		echo $xi;
		minijson_encode_ob_string($v, $truncsz);
		echo $Sd;
		minijson_encode_ob($x[$k], $si, $depth, $truncsz, $dumprsrc);
		$xi = $Si;
	}
	echo $xr . /*{*/'}';
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
	if (strncmp($s, "\xEF\xBB\xBF", 3) === 0)
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
			$ov = 'unexpected trailing garbage';
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
		if (($c = ord($s[$Sp])) === 0x20 ||
		    $c === 0x0A || $c === 0x09 || $c === 0x0D)
			++$Sp;
		else
			return $c;
	return -1;
}

function minijson_decode_array($s, &$Sp, $Sx, &$ov, $depth) {
	$ov = array();

	/* skip optional whitespace between tokens */
	$c = minijson_skip_wsp($s, $Sp, $Sx);

	/* check for end of array or first member */
	if ($c === 0x5D) {
		++$Sp;
		return true;
	} elseif ($c === -1) {
		$ov = 'unexpected EOS after ['/*]*/;
		return false;
	}

	while (true) {
		/* parse the member value */
		$v = NULL;
		if (!minijson_decode_value($s, $Sp, $Sx, $v, $depth)) {
			/* pass through error code */
			$ov = $v;
			return false;
		}
		/* consume, rinse, repeat */
		$ov[] = $v;

		/* skip optional whitespace between tokens */
		$c = minijson_skip_wsp($s, $Sp, $Sx);

		/* check for end of array or next member */
		if ($c === 0x2C) {
			++$Sp;
		} elseif ($c === 0x5D) {
			++$Sp;
			return true;
		} else {
			$ov = /*[*/'comma (,) or ] expected';
			return false;
		}
	}
}

function minijson_decode_object($s, &$Sp, $Sx, &$ov, $depth) {
	$ov = array();
	/* skip optional whitespace between tokens */
	$c = minijson_skip_wsp($s, $Sp, $Sx);

	/* check for end of object or first member */
	if ($c === 0x7D) {
		++$Sp;
		return true;
	} elseif ($c === -1) {
		$ov = 'unexpected EOS after {'/*}*/;
		return false;
	}

	while (true) {
		/* skip optional whitespace between tokens */
		$c = minijson_skip_wsp($s, $Sp, $Sx);

		/* look for the member key */
		if ($c !== 0x22) {
			$ov = 'key string for Object member expected';
			return false;
		}
		++$Sp;
		if (($k = minijson_decode_string($s, $Sp, $Sx)) !== true) {
			ob_end_clean();
			/* pass through error code */
			$ov = $k;
			return false;
		}
		$k = ob_get_clean();

		/* skip optional whitespace between tokens */
		$c = minijson_skip_wsp($s, $Sp, $Sx);

		/* check for separator between key and value */
		if ($c !== 0x3A) {
			$ov = 'colon (:) expected';
			return false;
		}
		++$Sp;

		/* parse the member value */
		$v = NULL;
		if (!minijson_decode_value($s, $Sp, $Sx, $v, $depth)) {
			/* pass through error code */
			$ov = $v;
			return false;
		}
		/* consume, rinse, repeat */
		$ov[$k] = $v;

		/* skip optional whitespace between tokens */
		$c = minijson_skip_wsp($s, $Sp, $Sx);

		/* check for end of object or next member */
		if ($c === 0x2C) {
			++$Sp;
		} elseif ($c === 0x7D) {
			++$Sp;
			return true;
		} else {
			$ov = /*{*/'comma (,) or } expected';
			return false;
		}
	}
}

function minijson_decode_value($s, &$Sp, $Sx, &$ov, $depth) {
	/* skip optional whitespace between tokens */
	$c = minijson_skip_wsp($s, $Sp, $Sx);

	/* parse start of Value token; falling through exits with false */
	if ($c === 0x22) {
		++$Sp;
		if (($ov = minijson_decode_string($s, $Sp, $Sx)) !== true) {
			ob_end_clean();
			return false;
		}
		$ov = ob_get_clean();
		return true;
	} elseif ($c === 0x7B) {
		if (--$depth > 0) {
			++$Sp;
			return minijson_decode_object($s, $Sp, $Sx, $ov, $depth);
		}
		$ov = 'recursion limit exceeded by Object';
	} elseif ($c === 0x5B) {
		if (--$depth > 0) {
			++$Sp;
			return minijson_decode_array($s, $Sp, $Sx, $ov, $depth);
		}
		$ov = 'recursion limit exceeded by Array';
	} elseif ($c <= 0x39 && ($c >= 0x30 || $c === 0x2D)) {
		return minijson_decode_number($s, $Sp, $Sx, $ov);
	} elseif ($c === 0x6E) {
		/* literal null? */
		if (substr_compare($s, 'null', $Sp, 4) === 0) {
			$Sp += 4;
			$ov = NULL;
			return true;
		}
		$ov = 'after “n”, “ull” expected';
	} elseif ($c === 0x74) {
		/* literal true? */
		if (substr_compare($s, 'true', $Sp, 4) === 0) {
			$Sp += 4;
			$ov = true;
			return true;
		}
		$ov = 'after “t”, “rue” expected';
	} elseif ($c === 0x66) {
		/* literal false? */
		if (substr_compare($s, 'false', $Sp, 5) === 0) {
			$Sp += 5;
			$ov = false;
			return true;
		}
		$ov = 'after “f”, “alse” expected';
	} elseif ($c <= 0x7E && $c >= 0x20) {
		$ov = 'unexpected “' . chr($c) . '”, Value expected';
	} elseif ($c !== -1) {
		$ov = sprintf('unexpected 0x%02X, Value expected', $c);
	} else {
		$ov = 'unexpected EOS, Value expected';
	}
	return false;
}

function minijson_decode_string($s, &$Sp, $Sx) {
	ob_start();
	while ($Sp < $Sx) {
		/* get next octet; switch on what to do with it */
		if (($c = ord(($ch = $s[$Sp++]))) === 0x22) {
			/* regular exit point for the loop */
			return true;
		}
		/* backslash escape? */
		if ($c === 0x5C) {
			if ($Sp >= $Sx)
				return 'incomplete escape sequence';
			$c = ord(($ch = $s[$Sp++]));
			if ($c === 0x22 || $c === 0x5C || $c === 0x2F)
				echo $ch;
			elseif ($c === 0x74)
				echo "\x09";
			elseif ($c === 0x6E)
				echo "\x0A";
			elseif ($c === 0x75) {
				$c = minijson_decode_uescape($s, $Sp, $Sx, $ch);
				if ($c >= 0xD800 && $c <= 0xDFFF)
					$c = minijson_decode_surrogate($s, $Sp,
					    $Sx, $c, $ch);
				if ($c === 0)
					return $ch;
				if ($c < 0x80)
					echo chr($c);
				elseif ($c < 0x0800)
					echo chr(0xC0 | ($c >> 6)) .
					    chr(0x80 | ($c & 0x3F));
				elseif ($c <= 0xFFFF)
					echo chr(0xE0 | ($c >> 12)) .
					    chr(0x80 | (($c >> 6) & 0x3F)) .
					    chr(0x80 | ($c & 0x3F));
				else
					echo chr(0xF0 | ($c >> 18)) .
					    chr(0x80 | (($c >> 12) & 0x3F)) .
					    chr(0x80 | (($c >> 6) & 0x3F)) .
					    chr(0x80 | ($c & 0x3F));
			} elseif ($c === 0x72)
				echo "\x0D";
			elseif ($c === 0x62)
				echo "\x08";
			elseif ($c === 0x66)
				echo "\x0C";
			else {
				$Sp -= 2;
				return "invalid escape sequence “\\{$ch}”";
			}
			continue;
		}
		echo $ch;
		if ($c < 0x80) {
			if ($c >= 0x20)
				continue;
			--$Sp;
			return sprintf('unexpected C0 control 0x%02X', $c);
		}
		/* UTF-8 BMP sequence (unrolled) */
		if ($c < 0xE0) {
			if ($c < 0xC2) {
				--$Sp;
				return sprintf('invalid UTF-8 lead octet 0x%02X',
				    $c);
			}
			if ($Sp + 1 > $Sx) {
				--$Sp;
				return 'incomplete UTF-8 sequence';
			}
			if (($c = ord(($ch = $s[$Sp++])) ^ 0x80) > 0x3F) {
				--$Sp;
				return sprintf('invalid UTF-8 trail octet 0x%02X',
				    $c ^ 0x80);
			}
			echo $ch;
			continue;
		} elseif ($c >= 0xF0) {
			--$Sp;
			return sprintf('invalid UTF-8 lead octet 0x%02X', $c);
		}
		if ($Sp + 2 > $Sx) {
			--$Sp;
			return 'incomplete UTF-8 sequence';
		}
		$wc = ($c & 0x0F) << 12;
		if (($c = ord(($ch = $s[$Sp++])) ^ 0x80) <= 0x3F) {
			$wc |= $c << 6;
			echo $ch;
		} else {
			--$Sp;
			return sprintf('invalid UTF-8 trail octet 0x%02X',
			    $c ^ 0x80);
		}
		if (($c = ord(($ch = $s[$Sp++])) ^ 0x80) <= 0x3F) {
			$wc |= $c;
			echo $ch;
		} else {
			--$Sp;
			return sprintf('invalid UTF-8 trail octet 0x%02X',
			    $c ^ 0x80);
		}
		if ($wc < 0x0800) {
			$Sp -= 3;
			return sprintf('non-minimal encoding for %04X', $wc);
		}
		if ($wc >= 0xD800 && $wc <= 0xDFFF) {
			$Sp -= 3;
			return sprintf('unescaped surrogate %04X', $wc);
		}
		if ($wc > 0xFFFD) {
			$Sp -= 3;
			return sprintf('unescaped non-character %04X', $wc);
		}
	}
	return 'unexpected EOS in String';
}

/* decodes the four nybbles after “\u” */
function minijson_decode_uescape($s, &$Sp, $Sx, &$e) {
	if ($Sp + 4 > $Sx) {
		$Sp -= 2;
		$e = 'incomplete escape sequence';
		return 0;
	}
	$wc = 0;
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
			$e = 'hexadecimal digit expected';
			return 0;
		}
	}
	if ($wc < 1 || $wc > 0xFFFD) {
		$Sp -= 6;
		$e = sprintf('invalid escape \\u%04X', $wc);
		return 0;
	}
	return $wc;
}

/* called if decoding the above resulted in a surrogate */
function minijson_decode_surrogate($s, &$Sp, $Sx, $wc, &$e) {
	/* loose low surrogate? */
	if ($wc >= 0xDC00) {
		$Sp -= 6;
		$e = sprintf('unexpected low surrogate \\u%04X', $wc);
		return 0;
	}
	/* wc is a high surrogate */
	if ($Sp + 6 > $Sx || $s[$Sp] !== '\\' || $s[$Sp + 1] !== 'u') {
		$e = 'escaped low surrogate expected';
		return 0;
	}
	$Sp += 2;
	if (($lc = minijson_decode_uescape($s, $Sp, $Sx, $e)) === 0)
		return 0;
	if ($lc < 0xDC00 || $lc > 0xDFFF) {
		$Sp -= 6;
		$e = sprintf('unexpected \\u%04X, low surrogate expected', $lc);
		return 0;
	}
	return 0x10000 + (($wc & 0x03FF) << 10) + ($lc & 0x03FF);
}

function minijson_decode_number($s, &$Sp, $Sx, &$ov) {
	$matches = array('');
	if (!preg_match('/-?(?:0|[1-9][0-9]*)(?:\.[0-9]+)?(?:[Ee][+-]?[0-9]+)?/A',
	    $s, $matches, 0, $Sp) || ($Ss = strlen($matches[0])) < 1) {
		$ov = 'Number expected';
		return false;
	}
	if ($Sp + $Ss > $Sx) {
		$ov = 'unexpected EOS in Number';
		return false;
	}
	$Sp += $Ss;
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
