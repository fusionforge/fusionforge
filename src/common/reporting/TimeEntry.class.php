<?php
/**
 * FusionForge reporting system
 *
 * Copyright 2003-2004, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
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

/**
 * Base error class
 */
require_once $gfcommon.'include/Error.class.php';

/**
 * Time Entry model object
 *
 * This is a simple OO-wrapper to the existing time entry methods.  At some point someone will want
 * to actually implement the procedural functions direct in this file.  This class is only used by
 * the SOAP interface.
 *
 * @author Tony Bibbs <tony@geeklog.net>
 * @copyright Copyright 2005, Tony Bibbs
 * @todo This is just a wrapper to the existing procedural methods.  At some point someone will
 * want to move that implementation here.
 * @todo I'm sure this isn't code per the gForge coding standards.  This should be fixed.
 *
 */
class TimeEntry extends Error {
    /**
     * Constructor
     *
     * @author Tony Bibbs <tony@geeklog.net>
     * @access public
     *
     */
    function TimeEntry() 
    {
    }

    /**
     * Creates a time entry record
     *
     * NOTE: this is a real hack as it uses the existing procedural code to call on functionality.
     * The biggest drawback is that this method will not be able to return the Primary Key for the
     * time entry record because the key is a unixtimestamp (see the way the UI uses timeadd.php
     * to fully appreciate what I mean).
     *
     * @author Tony Bibbs <tony@geeklog.net>
     * @access public
     * @param int $projectTaskId The project task the user is reporting time to
     * @param int $week The week the time being reported was done
     * @param int $daysAdjust Represents the offset to add to the given week to specify the day
     * @param int $timeCode The type of work that was done (general categorization)
     * @param float $hours The actual time spent
     * @return int This will be the Artificat ID otherwise it will be false if an error occurred
     * @todo I'm quite concerned that none of the form data is being sanitized for things like
     * unwanted HTML, JavaSript and SQL Injection.  Might be worth adding that sort of filtering
     * as provided by the KSES Filter (search Google).
     * @todo The check that looks to see if this method works is not language independent.  
     * someone that better understands how that all works will want to remove the hardcoded
     * 'successfully added'.
     *
     */
    function create($projectTaskId, $week, $daysAdjust, $timeCode, $hours)
    {
        $report_date=($week + ($days_adjust*REPORT_DAY_SPAN))+(12*60*60);
        $res = db_query_params ('INSERT INTO rep_time_tracking (user_id,week,report_date,project_task_id,time_code,hours) VALUES ($1,$2,$3,$4,$5,$6)',
				array (user_getid(),
				       $week,
				       $report_date,
				       $projectTaskId,
				       $timeCode,
				       $hours)) ;
        if (!$res) {
            exit_error(db_error(),'tracker');
        } else {
            $feedback.=_('Successfully Added');
        }
	return db_affected_rows($res);
    }  

    /**
     * Updates a timeEntry record.
     *
     * This isn't supported by the current timeadd.php code so I'm assuming that all 
     * that is expected is that instead of changing something you'd simply delete it
     * and readd it.  Messy, IMHO, but I am still including this method here to let
     * know I purposely left this unimplemented.
     *
     * @author Tony Bibbs <tony@geeklog.net>
     * @access public
     * @return boolean Always false
     *
     */
    function update()
    {
        // Not supported in timeadd.php
        return false;
    } 

    /**
     * Deletes an existing timeEntry record
     *
     * @author Tony Bibbs <tony@geeklog.net>
     * @access public
     * @param int $projectTaskId ID for the task which the time entry record belongs to.
     * @param int $reportDate
     * @param int $oldTimeCode ID of time code that was associated with time entry record.
     * @return boolean True if delete works, otherwise false)
     *
     */
    function delete($projectTaskId, $reportDate, $oldTimeCode)
    {
        global $_POST;

        // Trick procedural code into thinking this was posted via the HTML form
        $_POST['submit'] = 1;
        $_POST['delete'] = 1;

        // Sanitize the data at some point.
        $project_task_id = $projectTaskId;
        $report_date = $reportDate;
        $old_time_code = $oldTimeCode;

        // Prepare to have the procedural code process all of this.  We'll need to buffer any
        // output so we can gracefully ignore it since this class is only used by the SOAP
        // interface
        ob_start();

        // Now pull in the procedural code to handle the processing.
        require_once $GLOBALS['gfwww'].'reporting/timeadd.php';
        $tmpOutput = ob_get_contents();

        // Now discard any output.
        ob_clean();        
        
        if (!stristr($tmpOutput, 'successfully deleted')) return false;
        return true;
    }

    function getTimeCodes()
    {
    }
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
