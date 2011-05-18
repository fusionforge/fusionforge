<?php // -*-php-*-
// $Id: RatingsDb.php 7967 2011-03-07 13:08:01Z vargenau $

/*
 * @author:  Dan Frankowski (wikilens group manager), Reini Urban (as plugin)
 *
 * TODO:
 * - fix RATING_STORAGE = WIKIPAGE (dba, file)
 * - fix smart caching
 * - finish mysuggest.c (external engine with data from mysql)
 * - add the various show modes (esp. TopN queries in PHP)
 */
/*
 CREATE TABLE rating (
        dimension INT(4) NOT NULL,
        raterpage INT(11) NOT NULL,
        rateepage INT(11) NOT NULL,
        ratingvalue FLOAT NOT NULL,
        rateeversion INT(11) NOT NULL,
        isPrivate ENUM('yes','no'),
        tstamp TIMESTAMP(14) NOT NULL,
        PRIMARY KEY (dimension, raterpage, rateepage)
 );
*/

// For other than SQL backends. dba + adodb SQL ratings are allowed but deprecated.
// We will probably drop this hack.
if (!defined('RATING_STORAGE'))
    // for DATABASE_TYPE=dba and forced RATING_STORAGE=SQL we must use ADODB,
    // but this is problematic.
    define('RATING_STORAGE', $GLOBALS['request']->_dbi->_backend->isSQL() ? 'SQL' : 'WIKIPAGE');
    //define('RATING_STORAGE','WIKIPAGE');   // not fully supported yet

// leave undefined for internal, slow php engine.
//if (!defined('RATING_EXTERNAL'))
//    define('RATING_EXTERNAL',PHPWIKI_DIR . 'suggest.exe');

// Dimensions
if (!defined('EXPLICIT_RATINGS_DIMENSION'))
    define('EXPLICIT_RATINGS_DIMENSION', 0);
if (!defined('LIST_ITEMS_DIMENSION'))
    define('LIST_ITEMS_DIMENSION', 1);
if (!defined('LIST_OWNER_DIMENSION'))
    define('LIST_OWNER_DIMENSION', 2);
if (!defined('LIST_TYPE_DIMENSION'))
    define('LIST_TYPE_DIMENSION', 3);


//TODO: split class into SQL and metadata backends
class RatingsDb extends WikiDB {

    function RatingsDb() {
        global $request;
        $this->_dbi = &$request->_dbi;
        $this->_backend = &$this->_dbi->_backend;
        $this->dimension = null;
        if (RATING_STORAGE == 'SQL') {
            if (isa($this->_backend, 'WikiDB_backend_PearDB')) {
                $this->_sqlbackend = &$this->_backend;
                $this->dbtype = "PearDB";
            } elseif (isa($this->_backend, 'WikiDB_backend_ADODOB')) {
                $this->_sqlbackend = &$this->_backend;
                $this->dbtype = "ADODB";
            } else {
                include_once("lib/WikiDB/backend/ADODB.php");
                // It is not possible to decouple a ref from the source again. (4.3.11)
                // It replaced the main request backend. So we don't initialize _sqlbackend before.
                //$this->_sqlbackend = clone($this->_backend);
                $this->_sqlbackend = new WikiDB_backend_ADODB($GLOBALS['DBParams']);
                $this->dbtype = "ADODB";
            }
            $this->iter_class = "WikiDB_backend_".$this->dbtype."_generic_iter";

            extract($this->_sqlbackend->_table_names);
            if (empty($rating_tbl)) {
                $rating_tbl = (!empty($GLOBALS['DBParams']['prefix'])
                               ? $GLOBALS['DBParams']['prefix'] : '') . 'rating';
                $this->_sqlbackend->_table_names['rating_tbl'] = $rating_tbl;
            }
        } else {
            $this->iter_class = "WikiDB_Array_PageIterator";
        }
    }

