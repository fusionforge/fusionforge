<?php
/**
 * Minimal JSON generator and parser for FusionForge
 *
 * Copyright © 2010, 2011
 *	Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * All rights reserved.
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option)
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
 *-
 * Do *not* use PHP’s json_encode because it is broken.
 * Note that JSON is case-sensitive.  My notes are at:
 * https://www.mirbsd.org/cvs.cgi/contrib/hosted/tg/json.txt?rev=HEAD
 */

/*-
 * I was really, really bad at writing parsers. I still am really bad at
 * writing parsers.
 * -- Rasmus Lerdorf
 */

/**
 * Encodes an array (indexed or associative) as JSON.
 *
 * in:	array x
 * in:	string indent or bool false to skip beautification
 * out:	string encoded
 */
function minijson_encode($x, $ri="") {
	if (!isset($x) || is_null($x) || (is_float($x) &&
	    (is_nan($x) || is_infinite($x))))
		return "null";
	if ($x === true)
		return "true";
	if ($x === false)
		return "false";
	if (is_int($x)) {
		$y = (int)$x;
		$z = (string)$y;
		if ($x == $z)
			return $z;
		$x = (string)$x;
	}
	if (is_float($x)) {
		$rs = sprintf("%.14E", $x);
		$v = explode("E", $rs);
		$rs = rtrim($v[0], "0");
		if (substr($rs, -1) == ".") {
			$rs .= "0";
		}
		if ($v[1] != "-0" && $v[1] != "+0") {
			$rs .= "E" . $v[1];
		}
		return $rs;
	}
	if (is_string($x)) {
		$rs = "\"";
		$x .= "\0";
		/*
		 * A bit unbelievable: not only does mb_check_encoding
		 * not exist from the start, but also does it not check
		 * reliably – so converting forth and back is the way
		 * they recommend… also, JSON is not binary-safe either…
		 */
		$isunicode = false;
		$mb_encoding = false;
		if (function_exists('mb_internal_encoding') &&
		    function_exists('mb_convert_encoding')) {
			$mb_encoding = mb_internal_encoding();
			mb_internal_encoding("UTF-8");
			$z = mb_convert_encoding($x, "UTF-16LE", "UTF-8");
			$y = mb_convert_encoding($z, "UTF-8", "UTF-16LE");
			$isunicode = ($y == $x);
		}
		if ($isunicode) {
			$z = str_split($z, 2);
		} else {
			$z = str_split($x);
		}

		foreach ($z as $v) {
			$y = ord($v[0]);
			if ($isunicode) {
				$y |= ord($v[1]) << 8;
			}
			if ($y == 0) {
				break;
			} else if ($y == 8) {
				$rs .= "\\b";
			} else if ($y == 9) {
				$rs .= "\\t";
			} else if ($y == 10) {
				$rs .= "\\n";
			} else if ($y == 12) {
				$rs .= "\\f";
			} else if ($y == 13) {
				$rs .= "\\r";
			} else if ($y == 34) {
				$rs .= "\\\"";
			} else if ($y == 92) {
				$rs .= "\\\\";
			} else if ($y < 0x20 || $y > 0xFFFD ||
			    ($y >= 0xD800 && $y <= 0xDFFF) ||
			    ($y > 0x7E && (!$isunicode || $y < 0xA0))) {
				$rs .= sprintf("\\u%04X", $y);
			} else if ($isunicode && $y > 0x7E) {
				$rs .= mb_convert_encoding($v, "UTF-8",
				    "UTF-16LE");
			} else {
				$rs .= $v[0];
			}
		}
		if ($mb_encoding !== false) {
			mb_internal_encoding($mb_encoding);
		}
		return $rs."\"";
	}
	if (is_array($x)) {
		$k = array_keys($x);

		$isnum = true;
		foreach ($k as $v) {
			if (is_int($v)) {
				$y = (int)$v;
				$z = (string)$y;
				if ($v != $z) {
					$isnum = false;
					break;
				}
			} else {
				$isnum = false;
				break;
			}
		}

		if ($isnum) {
			/* all array keys are integers */
			$s = $k;
			sort($s, SORT_NUMERIC);
			/* test keys for order and delta */
			$y = 0;
			foreach ($s as $v) {
				if ($v != $y) {
					$isnum = false;
					break;
				}
				$y++;
			}
		}

		$si = $ri === false ? false : $ri . "  ";
		$first = true;
		if ($isnum) {
			/* all array keys are integers 0‥n */
			$rs = "[";
			if ($ri !== false)
				$rs .= "\n";
			foreach ($s as $v) {
				if ($first)
					$first = false;
				else if ($ri === false)
					$rs .= ",";
				else
					$rs .= ",\n";
				if ($si !== false)
					$rs .= $si;
				$rs .= minijson_encode($x[$v], $si);
			}
			if ($ri !== false)
				$rs .= "\n" . $ri;
			$rs .= "]";
			return $rs;
		}

		$rs = "{";
		if ($ri !== false)
			$rs .= "\n";
		foreach ($k as $v) {
			if (!isset($x[$v])) {
				continue;
			}

			if ($first)
				$first = false;
			else if ($ri === false)
				$rs .= ",";
			else
				$rs .= ",\n";
			if ($si !== false)
				$rs .= $si;
			$rs .= minijson_encode((string)$v, false);
			if ($ri === false)
				$rs .= ":";
			else
				$rs .= ": ";
			$rs .= minijson_encode($x[$v], $si);
		}
		if ($ri !== false)
			$rs .= "\n" . $ri;
		$rs .= "}";
		return $rs;
	}

	/* treat everything else as array or string */
	if (!is_scalar($x))
		return minijson_encode((array)$x, $ri);
	return minijson_encode((string)$x, $ri);
}

