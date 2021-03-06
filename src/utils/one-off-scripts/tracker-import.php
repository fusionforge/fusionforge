<?php
/*-
 * one-off script to import tracker items (limited)
 *
 * Copyright © 2012, 2013, 2014
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
 * Edit below; comments inline.  Imports a JSON generated by tracker-export
 * into a tracker; I believe that all fields are imported, except assignee,
 * which is by design; missing data is recorded still.
 *
 * Note: this script may require editing to adjust to the instance
 * and version of the forge used, e.g. it uses a form of ->addHistory()
 * whose third argument is $new_value which is not in FusionForge 5.3.
 */

require `forge_get_config source_path`.'/common/include/env.inc.php';
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

/* compat */
/*XXX what about aljeux’ ARTIFACT_EXTRAFIELDTYPE_FORMULA ? */
if (!defined('ARTIFACT_EXTRAFIELDTYPE_DATETIME'))
	define('ARTIFACT_EXTRAFIELDTYPE_DATETIME', 12);

function usage($rc=1) {
	echo "E: Usage: .../tracker-import.php 123 <t_123.json\n" .
	    "N: where 123 is the group_artifact_id of the tracker to append to\n";
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

/* read input and ensure it’s a JSON Array or Object */
$iv = false;
if (!minijson_decode(file_get_contents('php://stdin'), $iv)) {
	echo "E: input is invalid JSON: $iv\n";
	die;
}
if (!is_array($iv)) {
	echo "E: input top-level element is not an Array or Object\n";
	die;
}

/* validate input elements */
define('IT_STR', 1);
define('IT_LST', 2);	// list (Array or Object) of many
define('IT_ARR', 3);	// Object with key/value check
define('IT_NAT', 4);	// ∈ ℕ₀
define('IT_ANY', 5);	// even NULL
define('IP_REQ', 1);
define('IP_OPT', 2);
define('ICK_NO', 1);	// unchecked
define('ICK_SC', 2);	// schema check (only IT_LST[*] and IT_ARR)
define('ICK_FN', 3);	// function (false=bad, true=ok, array=rpl)

/* ICK_FN called with (parent, fieldname, value, IT_*, IP_*); */
/* IT_LST calls ICK_SC, ICK_FN once per entry */

/*
 * These are the fields we require in each entry. Note how we only
 * list those we actually import; so, if you change the below code
 * to import more, list them here, too.
 */
$schema_backlinks = array(
	array("field",			IT_STR, IP_REQ, ICK_NO, 0),
	array("group",			IT_STR, IP_REQ, ICK_NO, 0),
	array("item",			IT_NAT, IP_REQ, ICK_NO, 0),
	array("tracker",		IT_STR, IP_REQ, ICK_NO, 0),
    );
$schema_changelog = array(
	array("by",			IT_STR, IP_REQ, ICK_NO, 0),
	array("entrydate",		IT_NAT, IP_REQ, ICK_NO, 0),
	array("field_name",		IT_STR, IP_REQ, ICK_NO, 0),
	array("new_value",		IT_STR, IP_OPT, ICK_NO, 0),
	array("old_value",		IT_STR, IP_REQ, ICK_NO, 0),
    );
$schema_comments = array(
	array("adddate",		IT_NAT, IP_REQ, ICK_NO, 0),
	array("body",			IT_STR, IP_REQ, ICK_NO, 0),
	array("from_email",		IT_STR, IP_REQ, ICK_NO, 0),
	array("from_user",		IT_STR, IP_OPT, ICK_NO, 0),
    );
$schema_extrafields = array(
	array("alias",			IT_STR, IP_OPT, ICK_NO, 0),
	array("type",			IT_NAT, IP_REQ, ICK_NO, 0),
	array("value",			IT_ANY, IP_REQ, ICK_NO, 0),
    );
$schema_files = array(
	array("adddate",		IT_NAT, IP_REQ, ICK_NO, 0),
	array("base64_data",		IT_STR, IP_REQ, ICK_NO, 0),
	array("description",		IT_STR, IP_OPT, ICK_NO, 0),
	array("filename",		IT_STR, IP_REQ, ICK_NO, 0),
	array("submitter",		IT_STR, IP_OPT, ICK_NO, 0),
    );
$schema_votes = array(
	array("votage_percent",		IT_NAT, IP_REQ, ICK_NO, 0),
	array("voters",			IT_NAT, IP_REQ, ICK_NO, 0),
	array("votes",			IT_NAT, IP_REQ, ICK_NO, 0),
    );
$schema_item = array(
	array("_rpl_itempermalink",	IT_STR, IP_REQ, ICK_NO, 0),
	array("_rpl_taskpermalink",	IT_STR, IP_OPT, ICK_NO, 0),
	array("_votes",			IT_ARR, IP_OPT, ICK_SC, $schema_votes),
	array("assigned_email",		IT_STR, IP_OPT, ICK_NO, 0),
	array("assigned_realname",	IT_STR, IP_OPT, ICK_NO, 0),
	array("assigned_to",		IT_NAT, IP_OPT, ICK_NO, 0),
	array("assigned_unixname",	IT_STR, IP_OPT, ICK_NO, 0),
	array("close_date",		IT_NAT, IP_OPT, ICK_NO, 0),//…
	array("details",		IT_STR, IP_REQ, ICK_NO, 0),
	array("last_modified_date",	IT_NAT, IP_REQ, ICK_NO, 0),
	array("open_date",		IT_NAT, IP_REQ, ICK_NO, 0),
	array("priority",		IT_NAT, IP_REQ, ICK_NO, 0),
	array("status_id",		IT_NAT, IP_OPT, ICK_NO, 0),//…
	array("status_name",		IT_STR, IP_OPT, ICK_NO, 0),//…
	array("submitted_by",		IT_NAT, IP_REQ, ICK_NO, 0),
	array("submitted_email",	IT_STR, IP_OPT, ICK_NO, 0),
	array("submitted_realname",	IT_STR, IP_OPT, ICK_NO, 0),
	array("submitted_unixname",	IT_STR, IP_REQ, ICK_NO, 0),
	array("summary",		IT_STR, IP_REQ, ICK_NO, 0),
	array("~backlinks",		IT_LST, IP_OPT, ICK_SC, $schema_backlinks),
	array("~changelog",		IT_LST, IP_OPT, ICK_SC, $schema_changelog),
	array("~comments",		IT_LST, IP_OPT, ICK_SC, $schema_comments),
	array("~extrafields",		IT_LST, IP_OPT, ICK_SC, $schema_extrafields),
	array("~files",			IT_LST, IP_OPT, ICK_SC, $schema_files),
	array("~related_tasks",		IT_LST, IP_OPT, ICK_FN, 'schema_ick_nat'),
    );

function jsn_check_one($v, $schema, &$errstr, $nested) {
	if (!is_array($v)) {
		$s = "not an Object";
		$in = "";
 jsn_check_false:
		if (!$errstr) {
			/* do not overwrite previous one */
			$errstr = "item " . $nested . " " . ($in ? (
			    ($ip == IP_REQ ? "required" : "optional") .
			    " field " . $in . " ") : "") . "is " . $s;
		}
		return false;
	}
	$rv = array();
	foreach ($schema as $rule) {
		list($in, $it, $ip, $ick, $icfn) = $rule;
		if (!array_key_exists($in, $v)) {
			if ($ip == IP_REQ) {
				$s = "missing";
				goto jsn_check_false;
			}
			/* optional field */
			continue;
		}
		if (($v[$in] === NULL) && ($it != IT_ANY)) {
			$s = "NULL";
			goto jsn_check_move;
		}
		$vv = $v[$in];
		switch ($it) {
		case IT_STR:
			if (is_array($vv)) {
				$s = "not a scalar";
				goto jsn_check_move;
			}
			$vv = "" . $vv;
			break;
		case IT_LST:
			/*
			 * without ICK_SC this can also be an
			 * array of e.g. strings, so we do not
			 * check for array of arrays
			 */
		case IT_ARR:
			/* no other checks if ICK_NO */
			if (!is_array($vv)) {
				$s = "not an array";
				goto jsn_check_move;
			}
			break;
		case IT_NAT:
			if (is_array($vv)) {
				$s = "not a scalar";
				goto jsn_check_move;
			}
			$vv = "" . $vv;
			if (($tmp = util_nat0($vv)) === false) {
				$s = "not a positive-or-zero integer";
				goto jsn_check_move;
			}
			$vv = $tmp;
			break;
		case IT_ANY:
			break;
		default:
			/* someone made a boo-boo editing this script */
			echo "E: internal error: unknown type " .
			    $it . " for " . $nested . "." . $in . "\n";
			die;
		}
		switch ($ick) {
		case ICK_NO:
			$ickres = true;
			break;
		case ICK_SC:
			$s = "failing schema check";
			switch ($it) {
			case IT_ARR:
				$ickres = jsn_check_one($vv, $icfn,
				    $errstr, $nested . "." . $in);
				break;
			case IT_LST:
				$ickres = array();
				foreach ($vv as $ick_k => $ick_v) {
					$icktmp = jsn_check_one($ick_v,
					    $icfn, $errstr, $nested . "." .
					    $in . "[" . $ick_k . "]");
					if ($icktmp === false) {
						$ickres = false;
						break;
					}
					$ickres[$ick_k] =
					    ($icktmp === true) ?
					    $ick_v : $icktmp;
				}
				break;
			default:
				echo "E: internal error: ICK_SC type " .
				    $it . " for " . $nested . "." . $in . "\n";
				die;
			}
			break;
		case ICK_FN:
			$s = "failing user function check";
			switch ($it) {
			case IT_ARR:
				$ickres = call_user_func($icfn, $nested,
				    $in, $vv, $it, $ip);
				break;
			case IT_LST:
				$ickres = array();
				foreach ($vv as $ick_k => $ick_v) {
					$icktmp = call_user_func($icfn,
					    $nested, $in . "[" . $ick_k . "]",
					    $ick_v, $it, $ip);
					if ($icktmp === false) {
						$ickres = false;
						break;
					}
					$ickres[$ick_k] =
					    ($icktmp === true) ?
					    $ick_v : $icktmp;
				}
				break;
			default:
				echo "E: internal error: ICK_SC type " .
				    $it . " for " . $nested . "." . $in . "\n";
				die;
			}
			break;
		default:
			/* someone made a boo-boo editing this script */
			echo "E: internal error: unknown check " .
			    $ick . " for " . $nested . "." . $in . "\n";
			die;
		}
		if ($ickres === false) {
			/* $s set in the switch immediately above */
 jsn_check_move:
			if ($ip == IP_REQ)
				goto jsn_check_false;
			if (!$errstr) {
				/* do not overwrite previous one */
				$errstr = "item " . $nested . " " . ($in ? (
				    ($ip == IP_REQ ? "required" : "optional") .
				    " field " . $in . " ") : "") . "is " . $s;
			}
			echo "W: $errstr\n";
			$errstr = "";
		} else
			$rv[$in] = ($ickres === true) ? $vv : $ickres;
	}
	$em = array();
	foreach ($v as $vk => $vv) {
		if (!array_key_exists($vk, $rv)) {
			/* not yet seen => unknown */
			$em[$vk] = $vv;
		}
	}
	if ($em)
		$rv['~~not-in-schema'] = $em;
	return $rv;
}

function jsn_check($arr, $schema, &$errstr, $nested="") {
	$rv = array();
	if ($nested)
		$nested .= ".";
	foreach ($arr as $k => $v) {
		if (($rv[$k] = jsn_check_one($v, $schema, $errstr,
		    $nested . $k)) === false)
			return false;
	}
	return $rv;
}

function schema_ick_nat($nested, $in, $vv, $it, $ip) {
	return util_nat0($vv);
}

$ic = count($iv);
echo "I: $ic tracker items to consider\n";

$xs = "";
$tmp = jsn_check($iv, $schema_item, $xs);
if ($xs) {
	echo "E: $xs\n";
	die;
}
$iv = $tmp;
unset($tmp);
$ic = count($iv);
echo "I: $ic items are syntactically ok\n";

/* begin the import for sure */

session_set_admin();
$now = time();

/* get the Tracker */
$at =& artifactType_get_object($trk);
if (!$at || !is_object($at) || $at->isError()) {
	echo "E: cannot get tracker object\n";
	die;
}

$eflist = $at->getExtraFields();
$efnames = array();
$efaliases = array();
foreach ($eflist as $ef) {
	$efid = (int)$ef["extra_field_id"];
	$efnames[$ef["field_name"]] = $efid;
	if (util_ifsetor($ef["alias"]))
		$efaliases[preg_replace('/^@/', '', $ef["alias"])] = $efid;
	$efelems[$efid] = array();
	foreach ($at->getExtraFieldElements($efid) as $efelem) {
		$efelems[$efid][$efelem["element_name"]] = $efelem;
	}
}
$efnames_lo = array_change_key_case($efnames);
$efaliases_lo = array_change_key_case($efaliases);

/* absolute minimum needed for creating tracker items in $at */
$extra_fields = array();
if ($at->usesCustomStatuses()) {
	$i = $at->getCustomStatusField();
	$res = db_query_params('SELECT element_id
		FROM artifact_extra_field_elements
		WHERE extra_field_id=$1
		ORDER BY element_pos ASC, element_id ASC
		LIMIT 1 OFFSET 0',
	    array($i));
	$extra_fields[$i] = db_result($res, 0, 'element_id');
	$cselems_lo = array_change_key_case($efelems[$efid]);
}

/* count all items, for relation fields */
$all_items = array();
foreach ($iv as $k => $v) {
	$all_items[(int)$k] = true;
}
$tbd_links = array();

/* now import the items, one by one */

$j = 0;
db_begin();
foreach ($iv as $k => $v) {
	echo "I: importing $k (" . ++$j . "/$ic)\n";
	$importData = array();
	$missingData = array();
	$tbd_thislinks = array();
	$new_extra_fields = $extra_fields;
	if (util_ifsetor($v["~~not-in-schema"]))
		$missingData['unrecognised JSON slots'] = $v["~~not-in-schema"];
	if (isset($v["_votes"]) && ($v["_votes"]["votes"] ||
	    $v["_votes"]["voters"] || $v["_votes"]["votage_percent"]))
		$missingData['votes'] = $v["_votes"]["votes"] . "/" .
		    $v["_votes"]["voters"] . " (" .
		    $v["_votes"]["votage_percent"] . "%)";
	if ((isset($v["assigned_email"]) || isset($v["assigned_realname"]) ||
	    isset($v["assigned_to"]) || isset($v["assigned_unixname"])) &&
	    (!isset($v["assigned_to"]) || ($v["assigned_to"] != 100)) &&
	    util_ifsetor($v["assigned_realname"]) != "Nobody" &&
	    util_ifsetor($v["assigned_unixname"]) != "None") {
		$missingData['assignee'] = array();
		if (isset($v["assigned_email"]))
			$missingData['assignee']['email'] = $v["assigned_email"];
		if (isset($v["assigned_realname"]))
			$missingData['assignee']['realname'] = $v["assigned_realname"];
		if (isset($v["assigned_to"]))
			$missingData['assignee']['uid'] = $v["assigned_to"];
		if (isset($v["assigned_unixname"]))
			$missingData['assignee']['unixname'] = $v["assigned_unixname"];
	}
	$missingData['status'] = array();
	if (util_ifsetor($v["close_date"]))
		$missingData['status']['close date'] = $v["close_date"];
	if (util_ifsetor($v["status_id"]) || util_ifsetor($v["status_name"])) {
		$missingData['status']['forge'] = array();
		if (util_ifsetor($v["status_id"]))
			$missingData['status']['forge']['mapping'] =
			    $v["status_id"];
		if (util_ifsetor($v["status_name"]))
			$missingData['status']['forge']['status'] =
			    $v["status_name"];
	}
	if (util_ifsetor($v["~extrafields"])) {
		$efx = $v["~extrafields"];
		/* search for custom status */
		$fe = false;
		foreach ($efx as $fn => $tmp) {
			if (util_ifsetor($tmp["value"]) &&
			    ($tmp["type"] == ARTIFACT_EXTRAFIELDTYPE_STATUS)) {
				if ($fe === false) {
					$fe = $fn;
				} else {
					/* multiple status fields */
					$fe = false;
					$missingData['status']['error'] =
					    'multiple status fields found';
					break;
				}
			}
		}
		if ($fe) {
			$tmp = $efx[$fe];
			$missingData['status']['user'] = array(
				'field' => $fe,
				'status' => $tmp["value"],
			    );
			if (util_ifsetor($tmp["alias"]))
				$missingData['status']['user']['alias'] =
				    $tmp["alias"];
			unset($efx[$fe]);
		}
		/* try to mix and match */
		$efkeys = array_keys($efx);
		$efdone = array();
		$efused = array();
		/* 0. remove all entries with value NULL */
		foreach ($efkeys as $efkey) {
			if ($efx[$efkey]["value"] === NULL)
				unset($efx[$efkey]);
		}
		$efkeys = array_keys($efx);
		/* 1. case-sensitive name match */
		foreach ($efkeys as $efkey) {
			if (isset($efnames[$efkey])) {
				$efdone[$efkey] = $efnames[$efkey];
				$efused[$efnames[$efkey]] = true;
			}
		}
		/* 2. case-sensitive alias match */
		foreach ($efkeys as $efkey) {
			if (isset($efdone[$efkey]))
				continue;
			$efalias = preg_replace('/^@/', '',
			    util_ifsetor($efx[$efkey]["alias"], ''));
			if (!$efalias)
				continue;
			if (isset($efaliases[$efalias])) {
				$efid = $efaliases[$efalias];
			} else
				continue;
			if (!isset($efused[$efid])) {
				$efdone[$efkey] = $efid;
				$efused[$efid] = true;
			}
		}
		/* 3. case-insensitive name/alias mix-match */
		foreach ($efkeys as $efkey) {
			if (isset($efdone[$efkey]))
				continue;
			$efalias = strtolower(preg_replace('/^@/', '',
			    util_ifsetor($efx[$efkey]["alias"], '')));
			if (isset($efnames_lo[strtolower($efkey)])) {
				$efid = $efnames_lo[strtolower($efkey)];
			} elseif (isset($efaliases_lo[strtolower($efkey)])) {
				$efid = $efaliases_lo[strtolower($efkey)];
			} elseif ($efalias && isset($efnames_lo[$efalias])) {
				$efid = $efnames_lo[$efalias];
			} elseif ($efalias && isset($efaliases_lo[$efalias])) {
				$efid = $efaliases_lo[$efalias];
			} else
				continue;
			if (!isset($efused[$efid])) {
				$efdone[$efkey] = $efid;
				$efused[$efid] = true;
			}
		}
		/* 4. process all found ones */
		foreach ($efdone as $efkey => $efid) {
			$ef = $efx[$efkey];
			$value = $ef["value"];
			/* more or less same as Artifact::update() */
			/* except we decide by target field type */
			$type = (int)$eflist[$efid]["field_type"];
			if ($ef["type"] == ARTIFACT_EXTRAFIELDTYPE_STATUS ||
			    $type == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
				/* handled at some other place in the code */
 lose_extrafield:
				continue;
			}
			$was_array = true;
			if ($type == ARTIFACT_EXTRAFIELDTYPE_TEXT ||
			    $type == ARTIFACT_EXTRAFIELDTYPE_INTEGER ||
			    $type == ARTIFACT_EXTRAFIELDTYPE_DATETIME ||
			    $type == ARTIFACT_EXTRAFIELDTYPE_TEXTAREA) {
				if (is_array($value)) {
					/* eh now what? */
					$value = implode(",", $value);
				}
				/* straight value copy */
				goto found_extrafield;
			} elseif ($type == ARTIFACT_EXTRAFIELDTYPE_RELATION) {
				if (is_array($value))
					$value = implode(" ", $value);
				$value = preg_replace('/\[\#(\d+)\]/', "\\1",
				    trim($value));
				$value = preg_replace('/\\s+/', ' ', $value);
				$value = explode(' ', $value);
				foreach ($value as $tv) {
					if (!preg_match('/^(\d+)$/', $tv))
						/* invalid data */
						goto lose_extrafield;
					if (!isset($all_items[(int)$tv]))
						/* not imported */
						goto lose_extrafield;
				}
				/* fill in later */
				$tbd_thislinks[$efid] = $value;
				$value = '';
				goto found_extrafield;
			} elseif (!is_array($value)) {
				$was_array = false;
				$value = array($value);
			}
			$nv = array();
			foreach ($value as $tv) {
				if (!isset($efelems[$efid][$tv])) {
					/* value not found, lose import */
					goto lose_extrafield;
				}
				$nv[] = (int)$efelems[$efid][$tv]["element_id"];
			}
			$value = $was_array ? $nv : $nv[0];
 found_extrafield:
			/* import and remove not-imported mark */
			$new_extra_fields[$efid] = $value;
			unset($efx[$efkey]);
		}
		/* assign rest to unimported data, if any */
		if ($efx)
			$missingData['extrafields'] = $efx;
	}
	$didstatus = false;
	$do_status = false;
	$do_c_time = false;
	if ($at->usesCustomStatuses() &&
	    isset($missingData['status']['user'])) {
		/* custom status fields in both src and dst, try to match */
		$efid = $at->getCustomStatusField();
		$value = $missingData['status']['user']['status'];
		$i = util_ifsetor($missingData['status']['forge']['mapping'],
		    /* 0 is not used, only 1|2|3 */ 0);
		if (isset($efelems[$efid][$value])) {
			/* same element name */
			$value = $efelems[$efid][$value];
		} elseif (isset($cselems_lo[strtolower($value)])) {
			$value = $cselems_lo[strtolower($value)];
		} else
			goto status_no_extrafield_mapping;
		if ($i && ($i != $value["status_id"]))
			/* but not same status mapping */
			goto status_no_extrafield_mapping;
		/* mapping is imported */
		$didstatus = $value["status_id"];
		$new_extra_fields[$efid] = $value["element_id"];
		unset($missingData['status']['user']['status']);
		/* check names; if they match, everything is imported */
		$xa = $eflist[$efid]["field_name"];
		$xb = preg_replace('/^@/', '',
		    util_ifsetor($eflist[$efid]["alias"], ""));
		$xc = $missingData['status']['user']['field'];
		$xd = preg_replace('/^@/', '',
		    util_ifsetor($missingData['status']['user']['alias'], ""));
		if (!strcasecmp($xa, $xc) ||
		    ($xb && !strcasecmp($xb, $xc)) ||
		    ($xd && !strcasecmp($xa, $xd)) ||
		    ($xb && $xd && !strcasecmp($xb, $xd)))
			unset($missingData['status']['user']);
		goto status_mapping_done;
	}
 status_no_extrafield_mapping:
	/* try to map status to extrafield */
	if ($at->usesCustomStatuses() &&
	    isset($missingData['status']['forge']) &&
	    util_ifsetor($missingData['status']['forge']['status']) &&
	    isset($cselems_lo[($value = strtolower($missingData['status']['forge']['status']))]) &&
	    (!util_ifsetor($missingData['status']['forge']['mapping'], 0) ||
	    $missingData['status']['forge']['mapping'] == $cselems_lo[$value]["status_id"])) {
		$didstatus = $cselems_lo[$value]["status_id"];
		$new_extra_fields[$at->getCustomStatusField()] =
		    $cselems_lo[$value]["element_id"];
		unset($missingData['status']['forge']);
		goto status_mapping_done;
	}
	/* try to map status code to any extrafield value matching it */
	if ($at->usesCustomStatuses() &&
	    isset($missingData['status']['forge']) &&
	    util_ifsetor($missingData['status']['forge']['mapping'], 0)) {
		$i = $at->getCustomStatusField();
		$tmp = (int)$missingData['status']['forge']['mapping'];
		$res = db_query_params('SELECT element_id
			FROM artifact_extra_field_elements
			WHERE extra_field_id=$1
			    AND status_id=$2
			ORDER BY element_pos ASC, element_id ASC
			LIMIT 1 OFFSET 0',
		    array($i, $tmp));
		if ($res && db_numrows($res) > 0) {
			$didstatus = (int)db_result($res, 0, 'element_id');
			$new_extra_fields[$i] = $didstatus;
			goto status_mapping_done;
		}
	}
	if (!$at->usesCustomStatuses() &&
	    isset($missingData['status']['forge']) &&
	    util_ifsetor($missingData['status']['forge']['mapping'], 0)) {
		$do_status = (int)$missingData['status']['forge']['mapping'];
		$didstatus = $do_status;
		goto status_mapping_done;
	}
 status_mapping_done:
	/* if we have a mapping… */
	if ($didstatus !== false) {
		/* figure out if it’s known, and its name */
		$res = db_query_params('SELECT status_name
			FROM artifact_status
			WHERE id=$1
			LIMIT 1 OFFSET 0',
		    array($didstatus));
		/* if so… */
		if ($res && db_numrows($res) > 0) {
			/* compare with the mapping in the JSON */
			$i = util_ifsetor($missingData['status']['forge']['mapping'], 0);
			if ($i && ($i == $didstatus))
				unset($missingData['status']['forge']['mapping']);
			/* compare with the name in the JSON */
			$i = db_result($res, 0, 'status_name');
			if ($i && isset($missingData['status']['forge']) &&
			    isset($missingData['status']['forge']['status']) &&
			    !strcasecmp($i,
			    $missingData['status']['forge']['status'])) {
				/* same name, unset */
				unset($missingData['status']['forge']['status']);
			}
			/* check for merging closed date on !Open items */
			if ($didstatus != 1) {
				/* not Open => Closed or Deleted, most likely */
				if (isset($missingData['status']['close date'])) {
					$do_status = $didstatus;
					$do_c_time = $missingData['status']['close date'];
					unset($missingData['status']['close date']);
				}
			}
		}
	}
	foreach (array('forge', 'user') as $i)
		if (isset($missingData['status'][$i]) &&
		    !$missingData['status'][$i])
			unset($missingData['status'][$i]);
	if (!$missingData['status'])
		unset($missingData['status']);
	$x_rpl_taskpermalink = util_ifsetor($v["_rpl_taskpermalink"]);
	$x_rpl_taskpermalink = $x_rpl_taskpermalink ? : '#';

	/* get all standard data fields (we use) */

	$summary = $v["summary"];
	$details = $v["details"];
	/* assign to Nobody by default */
	$assigned_to = 100;
	$priority = $v["priority"];
	$importData['time'] = (int)$v["open_date"];

	/* take over the submitter, but only if they exist */
	if ($v["submitted_by"] != 100 && ($submitter =
	    user_get_object_by_name($v["submitted_unixname"])) &&
	    is_object($submitter) && !($submitter->isError())) {
		/* map the unixname of the submitter to our local user */
		$importData['user'] = $submitter->getID();
	} elseif (($submitter =
	    user_get_object_by_email($v["submitted_email"])) &&
	    is_object($submitter) && !($submitter->isError())) {
		/* map the eMail address of the submitter to our local user */
		$importData['user'] = $submitter->getID();
	} else {
		$submitter = false;
		/* submitted by Nobody */
		$importData['user'] = 100;
	}
	/* store away original data of submitter */
	$missingData['submitter'] = array(
		'uid' => $v["submitted_by"],
		'email' => $v["submitted_email"],
		'realname' => $v["submitted_realname"],
		'unixname' => $v["submitted_unixname"],
	    );
	if ($submitter !== false) {
		/* compare original and new data */
		if ($missingData['submitter']['uid'] == $submitter->getID())
			unset($missingData['submitter']['uid']);
		if ($missingData['submitter']['email'] == $submitter->getEmail())
			unset($missingData['submitter']['email']);
		if ($missingData['submitter']['realname'] == $submitter->getRealName())
			unset($missingData['submitter']['realname']);
		if ($missingData['submitter']['unixname'] == $submitter->getUnixName())
			unset($missingData['submitter']['unixname']);
		/* all equal? */
		if (!$missingData['submitter'])
			unset($missingData['submitter']);
	}

	/* prepend the old permalink in front of the details */
	$old_permalink = str_replace('#', sprintf('%d', $k),
	    $v["_rpl_itempermalink"]);
	$details = "Imported from: " . $old_permalink . "\n\n" . $details;

	/* instantiate a new item */
	$ah = new Artifact($at);
	if (!$ah || !is_object($ah) || $ah->isError()) {
		echo "E: cannot get the object\n";
		db_rollback();
		die;
	}

	/* actually create the item */
	if (!$ah->create($summary, $details, $assigned_to, $priority,
	    $new_extra_fields, $importData)) {
		echo "E: cannot import: " . $ah->getErrorMessage() . "\n";
		db_rollback();
		die;
	}
	if (($do_status !== false) && !$ah->setStatus($do_status, $do_c_time)) {
		echo "E: cannot set status: " . $ah->getErrorMessage() . "\n";
		db_rollback();
		die;
	}

	/* import comments */
	if (util_ifsetor($v["~comments"])) {
		foreach ($v["~comments"] as $tmp) {
			$fe = $tmp["from_email"];
			$fu = util_ifsetor($tmp["from_user"], "");
			if ($fu && ($tu = user_get_object_by_name($fu)) &&
			    is_object($tu) && !($tu->isError())) {
				/* nothing */;
			} elseif (($tu =
			    user_get_object_by_email($fe)) &&
			    is_object($tu) && !($tu->isError())) {
				/* nothing */;
			} else
				$tu = false;
			if ($tu) {
				if ($tu->getEmail() == $fe)
					$fe = "";
				if ($tu->getUnixName() == $fu)
					$fu = "";
				$fi = $tu->getID();
			} else
				$fi = 100;
			if ($fe || $fu) {
				$fb = "Originally submitted by ";
				if ($fu)
					$fb .= $fu . " ";
				if ($fe)
					$fb .= "<" . $fe . ">";
				$fb .= "\n\n";
			} else
				$fb = "";
			if (!db_query_params('INSERT INTO artifact_message
				(artifact_id,submitted_by,from_email,adddate,body)
				VALUES ($1,$2,$3,$4,$5)',
			    array(
				$ah->getID(),
				$fi,
				($tu ? $tu->getEmail() : $tmp["from_email"]),
				$tmp["adddate"],
				htmlspecialchars($fb . $tmp["body"]),
			    ))) {
				echo "E: cannot add comment: " .
				    db_error() . "\n";
				db_rollback();
				die;
			}
		}
	}

	/* import changelogs */
	if (util_ifsetor($v["~changelog"])) {
		foreach ($v["~changelog"] as $tmp) {
			$importData = array();
			$importData['time'] = (int)$tmp["entrydate"];
			if (($tu = user_get_object_by_name($tmp["by"])) &&
			    is_object($tu) && !($tu->isError())) {
				$importData['user'] = $tu->getID();
			} else {
				$importData['user'] = 100;
			}
			if (!$ah->addHistory($tmp["field_name"],
			    $tmp["old_value"],
			    util_ifsetor($tmp["new_value"], ""),
			    $importData)) {
				echo "E: cannot add history entry: " .
				    db_error() . "\n";
				db_rollback();
				die;
			}
		}
	}

	/* import files */
	if (util_ifsetor($v["~files"])) {
		$missingData['lost files'] = array();
		foreach ($v["~files"] as $tmp) {
			$importData = array();
			$importData['time'] = (int)$tmp["adddate"];
			$importData['user'] = 100;
			$fe = util_ifsetor($tmp["description"], 'None');
			$fu = util_ifsetor($tmp["submitter"], 100);
			if ($fu != 100 && strcasecmp($fu, "nobody")) {
				if (($tu = user_get_object_by_name($fu)) &&
				    is_object($tu) && !($tu->isError())) {
					$importData['user'] = $tu->getID();
				} else {
					$fe = "(by " . $fu . ") " . $fe;
				}
			}
			$fb = base64_decode($tmp["base64_data"]);
			if ($fb === false) {
				$missingData['lost files'][] = $tmp;
				continue;
			}
			$fi = new ArtifactFile($ah);
			if (!$fi || !is_object($fi) || $fi->isError()) {
				echo "E: cannot get the file object\n";
				db_rollback();
				die;
			}
			if (!$fi->create($tmp["filename"],
			    'application/octet-stream',
			    strlen($fb), $fb, $fe, $importData)) {
				echo "E: cannot create the file: " .
				    $fi->getErrorMessage() . "\n";
				db_rollback();
				die;
			}
		}
		if (!$missingData['lost files'])
			unset($missingData['lost files']);
	}

	/* import backlinks (from dst only; mixed are handled in tbd_links) */
	if (util_ifsetor($v["~backlinks"])) {
		/* put into arrays */
		$fi = array();
		foreach ($v["~backlinks"] as $tmp) {
			$fg = $tmp["group"];
			$ft = $tmp["tracker"];
			$ff = $tmp["field"];

			if (!isset($fi[$fg]))
				$fi[$fg] = array();
			if (!isset($fi[$fg][$ft]))
				$fi[$fg][$ft] = array();
			if (!isset($fi[$fg][$ft][$ff]))
				$fi[$fg][$ft][$ff] = array();
			$fi[$fg][$ft][$ff][$tmp["item"]] = true;
		}
		/* put into order */
		$fu = array();
		$kg = array_keys($fi);
		natcasesort($kg);
		foreach ($kg as $ig) {
			$kt = array_keys($fi[$ig]);
			natcasesort($kt);
			foreach ($kt as $it) {
				$kf = array_keys($fi[$ig][$it]);
				natcasesort($kf);
				foreach ($kf as $if) {
					$ki = array_keys($fi[$ig][$it][$if]);
					natcasesort($ki);
					foreach ($ki as $ii) {
						$fu[] = array($ig, $it, $if, $ii);
					}
				}
			}
		}
		/* put into text */
		$fb = "Backlinks:\n";
		$last_gn = false;
		$last_tn = false;
		$last_fn = false;
		foreach ($fu as $fi) {
			list($ig, $it, $if, $ii) = $fi;
			if ($ig !== $last_gn || $it !== $last_tn ||
			    $if !== $last_fn) {
				$fb .= "\n$ig: $it <<<i:(Relation: $if)>>>";
				$last_gn = $ig;
				$last_tn = $it;
				$last_fn = $if;
			}
			$fb .= "\n" . str_replace('#', sprintf('%d', $ii),
			    $v["_rpl_itempermalink"]);
		}
		if (!$ah->addMessage($fb)) {
			echo "E: cannot add backrel: " .
			    $ah->getErrorMessage() .
			    " / " . db_error() . "\n";
			db_rollback();
			die;
		}
	}

	/* import task relationships */
	if (util_ifsetor($v["~related_tasks"])) {
		$fb = "Task relationships:\n";
		foreach ($v["~related_tasks"] as $tmp) {
			$fb .= "\n" . str_replace('#', sprintf('%d', $tmp),
			    $x_rpl_taskpermalink);
		}
		if (!$ah->addMessage($fb)) {
			echo "E: cannot add taskrel: " .
			    $ah->getErrorMessage() .
			    " / " . db_error() . "\n";
			db_rollback();
			die;
		}
	}

	/* note import fallout */
	if ($missingData && !($ah->addMessage('"Lost data importing from ' .
	    $old_permalink . "\" =\t" . minijson_encode($missingData)))) {
		echo "E: cannot add message: " . $ah->getErrorMessage() .
		    " / " . db_error() . "\n";
		db_rollback();
		die;
	}

	/* log the import action */
	if (!$ah->addHistory("-last-modified-then-import",
	    date('Y-m-d H:i:s', $v["last_modified_date"]),
	    date('Y-m-d H:i:s', $now))) {
		echo "E: cannot seal history entry: " . db_error() . "\n";
		db_rollback();
		die;
	}
	if (!db_query_params('UPDATE artifact
		SET last_modified_date=$2
		WHERE artifact_id=$1',
	    array(
		$ah->getID(),
		$now,
	    ))) {
		echo "E: cannot seal entry mtime: " . db_error() . "\n";
		db_rollback();
		die;
	}
	$all_items[(int)$k] = $ah->getID();
	if ($tbd_thislinks)
		$tbd_links[$ah->getID()] = $tbd_thislinks;
	echo "D: imported $k as " . $ah->getID() . "\n";
}
if ($tbd_links) {
	echo "I: importing relationships between items...\n";
	foreach ($tbd_links as $aid => $fields) {
		foreach ($fields as $efid => $values) {
			$efd = array();
			foreach ($values as $v) {
				$efd[] = $all_items[$v];
			}
			$nfd = implode(" ", $efd);
			echo "D: #$aid $efid (" . implode(" ", $values) .
			    ") => ($nfd)\n";
			$res = db_query_params('DELETE FROM artifact_extra_field_data
				WHERE artifact_id=$1 AND extra_field_id=$2',
			    array($aid, $efid));
			if (!$res) {
				echo "E: could not delete data: " . db_error() . "\n";
				db_rollback();
				die;
			}
			$res = db_query_params('INSERT INTO artifact_extra_field_data
				(artifact_id,extra_field_id,field_data)
				VALUES ($1,$2,$3)',
			    array($aid, $efid, htmlspecialchars($nfd)));
			if (!$res) {
				echo "E: could not write data: " . db_error() . "\n";
				db_rollback();
				die;
			}
		}
	}
}
db_commit();
echo "I: done\n";
