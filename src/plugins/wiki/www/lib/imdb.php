<?php // $Id: imdb.php 7964 2011-03-05 17:05:30Z vargenau $
/**
 * Copyright 2004 Reini Urban
 *
 * This file is (not yet) part of PhpWiki.
 *
 * PhpWiki is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * PhpWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Accessors for a local imdb database.
 * Import it via amdbfront.
 * Get the dsn alias from lib/plugin/SqlResult.ini
 */
class imdb {

    function imdb () {
        global $DBParams;
        $ini = parse_ini_file(FindFile("config/SqlResult.ini"));
        $dsn = $ini['imdb'];
        if ($DBParams['dbtype'] == 'SQL') {
            $this->_dbh = DB::connect($dsn);
            $this->_dbtype = "PearDB";
        } else {
            if ($DBParams['dbtype'] != 'ADODB') {
                // require_once('lib/WikiDB/adodb/adodb-errorhandler.inc.php');
                require_once('lib/WikiDB/adodb/adodb-pear.inc.php');
            }
            $parsed = parseDSN($dsn);
            $this->_dbh = &ADONewConnection($parsed['phptype']);
            $conn = $this->_dbh->Connect($parsed['hostspec'],$parsed['username'],
                                         $parsed['password'], $parsed['database']);
            $this->_dbtype = "ADODB";
        }
        $this->_title_1_1 = array
            ("business","moviebudgets","colorinfo","mpaaratingsreasons");
        $this->_title_1_n = array
            ("akatitles","alternateversions",
             "miscellaneouscompanies","moviecountries",
             "certificates","completecast","completecrew","crazycredits",
             "genres","goofs","keywords","movielinks","plot","quotes","ratings","soundtracks",
             "specialeffectscompanies",
             "taglines","trivia","distributors","language","laserdisc","literature",
             "locations","miscellaneouscompanies",
             "productioncompanies","releasedates","runningtimes","soundmix","technical"
             );
    }

    /* key accessors. return a hash */
    function title($title_id) {
        $result = $this->_dbh->genericSqlIter("SELECT m.title, m.date FROM movies as m WHERE m.title_id = '$title_id'");
        return $result->next();
    }
    function name($name_id) {
        $result = $this->_dbh->genericSqlIter
            ("SELECT n.name, b.* "
             ." FROM name AS n"
             ." LEFT JOIN biographies AS b USING (name_id)"
             ." WHERE n.name_id = '$name_id' ");
        return $result->next();
    }

    /* main movie information, with just the top names */
    /* 1:1 title info: moviebudgets, colorinfo, business? */
    function movie($title_id) {
        $result = $this->_dbh->genericSqlIter("SELECT m.title, m.date"
                                              ." FROM movies as m"
                                              ." WHERE m.title_id = '$title_id'");
        $movie = $result->next();
        $movie['title_id'] = $title_id;
        /* add the individual results to hash */
        foreach (array_merge($this->_title_1_1,$this->_title_1_n) as $accessor) {
            if (method_exists($this,$accessor))
                $iter = $this->$accessor($title_id);
            else
                $iter = $this->_titleQuery($accessor, $accessor, $title_id);
            while ($row = $iter->next()) {
                $movie[$accessor][]  = $row;
            }
        }
        // add the names also?
        return $movie;
    }

    function movie_main($title_id) {
        return movie($title_id);
    }
    /* full movie information, with full cast and crew */
    function movie_full($title_id) {
        $movie = $this->movie($title_id);
        /* add the individual results to hash */
        foreach (array_merge($this->_cast,$this->_crew) as $accessor) {
            if (method_exists($this,$accessor))
                $iter = $this->$accessor($title_id);
            else
                $iter = $this->_titleQuery($accessor, $accessor, $title_id);
            while ($row = $iter->next()) {
                $movie[$accessor][]  = $row;
            }
        }
        return $movie;
    }

    /* combined movie information */
    function movie_combined($title_id) {
        $movie = $this->movie($title_id);
        /* add the individual results to hash */
        foreach (array_merge($this->_combined) as $accessor) {
            if (method_exists($this,$accessor))
                $iter = $this->$accessor($title_id);
            else
                $iter = $this->_titleQuery($accessor, $accessor, $title_id);
            while ($row = $iter->next()) {
                $movie[$accessor][]  = $row;
            }
        }
        return $movie;
    }