    // this is a singleton.  It ensures there is only 1 ratingsDB.
    function & getTheRatingsDb(){
        static $_theRatingsDb;

        if (!isset($_theRatingsDb)){
            $_theRatingsDb = new RatingsDb();
        }
        //echo "rating db is $_theRatingsDb";
        return $_theRatingsDb;
    }


/// *************************************************************************************
// FIXME
// from Reini Urban's RateIt plugin
    function addRating($rating, $userid, $pagename, $dimension) {
        if (RATING_STORAGE == 'SQL') {
            $page = $this->_dbi->getPage($pagename);
            $current = $page->getCurrentRevision();
            $rateeversion = $current->getVersion();
            $this->sql_rate($userid, $pagename, $rateeversion, $dimension, $rating);
        } else {
            $this->metadata_set_rating($userid, $pagename, $dimension, $rating);
        }
    }

    function deleteRating($userid=null, $pagename=null, $dimension=null) {
        if (is_null($dimension)) $dimension = $this->dimension;
        if (is_null($userid))    $userid = $this->userid;
        if (is_null($pagename))  $pagename = $this->pagename;
        if (RATING_STORAGE == 'SQL') {
            $this->sql_delete_rating($userid, $pagename, $dimension);
        } else {
            $this->metadata_set_rating($userid, $pagename, $dimension, -1);
        }
    }

    function getRating($userid=null, $pagename=null, $dimension=null) {
        if (RATING_STORAGE == 'SQL') {
            $ratings_iter = $this->sql_get_rating($dimension, $userid, $pagename);
            if ($rating = $ratings_iter->next() and isset($rating['ratingvalue'])) {
                return $rating['ratingvalue'];
            } else
                return false;
        } else {
            return $this->metadata_get_rating($userid, $pagename, $dimension);
        }
    }

    function getUsersRated($dimension=null, $orderby = null) {
        if (is_null($dimension)) $dimension = $this->dimension;
        //if (is_null($userid))    $userid = $this->userid;
        //if (is_null($pagename))  $pagename = $this->pagename;
        if (RATING_STORAGE == 'SQL') {
            $ratings_iter = $this->sql_get_users_rated($dimension, $orderby);
            if ($rating = $ratings_iter->next() and isset($rating['ratingvalue'])) {
                return $rating['ratingvalue'];
            } else
                return false;
        } else {
            return $this->metadata_get_users_rated($dimension, $orderby);
        }
    }

    /**
     * Get ratings.
     *
     * @param dimension  The rating dimension id.
     *                   Example: 0
     *                   [optional]
     *                   If this is null (or left off), the search for ratings
     *                   is not restricted by dimension.
     *
     * @param rater  The page id of the rater, i.e. page doing the rating.
     *               This is a Wiki page id, often of a user page.
     *               Example: "DanFr"
     *               [optional]
     *               If this is null (or left off), the search for ratings
     *               is not restricted by rater.
     *               TODO: Support an array
     *
     * @param ratee  The page id of the ratee, i.e. page being rated.
     *               Example: "DudeWheresMyCar"
     *               [optional]
     *               If this is null (or left off), the search for ratings
     *               is not restricted by ratee.
     *
     * @param orderby An order-by clause with fields and (optionally) ASC
     *                or DESC.
     *               Example: "ratingvalue DESC"
     *               [optional]
     *               If this is null (or left off), the search for ratings
     *               has no guaranteed order
     *
     * @param pageinfo The type of page that has its info returned (i.e.,
     *               'pagename', 'hits', and 'pagedata') in the rows.
     *               Example: "rater"
     *               [optional]
     *               If this is null (or left off), the info returned
     *               is for the 'ratee' page (i.e., thing being rated).
     *
     * @return DB iterator with results
     */
    function get_rating($dimension=null, $rater=null, $ratee=null,
                        $orderby = null, $pageinfo = "ratee") {
        if (RATING_STORAGE == 'SQL') {
            $ratings_iter = $this->sql_get_rating($dimension, $rater, $pagename);
            if ($rating = $ratings_iter->next() and isset($rating['ratingvalue'])) {
                return $rating['ratingvalue'];
            } else
                return false;
        } else {
            return $this->metadata_get_rating($rater, $pagename, $dimension);
        }
    }

