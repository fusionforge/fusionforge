<?php
/*
 * FusionForge reporting system
 *
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
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

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The class ("Contribution") has not been tested and/or
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

require_once $gfcommon.'reporting/Report.class.php';

class ReportDownloads extends Report {

	function ReportDownloads($group_id,$package_id,$start=0,$end=0) {
		$this->Report();

		if (!$start) {
			$start=mktime(0,0,0,date('m'),1,date('Y'));;
		}
		if (!$end) {
			$end=time();
		} else {
			$end--;
		}

		// Convert start & end date to month .
		$start_m = date('Ym', $start);
		$end_m = date('Ym', $end);

		if (!$group_id) {
			$this->setError('No Group_id');
			return false;
		}

		if (!$package_id) {
			$res = db_query_params ('SELECT package_id FROM frs_package WHERE frs_package.group_id = $1',
						array ($group_id)) ;
			$package_id = db_result($res, 0, 'package_id');
		}
		if (!$package_id) {
			$this->setError(_('There are no packages defined.'));
			return false;
		}

		$res = db_query_params ('SELECT frs_package.name, frs_release.name,
                       frs_file.filename, users.realname,
                       frs_dlstats_file.month || lpad(frs_dlstats_file.day::text,2,0::text),
                       users.user_name
                FROM frs_dlstats_file, frs_file, frs_release,
                     frs_package, users
                WHERE frs_dlstats_file.user_id = users.user_id
                  AND frs_dlstats_file.file_id = frs_file.file_id
                  AND frs_file.release_id = frs_release.release_id
                  AND frs_release.package_id = frs_package.package_id
                  AND frs_package.group_id = $1
                  AND frs_release.package_id = $2
                  AND frs_dlstats_file.month >= $3
                  AND frs_dlstats_file.month <= $4
                ORDER BY frs_dlstats_file.month DESC,
                       frs_dlstats_file.day DESC',
					array ($group_id,
					       $package_id,
					       $start_m,
					       $end_m)) ;

		$this->start_date=$start;
		$this->end_date=$end;

		if (!$res || db_error()) {
			$this->setError('ReportUserAct:: '.db_error());
			return false;
		}

		$rows=db_numrows($res);
		$arr = array();
		$i=0;
		if ($rows > 0) {
			while ($row = db_fetch_array ($res)) {
				$arr[$i++] = $row;
			}
		}

		$this->data =& $arr;
		return true;
	}
}

?>
