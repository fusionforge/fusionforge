#! /usr/bin/php
<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2003 (c) FusionForge, LLC
 * Copyright 2009 (c) Roland Mas
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
Before running the first time, execute initializing SQL:

CREATE TABLE user_metric_history(
month int not null,
day int not null,
user_id int not null,
ranking int not null,
metric float not null
);
CREATE INDEX user_metric_history_date_userid
ON user_metric_history(month,day,user_id);


        Nightly cron script to calculate the peer ratings

	The process starts with a seed group of users who are "trusted"
		to rate others

	After you are rated N times highly by other users, you can become trusted 
		and your ratings of others will begin to count

	Your rating is affected by how many times you are rated by others and 
		how highly they rate you and how highly rated they are

	How highly rated they are is affected by how many times they're rated 
		and how highly rated they are and so on up the chain

	For now, this process will run 8 times to get the calculations refined
		As more users are added, it may have to be run more

	Because of this circular dependency, the numbers are never "right", but 
		after a few runs, they should be refined "enough" to give us 
		what we want - a list of the top users on the site.

*/
require dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

$err='';
$threshhold='1.6';

db_begin();

db_query_params ('DELETE FROM user_metric0',
			array()) ;
$err .= db_error();

db_query_params ('select setval($1,1)',
		 array('user_metric0_pk_seq')) ;
$err .= db_error();

foreach(RBACEngine::getInstance()->getUsersByAllowedAction ('project_admin',forge_get_config('peer_rating_group')) as $u) {
	db_query_params ('INSERT INTO user_metric0 (user_id,times_ranked,avg_raters_importance,avg_rating,metric,percentile,importance_factor) VALUES ($1,5,1.25,3,0,0,1.25)',
			array (forge_get_config('peer_rating_group'))) ;
	$err .= db_error();
}

db_query_params ('UPDATE user_metric0 SET ranking=ranking-1',
			array()) ;

db_query_params ('UPDATE user_metric0 SET metric=(log(times_ranked::float)*avg_rating::float)::float, percentile=(100-(100*((ranking::float-1)/(select count(*) from user_metric0))))::float', 
		 array());
$err .= db_error();
db_query_params ('UPDATE user_metric0 SET importance_factor=(1+((percentile::float/100)*.5))::float', 
		 array()) ;
$err .= db_error();

db_query_params ('SELECT * INTO TEMPORARY TABLE user_metric_cur FROM user_metric0',
		 array()) ;
$err .= db_error();