    /* UR: What is this for? NOT USED!
       Maybe the list of users (ratees) who rated on this page.
     */
    function get_users_rated($dimension=null, $pagename = null, $orderby = null) {
        if (RATING_STORAGE == 'SQL') {
            $ratings_iter = $this->sql_get_users_rated($dimension, $pagename, $orderby);
        // iter as userid
        $users = array();
        while ($rating = $ratings_iter->next()) {
        $users[] = $rating['userid'];
        }
        return $users;
        } else {
            return $this->metadata_get_users_rated($dimension, $pagename, $orderby);
        }
    }

    /**
     * Like get_rating(), but return a WikiDB_PageIterator
     * FIXME!
     */
    function get_rating_page($dimension=null, $rater=null, $ratee=null,
                        $orderby = null, $pageinfo = "ratee") {
        if (RATING_STORAGE == 'SQL') {
            return $this->sql_get_rating($dimension, $rater, $ratee, $orderby, $pageinfo);
        } else {
        // empty dummy iterator
        $pages = array();
        return new WikiDB_Array_PageIterator($pages);
        }
    }

    /**
     * Delete a rating.
     *
     * @param rater  The page id of the rater, i.e. page doing the rating.
     *               This is a Wiki page id, often of a user page.
     * @param ratee  The page id of the ratee, i.e. page being rated.
     * @param dimension  The rating dimension id.
     *
     * @access public
     *
     * @return true upon success
     */
    function delete_rating($rater, $ratee, $dimension) {
        if (RATING_STORAGE == 'SQL') {
            $this->sql_delete_rating($rater, $ratee, $dimension);
        } else {
            $this->metadata_set_rating($rater, $ratee, $dimension, -1);
        }
    }

    /**
     * Rate a page.
     *
     * @param rater  The page id of the rater, i.e. page doing the rating.
     *               This is a Wiki page id, often of a user page.
     * @param ratee  The page id of the ratee, i.e. page being rated.
     * @param rateeversion  The version of the ratee page.
     * @param dimension  The rating dimension id.
     * @param rating The rating value (a float).
     *
     * @access public
     *
     * @return true upon success
     */
    function rate($rater, $ratee, $rateeversion, $dimension, $rating) {
        if (RATING_STORAGE == 'SQL') {
            $page = $this->_dbi->getPage($pagename);
            $current = $page->getCurrentRevision();
            $rateeversion = $current->getVersion();
            $this->sql_rate($userid, $pagename, $rateeversion, $dimension, $rating);
        } else {
            $this->metadata_set_rating($userid, $pagename, $dimension, $rating);
        }
    }

    //function getUsersRated(){}

//*******************************************************************************
    // TODO:
    // Use wikilens/RatingsUser.php for the php methods.
    //
    // Old:
    // Currently we have to call the "suggest" CGI
    //   http://www-users.cs.umn.edu/~karypis/suggest/
    // until we implement a simple recommendation engine.
    // Note that "suggest" is only free for non-profit organizations.
    // I am currently writing a binary CGI mysuggest using suggest, which loads
    // data from mysql.
    function getPrediction($userid=null, $pagename=null, $dimension=null) {
        if (is_null($dimension)) $dimension = $this->dimension;
        if (is_null($userid))    $userid   = $this->userid;
        if (is_null($pagename))  $pagename = $this->pagename;

        if (RATING_STORAGE == 'SQL') {
            $dbh = &$this->_sqlbackend;
            if (isset($pagename))
                $page = $dbh->_get_pageid($pagename);
            else
                return 0;
            if (isset($userid))
                $user = $dbh->_get_pageid($userid);
            else
                return 0;
        }
        if (defined('RATING_EXTERNAL') and RATING_EXTERNAL) {
            // how call mysuggest.exe? as CGI or natively
            //$rating = HTML::Raw("<!--#include virtual=".RATING_ENGINE." -->");
            $args = "-u$user -p$page -malpha"; // --top 10
            if (isset($dimension))
                $args .= " -d$dimension";
            $rating = passthru(RATING_EXTERNAL . " $args");
        } else {
            $rating = $this->php_prediction($userid, $pagename, $dimension);
        }
        return $rating;
    }

