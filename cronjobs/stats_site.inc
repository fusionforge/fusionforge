<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
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

function site_stats_day($year,$month,$day) {

	db_begin();

	$day_begin=mktime(0,0,0,$month,$day,$year);
	$day_end=($day_begin + 86400);

#	echo "Stats_site: $datetime::$day_begin::$day_end::".date('Ymd',$day_begin)."::".date('Ymd',$day_end)."--> $i\n";

	//
	//  build row for stats_site
	//
	$rel = db_query_params ('DELETE FROM stats_site WHERE month=$1 AND day=$2',
				array("$year$month",
				      $day));



	$rel = db_query_params ('INSERT INTO stats_site (month,day,uniq_users,sessions,total_users,new_users,new_projects) 
	VALUES ($1,
	$2,
	(SELECT COUNT(DISTINCT(user_id)) FROM user_session WHERE (time < $3 AND time > $4)),
	(SELECT COUNT(session_hash) FROM user_session WHERE (time < $3 AND time > $4)),
	(SELECT COUNT(user_id) FROM users WHERE ( add_date < $3 AND status=$5 )),
	(SELECT COUNT(user_id) FROM users WHERE ( add_date < $3 AND add_date > $4 )),
	(SELECT COUNT(group_id) FROM groups WHERE ( register_time < $3 AND register_time > $4 )))',
				array("$year$month",
				      $day,
				      $day_end,
				      $day_begin,
				      'A'));
	echo db_error();

	db_commit();

}

?>
