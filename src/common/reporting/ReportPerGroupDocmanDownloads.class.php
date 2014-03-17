<?php
/**
 * FusionForge reporting system
 *
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
 * Copyright 2009, Roland Mas
 * Copyright 2012, Franck Villaume
 * Copyright 2014, Franck Villaume - TrivialDev
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

/**
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

class ReportPerGroupDocmanDownloads extends Report {

	function __construct($group_id, $start = 0, $end = 0) {
		$this->Report();

		if (!$start) {
			$start = mktime(0, 0, 0, date('m'), 1, date('Y'));
		}
		if (!$end) {
			$end = time();
		} else {
			$end--;
		}

		// Convert start & end date to month .
		$start_m = date('Ym', $start);
		$end_m = date('Ym', $end);

		if (!$group_id) {
			$this->setError(_('No Valid Group Object'));
			return;
		}

		$res = db_query_params ('SELECT docdata_vw.filename, docman_dlstats_doc.user_id,
					docman_dlstats_doc.month || lpad(docman_dlstats_doc.day::text,2,0::text),
					docdata_vw.doc_group
					FROM docman_dlstats_doc, docdata_vw
					WHERE docdata_vw.group_id = $1
					AND docman_dlstats_doc.month >= $2
					AND docman_dlstats_doc.month <= $3
					AND docdata_vw.docid = docman_dlstats_doc.docid
					ORDER BY docman_dlstats_doc.month DESC,
					docman_dlstats_doc.day DESC',
					array ($group_id,
					       $start_m,
					       $end_m));

		$this->start_date = $start;
		$this->end_date = $end;

		if (!$res || db_error()) {
			$this->setError('ReportUserAct:: '.db_error());
			return;
		}

		$rows = db_numrows($res);
		$arr = array();
		$i = 0;
		if ($rows > 0) {
			while ($row = db_fetch_array($res)) {
				$arr[$i++] = $row;
			}
		}

		$this->data =& $arr;
	}
}