    /**
     * Slow item-based recommendation engine, similar to suggest RType=2.
     * Only the SUGGEST_EstimateAlpha part
     * Take wikilens/RatingsUser.php for the php methods.
     */
    function php_prediction($userid=null, $pagename=null, $dimension=null) {
        if (is_null($dimension)) $dimension = $this->dimension;
        if (is_null($userid))    $userid   = $this->userid;
        if (is_null($pagename))  $pagename = $this->pagename;
        if (empty($this->buddies)) {
            require_once("lib/wikilens/RatingsUser.php");
            require_once("lib/wikilens/Buddy.php");
            $user = RatingsUserFactory::getUser($userid);
            $this->buddies = getBuddies($user, $GLOBALS['request']->_dbi);
        }
        return $user->knn_uu_predict($pagename, $this->buddies, $dimension);
    }

    function getNumUsers($pagename=null, $dimension=null) {
        if (is_null($dimension)) $dimension = $this->dimension;
        if (is_null($pagename))  $pagename = $this->pagename;
        if (RATING_STORAGE == 'SQL') {
            $ratings_iter = $this->sql_get_rating($dimension, null, $pagename,
                                                  null, "ratee");
            return $ratings_iter->count();
        } else {
            if (!$pagename) return 0;
            $page = $this->_dbi->getPage($pagename);
            $data = $page->get('rating');
            if (!empty($data[$dimension]))
                return count($data[$dimension]);
            else
                return 0;
        }
    }

    function getAvg($pagename=null, $dimension=null) {
        if (is_null($dimension)) $dimension = $this->dimension;
        if (is_null($pagename))  $pagename = $this->pagename;
        if (RATING_STORAGE == 'SQL') {
            $dbi = &$this->_sqlbackend;
        if (isset($pagename) || isset($dimension)) {
        $where = "WHERE";
        }
            if (isset($pagename)) {
                $raterid = $this->_sqlbackend->_get_pageid($pagename, true);
                $where .= " raterpage=$raterid";
            }
            if (isset($dimension)) {
        if (isset($pagename)) $where .= " AND";
                $where .= " dimension=$dimension";
            }
            //$dbh = &$this->_dbi;
            extract($dbi->_table_names);
            $query = "SELECT AVG(ratingvalue) as avg"
           . " FROM $rating_tbl r, $page_tbl p "
           . $where
           . " GROUP BY raterpage";
            $result = $dbi->_dbh->query($query);
            $iter = new $this->iter_class($this, $result);
            $row = $iter->next();
            return $row['avg'];
        } else {
            if (!$pagename) return 0;
            $page = $this->_dbi->getPage($pagename);
            $data = $page->get('rating');
            if (!empty($data[$dimension]))
                // hash of userid => rating
                return array_sum(array_values($data[$dimension])) / count($data[$dimension]);
        else
        return 0;
        }
    }
//*******************************************************************************

