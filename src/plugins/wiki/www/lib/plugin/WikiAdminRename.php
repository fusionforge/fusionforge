<?php // -*-php-*-
rcs_id('$Id: WikiAdminRename.php 6301 2008-10-14 16:12:00Z vargenau $');
/*
 Copyright 2004,2005,2007 $ThePhpWikiProgrammingTeam
 Copyright 2008 Marc-Etienne Vargenau, Alcatel-Lucent

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

/**
 * Usage:   <?plugin WikiAdminRename ?> or called via WikiAdminSelect
 * @author:  Reini Urban <rurban@x-ray.at>
 *
 * KNOWN ISSUES:
 *   Requires PHP 4.2.
 */
require_once('lib/PageList.php');
require_once('lib/plugin/WikiAdminSelect.php');

class WikiPlugin_WikiAdminRename
extends WikiPlugin_WikiAdminSelect
{
    function getName() {
        return _("WikiAdminRename");
    }

    function getDescription() {
        return _("Rename selected pages");
    }

    function getVersion() {
        return preg_replace("/[Revision: $]/", '',
                            "\$Revision: 6301 $");
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),
             array(
		   's' 	=> false,
		   /* Columns to include in listing */
		   'info'     => 'pagename,mtime',
		   'updatelinks' => 0
		   ));
    }

    function renameHelper($name, $from, $to, $options = false) {
    	if ($options['regex'])
    	    return preg_replace('/'.$from.'/'.($options['icase']?'i':''), $to, $name);
    	elseif ($options['icase'])
    	    return str_ireplace($from, $to, $name);
    	else
            return str_replace($from, $to, $name);
    }

    function renamePages(&$dbi, &$request, $pages, $from, $to, $updatelinks=false) {
        $ul = HTML::ul();
        $count = 0;
        $post_args = $request->getArg('admin_rename');
        $options = array('regex' => @$post_args['regex'],
                         'icase' => @$post_args['icase']);
        foreach ($pages as $name) {
            if ( ($newname = $this->renameHelper($name, $from, $to, $options)) 
                 and $newname != $name )
            {
                if ($dbi->isWikiPage($newname))
                    $ul->pushContent(HTML::li(fmt("Page %s already exists. Ignored.",
                                                  WikiLink($newname))));
                elseif (! mayAccessPage('edit', $name))
                    $ul->pushContent(HTML::li(fmt("Access denied to rename page '%s'.",
                                                  WikiLink($name))));
                elseif ( $dbi->renamePage($name, $newname, $updatelinks)) {
                    /* not yet implemented for all backends */
                    $ul->pushContent(HTML::li(fmt("Renamed page '%s' to '%s'.",
                                                  $name, WikiLink($newname))));
                    $count++;
                } else {
                    $ul->pushContent(HTML::li(fmt("Couldn't rename page '%s' to '%s'.", 
                                                  $name, $newname)));
                }
            } else {
                $ul->pushContent(HTML::li(fmt("Couldn't rename page '%s' to '%s'.", 
                                              $name, $newname)));
            }
        }
        if ($count) {
            $dbi->touch();
            return HTML($ul, HTML::p(fmt("%s pages have been permanently renamed.",
                                         $count)));
        } else {
            return HTML($ul, HTML::p(fmt("No pages renamed.")));
        }
    }
    
    function run($dbi, $argstr, &$request, $basepage) {
    	$action = $request->getArg('action');
        if ($action != 'browse' and $action != 'rename' 
                                and $action != _("PhpWikiAdministration")."/"._("Rename"))
            return $this->disabled("(action != 'browse')");
        
        $args = $this->getArgs($argstr, $request);
        $this->_args = $args;
        $this->preSelectS($args, $request);

        $p = $request->getArg('p');
        if (!$p) $p = $this->_list;
        $post_args = $request->getArg('admin_rename');
        $next_action = 'select';
        $pages = array();
        if ($p && !$request->isPost())
            $pages = $p;
        if ($p && $request->isPost() &&
            !empty($post_args['rename']) && empty($post_args['cancel'])) {
            // without individual PagePermissions:
            if (!ENABLE_PAGEPERM and !$request->_user->isAdmin()) {
                $request->_notAuthorized(WIKIAUTH_ADMIN);
                $this->disabled("! user->isAdmin");
            }
            // DONE: error message if not allowed.
            if ($post_args['action'] == 'verify') {
                // Real action
                return $this->renamePages($dbi, $request, array_keys($p), 
                                          $post_args['from'], $post_args['to'], 
                                          !empty($post_args['updatelinks']));
            }
            if ($post_args['action'] == 'select') {
                if (!empty($post_args['from']))
                    $next_action = 'verify';
                foreach ($p as $name => $c) {
                    $pages[$name] = 1;
                }
            }
        }
        if ($next_action == 'select' and empty($pages)) {
            // List all pages to select from.
            $pages = $this->collectPages($pages, $dbi, $args['sortby'], $args['limit'], $args['exclude']);
        }
        /*if ($next_action == 'verify') {
            $args['info'] = "checkbox,pagename,renamed_pagename";
        }*/
        $pagelist = new PageList_Selectable
            (
             $args['info'], $args['exclude'],
             array('types' => 
                   array('renamed_pagename'
                         => new _PageList_Column_renamed_pagename('rename', _("Rename to")),
                         )));
        $pagelist->addPageList($pages);

        $header = HTML::div();
        if ($next_action == 'verify') {
            $button_label = _("Yes");
            $header->pushContent(
              HTML::p(HTML::strong(
                _("Are you sure you want to permanently rename the selected pages?"))));
            $header = $this->renameForm($header, $post_args);
        }
        else {
            $button_label = _("Rename selected pages");
            if (!$post_args and count($pages) == 1) {
                list($post_args['from'],) = array_keys($pages);
                $post_args['to'] = $post_args['from'];
            }
            $header = $this->renameForm($header, $post_args);
            $header->pushContent(HTML::p(_("Select the pages to rename:")));
        }

        $buttons = HTML::p(Button('submit:admin_rename[rename]', $button_label, 'wikiadmin'),
			   HTML::Raw('&nbsp;&nbsp;'),
                           Button('submit:admin_rename[cancel]', _("Cancel"), 'button'));

        return HTML::form(array('action' => $request->getPostURL(),
                                'method' => 'post'),
                          $header,
                          $buttons,
                          $pagelist->getContent(),
                          HiddenInputs($request->getArgs(),
                                        false,
                                        array('admin_rename')),
                          HiddenInputs(array('admin_rename[action]' => $next_action)),
                          ENABLE_PAGEPERM
                          ? ''
                          : HiddenInputs(array('require_authority_for_post' => WIKIAUTH_ADMIN)));
    }

    function checkBox (&$post_args, $name, $msg) {
    	$id = 'admin_rename-'.$name;
    	$checkbox = HTML::input(array('type' => 'checkbox',
                                      'name' => 'admin_rename['.$name.']',
                                      'id'   => $id,
                                      'value' => 1));
        if (!empty($post_args[$name]))
            $checkbox->setAttr('checked', 'checked');
        return HTML::div($checkbox, ' ', HTML::label(array('for' => $id), $msg));
    }

    function renameForm(&$header, $post_args) {
        $table = HTML::table();
        $this->_tablePush($table, _("Rename"). " ". _("from").': ',
			  HTML::input(array('name' => 'admin_rename[from]',
					    'size' => 90,
					    'value' => $post_args['from'])));
        $this->_tablePush($table, _("to").': ',
			  HTML::input(array('name' => 'admin_rename[to]',
					    'size' => 90,
					    'value' => $post_args['to'])));
	$this->_tablePush($table, '', $this->checkBox($post_args, 'regex', _("Regex?")));
        $this->_tablePush($table, '', $this->checkBox($post_args, 'icase', _("Case insensitive?")));
	if (DEBUG) // not yet stable
	    $this->_tablePush($table, '', $this->checkBox($post_args, 'updatelinks', 
						      _("Change pagename in all linked pages also?")));
        $header->pushContent($table);
        return $header;
    }
}

// TODO: grey out unchangeble pages, even in the initial list also?
// TODO: autoselect by matching name javascript in admin_rename[from]
// TODO: update rename[] fields when case-sensitive and regex is changed

// moved from lib/PageList.php
class _PageList_Column_renamed_pagename extends _PageList_Column {
    function _getValue ($page_handle, &$revision_handle) {
        global $request;
        $post_args = $request->getArg('admin_rename');
        $options = array('regex' => @$post_args['regex'],
                         'icase' => @$post_args['icase']);
                         
        $value = $post_args ? WikiPlugin_WikiAdminRename::renameHelper($page_handle->getName(), 
                                                          $post_args['from'], $post_args['to'],
                                                          $options)
                            : $page_handle->getName();                              
        $div = HTML::div(" => ",HTML::input(array('type' => 'text',
                                                  'name' => 'rename[]',
                                                  'value' => $value)));
        $new_page = $request->getPage($value);
        return $div;
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
