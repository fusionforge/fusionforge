<?php

/**
 * This file is (c) Copyright 2010 by Olivier BERGER, Institut
 * TELECOM
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * This program has been developed in the frame of the HELIOS
 * project with financial support of its funders.
 *
 */

include_once "lime-parse_engine.php";
include_once "cql-lime_parser.inc.php";

// change to true to get debug
$debug_founds=false;
//$debug_founds=true;

function debug_found($term, $message) {
	global $debug_founds;
	if($debug_founds) echo "found $term: ".$message."\n";
}

function tokenize($line) {
	// Numbers are tokens, as are all other non-whitespace characters.
	// Note: This isn't a particularly efficent tokenizer, but it gets the
	// job done.
	$out = array();
	//echo "tokenize '$line'\n";
	while (strlen($line)) {
		//echo "handle rest of the line : '$line'\n";
		$line = trim($line);
		if (preg_match('/^[0-9]+(\.[0-9]*)?/', $line, $regs)) {
			# It's a number
			$out[] = $regs[0];
			$line = substr($line, strlen($regs[0]));
		} else if (preg_match('/^[A-Za-z][A-Za-z0-9_]+/', $line, $regs)) {
			# It's a word
			$out[] = $regs[0];
			$line = substr($line, strlen($regs[0]));
		} else if (preg_match('/^"[^"]*"/', $line, $regs)) {
			# It's a string
			# we need to take care of embedded escaped quotes'\"'
			$outstr='';
			while(preg_match('/^"[^"]*"/', $line, $regs)) {
				$string = $regs[0];
				//print_r($string);
				if ( (strlen($string) > 2) && ($string[strlen($string)-2] == "\\") ) {
					//print_r('- outstr :');
					$outstr .= substr($string, 0, strlen($string)-1);
					//print_r($outstr);
					//print_r('- newline :');
					$line = substr($line, strlen($string)-1);
					//print_r($line);
				}
				else {
					$outstr .= $string;
					$line = substr($line, strlen($string));
					//print_r($line);
				}
			}
			$out[] = $outstr;
		} else {
			# It's some other character
			$out[] = $line[0];
			$line = substr($line, 1);
		}
	}
	return $out;
}

//$symbol_table = array();

function parse_cql($line) {
	//echo "calculate : ";
	global $parser;
	$parser = new parse_engine(new cql_lime_parser());
	global $parsed_results;
	$parsed_results = False;

	if (!strlen($line)) return $parsed_results;
	try {
		$parser->reset();
		// split the line into tokens and process them
		$tokens = tokenize($line);
		//echo "tokenized :";
		//print_r($tokens);
		foreach($tokens as $t) {
			if (is_numeric($t)) {
				$parser->eat('integer', doubleval($t));
			}
			else if ($t[0] == '"') {
				$parser->eat('string', $t);
			}
			else if (ctype_alpha($t)) {
				// a word
				if ($t == 'and') {
					$parser->eat('and_kw', $t);
				}
				elseif ($t == 'sort') {
					$parser->eat('sort_kw', $t);
				}
				else {
					$parser->eat('word', $t);
				}
			}
			elseif (preg_match('/^[A-Za-z][A-Za-z0-9_]+/', $t)){
				$parser->eat('word' ,$t);
			}
			else {
				switch($t) {
			  case '=' :
			  	$parser->eat('equal_kw', $t);
			  	break;
			  case '!' :
			  	$parser->eat('not_kw', $t);
			  	break;
			  case '<' :
			  	$parser->eat('lt_kw', $t);
			  	break;
			  case '>' :
			  	$parser->eat('gt_kw', $t);
			  	break;
			  default :
			  	$parser->eat("'$t'", null);
			  	break;
				}
			}
		}
		$parser->eat_eof();

	} catch (parse_error $e) {
		echo $e->getMessage(), "\n";
	}
	return $parsed_results;
}

if($debug_founds) {
	$parser = new parse_engine(new cql_lime_parser());
	while ($line = fgets(STDIN)) parse_cql(trim($line));
}