    /* movie company_credits information */
    function movie_company_credits($title_id) {
        $movie = $this->movie($title_id);
        /* add the individual results to hash */
        foreach (array_merge($this->_company_credits) as $accessor) {
            if (method_exists($this,$accessor))
                $iter = $this->$accessor($title_id);
            else
                $iter = $this->_titleQuery($accessor, $accessor, $title_id);
            while ($row = $iter->next()) {
                $movie[$accessor][]  = $row;
            }
        }
        return $movie;
    }

    /* 1:n title subselects: possibly multiple rows per title */
    /* accessors with same field and tablename are not needed */
    function _titleQuery($field, $table, $title_id) {
        return $this->_dbh->genericSqlIter
            ("SELECT $field FROM $table WHERE title_id = '$title_id'");
    }
    function akatitles($title_id) {
        return $this->_titleQuery("akatitle", "akatitles", $title_id);
    }
    function business($title_id) {
        return $this->_titleQuery("*", "business", $title_id);
    }
    function moviebudgets($title_id) {
        return $this->_titleQuery("b.currency, b.budget, b.info", "moviebudgets as b", $title_id);
    }
    function completecast($title_id) {
        return $this->_titleQuery("cast", "completecast", $title_id);
    }
    function completecrew($title_id) {
        return $this->_titleQuery("crew", "completecrew", $title_id);
    }
    function genres($title_id) {
        return $this->_titleQuery("genre", "genres", $title_id);
    }
    /* how many rows? */
    function goofs($title_id) {
        return $this->_titleQuery("text", "goofs", $title_id);
    }
    /* how many rows? */
    function keywords($title_id) {
        return $this->_titleQuery("keyword", "keywords", $title_id);
    }
    // (ml_id, description) values (1, 'followed by');
    function movielinks($title_id) {
        return $this->_dbh->genericSqlIter
            ("SELECT m.title,ml.description, mref.title AS title_ref"
             ." FROM movielinks AS l, ml, movies as m, movies AS mref"
             ." WHERE l.title_ref = mref.title_id AND l.title_id = mref.title_id"
             ." AND l.ml_id = ml.ml_id AND l.title_id = '$title_id'");
    }
    /* how many rows? */
    function plot($title_id) {
        return $this->_titleQuery("*", "plot", $title_id);
    }
    /* how many rows? */
    function quotes($title_id) {
        return $this->_titleQuery("*", "quotes", $title_id);
    }
    /* TODO: how? */
    function ratings($title_id) {
        return $this->_titleQuery("*", "ratings", $title_id);
    }
    function soundtracks($title_id) {
        return $this->_titleQuery("*", "soundtracks", $title_id);
    }
    function specialeffectscompanies($title_id) {
        return $this->_titleQuery("*", "specialeffectscompanies", $title_id);
    }
    /* how many rows? */
    function taglines($title_id) {
        return $this->_titleQuery("content", "taglines", $title_id);
    }
    /* how many rows? */
    function trivia($title_id) {
        return $this->_titleQuery("content", "trivia", $title_id);
    }
    function distributors($title_id) {
        return $this->_titleQuery("distributor", "distributors", $title_id);
    }
    function language($title_id) {
        return $this->_titleQuery("language", "language", $title_id);
    }
    function laserdisc($title_id) {
        return $this->_titleQuery("content", "laserdisc", $title_id);
    }
    function literature($title_id) {
        return $this->_titleQuery("literature", "literature", $title_id);
    }
    function locations($title_id) {
        return $this->_titleQuery("location", "locations", $title_id);
    }
    function miscellaneouscompanies($title_id) {
        return $this->_titleQuery("company", "miscellaneouscompanies", $title_id);
    }
    function mpaaratingsreasons($title_id) {
        return $this->_titleQuery("mpaarating", "mpaaratingsreasons", $title_id);
    }
    function productioncompanies($title_id) {
        return $this->_titleQuery("company", "productioncompanies", $title_id);
    }
    function soundmix($title_id) {
        return $this->_titleQuery("soundmix", "soundmix", $title_id);
    }
    function technical($title_id) {
        return $this->_titleQuery("technical", "technical", $title_id);
    }
    function releasedates($title_id) {
        return $this->_dbh->genericSqlIter
            ("SELECT c.country,r.releasedate,r.info"
             ." FROM country as c, releasedates AS r"
             ." WHERE c.country_id = r.country_id AND r.title_id = '$title_id'");
    }
    function runningtimes($title_id) {
        return $this->_dbh->genericSqlIter
            ("SELECT c.country,r.time,,r.info"
             ." FROM country as c, runningtimes AS r"
             ." WHERE c.country_id = r.country_id AND r.title_id = '$title_id'");
    }
    function moviecountries($title_id) {
        return $this->_dbh->genericSqlIter
            ("SELECT c.country FROM country AS c, moviecountries AS m"
             ." WHERE c.country_id = m.country_id AND m.title_id = '$title_id'");
    }
    function certificates($title_id) {
        return $this->_dbh->genericSqlIter
            ("SELECT co.country, c.certificate, c.info"
             ." FROM country as co, certificates as c"
             ." WHERE co.country_id = c.country_id AND c.title_id = '$title_id'"
             ." GROUP BY c.country_id");
    }

