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
 * Edit below; comments inline.  Exports all items in a tracker as JSON.
 */

require '/usr/share/gforge/common/include/env.inc.php';
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

function dbe2jsn($v) {
	/* fix mistake of how stuff is stored in the DB */
	$v = util_unconvert_htmlspecialchars($v);
	/* fix issue with how stuff may be stored in the DB */
	$v = util_sanitise_multiline_submission($v);
	/* but export using logical newlines */
	$v = str_replace("\r\n", "\n", $v);
	/* now we’ve got something we can use */
	return $v;
}

function u2jsn($id) {
	if ($id == 100) {
		return 'nobody';
	}
	if (($u = user_get_object($id)) && is_object($u) &&
	    !$u->isError()) {
		return $u->getUnixName();
	}
	return sprintf('u%u', $id);
}

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

/* pull a list of all tracker items */
$res = db_query_params('SELECT artifact_id FROM artifact
	WHERE group_artifact_id=$1',
    array($trk));
if (!$res || db_numrows($res) < 1 ||
    !($srclist = util_result_column_to_array($res))) {
	echo "error: " . db_error() . "\n";
	die;
}

$at = false;
function initialise_at($atx) {
	global $at, $efarr, $efmap, $efval, $usespm, $out;

	if (!($at = $atx) || !is_object($at) || $at->isError()) {
		echo "error no AT\n";
		db_rollback();
		die;
	}
	$efarr = $at->getExtraFields();
	$efval = array();
	$usespm = $at->getGroup()->usesPM();

	$efmap = array();
	foreach ($efarr as $f) {
		$efmap[($efid = (int)$f['extra_field_id'])] = array(
			'name' => util_unconvert_htmlspecialchars($f['field_name']),
			'alias' => preg_replace('/^@/', '',
			    preg_replace('/[0-9.]+$/', '', $f['alias'])),
			'type' => (int)$f['field_type'],
		    );
		if (!strcasecmp($efmap[$efid]['name'], $efmap[$efid]['alias'])) {
			/* skip when alias is the same as name */
			$efmap[$efid]['alias'] = "";
		}
		$efval[$efid] = array();
		foreach (ArtifactExtraField_getAvailableValues($efid) as $k => $v) {
			$efval[$efid][(int)$v['element_id']] =
			    util_unconvert_htmlspecialchars($v['element_name']);
		}
	}
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

	if ($at === false) {
		initialise_at($ah->getArtifactType());
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
			$rec[$k] = dbe2jsn($v);
			break;

		case 'group_id':
		case 'group_artifact_id':
		case 'artifact_id':
			/* skip */
			break;

		case 'assigned_to':
		case 'close_date':
		case 'last_modified_date':
		case 'open_date':
		case 'priority':
		case 'status_id':
		case 'submitted_by':
			$rec[$k] = (int)$v;
			break;

		default:
			$rec[$k] = $v;
			break;
		}
	}

	/* add a _permalink pseudo-field */
	$rec['_permalink'] = util_make_url('/tracker/t_follow.php/' . $aid);

	/* copy votes */
	$rec['_votes'] = array_combine(array(
		'votes',
		'voters',
		'votage_percent',
	    ), $ah->getVotes());

	/* copy related tasks and add task permalink format pseudo-field */
	if ($usespm) {
		$rec['~related_tasks'] = array();
		$taskcount = db_numrows($ah->getRelatedTasks());
		if ($taskcount >= 1) for ($i = 0; $i < $taskcount; ++$i) {
			$taskinfo = db_fetch_array($ah->relatedtasks, $i);
			$rec['~related_tasks'][] =
			    (int)$taskinfo['project_task_id'];
		}
		sort($rec['~related_tasks'], SORT_NUMERIC);
		$rec['_fmt_taskpermalink'] = util_make_url('/pm/t_follow.php/%d');
	}

	/* copy comments */
	$res = $ah->getMessages();
	$c = array();
	if ($res) while (($row = db_fetch_array($res))) {
		$c[] = array(
			'adddate' => (int)$row['adddate'],
			'from_email' => $row['from_email'],
			'body' => dbe2jsn($row['body']),
			'from_user' => u2jsn($row['user_id']),
		    );
	}
	$rec['~comments'] = $c;

	/* copy extra fields */
	foreach ($ah->getExtraFieldData() as $k => $v) {
		$k = (int)$k;
		switch ((int)$efarr[$k]['field_type']) {
		case 2:		/* ARTIFACT_EXTRAFIELDTYPE_CHECKBOX */
		case 5:		/* ARTIFACT_EXTRAFIELDTYPE_MULTISELECT */
			/* stored as arrays, values as-is */
			if (!is_array($v)) {
				/* error? */
				$v = array(100);
			}
			break;

		/* all others are stored crippled */
		default:
			$v = dbe2jsn($v);
			break;
		}
		switch ((int)$efarr[$k]['field_type']) {
		/* integers */
		case 10:	/* ARTIFACT_EXTRAFIELDTYPE_INTEGER */
			$res = (int)$v;
			break;

		/* list values */
		case 1:		/* ARTIFACT_EXTRAFIELDTYPE_SELECT */
		case 3:		/* ARTIFACT_EXTRAFIELDTYPE_RADIO */
		case 7:		/* ARTIFACT_EXTRAFIELDTYPE_STATUS */
			$res = $efval[$k][(int)$v];
			break;

		/* arrays of list values */
		case 2:		/* ARTIFACT_EXTRAFIELDTYPE_CHECKBOX */
		case 5:		/* ARTIFACT_EXTRAFIELDTYPE_MULTISELECT */
			$res = array();
			foreach ($v as $fv) {
				$res[] = $efval[$k][(int)$fv];
			}
			break;

		/* special handling */
		case 9:		/* ARTIFACT_EXTRAFIELDTYPE_RELATION */
			$res = array();
			foreach (preg_split("/\D+/", $v) as $fv) {
				if (!util_nat0($fv)) {
					continue;
				}
				$res[] = (int)$fv;
			}
			break;

		/* strings */
		case 4:		/* ARTIFACT_EXTRAFIELDTYPE_TEXT */
		case 6:		/* ARTIFACT_EXTRAFIELDTYPE_TEXTAREA */
		/* unknown */
		case 8:		/* ARTIFACT_EXTRAFIELDTYPE_ASSIGNEE */
		default:
			$res = $v;
			break;
		}
		$v = array(
			'type' => $efmap[$k]['type'],
			'value' => $res,
		    );
		if ($efmap[$k]['alias']) {
			$v['alias'] = $efmap[$k]['alias'];
		}
		$rec['~extrafields'][$efmap[$k]['name']] = $v;
	}

	/*
	 * here would be the place to add more pseudo-elements, like
	 * a _files Value…
	 */

	/* append to list of records to emit */
	$out[$aid] = $rec;
}

/* generate output */
echo minijson_encode($out) . "\n";
