<?php // -*-php-*-
// rcs_id('$Id: WikiAdminSelect.php 7447 2010-05-31 11:29:39Z vargenau $');
/*
 * Copyright 2002 $ThePhpWikiProgrammingTeam
 * Copyright 2008-2009 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * Allows selection of multiple pages which get passed to other
 * WikiAdmin plugins then. Then do Rename, Remove, Chmod, Chown, ...
 *
 * Usage:   <<WikiAdminSelect>>
 * Author:  Reini Urban <rurban@x-ray.at>
 *
 * This is the base class for most WikiAdmin* classes, using
 * collectPages() and preSelectS().
 * "list" PagePermissions supported implicitly by PageList.
 */
require_once('lib/PageList.php');

class WikiPlugin_WikiAdminSelect
extends WikiPlugin
{
    function getName() {
        return _("WikiAdminSelect");
    }

    function getDescription() {
        return _("Allows selection of multiple pages which get passed to other WikiAdmin plugins.");
    }

    function getDefaultArguments() {
        return array('s'       => '', // preselect pages
                     /* select pages by meta-data: */
                     'author'   => false,
                     'owner'    => false,
                     'creator'  => false,
                     'only'    => '',
                     'exclude' => '',
                     'info'    => 'most',
                     'sortby'  => 'pagename',
                     'limit'   => 0,
                     'paging'  => 'none'
                    );
    }

    /**
     * Default collector for all WikiAdmin* plugins.
     * preSelectS() is similar, but fills $this->_list
     */
    function collectPages(&$list, &$dbi, $sortby, $limit=0, $exclude='') {
        $allPages = $dbi->getAllPages(0, $sortby, $limit, $exclude);
        while ($pagehandle = $allPages->next()) {
            $pagename = $pagehandle->getName();
            if (empty($list[$pagename]))
                $list[$pagename] = 0;
        }
        return $list;
    }

    /**
     * Preselect a list of pagenames by the supporting the following args:
     * 's': comma-seperated list of pagename wildcards
     * 'author', 'owner', 'creator': from WikiDB_Page
     * 'only: forgot what the difference to 's' was.
     * Sets $this->_list, which is picked up by collectPages() and is a default for p[]
     */
    function preSelectS (&$args, &$request) {
        // override plugin argument by GET: probably not needed if s||="" is used
        // anyway, we force it for unique interface.
        if (!empty($request->getArg['s']))
            $args['s'] = $request->getArg['s'];
        if ( !empty($args['owner']) )
            $sl = PageList::allPagesByOwner($args['owner'],false,$args['sortby'],$args['limit'],$args['exclude']);
        elseif ( !empty($args['author']) )
            $sl = PageList::allPagesByAuthor($args['author'],false,$args['sortby'],$args['limit'],$args['exclude']);
        elseif ( !empty($args['creator']) )
            $sl = PageList::allPagesByCreator($args['creator'],false,$args['sortby'],$args['limit'],$args['exclude']);
        elseif ( !empty($args['s']) or !empty($args['only']) ) {
            // all pages by name
            $sl = explodePageList(empty($args['only']) ? $args['s'] : $args['only']);
        }
        $this->_list = array();
        if (!empty($sl)) {
            $request->setArg('verify', 1);
            foreach ($sl as $name) {
                if (!empty($args['exclude'])) {
                    if (!in_array($name, $args['exclude']))
                        $this->_list[$name] = 1;
                } else {
                    $this->_list[$name] = 1;
                }
            }
        }
        return $this->_list;
    }

    function run($dbi, $argstr, &$request, $basepage) {
        //if ($request->getArg('action') != 'browse')
        //    return $this->disabled("(action != 'browse')");
        $args = $this->getArgs($argstr, $request);
        $this->_args = $args;
        extract($args);
        $this->preSelectS($args, $request);

        $info = $args['info'];

        // array_multisort($this->_list, SORT_NUMERIC, SORT_DESC);
        $pagename = $request->getArg('pagename');
        // GetUrlToSelf() with all given params
        //$uri = $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI']; // without s would be better.
        //$uri = $request->getURLtoSelf();//false, array('verify'));
        $form = HTML::form(array('action' => $request->getPostURL(), 'method' => 'post'));
        if ($request->getArg('WikiAdminSelect') == _("Go"))
            $p = false;
        else
            $p = $request->getArg('p');
        //$p = @$GLOBALS['HTTP_POST_VARS']['p'];
        $form->pushContent(HTML::p(array('class' => 'wikitext'), _("Select: "),
                                   HTML::input(array('type' => 'text',
                                                     'name' => 's',
                                                     'value' => $args['s'])),
                                   HTML::input(array('type' => 'submit',
                                                     'name' => 'WikiAdminSelect',
                                                     'value' => _("Go")))));
        if (! $request->getArg('verify')) {
            $form->pushContent(HTML::input(array('type' => 'hidden',
                                                 'name' => 'action',
                                                 'value' => 'verify')));
            $form->pushContent(Button('submit:verify', _("Select pages"),
                                      'wikiadmin'),
                               Button('submit:cancel', _("Cancel"), 'button'));
        } else {
            global $WikiTheme;
            $form->pushContent(HTML::input(array('type' => 'hidden',
                                                 'name' => 'action',
                                                 'value' => 'WikiAdminSelect'))
                               );
            // Add the Buttons for all registered WikiAdmin plugins
            $plugin_dir = 'lib/plugin';
            if (defined('PHPWIKI_DIR'))
                $plugin_dir = PHPWIKI_DIR . "/$plugin_dir";
            $fs = new fileSet($plugin_dir, 'WikiAdmin*.php');
            $actions = $fs->getFiles();
            sort($actions);
            foreach ($actions as $f) {
                $f = preg_replace('/.php$/','', $f);
                $s = preg_replace('/^WikiAdmin/','', $f);
                if (!in_array($s,array("Select","Utils"))) { // disable Select and Utils
                    $form->pushContent(Button("submit:wikiadmin[$f]", _($s), "wikiadmin"));
                    $form->pushContent($WikiTheme->getButtonSeparator());
                }
            }
            // $form->pushContent(Button('submit:cancel', _("Cancel"), 'button'));
        }

        if ($request->isPost()
            && ! $request->getArg('wikiadmin')
            && !empty($p)) {
            $this->_list = array();
            // List all selected pages again.
            foreach ($p as $page => $name) {
                $this->_list[$name] = 1;
            }
        }
        elseif ($request->isPost()
                and $request->_user->isAdmin()
                and !empty($p)
                //and $request->getArg('verify')
                and ($request->getArg('action') == 'WikiAdminSelect')
                and $request->getArg('wikiadmin')
               )
        {
            // handle external plugin
            $loader = new WikiPluginLoader();
            $a = array_keys($request->getArg('wikiadmin'));
            $plugin_action = $a[0];
            $single_arg_plugins = array("Remove");
            if (in_array($plugin_action, $single_arg_plugins)) {
                $plugin = $loader->getPlugin($plugin_action);
                $ul = HTML::ul();
                foreach ($p as $page => $name) {
                    $plugin_args = "run_page=$name";
                    $request->setArg($plugin_action, 1);
                    $request->setArg('p', array($page => $name));
                    // if the plugin requires more args than the pagename,
                    // then this plugin will not return. (Rename, SearchReplace, ...)
                    $action_result = $plugin->run($dbi, $plugin_args, $request, $basepage);
                    $ul->pushContent(HTML::li(fmt("Selected page '%s' passed to '%s'.",
                                                  $name, $select)));
                    $ul->pushContent(HTML::ul(HTML::li($action_result)));
                }
            } else {
                // redirect to the plugin page.
                // in which page is this plugin?
                $plugin_action = preg_replace("/^WikiAdmin/","",$plugin_action);
                $args = array();
                foreach ($p as $page => $x) {
                  $args["p[$page]"] = 1;
                }
                header("Location: ".
                  WikiURL(_("PhpWikiAdministration")."/"._($plugin_action),$args,1));
                exit();
            }
        } elseif (empty($args['s'])) {
            // List all pages to select from.
            $this->_list = $this->collectPages($this->_list, $dbi, $args['sortby'], $args['limit']);
        }
        $pagelist = new PageList_Selectable($info, $args['exclude'], $args);
        $pagelist->addPageList($this->_list);
        $form->pushContent($pagelist->getContent());
        foreach ($args as $k => $v) {
            if (!in_array($k,array('s','WikiAdminSelect','action','verify')))
                $form->pushContent(HiddenInputs(array($k => $v))); // plugin params
        }
        if (! $request->getArg('select')) {
            return $form;
        } else {
            ; //return $action_result;
        }
    }

    function _tablePush(&$table, $first, $second) {
        $table->pushContent(
                            HTML::tr(
                                     HTML::td($first),
                                     HTML::td($second)));
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
