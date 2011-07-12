<?php
///usr/bin/php -q -d include_path=.:/etc/gforge:/usr/share/gforge:/usr/share/gforge/www/include check_and_update_forum_role_settings.php
// Script for add role settings for existing forums and remove role settings for deleted forums

require_once 'www/env.inc.php';
require_once $gfwww.'include/squal_pre.php';

$error = "";
$groups_res = db_query_params ('SELECT group_id FROM groups',
						       array ()) ;
if (!$groups_res) {
	$error .= 'Error: Enable to get groups : ' .db_error();
	return false;
}

db_begin();

// for each group
for ($g=0; $g<db_numrows($groups_res); $g++) {
	// select of all group's forums
	$forums_group_res = db_query_params ('SELECT group_forum_id FROM forum_group_list WHERE group_id=$1',
							       array (db_result($groups_res,$g,'group_id'))) ;
	if (!$forums_group_res) {
		$error .= 'Error: Enable to get forums from group '. db_result($groups_res,$g,'group_id') . ' : ' .db_error();
		return false;
	}

	// build of an array containing group's forums
	$forums_group = Array ();
	for ($f=0; $f<db_numrows($forums_group_res); $f++) {
		$forums_group[$f] = db_result($forums_group_res,$f,'group_forum_id');
	}

	// select of all group's roles
	$roles_group_res = db_query_params ('SELECT role_id FROM role WHERE group_id=$1',
							       array (db_result($groups_res,$g,'group_id'))) ;
	if (!$roles_group_res) {
		$error .= 'Error: Enable to get roles from group '. db_result($groups_res,$g,'group_id') . ' : ' .db_error();
		return false;
	}

	// for each role
	for ($r=0; $r<db_numrows($roles_group_res); $r++) {
		// select conf of each role
		$role_settings_res = db_query_params ('SELECT role_id, section_name, ref_id, value FROM role_setting WHERE role_id=$1 AND section_name=$2',
								       array (db_result($roles_group_res,$r,'role_id'),
								       'forum')) ;
		if (!$role_settings_res) {
			$error .= 'Error: Enable to get role settings from role '. db_result($roles_group_res,$r,'role_id') . ' : ' .db_error();
			return false;
		}

		// for each conf
		for ($c=0; $c<db_numrows($role_settings_res); $c++) {
			// check if the conf is corresponding to an existing forum of the group
			if (!in_array(db_result($role_settings_res,$c,'ref_id'),$forums_group)) {
				// remove the conf if it is not corresponding to an existing forum of the group
				$result = db_query_params('DELETE FROM role_setting WHERE role_id=$1 AND section_name=$2 AND ref_id=$3',
							  array (db_result($roles_group_res,$r,'role_id'),
								 'forum',
								 db_result($role_settings_res,$c,'ref_id'))) ;
				if (!$result) {
					db_rollback();
					$error .= 'Error: Enable to delete role setting\'s forum for role '.db_result($roles_group_res,$r,'role_id') . ' ' .db_error();
					return false;
				}
			}
		}
	}

	// for each forum
	for ($f=0; $f<sizeof($forums_group); $f++) {
		// for each role
		for ($r=0; $r<db_numrows($roles_group_res); $r++) {
			$role_setting_res = db_query_params ('SELECT role_id, section_name, ref_id, value FROM role_setting WHERE role_id=$1 AND section_name=$2 AND ref_id=$3',
								       array (db_result($roles_group_res,$r,'role_id'),
								       'forum',
								       $forums_group[$f])) ;
			if (!$role_setting_res) {
				$error .= 'Error: Enable to get role setting for forum '. $forums_group[$f] . ' : ' .db_error();
				return false;
			}
			// check if it exists a conf for each role
			if (db_numrows($role_setting_res) == 0){
				//add the role setting corresponding
				$result = db_query_params('INSERT INTO role_setting (role_id,section_name,ref_id,value) VALUES ($1,$2,$3,$4)',
							  array (db_result($roles_group_res,$r,'role_id'),
								 'forum',
								 $forums_group[$f],
								 1)) ;
				if (!$result) {
					db_rollback();
					$error .= 'Error: Enable to set role setting for forum '. $forums_group[$f] . ' : ' .db_error();
					return false;
				}
			}
		}
	}
}

db_commit();

echo $error;



?>