    /**
     * Get ratings.
     *
     * @param dimension  The rating dimension id.
     *                   Example: 0
     *                   [optional]
     *                   If this is null (or left off), the search for ratings
     *                   is not restricted by dimension.
     *
     * @param rater  The page id of the rater, i.e. page doing the rating.
     *               This is a Wiki page id, often of a user page.
     *               Example: "DanFr"
     *               [optional]
     *               If this is null (or left off), the search for ratings
     *               is not restricted by rater.
     *               TODO: Support an array
     *
     * @param ratee  The page id of the ratee, i.e. page being rated.
     *               Example: "DudeWheresMyCar"
     *               [optional]
     *               If this is null (or left off), the search for ratings
     *               is not restricted by ratee.
     *               TODO: Support an array
     *
     * @param orderby An order-by clause with fields and (optionally) ASC
     *                or DESC.
     *               Example: "ratingvalue DESC"
     *               [optional]
     *               If this is null (or left off), the search for ratings
     *               has no guaranteed order
     *
     * @param pageinfo The type of page that has its info returned (i.e.,
     *               'pagename', 'hits', and 'pagedata') in the rows.
     *               Example: "rater"
     *               [optional]
     *               If this is null (or left off), the info returned
     *               is for the 'ratee' page (i.e., thing being rated).
     *
     * @return DB iterator with results
     */
    function sql_get_rating($dimension=null, $rater=null, $ratee=null,
                            $orderby=null, $pageinfo = "ratee") {
        if (is_null($dimension)) $dimension = $this->dimension;
        $result = $this->_sql_get_rating_result($dimension, $rater, $ratee, $orderby, $pageinfo);
        return new $this->iter_class($this, $result);
    }

    function sql_get_users_rated($dimension=null, $pagename=null, $orderby=null) {
        if (is_null($dimension)) $dimension = $this->dimension;
        $result = $this->_sql_get_rating_result($dimension, null, $pagename, $orderby, "rater");
        return new $this->iter_class($this, $result);
    }

    // all users who rated this page resp if null all pages.. needed?
    function metadata_get_users_rated($dimension=null, $pagename=null, $orderby=null) {
        if (is_null($dimension)) $dimension = $this->dimension;
    $users = array();
    if (!$pagename) {
        // TODO: all pages?
        return new WikiDB_Array_PageIterator($users);
    }
    $page = $this->_dbi->getPage($pagename);
    $data = $page->get('rating');
    if (!empty($data[$dimension])) {
        //array($userid => (float)$rating);
        return new WikiDB_Array_PageIterator(array_keys($data[$dimension]));
        }
        return new WikiDB_Array_PageIterator($users);
    }

    /**
     * @access private
     * @return result ressource, suitable to the iterator
     */
    function _sql_get_rating_result($dimension=null, $rater=null, $ratee=null,
                                    $orderby=null, $pageinfo = "ratee") {
        // pageinfo must be 'rater' or 'ratee'
        if (($pageinfo != "ratee") && ($pageinfo != "rater"))
            return;
        $dbi = &$this->_sqlbackend;
        if (is_null($dbi))
            return;
        //$dbh = &$this->_dbi;
        extract($dbi->_table_names);
        $where = "WHERE r." . $pageinfo . "page = p.id";
        if (isset($dimension)) {
            $where .= " AND dimension=$dimension";
        }
        if (isset($rater)) {
            $raterid = $dbi->_get_pageid($rater, true);
            $where .= " AND raterpage=$raterid";
        }
        if (isset($ratee)) {
            if(is_array($ratee)){
                $where .= " AND (";
                for($i = 0; $i < count($ratee); $i++){
                    $rateeid = $dbi->_get_pageid($ratee[$i], true);
                    $where .= "rateepage=$rateeid";
                    if($i != (count($ratee) - 1)){
                        $where .= " OR ";
                    }
                }
                $where .= ")";
            } else {
                $rateeid = $dbi->_get_pageid($ratee, true);
                $where .= " AND rateepage=$rateeid";
            }
        }
        $orderbyStr = "";
        if (isset($orderby)) {
            $orderbyStr = " ORDER BY " . $orderby;
        }
        if (isset($rater) or isset($ratee)) $what = '*';
        // same as _get_users_rated_result()
        else {
        $what = 'DISTINCT p.pagename';
        if ($pageinfo == 'rater')
        $what = 'DISTINCT p.pagename as userid';
    }

        $query = "SELECT $what"
               . " FROM $rating_tbl r, $page_tbl p "
               . $where
               . $orderbyStr;
        $result = $dbi->_dbh->query($query);
        return $result;
    }

