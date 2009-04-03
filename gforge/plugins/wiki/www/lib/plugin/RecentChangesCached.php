<?php // -*-php-*-
rcs_id('$Id: RecentChangesCached.php 6406 2009-01-17 14:31:56Z rurban $');
/**
 Copyright 1999, 2000, 2001, 2002 $ThePhpWikiProgrammingTeam

 This file is part of PhpWiki.

 PhpWiki is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 PhpWiki is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with PhpWiki; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// +---------------------------------------------------------------------+
// | WikiPluginCached.php                                                |
// +---------------------------------------------------------------------+
// | Copyright (C) 2002 Johannes Große (Johannes Gro&szlig;e)            |
// | You may copy this code freely under the conditions of the GPL       |
// +---------------------------------------------------------------------+

/* There is a bug in it:
   When the cache is empty and you safe the wikipages,
   an immediately created cached output of
   RecentChanges will at the rss-image-link include
   an action=edit
*/


require_once "lib/WikiPluginCached.php";
require_once "lib/plugin/RecentChanges.php";

class WikiPlugin_RecentChangesCached
extends WikiPluginCached
{
    /* --------- overwrite virtual or abstract methods ---------------- */
    function getPluginType() {
        return PLUGIN_CACHED_HTML;
    }

    function getName() {
        return "RecentChangesCached";
    }

    function getDescription() {
        return 'Caches output of RecentChanges called with default arguments.';
    }

    function getVersion() {
        return preg_replace("/[Revision: $]/", '',
                            "\$Revision: 6406 $");
    }

    function getDefaultArguments() {
        return WikiPlugin_RecentChanges::getDefaultArguments();
    }

    function getExpire($dbi, $argarray, $request) {
        return '+900'; // 15 minutes
    }

    // We don't go through pi parsing, instead we go directly to the 
    // better plugin methods.
    function getHtml($dbi, $args, $request, $basepage) {
	$plugin = new WikiPlugin_RecentChanges();
	$changes = $plugin->getChanges($dbi, $args);
	return $plugin->format($changes, $args);
	/*
        $loader = new WikiPluginLoader;
        return $loader->expandPI('<?plugin RecentChanges '
            . WikiPluginCached::glueArgs($argarray)
                                 . ' ?>', $request, $this, $basepage);
        */
    }
    
    // ->box is used to display a fixed-width, narrow version with common header.
    // Just a limited list of pagenames, without date.
    // This does not use ->run, to avoid pi construction and deconstruction
    function box($args = false, $request = false, $basepage = false, $do_save = false) {
        if (!$request) $request =& $GLOBALS['request'];
        if (!isset($args['limit'])) $args['limit'] = 12;
        $args['format'] = 'box';
        $args['show_minor'] = false;
        $args['show_major'] = true;
        $args['show_deleted'] = 'sometimes';
        $args['show_all'] = false;
        $args['days'] = 90;
        
        $cache = $this->newCache();
        if (is_array($args))
            ksort($args);
        $argscopy = $args;
        unset($argscopy['limit']);
        $this->_args =& $args;
        $this->_type = $this->getPluginType();
        $this->_static = false;

        /* OLD: */
        //list($id, $url) = $this->genUrl($cache, $args);
        
        /* NEW: This cache entry needs an update on major changes.
         * So we should rather use an unique ID, because there will only be
         * one global cached box.
         */
        $id = $cache->generateId(serialize(array("RecentChangesCachedBox", $argscopy)));
        $content = $cache->get($id, 'imagecache');
        if ($do_save || !$content || !$content['html']) {
	    $this->resetError();
	    $plugin = new WikiPlugin_RecentChanges();
	    $title = WikiLink($this->getName(), '', SplitPagename($this->getName()));
	    $changes = $plugin->getChanges($request->_dbi, $args);
            $content['html'] = 
                      $this->makeBox($title,
                                     $plugin->format($changes, $args));
            if ($errortext = $this->getError()) {
                $this->printError($errortext, 'html');
                return HTML();
            }
            $do_save = true;
        }
        if ($do_save) {
            $content['args'] = md5($this->_pi);
            $expire = $this->getExpire($request->_dbi, $content['args'], $request);
            $cache->save($id, $content, $expire, 'imagecache');
        }
        if ($content['html'])
            return $content['html'];
        return HTML();
    }

    // force box cache update on major changes.
    function box_update($args = false, $request = false, $basepage = false) {
    	$this->box($args, $request, $basepage, true);
    }
    
    
} // WikiPlugin_RecentChangesCached

// $Log: not supported by cvs2svn $
// Revision 1.4  2004/03/08 18:17:10  rurban
// added more WikiGroup::getMembersOf methods, esp. for special groups
// fixed $LDAP_SET_OPTIONS
// fixed _AuthInfo group methods
//
// Revision 1.3  2003/02/21 23:01:10  dairiki
// Fixes to support new $basepage argument of WikiPlugin::run().
//
// Revision 1.2  2003/01/18 22:01:44  carstenklapp
// Code cleanup:
// Reformatting & tabs to spaces;
// Added copyleft, getVersion, getDescription, rcs_id.
//

// For emacs users
// Local Variables:
// mode: php
// tab-width: 4
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