/**
 * Decodes a UTF-8 string from JSON (ECMA 262).
 *
 * in:	string json
 * in:	reference output-variable (or error string)
 * in:	integer	(optional) recursion depth (default: 32)
 * out:	boolean	false if an error occured, true = output is valid
 */
function minijson_decode($sj, &$ov, $depth=32) {
	if (!isset($sj) || !$sj) {
		$ov = "empty input";
		return false;
	}

	/* mb_convert_encoding simply must exist for the decoder */
	$mb_encoding = mb_internal_encoding();
	mb_internal_encoding("UTF-8");

	/* see note about mb_check_encoding in the JSON encoder… */
	$wj = mb_convert_encoding($sj, "UTF-16LE", "UTF-8");
	$mj = mb_convert_encoding($wj, "UTF-8", "UTF-16LE");
	$rv = ($mj == $sj);
	unset($sj);
	unset($mj);

	if ($rv) {
		/* convert UTF-16LE string to array of wchar_t */
		$j = array();
		foreach (str_split($wj, 2) as $v) {
			$wc = ord($v[0]) | (ord($v[1]) << 8);
			$j[] = $wc;
		}
		$j[] = 0;
		unset($wj);

		/* skip Byte Order Mark if present */
		$p = 0;
		if ($j[$p] == 0xFEFF)
			$p++;

		/* parse recursively */
		$rv = minijson_decode_value($j, $p, $ov, $depth);
	} else {
		$ov = "input not valid UTF-8";
	}

	if ($rv) {
		/* skip optional whitespace after tokens */
		minijson_skip_wsp($j, $p);

		/* end of string? */
		if ($j[$p] !== 0) {
			/* no, trailing waste */
			$ov = "expected EOS at wchar #" . $p;
			$rv = false;
		}
	}

	mb_internal_encoding($mb_encoding);
	return $rv;
}

function minijson_skip_wsp(&$j, &$p) {
	/* skip all wide characters that are JSON whitespace */
	do {
		$wc = $j[$p++];
	} while ($wc == 0x09 || $wc == 0x0A || $wc == 0x0D || $wc == 0x20);
	$p--;
}

function minijson_get_hexdigit(&$j, &$p, &$v, $i) {
	$wc = $j[$p++];
	if ($wc >= 0x30 && $wc <= 0x39) {
		$v += $wc - 0x30;
	} else if ($wc >= 0x41 && $wc <= 0x46) {
		$v += $wc - 0x37;
	} else if ($wc >= 0x61 && $wc <= 0x66) {
		$v += $wc - 0x57;
	} else {
		$ov = sprintf("invalid hex in unicode escape" .
		    " sequence (%d) at wchar #%u", $i, $p);
		return false;
	}
	return true;
}

