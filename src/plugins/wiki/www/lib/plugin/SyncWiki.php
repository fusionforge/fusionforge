<?php // -*-php-*-
// $Id: SyncWiki.php 7955 2011-03-03 16:41:35Z vargenau $
/**
 * Copyright 2006 $ThePhpWikiProgrammingTeam
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * required argument:  url = <rpc interface to main wiki>
 * optional arguments: noimport, noexport, noupload
 *
 * 1. check RPC2 interface or admin url (lang?) of external wiki
 *    get external pagelist, only later than our last mergepoint
 * 2. Download all externally changed sources:
 *    If local page is older than the mergepoint, import it.
 *    If local page does not exist (deleted?), and there is no revision, import it.
 *    Else we deleted it. Skip the import, but don't delete the external. Should be added to conflict.
 *    If local page is newer than the mergepoint, then add it to the conflict pages.
 * 3. check our to_delete, to_add, to_merge
 * 4. get our pagelist of pages only later than our last mergepoint
 * 5. check external to_delete, to_add, to_merge
 * 6. store log (where, how?)
 */
require_once("lib/loadsave.php");
include_once("lib/plugin/WikiAdminUtils.php");

class WikiPlugin_SyncWiki
extends WikiPlugin_WikiAdminUtils
{
    function getName () {
        return _("SyncWiki");
    }

    function getDescription () {
        return _("Synchronize pages with external PhpWiki");
    }

    function getDefaultArguments() {
        return array('url'    => '',
                     'noimport' => 0,
                     'noexport' => 0,
                     'noupload' => 0,
                     'label'  => $this->getName(),
                     //'userid' => false,
                     'passwd' => false,
                     'sid'    => false,
                     );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        $args['action'] = 'syncwiki';
        extract($args);
        if (empty($args['url']))
            return $this->error(fmt("A required argument '%s' is missing.", "url"));
        if ($request->getArg('action') != 'browse')
            return $this->disabled("(action != 'browse')");
        $posted = $request->getArg('wikiadminutils');
        if ($request->isPost()
            and $posted['action'] == $action
            and $posted['url'] == $url) // multiple buttons
        {
            return $this->_do_syncwiki($request, $posted);
        }
        return $this->_makeButton($request, $args, $label);
    }

    function _do_syncwiki(&$request, $args) {
        global $charset;
        longer_timeout(240);

        if (!function_exists('wiki_xmlrpc_post')) {
            include_once("lib/XmlRpcClient.php");
        }
        $userid = $request->_user->_userid;
        $dbh = $request->getDbh();
        $merge_point = $dbh->get('mergepoint');
        if (empty($merge_point)) {
            $page = $dbh->getPage("ReleaseNotes"); // this is usually the latest official page
            $last = $page->getCurrentRevision(false);
            $merge_point = $last->get("mtime");    // for testing: 1160396075
            $dbh->set('mergepoint', $merge_point);
        }
        //TODO: remote auth, set session cookie
        $pagelist = wiki_xmlrpc_post('wiki.getRecentChanges',
                                     iso8601_encode($merge_point,1),
                                     $args['url'], $args);
        $html = HTML();
        //$html->pushContent(HTML::div(HTML::em("check RPC2 interface...")));
        if (gettype($pagelist) === "array") {
            //$request->_deferredPageChangeNotification = array();
            $request->discardOutput();
            StartLoadDump($request, _("Syncing this PhpWiki"));
            PrintXML(HTML::strong(fmt("Download all externally changed sources.")));
            echo "<br />\n";
            PrintXML(fmt("Retrieving from external url %s wiki.getRecentChanges(%s)...",
                     $args['url'], iso8601_encode($merge_point,1)));
            echo "<br />\n";
            $ouriter = $dbh->mostRecent(array('since' => $merge_point));
            //$ol = HTML::ol();
            $done = array();
            foreach ($pagelist as $ext) {
                $reaction = _("<unknown>");
                // compare existance and dates with local page
                $extdate = iso8601_decode($ext['lastModified']->scalar,1);
                // TODO: urldecode ???
                $name = utf8_decode($ext['name']);
                $our = $dbh->getPage($name);
                $done[$name] = 1;
                $ourrev  = $our->getCurrentRevision(false);
                $rel = '<=>';
                if (!$our->exists()) {
                    // we might have deleted or moved it on purpose?
                    // check date of latest revision if there's one, and > mergepoint
                    if (($ourrev->getVersion() > 1) and ($ourrev->get('mtime') > $merge_point)) {
                        // our was deleted after sync, and changed after last sync.
                        $this->_addConflict('delete', $args, $our, $extdate);
                        $reaction = (_(" skipped")." ("."locally deleted or moved".")");
                    } else {
                        $reaction = $this->_import($args, $our, $extdate);
                    }
                } else {
                    $ourdate = $ourrev->get('mtime');
                    if ($extdate > $ourdate and $ourdate < $merge_point) {
                            $rel = '>';
                        $reaction = $this->_import($args, $our, $extdate);
                    } elseif ($extdate > $ourdate and $ourdate >= $merge_point) {
                            $rel = '>';
                        // our is older then external but newer than last sync
                        $reaction = $this->_addConflict('import', $args, $our, $extdate);
                    } elseif ($extdate < $ourdate and $extdate < $merge_point) {
                            $rel = '>';
                        $reaction = $this->_export($args, $our);
                    } elseif ($extdate < $ourdate and $extdate >= $merge_point) {
                            $rel = '>';
                        // our is newer and external is also newer
                        $reaction = $this->_addConflict('export', $args, $our, $extdate);
                    } else {
                            $rel = '==';
                        $reaction = _("same date");
                    }
                }
                /*$ol->pushContent(HTML::li(HTML::strong($name)," ",
                                          $extdate,"<=>",$ourdate," ",
                                          HTML::strong($reaction))); */
                PrintXML(HTML::strong($name)," ",
                         $extdate," $rel ",$ourdate," ",
                         HTML::strong($reaction),
                         HTML::br());
                $request->chunkOutput();
            }
            //$html->pushContent($ol);
        } else {
            $html->pushContent("xmlrpc error:  wiki.getRecentChanges returned "
                          ."(".gettype($pagelist).") ".$pagelist);
            trigger_error("xmlrpc error:  wiki.getRecentChanges returned "
                          ."(".gettype($pagelist).") ".$pagelist, E_USER_WARNING);
            EndLoadDump($request);
            return $this->error($html);
        }

        if (empty($args['noexport'])) {
            PrintXML(HTML::strong(fmt("Now upload all locally newer pages.")));
            echo "<br />\n";
            PrintXML(fmt("Checking all local pages newer than %s...",
                     iso8601_encode($merge_point,1)));
            echo "<br />\n";
            while ($our = $ouriter->next()) {
                $name = $our->getName();
                if ($done[$name]) continue;
                $reaction = _(" skipped");
                $ext = wiki_xmlrpc_post('wiki.getPageInfo', $name, $args['url']);
                if (is_array($ext)) {
                    $extdate = iso8601_decode($ext['lastModified']->scalar,1);
                    $ourdate = $our->get('mtime');
                    if ($extdate < $ourdate and $extdate < $merge_point) {
                        $reaction = $this->_export($args, $our);
                    } elseif ($extdate < $ourdate and $extdate >= $merge_point) {
                        // our newer and external newer
                        $reaction = $this->_addConflict($args, $our, $extdate);
                    }
                } else {
                    $reaction = 'xmlrpc error';
                }
                PrintXML(HTML::strong($name)," ",
                         $extdate," < ",$ourdate," ",
                         HTML::strong($reaction),
                         HTML::br());
                $request->chunkOutput();
            }

            PrintXML(HTML::strong(fmt("Now upload all locally newer uploads.")));
            echo "<br />\n";
            PrintXML(fmt("Checking all local uploads newer than %s...",
                     iso8601_encode($merge_point,1)));
            echo "<br />\n";
            $this->_fileList = array();
            $prefix = getUploadFilePath();
            $this->_dir($prefix);
            $len = strlen($prefix);
            foreach ($this->_fileList as $path) {
                // strip prefix
                $file = substr($path,$len);
                $ourdate = filemtime($path);
                $oursize = filesize($path);
                $reaction = _(" skipped");
                $ext = wiki_xmlrpc_post('wiki.getUploadedFileInfo', $file, $args['url']);
                if (is_array($ext)) {
                    $extdate = iso8601_decode($ext['lastModified']->scalar,1);
                    $extsize = $ext['size'];
                    if (empty($extsize) or $extdate < $ourdate) {
                        $timeout = $oursize * 0.0002;  // assume 50kb/sec upload speed
                        $reaction = $this->_upload($args, $path, $timeout);
                    }
                } else {
                    $reaction = 'xmlrpc error wiki.getUploadedFileInfo not supported';
                }
                PrintXML(HTML::strong($name)," ",
                         "$extdate ($extsize) < $ourdate ($oursize)",
                         HTML::strong($reaction),
                         HTML::br());
                $request->chunkOutput();
            }
        }

        $dbh->set('mergepoint', time());
        EndLoadDump($request);
        return ''; //$html;
    }

    /* path must have ending slash */
    function _dir($path) {
        $dh = @opendir($path);
        while ($filename = readdir($dh)) {
            if ($filename[0] == '.')
                continue;
            $ft = filetype($path . $filename);
            if ($ft == 'file')
                array_push($this->_fileList, $path . $filename);
            else if ($ft == 'dir')
                $this->_dir($path . $filename . "/");
        }
        closedir($dh);
    }

    function _addConflict($what, $args, $our, $extdate = null) {
        $pagename = $our->getName();
        $meb = Button(array('action' => $args['action'],
                            'merge'=> true,
                            'source'=> $f),
                      _("Merge Edit"),
                      $args['pagename'],
                      'wikiadmin');
        $owb = Button(array('action' => $args['action'],
                            'overwrite'=> true,
                            'source'=> $f),
                      sprintf(_("%s force"), strtoupper(substr($what, 0, 1)).substr($what, 1)),
                      $args['pagename'],
                      'wikiunsafe');
        $this->_conflicts[] = $pagename;
        return HTML(fmt(_("Postponed %s for %s."), $what, $pagename), " ", $meb, " ", $owb);
    }

    // TODO: store log or checkpoint for restauration?
    function _import($args, $our, $extdate = null) {
        global $request;
        $reaction = 'import ';
        if ($args['noimport']) return ($reaction._("skipped"));
        //$userid = $request->_user->_userid;
        $name = $our->getName();
        $pagedata = wiki_xmlrpc_post('wiki.getPage', $name, $args['url']);
        if (is_object($pagedata)) {
            $pagedata = $pagedata->scalar;
            $ourrev  = $our->getCurrentRevision(true);
            $content = $ourrev->getPackedContent();
            if ($pagedata == $content)
                    return $reaction . _("skipped").' '._("same content");
            if (is_null($extdate))
                $extdate = time();
            $our->save(utf8_decode($pagedata), -1, array('author' => $userid,
                                                             'mtime' => $extdate));
            $reaction .= _("OK");
        } else
              $reaction .= (_("FAILED").' ('.gettype($pagedata).')');
        return $reaction;
    }

    // TODO: store log or checkpoint for restauration?
    function _export($args, $our) {
        global $request;
        $reaction = 'export ';
        if ($args['noexport']) return ($reaction._("skipped"));
        $userid  = $request->_user->_userid;
        $name    = $our->getName();
        $ourrev  = $our->getCurrentRevision(true);
        $content = $ourrev->getPackedContent();
        $extdata = wiki_xmlrpc_post('wiki.getPage', $name, $args['url']);
        if (is_object($extdata)) {
            $extdata = $extdata->scalar;
            if ($extdata == $content)
                    return $reaction . _("skipped").' '._("same content");
        }
        $mypass  = $request->getPref('passwd'); // this usually fails
        $success = wiki_xmlrpc_post('wiki.putPage',
                                    array($name, $content, $userid, $mypass), $args['url']);
        if (is_array($success)) {
            if ($success['code'] == 200)
                $reaction .= (_("OK").' '.$success['code']." ".$success['message']);
            else
                $reaction .= (_("FAILED").' '.$success['code']." ".$success['message']);
        } else
            $reaction .= (_("FAILED"));
        return $reaction;
    }

    // TODO: store log or checkpoint for restauration?
    function _upload($args, $path, $timeout) {
        global $request;
        $reaction = 'upload ';
        if ($args['noupload']) return ($reaction._("skipped"));

        //$userid  = $request->_user->_userid;
        $url = $args['url'];
        $url = str_replace("/RPC2.php","/index.php", $url);
        $server = parse_url($url);
        $http = new HttpClient($server['host'], $server['port']);
        $http->timeout = $timeout + 5;
        $success = $http->postfile($server['url'], $path);
        if ($success) {
            if ($http->getStatus() == 200)
                $reaction .= _("OK");
            else
                $reaction .= (_("FAILED").' '.$http->getStatus());
        } else
            $reaction .= (_("FAILED").' '.$http->getStatus()." ".$http->errormsg);
        return $reaction;
    }
};

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
