<?php
/**
 * Minimal JSON generator for FusionForge
 *
 * Copyright © 2010
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
 * Do *not* use PHP’s json_encode because it is broken. Rather,
 * use (and, if necessary, extend) this module.
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
	/* note: no float here (for now); be locales-aware! */
	if (is_string($x)) {
		$rs = "\"";
		foreach (str_split($x) as $v) {
			$y = ord($v);
			if ($y == 8) {
				$rs .= "\\b";
			} else if ($y == 9) {
				$rs .= "\\t";
			} else if ($y == 10) {
				$rs .= "\\n";
			} else if ($y == 12) {
				$rs .= "\\f";
			} else if ($y == 13) {
				$rs .= "\\r";
			} else if ($y < 0x20 || ($y > 0x7E && $y < 0xA0)) {
				$rs .= sprintf("\\u%04X", $y);
			} else if ($y > 0xFFFD) {
				/* XXX encode as UTF-16 */
				$rs .= "\\uFFFD";
			} else if ($v == "\"" || $v == "\\") {
				$rs .= "\\".$v;
			} else
				$rs .= $v;
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

		$first = true;
		if ($isnum) {
			/* all array keys are integers 0‥n */
			$rs = "[\n";
			foreach ($s as $v) {
				if ($first)
					$first = false;
				else
					$rs .= ",\n";
				$rs .= $ri . "  " .
				    minijson_encode($x[$v], $ri."  ");
			}
			return $rs."\n".$ri."]";
		}

		$rs = "{\n";
		foreach ($k as $v) {
			if ($first)
				$first = false;
			else
				$rs .= ",\n";
			$rs .= $ri . "  " . minijson_encode((string)$v) .
			    ": " . minijson_encode($x[$v], $ri."  ");
		}
		return $rs."\n".$ri."}";
	}

	/* treat everything else as array or string */
	if (!is_scalar($x))
		return minijson_encode((array)$x, $ri);
	return minijson_encode((string)$x, $ri);
}

?>
