<?php
/*-
 * Small test for the minijson encoder/decoder routines
 *
 * Copyright © 2011
 *	Thorsten Glaser <mirabilos@evolvis.org>
 * All rights reserved.
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

error_reporting(-1);

/****************************************************************/

/* $s_orig [parse] print_r->$s_printr [encode] $s_ecompact or $s_epadded */

/* $s_e* [decode] print_r ->$s_printre (no object slot when value==null) */

$s_orig = '[
    "JSON Test Pattern pass1",
    {"object with 1 member":["array with 1 element"]},
    {},
    [],
    -42,
    true,
    false,
    null,
    {
        "integer": 1234567890,
        "real": -9876.543210,
        "e": 0.123456789e-12,
        "E": 1.234567890E+34,
        "":  23456789012E66,
        "zero": 0,
        "one": 1,
        "space": " ",
        "quote": "\\"",
        "backslash": "\\\\",
        "controls": "\\b\\f\\n\\r\\t",
        "slash": "/ & \\/",
        "alpha": "abcdefghijklmnopqrstuvwyz",
        "ALPHA": "ABCDEFGHIJKLMNOPQRSTUVWYZ",
        "digit": "0123456789",
        "0123456789": "digit",
        "special": "`1~!@#$%^&*()_+-={\':[,]}|;.</>?",
        "hex": "\\u0123\\u4567\\u89AB\\uCDEF\\uabcd\\uef4A",
        "true": true,
        "false": false,
        "null": null,
        "array":[  ],
        "object":{  },
        "address": "50 St. James Street",
        "url": "http://www.JSON.org/",
        "comment": "// /* <!-- --",
        "# -- --> */": " ",
        " s p a c e d " :[1,2 , 3

,

4 , 5        ,          6           ,7        ],"compact":[1,2,3,4,5,6,7],
        "jsontext": "{\\"object with 1 member\\":[\\"array with 1 element\\"]}",
        "quotes": "&#34; \\u0022 %22 0x22 034 &#x22;",
        "\\/\\\\\\"\\uCAFE\\uBABE\\uAB98\\uFCDE\\ubcda\\uef4A\\b\\f\\n\\r\\t`1~!@#$%^&*()_+-=[]{}|;:\',./<>?"
: "A key can be any string"
    },
    0.5 ,98.6
,
99.44
,

1066,
1e1,
0.1e1,
1e-1,
1e00,2e+00,2e-00
,"rosebud"]';

$s_printr = 'Array
(
    [0] => JSON Test Pattern pass1
    [1] => Array
        (
            [object with 1 member] => Array
                (
                    [0] => array with 1 element
                )

        )

    [2] => Array
        (
        )

    [3] => Array
        (
        )

    [4] => -42
    [5] => 1
    [6] =>
    [7] =>
    [8] => Array
        (
            [integer] => 1234567890
            [real] => -9876.54321
            [e] => 1.23456789E-13
            [E] => 1.23456789E+34
            [] => 2.3456789012E+76
            [zero] => 0
            [one] => 1
            [space] =>
            [quote] => "
            [backslash] => \\
            [controls] => 

            [slash] => / & /
            [alpha] => abcdefghijklmnopqrstuvwyz
            [ALPHA] => ABCDEFGHIJKLMNOPQRSTUVWYZ
            [digit] => 0123456789
            [0123456789] => digit
            [special] => `1~!@#$%^&*()_+-={\':[,]}|;.</>?
            [hex] => ģ䕧覫췯ꯍ
            [true] => 1
            [false] =>
            [null] =>
            [array] => Array
                (
                )

            [object] => Array
                (
                )

            [address] => 50 St. James Street
            [url] => http://www.JSON.org/
            [comment] => // /* <!-- --
            [# -- --> */] =>
            [ s p a c e d ] => Array
                (
                    [0] => 1
                    [1] => 2
                    [2] => 3
                    [3] => 4
                    [4] => 5
                    [5] => 6
                    [6] => 7
                )

            [compact] => Array
                (
                    [0] => 1
                    [1] => 2
                    [2] => 3
                    [3] => 4
                    [4] => 5
                    [5] => 6
                    [6] => 7
                )

            [jsontext] => {"object with 1 member":["array with 1 element"]}
            [quotes] => &#34; " %22 0x22 034 &#x22;
            [/\\"쫾몾ꮘﳞ볚
	`1~!@#$%^&*()_+-=[]{}|;:\',./<>?] => A key can be any string
        )

    [9] => 0.5
    [10] => 98.6
    [11] => 99.44
    [12] => 1066
    [13] => 10
    [14] => 1
    [15] => 0.1
    [16] => 1
    [17] => 2
    [18] => 2
    [19] => rosebud
)
';

$s_printre = 'Array
(
    [0] => JSON Test Pattern pass1
    [1] => Array
        (
            [object with 1 member] => Array
                (
                    [0] => array with 1 element
                )

        )

    [2] => Array
        (
        )

    [3] => Array
        (
        )

    [4] => -42
    [5] => 1
    [6] =>
    [7] =>
    [8] => Array
        (
            [integer] => 1234567890
            [real] => -9876.54321
            [e] => 1.23456789E-13
            [E] => 1.23456789E+34
            [] => 2.3456789012E+76
            [zero] => 0
            [one] => 1
            [space] =>
            [quote] => "
            [backslash] => \\
            [controls] => 

            [slash] => / & /
            [alpha] => abcdefghijklmnopqrstuvwyz
            [ALPHA] => ABCDEFGHIJKLMNOPQRSTUVWYZ
            [digit] => 0123456789
            [0123456789] => digit
            [special] => `1~!@#$%^&*()_+-={\':[,]}|;.</>?
            [hex] => ģ䕧覫췯ꯍ
            [true] => 1
            [false] =>
            [array] => Array
                (
                )

            [object] => Array
                (
                )

            [address] => 50 St. James Street
            [url] => http://www.JSON.org/
            [comment] => // /* <!-- --
            [# -- --> */] =>
            [ s p a c e d ] => Array
                (
                    [0] => 1
                    [1] => 2
                    [2] => 3
                    [3] => 4
                    [4] => 5
                    [5] => 6
                    [6] => 7
                )

            [compact] => Array
                (
                    [0] => 1
                    [1] => 2
                    [2] => 3
                    [3] => 4
                    [4] => 5
                    [5] => 6
                    [6] => 7
                )

            [jsontext] => {"object with 1 member":["array with 1 element"]}
            [quotes] => &#34; " %22 0x22 034 &#x22;
            [/\\"쫾몾ꮘﳞ볚
	`1~!@#$%^&*()_+-=[]{}|;:\',./<>?] => A key can be any string
        )

    [9] => 0.5
    [10] => 98.6
    [11] => 99.44
    [12] => 1066
    [13] => 10
    [14] => 1
    [15] => 0.1
    [16] => 1
    [17] => 2
    [18] => 2
    [19] => rosebud
)
';

$s_ecompact = '["JSON Test Pattern pass1",{"object with 1 member":["array with 1 element"]},[],[],-42,true,false,null,{"integer":1234567890,"real":-9.87654321E+3,"e":1.23456789E-13,"E":1.23456789E+34,"":2.3456789012E+76,"zero":0,"one":1,"space":" ","quote":"\\"","backslash":"\\\\","controls":"\\b\\f\\n\\r\\t","slash":"/ & /","alpha":"abcdefghijklmnopqrstuvwyz","ALPHA":"ABCDEFGHIJKLMNOPQRSTUVWYZ","digit":"0123456789","0123456789":"digit","special":"`1~!@#$%^&*()_+-={\':[,]}|;.</>?","hex":"ģ䕧覫췯ꯍ","true":true,"false":false,"array":[],"object":[],"address":"50 St. James Street","url":"http://www.JSON.org/","comment":"// /* <!-- --","# -- --> */":" "," s p a c e d ":[1,2,3,4,5,6,7],"compact":[1,2,3,4,5,6,7],"jsontext":"{\\"object with 1 member\\":[\\"array with 1 element\\"]}","quotes":"&#34; \\" %22 0x22 034 &#x22;","/\\\\\\"쫾몾ꮘﳞ볚\\b\\f\\n\\r\\t`1~!@#$%^&*()_+-=[]{}|;:\',./<>?":"A key can be any string"},5.0E-1,9.86E+1,9.944E+1,1066,1.0E+1,1.0,1.0E-1,1.0,2.0,2.0,"rosebud"]';
$s_epadded = '[
  "JSON Test Pattern pass1",
  {
    "object with 1 member": [
      "array with 1 element"
    ]
  },
  [

  ],
  [

  ],
  -42,
  true,
  false,
  null,
  {
    "integer": 1234567890,
    "real": -9.87654321E+3,
    "e": 1.23456789E-13,
    "E": 1.23456789E+34,
    "": 2.3456789012E+76,
    "zero": 0,
    "one": 1,
    "space": " ",
    "quote": "\\"",
    "backslash": "\\\\",
    "controls": "\\b\\f\\n\\r\\t",
    "slash": "/ & /",
    "alpha": "abcdefghijklmnopqrstuvwyz",
    "ALPHA": "ABCDEFGHIJKLMNOPQRSTUVWYZ",
    "digit": "0123456789",
    "0123456789": "digit",
    "special": "`1~!@#$%^&*()_+-={\':[,]}|;.</>?",
    "hex": "ģ䕧覫췯ꯍ",
    "true": true,
    "false": false,
    "array": [

    ],
    "object": [

    ],
    "address": "50 St. James Street",
    "url": "http://www.JSON.org/",
    "comment": "// /* <!-- --",
    "# -- --> */": " ",
    " s p a c e d ": [
      1,
      2,
      3,
      4,
      5,
      6,
      7
    ],
    "compact": [
      1,
      2,
      3,
      4,
      5,
      6,
      7
    ],
    "jsontext": "{\\"object with 1 member\\":[\\"array with 1 element\\"]}",
    "quotes": "&#34; \\" %22 0x22 034 &#x22;",
    "/\\\\\\"쫾몾ꮘﳞ볚\\b\\f\\n\\r\\t`1~!@#$%^&*()_+-=[]{}|;:\',./<>?": "A key can be any string"
  },
  5.0E-1,
  9.86E+1,
  9.944E+1,
  1066,
  1.0E+1,
  1.0,
  1.0E-1,
  1.0,
  2.0,
  2.0,
  "rosebud"
]';

/****************************************************************/

require_once(dirname(__FILE__) . "/minijson.php");

$tnum = 0;
$passed = 0;
$failed = 0;

function test_r($label,$ok) {
	global $tnum, $passed, $failed;

	if ($ok) {
		echo "PASS " . ++$tnum . "-" . $label . "\n";
		$passed++;
		return false;
	} else {
		echo "FAIL " . ++$tnum . "-" . $label . "\n";
		$failed++;
		return true;
	}
}

function test_eq($label,$want,$got) {
	if ($want === $got)
		return test_r($label, true);
	test_r($label, false);
	echo " want: ";
	print_r($want);
	echo "\n got : ";
	print_r($got);
	echo "\n";
	return true;
}

$parsed = 'bla';
$presult = minijson_decode($s_orig, $parsed);
if (test_eq("can-parse", true, $presult)) {
	echo " got : ";
	print_r($parsed);
	echo "\n";
}
if (!$parsed || $parsed == 'bla')
	echo "FAIL parse-basic\n";
$printrd = print_r($parsed, true);
test_eq("parsed", $s_printr, $printrd);

$encoded = minijson_encode($parsed, false);
test_eq("encode-compact", $s_ecompact, $encoded);
$reparsed = 'bla';
$presult = minijson_decode($encoded, $reparsed);
test_eq("can-reparse-compact", true, $presult);
if (!$reparsed || $reparsed == 'bla')
	echo "FAIL reparse-compact-basic\n";
$printrd = print_r($reparsed, true);
test_eq("reparsed-compact", $s_printre, $printrd);

$encoded = minijson_encode($parsed);
test_eq("encode-padded", $s_epadded, $encoded);
$reparsed = 'bla';
$presult = minijson_decode($encoded, $reparsed);
test_eq("can-reparse-padded", true, $presult);
if (!$reparsed || $reparsed == 'bla')
	echo "FAIL reparse-padded-basic\n";
$printrd = print_r($reparsed, true);
test_eq("reparsed-padded", $s_printre, $printrd);

echo "Total failed: " . $failed . "/" . $tnum . "\n";
echo "Total passed: " . $passed . "/" . $tnum . "\n";
exit ($failed == 0 ? 0 : 1);
