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

require_once ("squal_pre.php");
require_once ("common/include/cron_utils.php");
require_once ("common/novaforge/log.php");
require_once ("plugins/mantis/include/synchronizefunctions.php");

$cron_entry_log = "";
log_cron_error (date ("d/m/Y H:i:s") . " - Start of Mantis projects synchronization", __FILE__);
if (synchronize (null, $array_errors) == true)
{
	log_cron_error ("The Mantis projects have been successfully synchronized", __FILE__);
}
else
{
	log_cron_error ("The synchronization of Mantis projects failed", __FILE__);
	foreach ($array_errors as $error)
	{
		log_cron_error ($error, __FILE__);
	}
}
log_cron_error (date ("d/m/Y H:i:s") . " - End of Mantis projects synchronization", __FILE__);
cron_entry (23, $cron_entry_log);

function log_cron_error ($message, $file = null, $function = null, $class = null)
{
	global
		$cron_entry_log;

	if (strlen ($cron_entry_log) > 0)
	{
		$cron_entry_log .= "\n";
	}
	$cron_entry_log .= $message;
	log_error ($message, $file, $function, $class);
}

?>