    /**
     * Delete a rating.
     *
     * @param rater  The page id of the rater, i.e. page doing the rating.
     *               This is a Wiki page id, often of a user page.
     * @param ratee  The page id of the ratee, i.e. page being rated.
     * @param dimension  The rating dimension id.
     *
     * @access public
     *
     * @return true upon success
     */
    function sql_delete_rating($rater, $ratee, $dimension) {
        //$dbh = &$this->_dbi;
        $dbi = &$this->_sqlbackend;
        extract($dbi->_table_names);

        $dbi->lock();
        $raterid = $dbi->_get_pageid($rater, true);
        $rateeid = $dbi->_get_pageid($ratee, true);
        $where = "WHERE raterpage=$raterid and rateepage=$rateeid";
        if (isset($dimension)) {
            $where .= " AND dimension=$dimension";
        }
        $dbi->_dbh->query("DELETE FROM $rating_tbl $where");
        $dbi->unlock();
        return true;
    }

    /**
     * Rate a page.
     *
     * @param rater  The page id of the rater, i.e. page doing the rating.
     *               This is a Wiki page id, often of a user page.
     * @param ratee  The page id of the ratee, i.e. page being rated.
     * @param rateeversion  The version of the ratee page.
     * @param dimension  The rating dimension id.
     * @param rating The rating value (a float).
     *
     * @access public
     *
     * @return true upon success
     */
    //               ($this->userid, $this->pagename, $page->version, $this->dimension, $rating);
    function sql_rate($rater, $ratee, $rateeversion, $dimension, $rating) {
        $dbi = &$this->_sqlbackend;
        extract($dbi->_table_names);
        if (empty($rating_tbl))
            $rating_tbl = $this->_dbi->getParam('prefix') . 'rating';

        $dbi->lock();
        $raterid = $dbi->_get_pageid($rater, true);
        $rateeid = $dbi->_get_pageid($ratee, true);
        assert($raterid);
        assert($rateeid);
        //mysql optimize: REPLACE if raterpage and rateepage are keys
        $dbi->_dbh->query("DELETE from $rating_tbl WHERE dimension=$dimension AND raterpage=$raterid AND rateepage=$rateeid");
        $where = "WHERE raterpage='$raterid' AND rateepage='$rateeid'";
        $insert = "INSERT INTO $rating_tbl (dimension, raterpage, rateepage, ratingvalue, rateeversion)"
            ." VALUES ('$dimension', $raterid, $rateeid, '$rating', '$rateeversion')";
        $dbi->_dbh->query($insert);

        $dbi->unlock();
        return true;
    }

    function metadata_get_rating($userid, $pagename, $dimension) {
        if (!$pagename) return false;
        $page = $this->_dbi->getPage($pagename);
        $data = $page->get('rating');
        if (!empty($data[$dimension][$userid]))
            return (float)$data[$dimension][$userid];
        else
            return false;
    }

    function metadata_set_rating($userid, $pagename, $dimension, $rating = -1) {
        if (!$pagename) return false;
        $page = $this->_dbi->getPage($pagename);
        $data = $page->get('rating');
        if ($rating == -1)
            unset($data[$dimension][$userid]);
        else {
            if (empty($data[$dimension]))
                $data[$dimension] = array($userid => (float)$rating);
            else
                $data[$dimension][$userid] = (float)$rating;
        }
        $page->set('rating',$data);
    }

}

