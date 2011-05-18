<?php // -*-php-*-
// $Id: file.php 7956 2011-03-03 17:08:31Z vargenau $

/**
 * Copyright 1999, 2000, 2001, 2002, 2003 $ThePhpWikiProgrammingTeam
 *
 * This file is part of PhpWiki.
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
 * Backend for handling file storage.
 *
 * Author: Jochen Kalmbach, Jochen@kalmbachnet.de
 */

/*
 * TODO:
 * - Implement "optimize" / "sync" / "check" / "rebuild"
 * - Optimize "get_previous_version"
 * - Optimize "get_links" (reversed = true)
 * - Optimize "get_all_revisions"
 * - Optimize "most_popular" (separate file for "hitcount",
 *   which contains all pages)
 * - Optimize "most_recent"
 * - What should be done in "lock"/"unlock"/"close" ?
 * - "WikiDB_backend_file_iter": Do I need to return 'version' and 'versiondata' ?
 *
 */

require_once('lib/WikiDB/backend.php');
require_once('lib/ErrorManager.php');

class WikiDB_backend_file
extends WikiDB_backend
{
    var $data_dir;
    var $_dir_names;

    var $_page_data;          // temporarily stores the pagedata (via _loadPageData)
    var $_page_version_data;  // temporarily stores the versiondata (via _loadVersionData)
    var $_latest_versions;    // temporarily stores the latest version-numbers (for every pagename)

    function WikiDB_backend_file( $dbparam )
    {
        $this->data_dir = $dbparam['directory'];
        if (file_exists($this->data_dir) and is_file($this->data_dir))
        	unlink($this->data_dir);
        if (is_dir($this->data_dir) == false) {
            mkdir($this->data_dir, 0755);
        }

        $this->_dir_names
            = array('ver_data'     => $this->data_dir.'/'.'ver_data',
                    'page_data'    => $this->data_dir.'/'.'page_data',
                    'latest_ver'   => $this->data_dir.'/'.'latest_ver',
                    'links'        => $this->data_dir.'/'.'links' );

        foreach ($this->_dir_names as $key => $val) {
        	if (file_exists($val) and is_file($val))
        	    unlink($val);
            if (is_dir($val) == false)
                mkdir($val, 0755);
        }

        $this->_page_data = NULL;
        $this->_page_version_data = NULL;
        $this->_latest_versions = NULL;


    }

    // *********************************************************************
    // common file load / save functions:
    function _pagename2filename($type, $pagename, $version) {
         if ($version == 0)
             return $this->_dir_names[$type].'/'.urlencode($pagename);
         else
             return $this->_dir_names[$type].'/'.urlencode($pagename).'--'.$version;
    }

    function _loadPage($type, $pagename, $version, $set_pagename = true) {
      $filename = $this->_pagename2filename($type, $pagename, $version);
      if (!file_exists($filename)) return NULL;
      if (!filesize($filename)) return array();
      if ($fd = @fopen($filename, "rb")) {
         $locked = flock($fd, 1); # Read lock
         if (!$locked) {
            ExitWiki("Timeout while obtaining lock. Please try again");
         }
         if ($data = fread($fd, filesize($filename))) {
            $pd = unserialize($data);
            if ($set_pagename == true)
                $pd['pagename'] = $pagename;
            if ($version != 0)
                $pd['version'] = $version;
	    if (!is_array($pd))
		ExitWiki(sprintf(gettext("'%s': corrupt file"),
				 htmlspecialchars($filename)));
            else
              return $pd;
	 }
	 fclose($fd);
      }
      return NULL;
    }

    function _savePage($type, $pagename, $version, $data) {
        $filename = $this->_pagename2filename($type, $pagename, $version);
        if($fd = fopen($filename, 'a+b')) {
            $locked = flock($fd,2); // Exclusive blocking lock
           if (!$locked) {
              ExitWiki("Timeout while obtaining lock. Please try again");
           }

           rewind($fd);
           ftruncate($fd, 0);
           $pagedata = serialize($data);
           $len = strlen($pagedata);
           $num = fwrite($fd, $pagedata, $len);
           assert($num == $len);
           fclose($fd);
        } else {
           ExitWiki("Error while writing page '$pagename'");
        }
    }

    function _removePage($type, $pagename, $version) {
        $filename = $this->_pagename2filename($type, $pagename, $version);
        if (!file_exists($filename)) return NULL;
        $f = @unlink($filename);
        if ($f == false)
            trigger_error("delete file failed: ".$filename." ver: ".$version, E_USER_WARNING);
    }

    // *********************************************************************

    // *********************************************************************
    // Load/Save Version-Data
    function _loadVersionData($pagename, $version) {
        if ($this->_page_version_data != NULL) {
            if ( ($this->_page_version_data['pagename'] == $pagename) &&
                ($this->_page_version_data['version'] == $version) ) {
                return $this->_page_version_data;
             }
        }
        $vd = $this->_loadPage('ver_data', $pagename, $version);
        if ($vd != NULL) {
            $this->_page_version_data = $vd;
            if ( ($this->_page_version_data['pagename'] == $pagename) &&
                ($this->_page_version_data['version'] == $version) ) {
                return $this->_page_version_data;
             }
        }
        return NULL;
    }

    function _saveVersionData($pagename, $version, $data) {
        $this->_savePage('ver_data', $pagename, $version, $data);

        // check if this is a newer version:
        if ($this->_getLatestVersion($pagename) < $version) {
            // write new latest-version-info
            $this->_setLatestVersion($pagename, $version);
        }
    }


    // *********************************************************************
    // Load/Save Page-Data
    function _loadPageData($pagename) {
        if ($this->_page_data != NULL) {
            if ($this->_page_data['pagename'] == $pagename) {
                return $this->_page_data;
             }
        }
        $pd = $this->_loadPage('page_data', $pagename, 0);
        if ($pd != NULL)
            $this->_page_data = $pd;
        if ($this->_page_data != NULL) {
            if ($this->_page_data['pagename'] == $pagename) {
                return $this->_page_data;
             }
        }
        return array();  // no values found
    }

    function _savePageData($pagename, $data) {
        $this->_savePage('page_data', $pagename, 0, $data);
    }

    // *********************************************************************
    // Load/Save Latest-Version
    function _saveLatestVersions() {
        $data = $this->_latest_versions;
        if ($data == NULL)
            $data = array();
        $this->_savePage('latest_ver', 'latest_versions', 0, $data);
    }

    function _setLatestVersion($pagename, $version) {
        // make sure the page version list is loaded:
        $this->_getLatestVersion($pagename);
        if ($version > 0) {
            $this->_getLatestVersion($pagename);
            $this->_latest_versions[$pagename] = $version;
        }
        else {
            // Remove this page from the Latest-Version-List:
            unset($this->_latest_versions[$pagename]);
        }
        $this->_saveLatestVersions();
    }

    function _loadLatestVersions() {
        if ($this->_latest_versions != NULL)
            return;

        $pd = $this->_loadPage('latest_ver', 'latest_versions', 0, false);
        if ($pd != NULL)
            $this->_latest_versions = $pd;
        else
            $this->_latest_versions = array(); // empty array
    }

    function _getLatestVersion($pagename) {
       $this->_loadLatestVersions();
       if (array_key_exists($pagename, $this->_latest_versions) == false)
           return 0; // do version exists
       return $this->_latest_versions[$pagename];
    }


    // *********************************************************************
    // Load/Save Page-Links
    function _loadPageLinks($pagename) {
        $pd = $this->_loadPage('links', $pagename, 0, false);
        if ($pd != NULL)
            return $pd;;
        return array();  // no values found
    }

    function _savePageLinks($pagename, $links) {
        $this->_savePage('links', $pagename, 0, $links);
    }



    /**
     * Get page meta-data from database.
     *
     * @param $pagename string Page name.
     * @return hash
     * Returns a hash containing the page meta-data.
     * Returns an empty array if there is no meta-data for the requested page.
     * Keys which might be present in the hash are:
     * <dl>
     *  <dt> locked  <dd> If the page is locked.
     *  <dt> hits    <dd> The page hit count.
     *  <dt> created <dd> Unix time of page creation. (FIXME: Deprecated: I
     *                    don't think we need this...)
     * </dl>
     */
    function get_pagedata($pagename) {
        return $this->_loadPageData($pagename);
    }

    /**
     * Update the page meta-data.
     *
     * Set page meta-data.
     *
     * Only meta-data whose keys are preset in $newdata is affected.
     *
     * For example:
     * <pre>
     *   $backend->update_pagedata($pagename, array('locked' => 1));
     * </pre>
     * will set the value of 'locked' to 1 for the specified page, but it
     * will not affect the value of 'hits' (or whatever other meta-data
     * may have been stored for the page.)
     *
     * To delete a particular piece of meta-data, set it's value to false.
     * <pre>
     *   $backend->update_pagedata($pagename, array('locked' => false));
     * </pre>
     *
     * @param $pagename string Page name.
     * @param $newdata hash New meta-data.
     */
    /**
     * This will create a new page if page being requested does not
     * exist.
     */
    function update_pagedata($pagename, $newdata) {
        $data = $this->get_pagedata($pagename);
        if (count($data) == 0) {
            $this->_savePageData($pagename, $newdata);  // create a new pagedata-file
            return;
        }

        foreach ($newdata as $key => $val) {
            if (empty($val))
                unset($data[$key]);
            else
                $data[$key] = $val;
        }
        $this->_savePageData($pagename, $data);  // write new pagedata-file
    }


    /**
     * Get the current version number for a page.
     *
     * @param $pagename string Page name.
     * @return int The latest version number for the page.  Returns zero if
     *  no versions of a page exist.
     */
    function get_latest_version($pagename) {
        return $this->_getLatestVersion($pagename);
    }

    /**
     * Get preceding version number.
     *
     * @param $pagename string Page name.
     * @param $version int Find version before this one.
     * @return int The version number of the version in the database which
     *  immediately preceeds $version.
     *
     * FIXED: Check if this version really exists!
     */
    function get_previous_version($pagename, $version) {
        $prev = ($version > 0 ? $version - 1 : 0);
    	while ($prev and !file_exists($this->_pagename2filename('ver_data', $pagename, $prev))) {
            $prev--;
    	}
    	return $prev;
    }

    /**
     * Get revision meta-data and content.
     *
     * @param $pagename string Page name.
     * @param $version integer Which version to get.
     * @param $want_content boolean
     *  Indicates the caller really wants the page content.  If this
     *  flag is not set, the backend is free to skip fetching of the
     *  page content (as that may be expensive).  If the backend omits
     *  the content, the backend might still want to set the value of
     *  '%content' to the empty string if it knows there's no content.
     *
     * @return hash The version data, or false if specified version does not
     *    exist.
     *
     * Some keys which might be present in the $versiondata hash are:
     * <dl>
     * <dt> %content
     *  <dd> This is a pseudo-meta-data element (since it's actually
     *       the page data, get it?) containing the page content.
     *       If the content was not fetched, this key may not be present.
     * </dl>
     * For description of other version meta-data see WikiDB_PageRevision::get().
     * @see WikiDB_PageRevision::get
     */
    function get_versiondata($pagename, $version, $want_content = false) {
	$vd = $this->_loadVersionData($pagename, $version);
        if ($vd == NULL)
            return false;
        return $vd;
    }

    /**
     * Rename all files for this page
     *
     * @access protected   Via WikiDB
     */
    function rename_page($pagename, $to) {
        $version = _getLatestVersion($pagename);
        foreach ($this->_dir_names as $type => $path) {
            if (is_dir($path)) {
                $filename = $this->_pagename2filename($type, $pagename, $version);
                $new = $this->_pagename2filename($type, $to, $version);
                @rename($filename,$new);
            }
        }
        $this->update_pagedata($pagename, array('pagename' => $to));
        return true;
    }

    /**
     * See ADODB for a better delete_page(), which can be undone and is seen in RecentChanges.
     */
    function delete_page($pagename) {
        $this->purge_page($pagename);
    }

    /**
     * Delete page from the database.
     *
     * Delete page (and all it's revisions) from the database.
     *
     * @param $pagename string Page name.
     */
    function purge_page($pagename) {
        $ver = $this->get_latest_version($pagename);
        while ($ver > 0) {
            $this->_removePage('ver_data', $pagename, $ver);
            $ver = $this->get_previous_version($pagename, $ver);
        }
        $this->_removePage('page_data', $pagename, 0);
        $this->_removePage('links', $pagename, 0);
        // remove page from latest_version...
        $this->_setLatestVersion($pagename, 0);
    }

    /**
     * Delete an old revision of a page.
     *
     * Note that one is never allowed to delete the most recent version,
     * but that this requirement is enforced by WikiDB not by the backend.
     *
     * In fact, to be safe, backends should probably allow the deletion of
     * the most recent version.
     *
     * @param $pagename string Page name.
     * @param $version integer Version to delete.
     */
    function delete_versiondata($pagename, $version) {
        if ($this->get_latest_version($pagename) == $version) {
            // try to delete the latest version!
            // so check if an older version exist:
            if ($this->get_versiondata($pagename,
                                       $this->get_previous_version($pagename, $version),
                                       false) == false) {
              // there is no older version....
              // so the completely page will be removed:
              $this->delete_page($pagename);
              return;
            }
        }
        $this->_removePage('ver_data', $pagename, $version);
    }

    /**
     * Create a new page revision.
     *
     * If the given ($pagename,$version) is already in the database,
     * this method completely overwrites any stored data for that version.
     *
     * @param $pagename string Page name.
     * @param $version int New revisions content.
     * @param $data hash New revision metadata.
     *
     * @see get_versiondata
     */
    function set_versiondata($pagename, $version, $data) {
        $this->_saveVersionData($pagename, $version, $data);
    }

    /**
     * Update page version meta-data.
     *
     * If the given ($pagename,$version) is already in the database,
     * this method only changes those meta-data values whose keys are
     * explicity listed in $newdata.
     *
     * @param $pagename string Page name.
     * @param $version int New revisions content.
     * @param $newdata hash New revision metadata.
     * @see set_versiondata, get_versiondata
     */
    function update_versiondata($pagename, $version, $newdata) {
        $data = $this->get_versiondata($pagename, $version, true);
        if (!$data) {
            assert($data);
            return;
        }
        foreach ($newdata as $key => $val) {
            if (empty($val))
                unset($data[$key]);
            else
                $data[$key] = $val;
        }
        $this->set_versiondata($pagename, $version, $data);
    }

    /**
     * Set links for page.
     *
     * @param $pagename string Page name.
     *
     * @param $links array List of page(names) which page links to.
     */
    function set_links($pagename, $links) {
        $this->_savePageLinks($pagename, $links);
    }

    /**
     * Find pages which link to or are linked from a page.
     *
     * @param $pagename string Page name.
     * @param $reversed boolean True to get backlinks.
     *
     * FIXME: array or iterator?
     * @return object A WikiDB_backend_iterator.
     */
    function get_links($pagename, $reversed=true, $include_empty=false,
                       $sortby='', $limit='', $exclude='',
                       $want_relations=false)
    {
        if ($reversed == false)
            return new WikiDB_backend_file_iter($this, $this->_loadPageLinks($pagename));

        $this->_loadLatestVersions();
        $pagenames = $this->_latest_versions;  // now we have an array with the key is the pagename of all pages

        $out = array();  // create empty out array

        foreach ($pagenames as $key => $val) {
            $links = $this->_loadPageLinks($key);
	    foreach ($links as $key2 => $val2) {
                if ($val2['linkto'] == $pagename)
                    array_push($out, $key);
            }
        }
        return new WikiDB_backend_file_iter($this, $out);
    }

    /**
     * Get all revisions of a page.
     *
     * @param $pagename string The page name.
     * @return object A WikiDB_backend_iterator.
     */
    /*
    function get_all_revisions($pagename) {
        include_once('lib/WikiDB/backend/dumb/AllRevisionsIter.php');
        return new WikiDB_backend_dumb_AllRevisionsIter($this, $pagename);
    }
    */

    /**
     * Get all pages in the database.
     *
     * Pages should be returned in alphabetical order if that is
     * feasable.
     *
     * @access protected
     *
     * @param $include_defaulted boolean
     * If set, even pages with no content will be returned
     * --- but still only if they have at least one revision (not
     * counting the default revision 0) entered in the database.
     *
     * Normally pages whose current revision has empty content
     * are not returned as these pages are considered to be
     * non-existing.
     *
     * @return object A WikiDB_backend_iterator.
     */
    function get_all_pages($include_empty=false, $sortby='', $limit='', $exclude='') {
    	require_once("lib/PageList.php");
        $this->_loadLatestVersions();
        $a = array_keys($this->_latest_versions);
        if (empty($a))
            return new WikiDB_backend_file_iter($this, $a);
        $sortby = $this->sortby($sortby, 'db', $this->sortable_columns());
        switch ($sortby) {
        case '': break;
        case 'pagename ASC':  sort($a); break;
        case 'pagename DESC': rsort($a); break;
        }
        return new WikiDB_backend_file_iter($this, $a);
    }

    function sortable_columns() {
        return array('pagename');
    }

    function numPages($filter=false, $exclude='') {
        $this->_loadLatestVersions();
        return count($this->_latest_versions);
    }

    /**
     * Lock backend database.
     *
     * Calls may be nested.
     *
     * @param $write_lock boolean Unless this is set to false, a write lock
     *     is acquired, otherwise a read lock.  If the backend doesn't support
     *     read locking, then it should make a write lock no matter which type
     *     of lock was requested.
     *
     *     All backends <em>should</em> support write locking.
     */
    function lock($write_lock = true) {
        //trigger_error("lock: Not Implemented", E_USER_WARNING);
    }

    /**
     * Unlock backend database.
     *
     * @param $force boolean Normally, the database is not unlocked until
     *  unlock() is called as many times as lock() has been.  If $force is
     *  set to true, the the database is unconditionally unlocked.
     */
    function unlock($force = false) {
        //trigger_error("unlock: Not Implemented", E_USER_WARNING);
    }


    /**
     * Close database.
     */
    function close () {
        //trigger_error("close: Not Implemented", E_USER_WARNING);
    }

    /**
     * Synchronize with filesystem.
     *
     * This should flush all unwritten data to the filesystem.
     */
    function sync() {
        //trigger_error("sync: Not Implemented", E_USER_WARNING);
    }

    /**
     * Optimize the database.
     */
    function optimize() {
        return 0;//trigger_error("optimize: Not Implemented", E_USER_WARNING);
    }

    /**
     * Check database integrity.
     *
     * This should check the validity of the internal structure of the database.
     * Errors should be reported via:
     * <pre>
     *   trigger_error("Message goes here.", E_USER_WARNING);
     * </pre>
     *
     * @return boolean True iff database is in a consistent state.
     */
    function check() {
        //trigger_error("check: Not Implemented", E_USER_WARNING);
    }

    /**
     * Put the database into a consistent state.
     *
     * This should put the database into a consistent state.
     * (I.e. rebuild indexes, etc...)
     *
     * @return boolean True iff successful.
     */
    function rebuild() {
        //trigger_error("rebuild: Not Implemented", E_USER_WARNING);
    }

    function _parse_searchwords($search) {
        $search = strtolower(trim($search));
        if (!$search)
            return array(array(),array());

        $words = preg_split('/\s+/', $search);
        $exclude = array();
        foreach ($words as $key => $word) {
            if ($word[0] == '-' && $word != '-') {
                $word = substr($word, 1);
                $exclude[] = preg_quote($word);
                unset($words[$key]);
            }
        }
        return array($words, $exclude);
    }

};

