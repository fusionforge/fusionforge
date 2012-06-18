<?php
/*-
 * one-off script to export tracker items (limited)
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
 * Edit below; comments inline.  Exports all open items in a tracker,
 * although only part of the data, as JSON.
 */

require "/usr/share/gforge/common/include/env.inc.php";
require_once $gfcommon."include/pre.php";
require_once $gfcommon.'include/minijson.php';
require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'tracker/ArtifactFile.class.php';
require_once $gfwww.'tracker/include/ArtifactFileHtml.class.php';
require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfwww.'tracker/include/ArtifactTypeHtml.class.php';
require_once $gfwww.'tracker/include/ArtifactHtml.class.php';
require_once $gfcommon.'tracker/ArtifactCanned.class.php';
require_once $gfcommon.'tracker/ArtifactTypeFactory.class.php';

function usage($rc=1) {
	echo "Usage: .../tracker-export.php 123\n" .
	    "\twhere 123 is the group_artifact_id of the tracker to export\n";
	exit($rc);
}

if (count($argv) != 2) {
	usage();
}
$argv0 = array_shift($argv);
$argv1 = array_shift($argv);

if ($argv1 == '-h') {
	usage(0);
}
if (!($trk = util_nat0($argv1))) {
	usage();
}

session_set_admin();

/* pull a list of all open tracker items */
$res = db_query_params('SELECT artifact_id FROM artifact
	WHERE status_id=1 AND group_artifact_id=$1',
    array($trk));
if (!$res || db_numrows($res) < 1 ||
    !($srclist = util_result_column_to_array($res))) {
	echo "error: " . db_error() . "\n";
	die;
}

$out = array();
foreach ($srclist as $aidx) {
	/* retrieve the current item */
	$aid = (int)$aidx;
	$ah =& artifact_get_object($aid);
	if (!$ah || !is_object($ah) || $ah->isError()) {
		echo "error item $aidx\n";
		db_rollback();
		die;
	}

	/* prepare an export record */
	$rec = array();
	foreach ($ah->data_array as $k => $v) {
		/* skip numeric fields */
		if (!preg_match('/^[a-z]/', $k)) {
			continue;
		}

		/* distinguish actions for specific fields */
		switch ($k) {
		case 'summary':
		case 'details':
			/* fix mistake of how stuff is stored in the DB */
			$v = util_unconvert_htmlspecialchars($v);
			/* fix issue with how stuff may be stored in the DB */
			$v = util_sanitise_multiline_submission($v);
			/* but export using logical newlines */
			$v = str_replace("\r\n", "\n", $v);
			/* now we’ve got something we can use */
			$rec[$k] = $v;
			break;

		/* uncomment this to not emit the tracker id */
		//case 'group_artifact_id':
		//	break;

		default:
			$rec[$k] = $v;
			break;
		}
	}

	/* add a _permalink pseudo-field */
	$rec['_permalink'] = util_make_url('/tracker/t_follow.php/' . $aid);

	/*
	 * here would be the place to add more pseudo-elements, like
	 * a _comments Array, an _extrafields Value, a _files Value…
	 */

	/* append to list of records to emit */
	$out[$aid] = $rec;
}

/* generate output */
echo minijson_encode($out) . "\n";
