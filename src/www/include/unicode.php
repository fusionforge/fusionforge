<?php
/**
 *
 * fabio bertagnin fbertagnin@mail.transiciel.com
 * Copyright 2010, FusionForge Team
 * http://fusionforge.org
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

if (!defined('UNICODE.PHP'))
{
	define ('UNICODE.PHP', '1');
	function convert_unicode ($text)
	{
		$rep = $text;
		//$rep = mb_convert_encoding ($rep, "ascii", "UTF-8");
		$rep = utf8_decode($rep);
		return $rep;
	}
	
}
?>