class WikiDB_backend_file_iter extends WikiDB_backend_iterator
{
    function WikiDB_backend_file_iter(&$backend, &$query_result, $options=array()) {
        $this->_backend = &$backend;
        $this->_result = $query_result;
        $this->_options = $options;

        if (count($this->_result) > 0)
            reset($this->_result);
    }

    function next() {
        if (!$this->_result)
            return false;
        if (count($this->_result) <= 0)
            return false;

        $e = each($this->_result);
        if ($e == false) {
            return false;
        }

        $pn = $e[1];
        if (is_array($pn) and isset($pn['linkto'])) { // support relation link iterator
            $pn = $pn['linkto'];
        }
        $pagedata = $this->_backend->get_pagedata($pn);
        // don't pass _cached_html via iterators
        if (isset($pagedata['_cached_html']))
            unset($pagedata['_cached_html']);
        unset($pagedata['pagename']);
        $rec = array('pagename' => $pn,
                     'pagedata' => $pagedata);
        if (is_array($e[1])) {
            $rec['linkrelation'] = $e[1]['relation'];
        }
        //$rec['version'] = $backend->get_latest_version($pn);
        //$rec['versiondata'] = $backend->get_versiondata($pn, $rec['version'], true);
        return $rec;
    }
    function asArray() {
        reset($this->_result);
        return $this->_result;
    }
    function count() {
    	return count($this->_result);
    }
    function free () {
        $this->_result = array();
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
