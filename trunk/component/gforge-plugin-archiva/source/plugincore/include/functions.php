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

require_once ("common/novaforge/log.php");

function deleteArchivaConfigEntry ($name, $current_value = null)
{
	$ok = false;
	$query = "DELETE FROM plugin_archiva_config WHERE name='". $name ."'";
	if (isset ($current_value) == true)
	{
		$query .= " AND value='". $current_value."'";
	}
	$result = db_query ($query);
	if ($result !== false)
	{
		$ok = true;
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}

function addArchivaConfigEntry ($name, $value)
{
	$ok = false;
	$query = "INSERT INTO plugin_archiva_config (name, value) VALUES ('". $name ."','" . $value . "')";
	$result = db_query ($query);
	if ($result !== false)
	{
		$id = db_insertid ($result, "plugin_archiva_config", "default_id");
		if ($id != 0)
		{
			$ok = true;
		}
		else
		{
			log_error ("Function db_insertid() failed after query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}

function updateArchivaConfigEntry ($name, $value, $current_value = null)
{
	$ok = false;
	$query = "UPDATE plugin_archiva_config SET value='" . $value . "' WHERE name='" .  $name ."'";
	if ($current_value != null)
	{
		$query .= " AND value='". $current_value."'";
	}
	$result = db_query ($query);
	if ($result !== false)
	{
		$ok = true;
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}

function getArchivaConfigEntry ($name, &$value)
{
	$ok = false;
	$query = "SELECT value FROM plugin_archiva_config WHERE name='". $name ."'";
	$result = db_query ($query);
	if ($result !== false)
	{
		$ok = true;
		if (db_numrows ($result) > 0)
		{
			$value = db_result ($result, 0, "value");
		}
		else
		{
			$value = null;
		}
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}

?>