    /* 1:n name subselects: possibly multiple rows per name */
    /*
create table akanames (name_id integer unsigned not null, akaname varchar(255) not null);
create table biographies (name_id integer unsigned not null, RN text, NK text, DB text, DD text, HT text, BG text, BO text, BT text, PI text, OW text, TR text, QU text, SA text, WN text, SP text, TM text, IT text, AT text, PT text, CV text, AG text, primary key (name_id));
create table guestappearances (name_id integer unsigned not null, title_id integer unsigned not null, role varchar(255));
create table characters (name_id integer unsigned not null, title_id integer unsigned not null, role varchar(255), position integer unsigned, job_id tinyint unsigned not null);
     */
    function akanames($name_id) {
        return $this->_dbh->genericSqlIter
            ("SELECT akanames FROM akanames WHERE name_id = '$name_id'");
    }
    function guestappearances($name_id) {
        return $this->_dbh->genericSqlIter
            ("SELECT g.role, m.movie, m.date"
             ." FROM guestappearances as g"
             ." WHERE g.name_id = '$name_id' AND g.title_id = m.title_id");
    }
    function biographies($name_id) {
        return $this->_dbh->genericSqlIter
            ("SELECT n.name, b.*"
             ." FROM name as n"
             ." LEFT JOIN biographies as b USING (name_id)"
             ." WHERE n.name_id = '$name_id' ");
    }

    /* Search functions */
    function searchTitle($title) {
        return $this->_search($title, '_sql_title_clause',
                              "SELECT m.title_id, m.title, m.date".
                              " FROM movies as m".
                              " WHERE ",
                              "ORDER BY m.date DESC");
    }

    function searchName($name) {
        return $this->_search($name, '_sql_name_clause',
                              "SELECT n.name_id, n.name, j.description, c.role, m.title_id, m.title".
                              " FROM names as n, jobs as j, characters as c, movies as m".
                              " WHERE n.name_id = c.name_id".
                              " AND m.title_id = c.title_id".
                              " AND c.job_id = j.job_id".
                              " AND ",
                              "GROUP BY m.title_id ORDER BY j.description");
    }

    /* Search helpers */
    // quote the LIKE argument and construct the WHERE clause
    function _sql_match_clause($field, $word) {
        //not sure if we need this.  ADODB may do it for us
        $word = preg_replace('/(?=[%_\\\\])/', "\\", $word);
        // (we need it for at least % and _ --- they're the wildcard characters
        //  for the LIKE operator, and we need to quote them if we're searching
        //  for literal '%'s or '_'s.  --- I'm not sure about \, but it seems to
        //  work as is.
        $word = $this->_dbh->qstr("%".strtolower($word)."%");
        return "LOWER($field) LIKE $word";
    }

    function _sql_title_clause($word) {
        return $this->_sql_match_clause("title",$word);
    }
    function _sql_name_clause($word) {
        return $this->_sql_match_clause("name",$word);
    }

    function _search($what, $callback_fn, $query, $order = '') {
        include_once("lib/TextSearchQuery.php");
        // $dbh = $GLOBALS['request']->getDbh();
        //TODO: check if the db is mysql resp. capable of google like search.
        //      postgresql tsearch2 for example
        // See TextSearchQuery.php
        $search = new TextSearchQuery($what);
        $callback = new WikiMethodCb($this, $callback_fn);
        $search_clause = $search->makeSqlClause($callback);
        $result = $this->_dbh->genericSqlIter($query . " " . $search_clause . " " . $order);
    }

    /*
// all movies with actor:
SELECT m.title, m.date, n.name, c.role
  FROM movies as m, names as n, jobs as j, characters as c
  WHERE n.name LIKE "%%where%%"
  AND m.title_id = c.title_id
  AND n.name_id = c.name_id
  AND c.job_id = j.job_id
  AND j.description = 'Actor'
  ORDER BY m.date DESC
    */

};

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