function minijson_decode_array(&$j, &$p, &$ov, $depth) {
	$ov = array();
	$first = true;

	/* I wish there were a goto in PHP… */
	while (true) {
		/* skip optional whitespace between tokens */
		minijson_skip_wsp($j, $p);

		/* end of the array? */
		if ($j[$p] == 0x5D) {
			/* regular exit point for the loop */

			$p++;
			return true;
		}

		/* member separator? */
		if ($j[$p] == 0x2C) {
			$p++;
			if ($first) {
				/* no comma before the first member */
				$ov = "unexpected comma at wchar #" . $p;
				return false;
			}
		} else if (!$first) {
			/*
			 * all but the first member require a separating
			 * comma; this also catches e.g. trailing
			 * rubbish after numbers
			 */
			$ov = "expected comma at wchar #" . $p;
			return false;
		}
		$first = false;

		/* parse the member value */
		$v = NULL;
		if (!minijson_decode_value($j, $p, $v, $depth)) {
			/* pass through error code */
			$ov = $v;
			return false;
		}
		$ov[] = $v;
	}
}

function minijson_decode_object(&$j, &$p, &$ov, $depth) {
	$ov = array();
	$first = true;

	while (true) {
		/* skip optional whitespace between tokens */
		minijson_skip_wsp($j, $p);

		/* end of the object? */
		if ($j[$p] == 0x7D) {
			/* regular exit point for the loop */

			$p++;
			return true;
		}

		/* member separator? */
		if ($j[$p] == 0x2C) {
			$p++;
			if ($first) {
				/* no comma before the first member */
				$ov = "unexpected comma at wchar #" . $p;
				return false;
			}
		} else if (!$first) {
			/*
			 * all but the first member require a separating
			 * comma; this also catches e.g. trailing
			 * rubbish after numbers
			 */
			$ov = "expected comma at wchar #" . $p;
			return false;
		}
		$first = false;

		/* skip optional whitespace between tokens */
		minijson_skip_wsp($j, $p);

		/* parse the member key */
		if ($j[$p++] != 0x22) {
			$ov = "expected key string at wchar #" . $p;
			return false;
		}
		$k = null;
		if (!minijson_decode_string($j, $p, $k)) {
			/* pass through error code */
			$ov = $k;
			return false;
		}

		/* skip optional whitespace between tokens */
		minijson_skip_wsp($j, $p);

		/* key-value separator? */
		if ($j[$p++] != 0x3A) {
			$ov = "expected colon at wchar #" . $p;
			return false;
		}

		/* parse the member value */
		$v = NULL;
		if (!minijson_decode_value($j, $p, $v, $depth)) {
			/* pass through error code */
			$ov = $v;
			return false;
		}
		$ov[$k] = $v;
	}
}

function minijson_decode_value(&$j, &$p, &$ov, $depth) {
	/* skip optional whitespace between tokens */
	minijson_skip_wsp($j, $p);

	/* parse begin of Value token */
	$wc = $j[$p++];

	/* style: falling through exits with false */
	if ($wc == 0) {
		$ov = "unexpected EOS at wchar #" . $p;
	} else if ($wc == 0x6E) {
		/* literal null? */
		if ($j[$p++] == 0x75 &&
		    $j[$p++] == 0x6C &&
		    $j[$p++] == 0x6C) {
			$ov = NULL;
			return true;
		}
		$ov = "expected ull after n near wchar #" . $p;
	} else if ($wc == 0x74) {
		/* literal true? */
		if ($j[$p++] == 0x72 &&
		    $j[$p++] == 0x75 &&
		    $j[$p++] == 0x65) {
			$ov = true;
			return true;
		}
		$ov = "expected rue after t near wchar #" . $p;
	} else if ($wc == 0x66) {
		/* literal false? */
		if ($j[$p++] == 0x61 &&
		    $j[$p++] == 0x6C &&
		    $j[$p++] == 0x73 &&
		    $j[$p++] == 0x65) {
			$ov = false;
			return true;
		}
		$ov = "expected alse after f near wchar #" . $p;
	} else if ($wc == 0x5B) {
		if (--$depth > 0) {
			return minijson_decode_array($j, $p, $ov, $depth);
		}
		$ov = "recursion limit exceeded at wchar #" . $p;
	} else if ($wc == 0x7B) {
		if (--$depth > 0) {
			return minijson_decode_object($j, $p, $ov, $depth);
		}
		$ov = "recursion limit exceeded at wchar #" . $p;
	} else if ($wc == 0x22) {
		return minijson_decode_string($j, $p, $ov);
	} else if ($wc == 0x2D || ($wc >= 0x30 && $wc <= 0x39)) {
		$p--;
		return minijson_decode_number($j, $p, $ov);
	} else {
		$ov = sprintf("unexpected U+%04X at wchar #%u", $wc, $p);
	}
	return false;
}

