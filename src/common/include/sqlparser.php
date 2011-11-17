<?php
/**
 * FusionForge PostgreSQL file parser
 *
 * Copyright 2002-2008, Roland Mas (Perl implementation)
 * Copyright 2011, Roland Mas (PHP rewrite)
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

function get_next_line($f) {
	$l = fgets($f);
	if ($l === false) {
		return false;
	}
	$l = rtrim($l, "\n\r");
	return $l;
}

define ('GREEN', "\033[01;32m" );
define ('NORMAL', "\033[00m" );
define ('RED', "\033[01;31m" );

function parse_sql_file($filename) {
	$f = fopen($filename, 'r');
	if (!$f) {
		error_log("$filename not found");
		return false;
	}

	$states = array ('INIT' => 0,
			 'SCAN' => 1,
			 'SQL_SCAN' => 2,
			 'IN_SQL' => 3,
			 'END_SQL' => 4,
			 'QUOTE_SCAN' => 5,
			 'IN_QUOTE' => 6,
			 'START_COPY' => 7,
			 'IN_COPY' => 8,
			 'ERROR' => 9,
			 'IN_COMMENT' => 10,
			 'IN_SQL_COMMENT' => 11,
			 'IN_DOLDOL' => 12,
			 'DONE' => 999) ;

	$names = array_flip($states);

	$state = $states['INIT'];

	$par_level = 0;
	$com_level = 0;
	$doldolstack = array();
	$l = '';
	$sql = '';
	$chunk = '';
	$rest = '';
	$sql_list = array();
	$copy_table = '';
	$copy_rest = '';
	$copy_data = array();
	$copy_data_tmp = array();
	$copy_field = '';
	
	while ($state != $states['DONE']) {
		// error_log("STATE_LOOP: state=$names[$state], l=".RED.$l.NORMAL.", chunk=".RED.$chunk.NORMAL.", rest=".RED.$rest.NORMAL);
		// error_log(RED."<".GREEN.$sql.RED.">".NORMAL);
		$matches = array();

		switch ($state) {
		case $states['INIT']:
			// error_log('INIT');
			
			$l = get_next_line($f);
			if ($l === false) {
				$state = $states['DONE'];
				continue;
			}

			$state = $states['SCAN'];
			break; // End of INIT

		case $states['SCAN']:
			// error_log('SCAN');
			if (($l == '') || preg_match('/^\s*$/', $l) || preg_match('/^\s*--/', $l)) {
				$l = get_next_line($f);
				if ($l === false) {
					$state = $states['DONE'];
					continue;
				}
				continue;
			} elseif (preg_match('/\s*copy\s+\"[\w_]+\"\s*(\([\w, "]+\))?\s*from\s+stdin\s*;/i', $l)
				  || preg_match('/\s*copy\s+[\w_]+\s*(\([\w, "]+\))?\s*from\s+stdin\s*;/i', $l)) {
				$state = $states['START_COPY'];
				continue;
			} else {
				$sql = '';
				$state = $states['SQL_SCAN'];
				continue;
			}
			break; // End of SCAN

		case $states['IN_COMMENT']:
			// error_log('IN_COMMENT');
			if (($l == '') || preg_match('/^\s*$/', $l)) {
				$l = get_next_line($f);
				if ($l === false) {
					error_log("End of file reached during a comment");
					$state = $states['ERROR'];
					continue;
				}
				$state = $states['IN_COMMENT'];
				continue;
			} elseif (preg_match(',\*/,', $l) || preg_match(',/\*,', $l)) {
				$l = preg_replace(',.*?((/\*)|(\*/)),', '$1', $l, 1);
				$chunk = substr($l,0,2);
				$rest = substr($l,2);
				if ($chunk == '/*') {
					$com_level += 1;
				} else {
					$com_level -= 1;
				}
				if ($com_level == 0) {
					$state = $states['SQL_SCAN'];
					continue;
				} else {
					$state = $states['IN_COMMENT'];
					continue;
				}
			} else {
				$l = get_next_line($f);
				if ($l === false) {
					error_log("End of file reached during a comment");
					$state = $states['ERROR'];
					continue;
				}
				$state = $states['IN_COMMENT'];
				continue;
			}
			break; // End of IN_COMMENT

		case $states['IN_SQL_COMMENT']:
			// error_log('IN_SQL_COMMENT');
			if (($rest == '') || preg_match('/^\s*$/')) {
				$rest = get_next_line($f);
				if ($rest === false) {
					error_log("End of file reached during a comment");
					$state = $states['ERROR'];
					continue;
				}
				$state = $states['IN_SQL_COMMENT'];
				continue;
			} elseif (preg_match(',\*/,', $rest) || preg_match(',/\*,', $rest)) {
				$rest = preg_replace(',.*?((/\*)|(\*/)),', '$1', $l, 1);
				$chunk = substr($rest,0,2);
				$rest = substr($rest,2);
				if ($chunk == '/*') {
					$com_level += 1;
				} else {
					$com_level -= 1;
				}
				if ($com_level == 0) {
					$state = $states['IN_SQL'];
					continue;
				} else {
					$state = $states['IN_SQL_COMMENT'];
					continue;
				}
			} else {
				$rest = get_next_line($f);
				if ($rest === false) {
					error_log("End of file reached during a comment");
					$state = $states['ERROR'];
					continue;
				}
				$state = $states['IN_SQL_COMMENT'];
				continue;
			}				
			break; // End of IN_SQL_COMMENT

		case $states['SQL_SCAN']:
			// error_log('SQL_SCAN');
			if (($l == '') || preg_match('/^\s*$/', $l) || preg_match('/^--/', $l)) {
				$l = get_next_line($f);
				if ($l === false) {
					error_log("End of file reached during an SQL statement");
					$state = $states['ERROR'];
					continue;
				}
				$state = $states['SQL_SCAN'];
				continue;
			} elseif (preg_match(',^\s*/\*,', $l)) {
				$l = preg_replace(',^\s*/\*,','',$l, 1);
				$com_level = 1;
				$state = $states['IN_COMMENT'];
				continue;
			} elseif (preg_match(',^(.*?)\$([\w]*)\$,', $l, $matches)) {
				$sql .= $matches[1].'$'.$matches[2].'$';
				array_push($doldolstack,$matches[2]);
				$l = preg_replace(',^(.*?)\$[\w]*\$,','',$l, 1);
				$state = $states['IN_DOLDOL'];
				continue;
			} else {
				preg_match(',^([^()\';-]*)(.*),', $l, $matches);
				$chunk = $matches[1];
				$rest = $matches[2];
				$sql .= $chunk;
				$state = $states['IN_SQL'];
				continue;
			}
				
			break; // End of SQL_SCAN

		case $states['IN_SQL']:
			// error_log('IN_SQL');
			if (preg_match(',^\s*/\*,',$rest)) {
				$rest = preg_replace(',^\s*/\*,','',$rest, 1);
				$com_level = 1;
				$state = $states['IN_SQL_COMMENT'];
				continue;
			} elseif (preg_match('/^\(/', $rest)) {
				$par_level += 1;
				$sql .= '(';
				$rest = substr($rest, 1);
				$l = $rest;
				$state = $states['SQL_SCAN'];
				continue;
			} elseif (preg_match('/^\)/', $rest) && $par_level > 0) {
				$par_level -= 1;
				$sql .= ')';
				$rest = substr($rest, 1);
				$l = $rest;
				$state = $states['SQL_SCAN'];
				continue;
			} elseif (preg_match('/^\)/', $rest)) {
				error_log("Detected ')' without any matching '('");
				$state = $states['ERROR'];
				continue;
			} elseif (preg_match('/^--/', $rest)) {
				$rest = '';
				$l = $rest;
				$state = $states['SQL_SCAN'];
				continue;
			} elseif (preg_match('/^-/', $rest)) {
				$sql .= '-';
				$rest = substr($rest, 1);
				$l = $rest;
				$state = $states['SQL_SCAN'];
				continue;
			} elseif (preg_match('/^;/', $rest) && ($par_level == 0)) {
				$sql .= ';';
				$rest = substr($rest, 1);
				$state = $states['END_SQL'];
				continue;
			} elseif (preg_match('/^;/', $rest)) {
				error_log("Detected ';' within a parenthesis");
				$state = $states['ERROR'];
				continue;
			} elseif ($rest == '') {
				$l = $rest;
				$sql .= ' ';
				$state = $states['SQL_SCAN'];
				continue;
			} elseif (preg_match("/^\\'/", $rest)) {
				$oldrest = $rest;
				$sql .= "'";
				$rest = substr($rest, 1);
				$l = $rest;
				$state = $states['IN_QUOTE'];
				continue;
			} else {
				error_log("Unknown event in IN_SQL state");
				$state = $states['ERROR'];
				continue;
			}
			break; // End of IN_SQL

		case $states['END_SQL']:
			// error_log('END_SQL');
			if (preg_match('/^\s*$/', $sql)) {
				$sql = '';
				$l = $rest;
				$state = $states['SCAN'];
				continue;
			} else {
				array_push($sql_list, $sql);
				// error_log(RED."---->".$sql.NORMAL);
				$sql = '';
				$l = $rest;
				$state = $states['SCAN'];
				continue;
			}
			break; // End of END_SQL

		case $states['QUOTE_SCAN']:
			// error_log('QUOTE_SCAN');
			if ($rest == '') {
				$sql .= "\n";
				$l = get_next_line($f);
				if ($l === false) {
					error_log("End of file reached during a quoted string");
					$state = $states['ERROR'];
					continue;
				}
				$rest = $l;
				$state = $states['QUOTE_SCAN'];
				continue;
			} else {
				preg_match("/^([^\\\']*)(.*)/", $l, $matches);
				$chunk = $matches[1];
				$rest = $matches[2];
				$sql .= $chunk;
				$state = $states['IN_QUOTE'];
				continue;
			}
			break; // End of QUOTE_SCAN

		case $states['IN_QUOTE']:
			// error_log('IN_QUOTE');
			if (preg_match("/^'/", $rest)) {
				$sql .= "'";
				$rest = substr($rest, 1);
				$l = $rest;
				$state = $states['SQL_SCAN'];
				continue;
			} elseif (preg_match("/^\\\'/", $rest)) {
				$sql .= "''";
				$rest = substr($rest, 2);
				$state = $states['IN_QUOTE'];
				continue;
			} elseif (preg_match("/^\\\[^\\\]/", $rest)) {
				$sql .= '\\';
				$rest = substr($rest, 1);
				$state = $states['IN_QUOTE'];
				continue;
			} elseif (preg_match('/^\\\$/', $rest)) {
				$sql .= "\n";
				$rest = substr($rest, 1);
				$state = $states['IN_QUOTE'];
				continue;
			} else {
				$l = $rest;
				$state = $states['QUOTE_SCAN'];
				continue;
			}
			break; // End of IN_QUOTE

		case $states['IN_DOLDOL']:
			// error_log('IN_DOLDOL');
			$cur = $doldolstack[0];
			if (preg_match(",^(.*?)\\$([\w]*)\\$,", $l, $matches)) {
				$sql .= $matches[1].'$'.$matches[2].'$';
				$found = $matches[2];
				if ($found == $cur) {
					array_pop($doldolstack);
					if (count($doldolstack) > 0) {
						$state = $states['IN_DOLDOL'];
						continue;
					} else {
						$rest = preg_replace(",^.*?\\$[\w]*\\$,",'',$l, 1);
						$l = preg_replace(",^.*?\\\$[\w]*\\\$,",'',$l,1);
						$state = $states['SQL_SCAN'];
						continue;
					}
				} else {
					array_push($doldolstack, $found);
					$state = $states['IN_DOLDOL'];
					continue;
				}
				$l = preg_replace(",^.*?\\\$[\w]*\\\$,",'',$l,1);
				$state = $states['IN_DOLDOL'];
				continue;
			} else {
				$sql .= $l."\n";
				$l = get_next_line($f);
				if ($l === false) {
					error_log("End of file reached during a dollar-quoted string");
					$state = $states['ERROR'];
					continue;
				}
				$state = $states['IN_DOLDOL'];
				continue;
			}
			break; // End of IN_DOLDOL

		case $states['START_COPY']:
			// error_log('START_COPY');
			if (preg_match('/\s*copy\s+\"([\w_]+)\"\s*(\\([\w, "]+\\))?\s*from\s+stdin\s*;(.*)/i', $l, $matches)) {
				$copy_table = $matches[1];
				$copy_field_list = trim($matches[2]);
				if ($copy_field_list != '') {
					$copy_field_list = ' '.$copy_field_list;
				}
				$copy_rest = $matches[3];
				$l = get_next_line($f);
				if ($l === false) {
					error_log("End of file reached during a COPY statement");
					$state = $states['ERROR'];
					continue;
				}
				$state = $states['IN_COPY'];
				continue;
			} elseif (preg_match('/\s*copy\s+([\w_]+)\s*(\\([\w, "]+\\))?\s*from\s+stdin\s*;(.*)/i', $l, $matches)) {
				$copy_table = $matches[1];
				$copy_field_list = trim($matches[2]);
				if ($copy_field_list != '') {
					$copy_field_list = ' '.$copy_field_list;
				}
				$copy_rest = $matches[3];
				$l = get_next_line($f);
				if ($l === false) {
					error_log("End of file reached during a COPY statement");
					$state = $states['ERROR'];
					continue;
				}
				$state = $states['IN_COPY'];
				continue;
			} else {
				error_log("Unknown event in START_COPY state");
				continue;
			}
			break; // End of START_COPY

		case $states['IN_COPY']:
			// error_log('IN_COPY');
			if ($l == '\.') {
				$l = $copy_rest;
				$state = $states['SCAN'];
				continue;
			} else {
				$copy_data = array();
				$copy_data_tmp = explode ("\t", $l);
				foreach ($copy_data_tmp as $copy_field) {
					if ($copy_field == '\N') {
						$copy_field = 'NULL';
					} else {
						$copy_field = preg_replace('/\'/','\'\'', $copy_field);
						$copy_field = "'".$copy_field."'";
					}
					array_push($copy_data, $copy_field);
				}
				$sql = "INSERT INTO \"$copy_table\"$copy_field_list VALUES (";
				$sql .= implode (', ', $copy_data);
				$sql .= ")";
				array_push($sql_list, $sql);
				// error_log(RED."---->".$sql.NORMAL);
				$l = get_next_line($f);
				if ($l === false) {
					error_log("End of file reached during a COPY statement");
					$state = $states['ERROR'];
					continue;
				}
				$state = $states['IN_COPY'];
				continue;
			}
			break; // End of IN_COPY
			
		case $states['DONE']:
			// error_log('DONE');
			// We're done.
			break; // End of DONE
			
		case $states['ERROR']:
			// error_log('ERROR');
			error_log("Reached the ERROR state, dying.  State machine is buggy.");
			exit (1);
			break; // End of ERROR
			
		default:
			error_log("State machine went to an unknown state, redirecting to ERROR");
			$state = $states['ERROR'];
			break;
		}
		// error_log("State=$names[$state] after switch");
	}

	fclose($f);
	return $sql_list;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
