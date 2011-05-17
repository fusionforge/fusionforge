<?php // -*-php-*-
// rcs_id('$Id: _BackendInfo.php 7638 2010-08-11 11:58:40Z vargenau $');
/**
 * Copyright 1999,2000,2001,2002,2006,2007 $ThePhpWikiProgrammingTeam
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

require_once('lib/Template.php');
/**
 */
class WikiPlugin__BackendInfo
extends WikiPlugin
{
    function getName () {
        return _("DebugInfo");
    }

    function getDescription () {
        return sprintf(_("Get debugging information for %s."), '[pagename]');
    }

    function getDefaultArguments() {
        return array('page' => '[pagename]',
                     'notallversions' => 0);
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        extract($args);
        if (empty($page))
            return $this->error("page missing");

        $backend = &$dbi->_backend;
        $this->chunk_split = true;
        $this->readonly_pagemeta = array();
        $this->hidden_pagemeta = array ('_cached_html');

        $html = HTML(HTML::h3(fmt("Querying backend directly for '%s'",
                                  $page)));

        $table = HTML::table(array('border' => 1,
                                   'cellpadding' => 2,
                                   'cellspacing' => 0));
        $pagedata = $backend->get_pagedata($page);
        if (!$pagedata) {
            // FIXME: invalid HTML
            $html->pushContent(HTML::p(fmt("No pagedata for %s", $page)));
        }
        else {
            $this->_fixupData($pagedata);
            $table->pushContent($this->_showhash("get_pagedata('$page')", $pagedata));
        }
        if (!$notallversions) {
            $version = $backend->get_latest_version($page);
            $vdata = $backend->get_versiondata($page, $version, true);
            $this->_fixupData($vdata);
            $table->pushContent(HTML::tr(HTML::td(array('colspan' => 2))));
            $table->pushContent($this->_showhash("get_versiondata('$page',$version)",
                                                 $vdata));
        } else {
            for ($version = $backend->get_latest_version($page);
                 $version;
                 $version = $backend->get_previous_version($page, $version))
                {
                    $vdata = $backend->get_versiondata($page, $version, true);
                    $this->_fixupData($vdata);
                    $table->pushContent(HTML::tr(HTML::td(array('colspan' => 2))));
                    $table->pushContent($this->_showhash("get_versiondata('$page',$version)",
                                                         $vdata));
                }
        }

        $linkdata = $backend->get_links($page, false);
        if ($linkdata->count())
            $table->pushContent($this->_showhash("get_links('$page')", $linkdata->asArray()));
        $relations = $backend->get_links($page, false, false, false, false, false, true);
        if ($relations->count()) {
            $table->pushContent($this->_showhash("get_relations('$page')", array()));
            while ($rel = $relations->next())
                $table->pushContent($this->_showhash(false, $rel));
        }
        $linkdata = $backend->get_links($page, true);
        if ($linkdata->count())
            $table->pushContent($this->_showhash("get_backlinks('$page')", $linkdata->asArray()));

        $html->pushContent($table);
        return $html;
    }

    /**
     * Really should have a _fixupPagedata and _fixupVersiondata, but this works.
     * also used in plugin/EditMetaData
     */
    function _fixupData(&$data, $prefix='') {
        if (!is_array($data)) return;

        global $request;
        $user = $request->getUser();
        foreach ($data as $key => $val) {
            $fullkey = $prefix . '[' . $key . ']';
            if (is_integer($key)) {
                    ;
            } elseif ($key == 'passwd' and !$user->isAdmin()) {
                $data[$key] = $val ? _("<not displayed>") : _("<empty>");
            } elseif ($key and $key == '_cached_html') {
                $val = TransformedText::unpack($val);
                ob_start();
                print_r($val);
                $data[$key] = HTML::pre(ob_get_contents());
                ob_end_clean();
            }
            elseif (is_bool($val)) {
                    $data[$key] = $this->_showvalue($key, $val ? "true" : "false", $prefix);
            }
            elseif (is_string($val) && ((substr($val, 0, 2) == 'a:'
                                         or (substr($val, 0, 2) == 'O:'))))
            {
                // how to indent this table?
                $val = unserialize($val);
                $this->_fixupData($val, $fullkey);
                $data[$key] = HTML::table(array('border' => 1,
                                                'cellpadding' => 2,
                                                'cellspacing' => 0),
                                          $this->_showhash(false, $val, $fullkey));
            }
            elseif (is_array($val)) {
                // how to indent this table?
                $this->_fixupData($val, $fullkey);
                $data[$key] = HTML::table(array('border' => 1,
                                                'cellpadding' => 2,
                                                'cellspacing' => 0),
                                          $this->_showhash(false, $val, $fullkey));
            } elseif (is_object($val)) {
                // how to indent this table?
                ob_start();
                print_r($val);
                $val = HTML::pre(ob_get_contents());
                ob_end_clean();
                $data[$key] = HTML::table(array('border' => 1,
                                                'cellpadding' => 2,
                                                'cellspacing' => 0),
                                          $this->_showhash(false, $val, $fullkey));
            }
            elseif ($key and $key == '%content') {
                if ($val === true)
                    $val = '<true>';
                elseif (strlen($val) > 40)
                    $val = substr($val,0,40) . " ...";
                $data[$key] = $val;
            }
        }
        unset($data['%pagedata']); // problem in backend
    }

    /* also used in plugin/EditMetaData */
    function _showhash ($heading, $hash, $prefix='') {
        $rows = array();
        if ($heading)
            $rows[] = HTML::tr(array('bgcolor' => '#ffcccc',
                                     'style' => 'color:#000000'),
                               HTML::td(array('colspan' => 2,
                                              'style' => 'color:#000000'),
                                        $heading));
        if (!is_array($hash)) return array();
        ksort($hash);
        foreach ($hash as $key => $val) {
            if ($this->chunk_split and is_string($val)) $val = chunk_split($val);
            $rows[] = HTML::tr(HTML::td(array('align' => 'right',
                                              'bgcolor' => '#cccccc',
                                              'style' => 'color:#000000'),
                                        HTML(HTML::raw('&nbsp;'), $key,
                                             HTML::raw('&nbsp;'))),
                               HTML::td(array('bgcolor' => '#ffffff',
                                              'style' => 'color:#000000'),
                                        $this->_showvalue($key, $val, $prefix))
                               );
        }
        return $rows;
    }

    /* also used in plugin/EditMetaData */
    function _showvalue ($key, $val, $prefix='') {
        return $val ? $val : HTML::raw('&nbsp;');
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
