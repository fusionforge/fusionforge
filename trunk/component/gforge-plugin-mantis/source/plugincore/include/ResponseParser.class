<?php
/*
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
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
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

class ResponseParser
{
	var $array_attributes;
	var $array_bugs;
	var $array_users;

	function parse ($xml)
	{
		$ok = false;
		$this->array_attributes = array ();
		$this->array_bugs = array ();
		$this->array_users = array ();
		$parser = xml_parser_create ();
		xml_set_object ($parser, $this);
		xml_parser_set_option ($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_set_element_handler ($parser, "element_open", "element_close");
		if (xml_parse ($parser, $xml) == 1)
		{
			$ok = true;
		}
		else
		{
			log_error ("Parsing error at line " . xml_get_current_line_number ($parser) . ", column " . xml_get_current_column_number ($parser) . " (code " . xml_get_error_code ($parser) . ": " . xml_error_string (xml_get_error_code ($parser)) .")", __FILE__, __FUNCTION__, __CLASS__);
		}
		xml_parser_free ($parser);
		return $ok;
	}

	function element_open ($parser, $name, $attribs)
	{
		switch ($name)
		{
			case "BUG" :
				$this->array_bugs [] = $attribs;
				break;
			case "USER" :
				$this->array_users [] = $attribs;
				break;
			default :
				if (count ($attribs) > 0)
				{
					$this->array_attributes [$name] = $attribs;
				}
		}
	}

	function element_close ($parser, $name)
	{
	}

}

?>
