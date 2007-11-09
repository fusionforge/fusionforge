<?php
/**
 * GForge Doc Mgr Facility
 *
 * 
 * Fabio Bertagnin november 2005
 *
 * @version   $Id: 04_IMPROVDOC_75_document_specific_search_engine.dpatch,v 1.1 2006/01/11 17:02:45 fabio Exp $
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


/*
	Document Manager

	by Fabio Bertagnin

*/


class Parsedata {
	/**
	 *  Constructor.
	 *
	 *	@param
	 *	@return
	 */
	 var $parsers;
	 var $p_path = "";
	 
	function Parsedata($ppath="") 
	{
		$this->p_path = $ppath;
		$p = get_parser_list ($ppath);
		$this->parsers = $p;
		return true;
	}
	function get_parse_data ($data, $title, $description, $filetype)
	{
		$parser = "";
		$rep = "";
		$data1 = $data;
		if (array_key_exists($filetype, $this->parsers))
		{ 
			// parse data if good parser exists
			$parser = $this->p_path.$this->parsers[$filetype];
			$filename = rand(10000,99999);
			$filename = "/tmp/gfd$filename.tmp";
			$fp = fopen ($filename, "w");
			fwrite ($fp, $data1);
			fclose ($fp);
			
			$cmd = "$parser $filename";
			echo "cmd $cmd<br />";
			$rep = shell_exec ($cmd);
			echo "rep : <br />$rep<br />";
			unlink ("$filename");
			
			// pour tests et debug
			// echo "parser $parser<br />";
			// echo "$rep<br /><br />";
			// exit();
		}
		// alwais parse titre and description
		$data2 = utf8_decode(" $title");
		$data2 .= utf8_decode(" $description");
		// $data2 = ereg_replace ("\n", " ", $data2);
		// temporary file for traitement
		$filename = rand(10000,99999);
		$filename = "/tmp/gfi$filename.tmp";
		$fp = fopen ($filename, "w");
		fwrite ($fp, $data2);
		fclose ($fp);
		$cmd = $this->p_path.$this->parsers["text/plain"];
		$cmd = "$cmd $filename";
		$rep1 = shell_exec ($cmd);
		return ereg_replace ("\n", " ", "$rep $rep1");
	}
	
	
	function print_debug ($text)
	{
		echo "$text \n";
		ob_flush();
	}
}

function get_parser_list ($parser_path)
{
	$file = $parser_path."parser_list.txt";
	$rep = array();
	$fp = fopen ($file, "r");
	if ($fp)
	{
		$buff = fread($fp, 2048);
		$a1 = explode ("\n", $buff);
		foreach ($a1 as $a)
		{
			if (trim($a) != "" && substr($a, 0,1) != "#")
			{
				$a2 = explode ("\t", $a);
				$rep[$a2[0]] = $a2[1];
			}
		}
	}
	return $rep;
}

?>
