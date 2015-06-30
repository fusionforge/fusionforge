<?php

/**
 * Copyright 1999,2000,2001,2002,2007 $ThePhpWikiProgrammingTeam
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
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Plugin EditMetaData
 *
 * This plugin shows the current page-level metadata and gives an
 * entry box for adding a new field or changing an existing one. (A
 * field can be deleted by specifying a blank value.) Certain fields,
 * such as 'hits' cannot be changed.
 *
 * If there is a reason to do so, I will add support for revision-
 * level metadata as well.
 *
 * Access by restricted to ADMIN_USER
 *
 * Written by Michael Van Dam, to test out some ideas about
 * PagePermissions and PageTypes.
 *
 * Rewritten for recursive array support by Reini Urban.
 */

require_once 'lib/plugin/DebugBackendInfo.php';

class WikiPlugin_EditMetaData
    extends WikiPlugin_DebugBackendInfo
{
    public $_args;

    function getDescription()
    {
        return sprintf(_("Edit metadata for %s."), '[pagename]');
    }

    function getDefaultArguments()
    {
        return array('page' => '[pagename]');
    }

    /**
     * @param WikiDB $dbi
     * @param string $argstr
     * @param WikiRequest $request
     * @param string $basepage
     * @return mixed
     */
    function run($dbi, $argstr, &$request, $basepage)
    {
        $this->_args = $this->getArgs($argstr, $request);
        extract($this->_args);
        if (!$page)
            return '';

        $this->hidden_pagemeta = array('_cached_html');
        $this->readonly_pagemeta = array('hits', 'passwd');
        $dbi = $request->getDbh();
        $p = $dbi->getPage($page);
        $pagemeta = $p->getMetaData();
        $this->chunk_split = false;

        // Look at arguments to see if submit was entered. If so,
        // process this request before displaying.
        //
        if ($request->isPost()
            and $request->_user->isAdmin()
                and $request->getArg('metaedit')
        ) {
            $metafield = trim($request->getArg('metafield'));
            $metavalue = trim($request->getArg('metavalue'));
            $meta = $request->getArg('meta');
            $changed = 0;
            // meta[__global[_upgrade][name]] => 1030.13
            foreach ($meta as $key => $val) {
                if ($val != $pagemeta[$key]
                    and !in_array($key, $this->readonly_pagemeta)
                ) {
                    $changed++;
                    $p->set($key, $val);
                }
            }
            if ($metafield and !in_array($metafield, $this->readonly_pagemeta)) {
                // __global[_upgrade][name] => 1030.13
                if (preg_match('/^(.*?)\[(.*?)\]$/', $metafield, $matches)) {
                    list(, $array_field, $array_key) = $matches;
                    $array_value = $pagemeta[$array_field];
                    $array_value[$array_key] = $metavalue;
                    if ($pagemeta[$array_field] != $array_value) {
                        $changed++;
                        $p->set($array_field, $array_value);
                    }
                } elseif ($pagemeta[$metafield] != $metavalue) {
                    $changed++;
                    $p->set($metafield, $metavalue);
                }
            }
            if ($changed) {
                $dbi->touch();
                $url = $request->getURLtoSelf(array(),
                    array('meta', 'metaedit', 'metafield', 'metavalue'));
                $request->redirect($url);
                // The rest of the output will not be seen due to the
                // redirect.
                return '';
            }
        }

        // Now we show the meta data and provide entry box for new data.
        $html = HTML();
        if (!$pagemeta) {
            // FIXME: invalid HTML
            $html->pushContent(HTML::p(fmt("No metadata for %s", $page)));
            $table = HTML();
        } else {
            $table = HTML::table(array('class' => 'bordered'));
            $this->_fixupData($pagemeta);
            $table->pushContent($this->_showhash("MetaData('$page')", $pagemeta));
        }

        if ($request->_user->isAdmin()) {
            $action = $request->getPostURL();
            $hiddenfield = HiddenInputs($request->getArgs());
            $instructions = _("Add or change a page-level metadata 'key=>value' pair. Note that you can remove a key by leaving the value-box empty.");
            $keyfield = HTML::input(array('name' => 'metafield'), '');
            $valfield = HTML::input(array('name' => 'metavalue'), '');
            $button = Button('submit:metaedit', _("Submit"), false);
            $form = HTML::form(array('action' => $action,
                    'method' => 'post',
                    'accept-charset' => 'UTF-8'),
                $hiddenfield,
                // edit existing fields
                $table,
                // add new ones
                $instructions, HTML::br(),
                $keyfield, ' => ', $valfield,
                HTML::raw('&nbsp;'), $button
            );

            $html->pushContent($form);
        } else {
            $html->pushContent(HTML::em(_("Requires WikiAdmin privileges to edit.")));
        }
        return $html;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