/*
class RatingsDB_backend_PearDB
extends WikiDB_backend_PearDB {
    function get_rating($dimension=null, $rater=null, $ratee=null,
                        $orderby=null, $pageinfo = "ratee") {
        $result = $this->_get_rating_result(
                         $dimension, $rater, $ratee, $orderby, $pageinfo);
        return new WikiDB_backend_PearDB_generic_iter($this, $result);
    }

    function get_users_rated($dimension=null, $orderby=null) {
        $result = $this->_get_users_rated_result(
                         $dimension, $orderby);
        return new WikiDB_backend_PearDB_generic_iter($this, $result);
    }

    function get_rating_page($dimension=null, $rater=null, $ratee=null,
                             $orderby=null, $pageinfo = "ratee") {
        $result = $this->_get_rating_result(
                         $dimension, $rater, $ratee, $orderby, $pageinfo);
        return new WikiDB_backend_PearDB_iter($this, $result);
    }

    function _get_rating_result($dimension=null, $rater=null, $ratee=null,
                                $orderby=null, $pageinfo = "ratee") {
        // pageinfo must be 'rater' or 'ratee'
        if (($pageinfo != "ratee") && ($pageinfo != "rater"))
            return;

        $dbh = &$this->_dbh;
        extract($this->_table_names);

        $where = "WHERE r." . $pageinfo . "page = p.id";
        if (isset($dimension)) {
            $where .= " AND dimension=$dimension";
        }
        if (isset($rater)) {
            $raterid = $this->_get_pageid($rater, true);
            $where .= " AND raterpage=$raterid";
        }
        if (isset($ratee)) {
            if(is_array($ratee)){
                $where .= " AND (";
                for($i = 0; $i < count($ratee); $i++){
                    $rateeid = $this->_get_pageid($ratee[$i], true);
                    $where .= "rateepage=$rateeid";
                    if($i != (count($ratee) - 1)){
                        $where .= " OR ";
                    }
                }
                $where .= ")";
            } else {
                $rateeid = $this->_get_pageid($ratee, true);
                $where .= " AND rateepage=$rateeid";
            }
        }

        $orderbyStr = "";
        if (isset($orderby)) {
            $orderbyStr = " ORDER BY " . $orderby;
        }

        $query = "SELECT *"
            . " FROM $rating_tbl r, $page_tbl p "
            . $where
            . $orderbyStr;

        $result = $dbh->query($query);

        return $result;
    }

    function _get_users_rated_result($dimension=null, $orderby=null) {
        $dbh = &$this->_dbh;
        extract($this->_table_names);

        $where = "WHERE p.id=r.raterpage";
        if (isset($dimension)) {
            $where .= " AND dimension=$dimension";
        }
        $orderbyStr = "";
        if (isset($orderby)) {
            $orderbyStr = " ORDER BY " . $orderby;
        }

        $query = "SELECT DISTINCT p.pagename"
            . " FROM $rating_tbl r, $page_tbl p "
            . $where
            . $orderbyStr;

        $result = $dbh->query($query);

        return $result;
    }
    function delete_rating($rater, $ratee, $dimension) {
        $dbh = &$this->_dbh;
        extract($this->_table_names);

        $this->lock();
        $raterid = $this->_get_pageid($rater, true);
        $rateeid = $this->_get_pageid($ratee, true);

        $dbh->query("DELETE FROM $rating_tbl WHERE raterpage=$raterid and rateepage=$rateeid and dimension=$dimension");
        $this->unlock();
        return true;
    }

    function rate($rater, $ratee, $rateeversion, $dimension, $rating, $isPrivate = 'no') {
        $dbh = &$this->_dbh;
        extract($this->_table_names);

        $this->lock();
        $raterid = $this->_get_pageid($rater, true);
        $rateeid = $this->_get_pageid($ratee, true);

        $dbh->query("DELETE FROM $rating_tbl WHERE raterpage=$raterid and rateepage=$rateeid and dimension=$dimension and isPrivate='$isPrivate'");
        // NOTE: Leave tstamp off the insert, and MySQL automatically updates it
        $dbh->query("INSERT INTO $rating_tbl (dimension, raterpage, rateepage, ratingvalue, rateeversion, isPrivate) VALUES ($dimension, $raterid, $rateeid, $rating, $rateeversion, '$isPrivate')");
        $this->unlock();
        return true;
    }
}
*/

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
