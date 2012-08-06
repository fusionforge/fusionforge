<?php

/**
 * SOAP Tracker Include - this file contains wrapper functions for the SOAP interface
 *
 * Copyright 2005 Tony Bibbs <tony@geeklog.net>
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

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'reporting/TimeEntry.class.php';

//addTimeEntry
$server->register(
        'addTimeEntry',
        array(
                'session_ser'=>'xsd:string',
                'projectTaskId'=>'xsd:int',
                'week'=>'xsd:int',
                'daysAdjust'=>'xsd:int',
                'timeCode'=>'xsd:int',
                'hours'=>'xsd:float'
        ),
        array('addTimeEntryResponse'=>'xsd:int'),
        $uri,$uri.'#addTimeEntry','rpc','encoded'
);

//
//      addArtifact
//

function &addTimeEntry($session_ser, $projectTaskId, $week, $daysAdjust, $timeCode, $hours)
{
        continue_session($session_ser);

        $teObj = new TimeEntry();
	error_log("addTimeEntry ($projectTaskId, $week, $daysAdjust, $timeCode, $hours)");
        return $teObj->create($projectTaskId, $week, $daysAdjust, $timeCode, $hours);
}

?>
