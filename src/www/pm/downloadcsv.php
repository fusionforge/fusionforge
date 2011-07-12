<?php
/*
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
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

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The provided file ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

/*
Record description for .csv file format

project_task_id					This is the ID in gforge database
external_task_id				The equivalent of project_task_id but determined by
									external application, such as MS Project
parent_id						The project_task_id of the parent task, if any
external_parent_id				The equivalent of parent project_task_id but
									determined by external application, such as MS Project
title							Name                ********may contain , characters???
duration						Days
work							Hours
start_date
end_date
percent_complete
priority						low, medium, high
notes							Details             ********may contain , characters???
resource1_unixname
resource2_unixname
resource3_unixname
resource4_unixname
resource5_unixname
dependenton1_project_task_id
dependenton1_external_task_id
dependenton1_linktype			SS SF FS FF
dependenton2_project_task_id
dependenton2_external_task_id
dependenton2_linktype
dependenton3_project_task_id
dependenton3_external_task_id
dependenton3_linktype
dependenton4_project_task_id
dependenton4_external_task_id
dependenton4_linktype
dependenton5_project_task_id
dependenton5_external_task_id
dependenton5_linktype
*/

require_once $gfcommon.'include/User.class.php';
require_once $gfcommon.'pm/ProjectTaskFactory.class.php';

$headers = getIntFromRequest('headers');
$full = getIntFromRequest('full');
$sep = getStringFromRequest('sep', ',');

$date = date('Y-m-d');

header('Content-type: text/csv');
header('Content-disposition: filename="tasks-'.$date.'.csv"');

$ptf = new ProjectTaskFactory($pg);
if (!$ptf || !is_object($ptf)) {
    exit_error(_('Could Not Get ProjectTaskFactory'),'pm');
} elseif ($ptf->isError()) {
    exit_error($ptf->getErrorMessage(),'pm');
}
$ptf->order='external_id';
$pt_arr =& $ptf->getTasks();
if ($ptf->isError()) {
    exit_error($ptf->getErrorMessage(),'pm');
}

//
//	Iterate the array of tasks and dump them out to a comma-separated file
//

$arrRemove = array("\r\n", "\n", $sep);

$header = array(
	'project_task_id',
	'external_task_id',
	'parent_id',
	'external_parent_id',
	'title',
	'duration',
	'work',
	'start_date',
	'end_date',
	'percent_complete',
	'priority',
	'notes',
	'resource1_unixname',
	'resource2_unixname',
	'resource3_unixname',
	'resource4_unixname',
	'resource5_unixname',
	'dependenton1_project_task_id',
	'dependenton1_external_task_id',
	'dependenton1_linktype',
	'dependenton2_project_task_id',
	'dependenton2_external_task_id',
	'dependenton2_linktype',
	'dependenton3_project_task_id',
	'dependenton3_external_task_id',
	'dependenton3_linktype',
	'dependenton4_project_task_id',
	'dependenton4_external_task_id',
	'dependenton4_linktype',
	'dependenton5_project_task_id',
	'dependenton5_external_task_id',
	'dependenton5_linktype');

if ($full) {
$header = array(
	'project_task_id',
	'external_task_id',
	'parent_id',
	'external_parent_id',
	'title',
	'category',
	'duration',
	'work',
	'start_date',
	'end_date',
	'percent_complete',
	'priority',
	'notes',
	'resource1_unixname',
	'resource2_unixname',
	'resource3_unixname',
	'resource4_unixname',
	'resource5_unixname',
	'dependenton1_project_task_id',
	'dependenton1_external_task_id',
	'dependenton1_linktype',
	'dependenton2_project_task_id',
	'dependenton2_external_task_id',
	'dependenton2_linktype',
	'dependenton3_project_task_id',
	'dependenton3_external_task_id',
	'dependenton3_linktype',
	'dependenton4_project_task_id',
	'dependenton4_external_task_id',
	'dependenton4_linktype',
	'dependenton5_project_task_id',
	'dependenton5_external_task_id',
	'dependenton5_linktype');
}

if ($headers) {
	echo join($sep, $header)."\n";
}

for ($i=0; $i<count($pt_arr); $i++) {

	echo $pt_arr[$i]->getID().$sep.
		$pt_arr[$i]->getExternalID().$sep.
		$pt_arr[$i]->getParentID().$sep.
		$sep.
		str_replace($arrRemove, ' ', $pt_arr[$i]->getSummary()).$sep;
	if ($full) {
		echo $pt_arr[$i]->getCategoryName().$sep;
	}
	echo $pt_arr[$i]->getDuration().$sep.
		$pt_arr[$i]->getHours().$sep.
		date('Y-m-d H:i:s',$pt_arr[$i]->getStartDate()).$sep.
		date('Y-m-d H:i:s',$pt_arr[$i]->getEndDate()).$sep.
		$pt_arr[$i]->getPercentComplete().$sep.
		$pt_arr[$i]->getPriority().$sep.
		str_replace($arrRemove, ' ', $pt_arr[$i]->getDetails()).$sep;

		$users =& user_get_objects($pt_arr[$i]->getAssignedTo());
		for ($j=0; $j<5; $j++) {
			if ($j < count($users)) {
				if ($users[$j]->getUnixName() != 'none') {
					echo $users[$j]->getUnixName();
				}
			}
			echo $sep;
		}

		$dependentOn = $pt_arr[$i]->getDependentOn();
		$keys=array_keys($dependentOn);
		for ($j=0; $j<5; $j++) {
			if ($j < count($keys)) {
				echo $keys[$j].$sep.$sep.$dependentOn[$keys[$j]];
			} else {
				echo $sep.$sep;
			}
			if ($j<4) {
				echo $sep;
			}
		}
	echo "\n";
}
?>
