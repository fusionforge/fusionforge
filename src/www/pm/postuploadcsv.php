<?php
/**
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
 * Copyright 2015-2016, Franck Villaume - TrivialDev
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

require_once $gfcommon.'pm/import_utils.php';

$input_file = getUploadedFile('userfile');
if (isset($input_file) && isset($input_file['tmp_name']) &&
	is_uploaded_file($input_file['tmp_name'])) {

	if (function_exists('finfo_open')) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$uploaded_data_type = finfo_file($finfo, $input_file['tmp_name']);
	} else {
		$uploaded_data_type = $input_file['type'];
	}

	if ($uploaded_data_type === "text/plain") {

		$handle = fopen($input_file['tmp_name'], 'r');
		$tasks = array();

		// Detect separator & if headers are present or not.
		$sep = ',';
		$values = fgetcsv($handle, 4096, $sep);
		if (count($values) == 1) {
			$sep = ';';
			fseek($handle, 0);
			$values = fgetcsv($handle, 4096, $sep);
		}
		$headers = (in_array('project_task_id', $values) && in_array('title', $values));

		// Rewind the file.
		fseek($handle, 0);

		if ($headers) {
			// Headers are given in the file (first line).
			$headers = array_flip(fgetcsv($handle, 4096, $sep));
			while (($values = fgetcsv($handle, 4096, $sep)) !== false) {
				$task = array();
				foreach($headers as $name => $id) {
					if ($name == 'project_task_id') $name = 'id';
					if ($name == 'title') $name = 'name';
					$task[$name] = $values[$id];
				}
				$tasks[] = $task;
			}
		} else {
			// Original code (default format, no headers)
			while (($cols = fgetcsv($handle, 4096, $sep)) !== false) {

				$resources = array();
				for ($i=13;$i<18;$i++) {
					if (trim($cols[$i]) != '') {
						$resources[] = array('user_name'=>$cols[$i]);
					}
				}

				$dependentOn = array();

				for ($i=18;$i<31;$i=$i+3) {
					if (trim($cols[$i]) != '') {
						$dependentOn[] = array('task_id'=>$cols[$i], 'msproj_id'=>$cols[$i+1], 'task_name'=>'', 'link_type'=>$cols[$i+2]);
					}
				}

				$tasks[] = array('id'=>$cols[0],
						'msproj_id'=>$cols[1],
						'parent_id'=>$cols[2],
						'parent_msproj_id'=>$cols[3],
						'name'=>$cols[4],
						'category'=>$cols[5],
						'duration'=>$cols[6],
						'work'=>$cols[7],
						'start_date'=>$cols[8],
						'end_date'=>$cols[9],
						'percent_complete'=>$cols[10],
						'priority'=>$cols[11],
						'resources'=>$resources,
						'dependenton'=>$dependentOn,
						'notes'=>$cols[12]);
			}
		}
		$res = &pm_import_tasks($group_project_id, $tasks, getIntFromRequest('replace') == 1);
	} else {
		$res['errormessage'] = _('Wrong file type. Only plain CSV file supported');
	}
} else {
	$res['errormessage'] = _('Parameter error');
}

if (isset($res['success']) && $res['success']) {
	$feedback .= _('Import was successful');
} else {
	$error_msg .= $res['errormessage'];
}