for ($i=1; $i<9; $i++) {
	$j=($i-1);

	/*
		Set up an interim table to grab and average all trusted result
	*/
	db_drop_table_if_exists ("user_metric_tmp_next");
	db_drop_table_if_exists ("user_metric_next");

	$res = db_query_params ('CREATE TEMPORARY TABLE user_metric_tmp_next (
		user_id int not null default 0, 
		times_ranked float(8) null default 0,
		avg_raters_importance float(8) not null default 0,
		avg_rating float(8) not null default 0,
		metric float(8) not null default 0)',
				array()) ;
        if (!$res) {
                $err .= "Error in round $i creating table: " . db_error();
                exit;
        }

	/*
		Now grab/average trusted ratings into this table
	*/

	$res = db_query_params ('INSERT INTO user_metric_tmp_next
	   	SELECT user_ratings.user_id,count(*) AS count,
		avg(user_metric_cur.importance_factor),
		avg(user_ratings.rating),0
		FROM user_ratings,user_metric_cur
		WHERE user_ratings.rated_by=user_metric_cur.user_id
		GROUP BY user_ratings.user_id',
				array()) ;
	if (!$res) {
		$err .= "Error in round $i inserting average ratings: " . db_error();
		exit;
		
	}

	/*
		Now calculate the metric on the temp table

		This metric will be used in the next step to calculate ranking and importance
	*/

	$res = db_query_params ('UPDATE user_metric_tmp_next SET metric=(log(times_ranked)*avg_raters_importance*avg_rating)',
				array()) ;
	if (!$res) {
		$err .= "Error in round $i calculating metric: " . db_error();
		exit;
		
	}

	$res = db_query_params ('DELETE FROM user_metric_tmp_next WHERE metric < $1',
				array ($threshhold)) ;
	if (!$res) {
                $err .= "Error in round $i deleting < threshhold ids: " . db_error();
                exit;
                
        }

	/*
		Now we need to carry forward trusted IDs from the last round into this 
		Round, as prior round people may not have been ranked enough times by 
		new people in this round to stay in
	*/

	$res = db_query_params ('INSERT INTO user_metric_tmp_next
		SELECT user_id,times_ranked,avg_raters_importance,avg_rating,metric
		FROM user_metric_cur
		WHERE NOT EXISTS 
		(SELECT user_id FROM user_metric_tmp_next
		WHERE user_metric_tmp_next.user_id=user_metric_cur.user_id)',
				array ()) ;
        if (!$res) {
                $err .= "Error in round $i inserting final data: " . db_error();
                exit;
        }

	/*
		Now calculate the metric for this round

		Create the final table, then insert the data
	*/

	db_drop_table_if_exists ("user_metric_next");
	db_drop_sequence_if_exists ("user_metric_next_ranking_seq");

	$res = db_query_params ('CREATE TEMPORARY TABLE user_metric_next (
		ranking serial,
		user_id int not null default 0,
		times_ranked int not null default 0,
		avg_raters_importance float(8) not null default 0,
		avg_rating float(8) not null default 0,
		metric float(8) not null default 0,
		percentile float(8) not null default 0,
		importance_factor float(8) not null default 0)',
				array ()) ;

	if (!$res) {
                $err .= "Error in round $i creating table: " . db_error();
                exit;
        }

	/*
		Insert the data in ranked order
	*/

	$res = db_query_params ('INSERT INTO user_metric_next (user_id,times_ranked,avg_raters_importance,avg_rating,metric)
		SELECT user_id,times_ranked,avg_raters_importance,avg_rating,metric
		FROM user_metric_tmp_next
		ORDER BY metric DESC',
				array ()) ;
	if (!$res) {
		$err .= "Error in round $i inserting final data: " . db_error();
		exit;
	}

	/*
		Get the row count so we can calc the percentile below
	*/
	$res = db_query_params ('SELECT COUNT(*) FROM user_metric_next',
				array ());
	if (!$res) {
		$err .= "Error in round $i getting row count: " . db_error();
		exit;
	}

	// Only do final percentile if row count is not zero
	if (db_result($res,0,0)) {

		/*
		 Update with final percentile and importance
		*/
		$res = db_query_params ('UPDATE user_metric_next SET
			percentile=(100-(100*((ranking::float-1)/$1)))',
					array (db_result($res,0,0))) ;
	    if (!$res || db_affected_rows($res) < 1) {
		$err .= "Error in round $i setting percentile: " . db_error();
		exit;
	    }
	    $res = db_query_params ('UPDATE user_metric_next SET
		importance_factor=(1+((percentile/100)*.5))',
				    array ()) ;
	    if (!$res || db_affected_rows($res) < 1) {
		$err .= "Error in round $i setting importance factor: " . db_error();
		exit;
	    }
	}

	db_drop_table_if_exists ("user_metric_tmp_cur");
	db_drop_table_if_exists ("user_metric_cur");
	db_query_params ('SELECT * INTO user_metric_cur FROM user_metric_next',
			 array()) ;
	db_query_params ('SELECT * INTO user_metric_tmp_cur FROM user_metric_tmp_next',
			 array()) ;
	db_drop_table_if_exists ("user_metric_tmp_next");
	db_drop_table_if_exists ("user_metric_next");
}

db_query_params ('DELETE FROM user_metric',
		 array()) ;
db_query_params ('INSERT INTO user_metric SELECT * FROM user_metric_cur',
		 array()) ;

db_drop_table_if_exists ("user_metric_tmp_cur");
db_drop_table_if_exists ("user_metric_cur");

db_commit();

$t = time();
$ts_month = date('Ym', $t);
$ts_day = date('d', $t);

db_begin();
db_query_params ('DELETE FROM user_metric_history WHERE month=$1 AND day=$2',
			array($ts_month,
			$ts_day)) ;

db_query_params ('
	INSERT INTO user_metric_history
	SELECT $1,$2,user_id,ranking,metric
	FROM user_metric
',
			array($ts_month,
			$ts_day)) ;

$err .= db_error();

cron_entry(1,$err);

db_commit();

?>