function minijson_decode_string(&$j, &$p, &$ov) {
	/* UTF-16LE string buffer */
	$s = "";

	while (true) {
		$wc = $j[$p++];
		if ($wc < 0x20) {
			$ov = "unescaped control character $wc at wchar #" . $p;
			return false;
		} else if ($wc == 0x22) {
			/* regular exit point for the loop */

			/* convert to UTF-8, then re-check against UTF-16 */
			$ov = mb_convert_encoding($s, "UTF-8", "UTF-16LE");
			$tmp = mb_convert_encoding($ov, "UTF-16LE", "UTF-8");
			if ($tmp != $s) {
				$ov = "no Unicode string before wchar #" . $p;
				return false;
			}
			return true;
		} else if ($wc == 0x5C) {
			$wc = $j[$p++];
			if ($wc == 0x22 ||
			    $wc == 0x2F ||
			    $wc == 0x5C) {
				$s .= chr($wc) . chr(0);
			} else if ($wc == 0x62) {
				$s .= chr(0x08) . chr(0);
			} else if ($wc == 0x66) {
				$s .= chr(0x0C) . chr(0);
			} else if ($wc == 0x6E) {
				$s .= chr(0x0A) . chr(0);
			} else if ($wc == 0x72) {
				$s .= chr(0x0D) . chr(0);
			} else if ($wc == 0x74) {
				$s .= chr(0x09) . chr(0);
			} else if ($wc == 0x75) {
				$v = 0;
				for ($tmp = 1; $tmp <= 4; $tmp++) {
					$v <<= 4;
					if (!minijson_get_hexdigit($j, $p,
					    $v, $tmp)) {
						/* pass through error code */
						return false;
					}
				}
				if ($v < 1 || $v > 0xFFFD) {
					$ov = "non-Unicode escape $v before wchar #" . $p;
					return false;
				}
				$s .= chr($v & 0xFF) . chr($v >> 8);
			} else {
				$ov = "invalid escape sequence at wchar #" . $p;
				return false;
			}
		} else if ($wc > 0xD7FF && $wc < 0xE000) {
			$ov = "surrogate $wc at wchar #" . $p;
			return false;
		} else if ($wc > 0xFFFD) {
			$ov = "non-Unicode char $wc at wchar #" . $p;
			return false;
		} else {
			$s .= chr($wc & 0xFF) . chr($wc >> 8);
		}
	}
}

function minijson_decode_number(&$j, &$p, &$ov) {
	$s = "";
	$isint = true;

	/* check for an optional minus sign */
	$wc = $j[$p++];
	if ($wc == 0x2D) {
		$s = "-";
		$wc = $j[$p++];
	}

	if ($wc == 0x30) {
		/* begins with zero (0 or 0.x) */
		$s .= "0";
		$wc = $j[$p++];
		if ($wc >= 0x30 && $wc <= 0x39) {
			$ov = "no leading zeroes please at wchar #" . $p;
			return false;
		}
	} else if ($wc >= 0x31 && $wc <= 0x39) {
		/* begins with 1‥9 */
		while ($wc >= 0x30 && $wc <= 0x39) {
			$s .= chr($wc);
			$wc = $j[$p++];
		}
	} else {
		$ov = "decimal digit expected at wchar #" . $p;
		if ($s[0] != "-") {
			/* we had none, so it’s allowed to prepend one */
			$ov = "minus sign or " . $ov;
		}
		return false;
	}

	/* do we have a fractional part? */
	if ($wc == 0x2E) {
		$s .= ".";
		$isint = false;
		$wc = $j[$p++];
		if ($wc < 0x30 || $wc > 0x39) {
			$ov = "fractional digit expected at wchar #" . $p;
			return false;
		}
		while ($wc >= 0x30 && $wc <= 0x39) {
			$s .= chr($wc);
			$wc = $j[$p++];
		}
	}

	/* do we have an exponent, treat number as mantissa? */
	if ($wc == 0x45 || $wc == 0x65) {
		$s .= "E";
		$isint = false;
		$wc = $j[$p++];
		if ($wc == 0x2B || $wc == 0x2D) {
			$s .= chr($wc);
			$wc = $j[$p++];
		}
		if ($wc < 0x30 || $wc > 0x39) {
			$ov = "exponent digit expected at wchar #" . $p;
			return false;
		}
		while ($wc >= 0x30 && $wc <= 0x39) {
			$s .= chr($wc);
			$wc = $j[$p++];
		}
	}
	$p--;

	if ($isint) {
		/* no fractional part, no exponent */

		$v = (int)$s;
		if ((string)$v == $s) {
			$ov = $v;
			return true;
		}
	}
	$ov = (float)$s;
	return true;
}

?>
