<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

function site_stats_day($year,$month,$day) {

	db_begin();

	$day_begin=mktime(0,0,0,$month,$day,$year);
	$day_end=($day_begin + 86400);

	echo "Stats_site: $datetime::$day_begin::$day_end::".date('Ymd',$day_begin)."::".date('Ymd',$day_end)."--> $i\n";

	//
	//  build row for stats_site
	//
	$rel=db_query("DELETE FROM stats_site WHERE month='$year$month' AND day='$day'");

	$sql="INSERT INTO stats_site (month,day,uniq_users,sessions,total_users,new_users,new_projects) 
	VALUES ('$year$month',
	'$day',
	(SELECT COUNT(DISTINCT(user_id)) FROM session WHERE (time < '$day_end' AND time > '$day_begin')),
	(SELECT COUNT(session_hash) FROM session WHERE (time < '$day_end' AND time > $day_begin)),
	(SELECT COUNT(user_id) FROM users WHERE ( add_date < '$day_end' AND status='A' )),
	(SELECT COUNT(user_id) FROM users WHERE ( add_date < '$day_end' AND add_date > '$day_begin' )),
	(SELECT COUNT(group_id) FROM groups WHERE ( register_time < '$day_end' AND register_time > '$day_begin' )))";

	$rel=db_query($sql);
	echo db_error();

	db_commit();

}

?>
