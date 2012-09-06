<?php
/*-
 * one-off script to mass-move tracker items
 *
 * Copyright © 2012
 *	Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * All rights reserved.
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
 *-
 * Edit below; comments inline. This example moves a lot¹ of tracker
 * items from the trackers with the atid 378 and 667 to 686 and from
 * atid 209 to 688, keeping everything except the assignee (but see
 * below for where to change to keep that too, IFF the assignees are
 * all technicians in the destination trackers as well). The trackers
 * must already have been (temporarily²) moved to the group in which
 * the destination tracker lives in (but can be moved back afterwards).
 *
 * ① e.g. all open ones, got from the following SQL:
 *	SELECT artifact_id FROM artifact WHERE status_id=1 AND group_artifact_id=378;
 *
 * ② e.g. with the following SQL:
 *	UPDATE artifact_group_list SET group_id=145 WHERE group_artifact_id IN (378, 667, 209);
 */

require '/usr/share/gforge/common/include/env.inc.php';
require_once $gfcommon."include/pre.php";
require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'tracker/ArtifactFile.class.php';
require_once $gfwww.'tracker/include/ArtifactFileHtml.class.php';
require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfwww.'tracker/include/ArtifactTypeHtml.class.php';
require_once $gfwww.'tracker/include/ArtifactHtml.class.php';
require_once $gfcommon.'tracker/ArtifactCanned.class.php';
require_once $gfcommon.'tracker/ArtifactTypeFactory.class.php';

session_set_admin();

$mvm = array(
	/* from tracker => array(item, item, …) */
	378 => array(
		1829,
		1840,
		1841,
		1908,
		2323,
		2324,
		2370,
		2374,
		2375,
		2382,
		2383,
		2389,
		2391,
		2396,
		2397,
		2399,
		2406,
		2440,
		2496,
		2502,
		2504,
		2508,
		2547,
		2635,
		2638,
		2639,
		2656,
		2862,
		2872,
		2897,
		2898,
		2922,
		2932,
		2933,
		2942,
		2943,
		2946,
	),
	667 => array(
		1595,
		2330,
		2986,
		2987,
		2989,
		2991,
		2992,
		2995,
	),
	209 => array(
		576,
		610,
		619,
		693,
		717,
		887,
		889,
		890,
		894,
		898,
		918,
		928,
		942,
		992,
		1002,
		1003,
		1004,
		1019,
		1074,
		1078,
		1085,
		1149,
		1154,
		1361,
		1441,
		1451,
		1454,
		1455,
		1620,
		1647,
		1677,
		1678,
		1679,
		1680,
		1687,
		1751,
		1753,
		1786,
		1787,
		1843,
		1851,
		1856,
		1857,
		1889,
		1953,
		1963,
		1982,
		2013,
		2129,
		2130,
		2166,
		2168,
		2173,
		2273,
		2300,
		2309,
		2310,
		2311,
		2312,
		2327,
		2332,
		2335,
		2363,
		2366,
		2380,
		2403,
		2416,
		2435,
		2436,
		2437,
		2438,
		2450,
		2466,
		2499,
		2501,
		2544,
		2554,
		2634,
		2641,
		2655,
		2661,
		2666,
		2867,
		2899,
		2902,
		2903,
		2904,
		2939,
		2974,
		2975,
		2993,
		2996,
		2997,
	),
);

db_begin();
foreach ($mvm as $srctrk => $srclist) {
	/* destination tracker: here, selected by source tracker */
	$dsttrk = ($srctrk == 209 ? 688 : 686);
	foreach ($srclist as $srcitemid) {
		$srcitem =& artifact_get_object($srcitemid);
		if (!$srcitem || !is_object($srcitem) || $srcitem->isError()) {
			echo "error item $srcitemid\n";
			db_rollback();
			die;
		}
		if (!$srcitem->update(
		    $srcitem->getPriority(),	/* keep priority the same */
		    $srcitem->getStatusID(),	/* keep status the same */
			/*
			 * assign them all to Nobody,
			 * since technicians may not match
			 */
		    100 /*$srcitem->getAssignedTo()*/,
		    false,			/* keep summary the same */
		    100,			/* no canned response */
		    false,			/* keep details the same */
		    $dsttrk,			/* move to this tracker */
			/* and keep extra fields the same */
		    $srcitem->getExtraFieldData())) {
			echo "item $srcitemid: " . $srcitem->getErrorMessage()."\n";
			db_rollback();
			die;
		}
	}
}
db_commit();
echo "ok\n";
