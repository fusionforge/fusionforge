<?php
/*
 *
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

error_reporting (E_ALL);
openlog ("gforge", LOG_PID | LOG_PERROR, LOG_LOCAL5);

function log_error ($message, $file = null, $function = null, $class = null)
{
	log_base (LOG_ERR, $message, $file, $function, $class);
}

function log_warning ($message, $file = null, $function = null, $class = null)
{
	log_base (LOG_WARNING, $message, $file, $function, $class);
}

function log_info ($message, $file = null, $function = null, $class = null)
{
	log_base (LOG_INFO, $message, $file, $function, $class);
}

function log_debug ($message, $file = null, $function = null, $class = null)
{
	log_base (LOG_DEBUG, $message, $file, $function, $class);
}

function log_base ($priority, $message, $file = null, $function = null, $class = null)
{
	$full_message = "";
	if (isset ($file) == true)
	{
		$full_message .= $file;
	}
	if (isset ($class) == true)
	{
		if (isset ($file) == true)
		{
			$full_message .= " - ";
		}
		$full_message .= $class;
	}
	if (isset ($function) == true)
	{
		if (isset ($class) == true)
		{
			$full_message .= "::";
		}
		else
		{
			if (isset ($file) == true)
			{
				$full_message .= " - ";
			}
		}
		$full_message .= $function . "()";
	}
	if ((isset ($file) == true)
	||  (isset ($class) == true)
	||  (isset ($function) == true))
	{
		$full_message .= " - ";
	}
	$full_message .= $message;
	syslog ($priority, $full_message);	
}

?>
