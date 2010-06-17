<?php // -*-php-*-
rcs_id('$Id: PluginManager.php,v 1.20 2007/01/03 21:23:57 rurban Exp $');
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

// Set this to true if you don't want regular users to view this page.
// So far there are no known security issues.
define('REQUIRE_ADMIN', false);

class WikiPlugin_PluginManager
extends WikiPlugin
{
    function getName () {
        return _("PluginManager");
    }

    function getDescription () {
        return _("Description: Provides a list of plugins on this wiki.");
    }

    function getVersion() {
        return preg_replace("/[Revision: $]/", '',
                            "\$Revision: 1.20 $");
    }

    function getDefaultArguments() {
        return array('info' => 'args');
    }

    function run($dbi, $argstr, &$request, $basepage) {
        extract($this->getArgs($argstr, $request));

        $h = HTML();
        $this->_generatePageheader($info, $h);

        if (! REQUIRE_ADMIN || $request->_user->isadmin()) {
            $h->pushContent(HTML::h2(_("Plugins")));

            $table = HTML::table(array('class' => "pagelist"));
            $this->_generateColgroups($info, $table);
            $this->_generateColheadings($info, $table);
            $this->_generateTableBody($info, $dbi, $request, $table);
            $h->pushContent($table);

            //$h->pushContent(HTML::h2(_("Disabled Plugins")));
        }
        else {
            $h->pushContent(fmt("You must be an administrator to %s.",
                                _("use this plugin")));
        }
        return $h;
    }

    function _generatePageheader(&$info, &$html) {
        $html->pushContent(HTML::p($this->getDescription()));
    }

    function _generateColgroups(&$info, &$table) {
        // specify last two column widths
        $colgroup = HTML::colgroup();
        $colgroup->pushContent(HTML::col(array('width' => '0*')));
        $colgroup->pushContent(HTML::col(array('width' => '0*',
                                               'align' => 'right')));
        $colgroup->pushContent(HTML::col(array('width' => '9*')));
        if ($info == 'args')
            $colgroup->pushContent(HTML::col(array('width' => '2*')));
        $table->pushcontent($colgroup);
    }

    function _generateColheadings(&$info, &$table) {
        // table headings
        $tr = HTML::tr();
        $headings = array(_("Plugin"), _("Version"), _("Description"));
        if ($info == 'args')
            $headings []= _("Arguments");
        foreach ($headings as $title) {
            $tr->pushContent(HTML::td($title));
        }
        $table->pushContent(HTML::thead($tr));
    }

    function _generateTableBody(&$info, &$dbi, &$request, &$table) {

        global $WikiTheme;

        $plugin_dir = 'lib/plugin';
        if (defined('PHPWIKI_DIR'))
            $plugin_dir = PHPWIKI_DIR . "/$plugin_dir";
        $pd = new fileSet($plugin_dir, '*.php');
        $plugins = $pd->getFiles();
        unset($pd);
        sort($plugins);

        // table body
        $tbody = HTML::tbody();
        $row_no = 0;

        $w = new WikiPluginLoader;
        foreach ($plugins as $pluginName) {
            // instantiate a plugin
            $pluginName = str_replace(".php", "", $pluginName);
            $temppluginclass = "<? plugin $pluginName ?>"; // hackish
            $p = $w->getPlugin($pluginName, false); // second arg?
            // trap php files which aren't WikiPlugin~s
            if (!strtolower(substr(get_parent_class($p), 0, 10)) == 'wikiplugin') {
                // Security: Hide names of extraneous files within
                // plugin dir from non-admins.
                if ($request->_user->isAdmin())
                    trigger_error(sprintf(_("%s does not appear to be a WikiPlugin."),
                                          $pluginName . ".php"));
                continue; // skip this non WikiPlugin file
            }
            $desc = $p->getDescription();
            $ver = $p->getVersion();
            $arguments = $p->getArgumentsDescription();
            unset($p); //done querying plugin object, release from memory

            // This section was largely improved by Pierrick Meignen:
            // make a link if an actionpage exists
            $pluginNamelink = $pluginName;
            $pluginDocPageName = _("Help").":" . $pluginName . "Plugin";

            $pluginDocPageNamelink = false;
            $localizedPluginName = '';
            $localizedPluginDocPageName = '';

            if($GLOBALS['LANG'] != "en"){
                if (_($pluginName) != $pluginName)
                    $localizedPluginName = _($pluginName);
                if($localizedPluginName && $dbi->isWikiPage($localizedPluginName))
                    $pluginDocPageNamelink = WikiLink($localizedPluginName,'if_known');
                
                if (_($pluginDocPageName) != $pluginDocPageName)
                    $localizedPluginDocPageName = _($pluginDocPageName);
                if($localizedPluginDocPageName && 
                   $dbi->isWikiPage($localizedPluginDocPageName))
                    $pluginDocPageNamelink = 
			WikiLink($localizedPluginDocPageName, 'if_known');
            }
            else {
                $pluginNamelink = WikiLink($pluginName, 'if_known');
                
                if ($dbi->isWikiPage($pluginDocPageName))
                    $pluginDocPageNamelink = WikiLink($pluginDocPageName,'if_known');
            }

            if (isa($WikiTheme, 'WikiTheme_gforge')) {
                $pluginDocPageNamelink = WikiLink($pluginDocPageName, 'known'); 
            }

            // highlight alternate rows
            $row_no++;
            $group = (int)($row_no / 1); //_group_rows
            $class = ($group % 2) ? 'evenrow' : 'oddrow';
            // generate table row
            $tr = HTML::tr(array('class' => $class));
            if ($pluginDocPageNamelink) {
                // plugin has a description page 'Help:' . 'PluginName' . 'Plugin'
                $tr->pushContent(HTML::td($pluginNamelink, HTML::br(),
                                          $pluginDocPageNamelink));
                $pluginDocPageNamelink = false;
            }
            else {
                // plugin just has an actionpage
                $tr->pushContent(HTML::td($pluginNamelink));
            }
            $tr->pushContent(HTML::td($ver), HTML::td($desc));
            if ($info == 'args') {
                // add Arguments column
                $style = array('style'
                               => 'font-family:monospace;font-size:smaller');
                $tr->pushContent(HTML::td($style, $arguments));
            }
            $tbody->pushContent($tr);
        }
        $table->pushContent($tbody);
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
