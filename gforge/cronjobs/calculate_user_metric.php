#! /usr/bin/php5 -f
<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2003 (c) GForge, LLC
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

//exit;

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

require $gfwww.'include/squal_pre.php';
require $gfcommon.'include/cron_utils.php';

$err='';
$threshhold='1.6';

db_begin();

db_query("DELETE FROM user_metric0");
$err .= db_error();

if ($sys_database_type != 'mysql') {
	db_query("select setval('user_metric0_pk_seq',1)");
	$err .= db_error();
}

db_query("INSERT INTO user_metric0 
(user_id,times_ranked,avg_raters_importance,avg_rating,metric,percentile,importance_factor)
SELECT user_id,5,1.25,3,0,0,1.25
FROM user_group
WHERE
user_group.group_id='$sys_peer_rating_group'
AND user_group.admin_flags='A';");

$err .= db_error();

db_query("UPDATE user_metric0 SET ranking=ranking-1");

if ($sys_database_type == 'mysql') {
	$sql="
	SELECT count(*) FROM user_metric0 INTO @total;
	UPDATE user_metric0 SET
	metric=(log(times_ranked)*avg_rating),
	percentile=(100-(100*(ranking-1.0)/@total));";

	db_mquery($sql);
	$err .= db_error();
	db_next_result();
	$err .= db_error();
} else {
	$sql="UPDATE user_metric0 SET
	metric=(log(times_ranked::float)*avg_rating::float)::float,
	percentile=(100-(100*((ranking::float-1)/(select count(*) from user_metric0))))::float;";

	db_query($sql);
	$err .= db_error();
}

if ($sys_database_type == 'mysql') {
	$sql="UPDATE user_metric0 SET importance_factor=(1+((percentile/100.0)*.5));";
} else {
	$sql="UPDATE user_metric0 SET importance_factor=(1+((percentile::float/100)*.5))::float;";
}

db_query($sql);
$err .= db_error();

for ($i=1; $i<9; $i++) {
	// $err .= '<br />Starting round: '.$i;

	$j=($i-1);

	/*
		Set up an interim table to grab and average all trusted result
	*/
	db_drop_table_if_exists ("user_metric_tmp1_".$i);

	$sql="CREATE TABLE user_metric_tmp1_$i (
		user_id int not null default 0, 
		times_ranked float(8) null default 0,
		avg_raters_importance float(8) not null default 0,
		avg_rating float(8) not null default 0,
		metric float(8) not null default 0);";
	$res=db_query($sql);
        if (!$res) {
                $err .= "Error in round $i inserting final data: ";
                $err .= '<p>'.$sql.'<p>';
                $err .= db_error();
                exit;
        }

	/*
		Now grab/average trusted ratings into this table
	*/

    $sql="INSERT INTO user_metric_tmp1_$i
	   	SELECT user_ratings.user_id,count(*) AS count,
		avg(user_metric$j.importance_factor),
		avg(user_ratings.rating),0
		FROM user_ratings,user_metric$j
		WHERE user_ratings.rated_by=user_metric$j.user_id
		GROUP BY user_ratings.user_id";

	$res=db_query($sql);
	if (!$res) {
		$err .= "Error in round $i inserting average ratings: ";
		$err .= '<p>'.$sql.'<p>';
		$err .= db_error();
		exit;
		
	}

	/*
		Now calculate the metric on the temp table

		This metric will be used in the next step to calculate ranking and importance
	*/

	$sql="UPDATE user_metric_tmp1_$i SET metric=(log(times_ranked)*avg_raters_importance*avg_rating);";
	$res=db_query($sql);
	if (!$res) {
		$err .= "Error in round $i calculating metric: ";
		$err .= '<p>'.$sql.'<p>';
		$err .= db_error();
		exit;
		
	}

	$sql="DELETE FROM user_metric_tmp1_$i WHERE metric < $threshhold";
	$res=db_query($sql);
	if (!$res) {
                $err .= "Error in round $i deleting < threshhold ids: ";
		$err .= '<p>'.$sql.'<p>';
                $err .= db_error();
                exit;
                
        }

	/*
		Now we need to carry forward trusted IDs from the last round into this 
		Round, as prior round people may not have been ranked enough times by 
		new people in this round to stay in
	*/

	$sql="INSERT INTO user_metric_tmp1_$i 
		SELECT user_id,times_ranked,avg_raters_importance,avg_rating,metric
		FROM user_metric$j 
		WHERE NOT EXISTS 
		(SELECT user_id FROM user_metric_tmp1_$i 
		WHERE user_metric_tmp1_$i.user_id=user_metric$j.user_id);";

	$res=db_query($sql);
        if (!$res) {
                $err .= "Error in round $i inserting final data: ";
                $err .= '<p>'.$sql.'<p>';
                $err .= db_error();
                exit;
        }

	/*
		Now calculate the metric for this round

		Create the final table, then insert the data
	*/

	// $err .= '<br />Starting Final Metric';

	db_drop_table_if_exists ("user_metric".$i);
	db_drop_sequence_if_exists ("user_metric".$i."_ranking_seq");

	$sql="CREATE TABLE user_metric$i (
		ranking serial,
		user_id int not null default 0,
		times_ranked int not null default 0,
		avg_raters_importance float(8) not null default 0,
		avg_rating float(8) not null default 0,
		metric float(8) not null default 0,
		percentile float(8) not null default 0,
		importance_factor float(8) not null default 0);";

	$res=db_query($sql);
	if (!$res) {
                $err .= "Error in round $i inserting final data: ";
                $err .= '<p>'.$sql.'<p>';
                $err .= db_error();
                exit;
        }

	/*
		Insert the data in ranked order
	*/

	$sql="INSERT INTO user_metric$i (user_id,times_ranked,avg_raters_importance,avg_rating,metric)
		SELECT user_id,times_ranked,avg_raters_importance,avg_rating,metric
		FROM user_metric_tmp1_$i
		ORDER BY metric DESC;";
	$res=db_query($sql);
	if (!$res) {
		$err .= "Error in round $i inserting final data: ";
		$err .= '<p>'.$sql.'<p>';
		$err .= db_error();
		exit;
	}

	/*
		Get the row count so we can calc the percentile below
	*/
	$res=db_query("SELECT COUNT(*) FROM user_metric$i");
	if (!$res) {
		$err .= "Error in round $i getting row count: ";
		$err .= '<p>'.$sql.'<p>';
		$err .= db_error();
		exit;
	}

	//$err .= '<br />Issuing Final Update';
	// Only do final percentile if row count is not zero
	if (db_result($res,0,0)) {

	    /*
	    	Update with final percentile and importance
	    */
		if ($sys_database_type == 'mysql') {
			$sql="UPDATE user_metric$i SET
			percentile=(100-(100*((ranking-1.0)/". db_result($res,0,0) .")))";
		} else {
			$sql="UPDATE user_metric$i SET
			percentile=(100-(100*((ranking::float-1)/". db_result($res,0,0) .")))";
		}
	    $res=db_query($sql);
	    if (!$res || db_affected_rows($res) < 1) {
		$err .= "Error in round $i setting percentile: ";
		$err .= '<p>'.$sql.'<p>';
		$err .= db_error();
		exit;
	    }
	    $sql="UPDATE user_metric$i SET
		importance_factor=(1+((percentile/100)*.5));";
	    $res=db_query($sql);
	    if (!$res || db_affected_rows($res) < 1) {
		$err .= "Error in round $i setting importance factor: ";
		$err .= '<p>'.$sql.'<p>';
		$err .= db_error();
		exit;
	    }
	}
}

db_commit();
db_query("DELETE FROM user_metric;");
db_query("INSERT INTO user_metric SELECT * FROM user_metric".($i-1).";");
//$err .= '<p>'.db_error().'<p>';

/*
	Now run through and drop the tmp tables
*/
// $err .= "<p>Cleaning up tables<p>";

for ($i=1; $i<9; $i++) {
	db_drop_table_if_exists ("user_metric_tmp1_".$i);
	db_drop_sequence_if_exists ("user_metric_tmp1_".$i."_ranking_seq");
	db_drop_table_if_exists ("user_metric".$i);
};

$err .= db_error();

$t = time();
$ts_month = date('Ym', $t);
$ts_day = date('d', $t);

db_begin();
db_query("DELETE FROM user_metric_history WHERE month='$ts_month' AND day='$ts_day'");
db_query("
	INSERT INTO user_metric_history
	SELECT '$ts_month','$ts_day',user_id,ranking,metric
	FROM user_metric
");
$err .= db_error();

cron_entry(1,$err);

db_commit();

?>
