<?php // -*-php-*-
rcs_id('$Id: Template.php,v 1.9 2007/03/04 14:09:13 rurban Exp $');
/*
 Copyright 2005 $ThePhpWikiProgrammingTeam

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
 * Template: Parametrized blocks.
 *    Include text from a wiki page and replace certain placeholders by parameters.
 *    Similiar to CreatePage with the template argument, but at run-time.
 *    Similiar to the mediawiki templates but not with the "|" parameter seperator.
 * Usage:   <?plugin Template page=TemplateFilm vars="title=rurban&year=1999" ?>
 * Author:  Reini Urban
 * See also: http://meta.wikimedia.org/wiki/Help:Template
 *
 * Parameter expansion:
 *   vars="var1=value1&var2=value2"
 * We only support named parameters, not numbered ones as in mediawiki, and 
 * the placeholder is %%var%% and not {{{var}}} as in mediawiki.
 *
 * The following predefined variables are automatically expanded if existing:
 *   pagename
 *   mtime     - last modified date + time
 *   ctime     - creation date + time
 *   author    - last author
 *   owner     
 *   creator   - first author
 *   SERVER_URL, DATA_PATH, SCRIPT_NAME, PHPWIKI_BASE_URL and BASE_URL
 *
 * <noinclude> .. </noinclude> is stripped
 *
 * See also:
 * - ENABLE_MARKUP_TEMPLATE = true: (lib/InlineParser.php)
 *   Support a mediawiki-style syntax extension which maps 
 *     {{TemplateFilm|title=Some Good Film|year=1999}}
 *   to 
 *     <?plugin Template page=TemplateFilm vars="title=Some Good Film&year=1999" ?>
 */

class WikiPlugin_Template
extends WikiPlugin
{
    function getName() {
        return _("Template");
    }

    function getDescription() {
        return _("Parametrized page inclusion.");
    }

    function getVersion() {
        return preg_replace("/[Revision: $]/", '',
                            "\$Revision: 1.9 $");
    }

    function getDefaultArguments() {
        return array( 
                     'page'    => false, // the page to include
                     'vars'    => false, // TODO: get rid of this, all remaining args should be vars
                     'rev'     => false, // the revision (defaults to most recent)
                     'section' => false, // just include a named section
                     'sectionhead' => false // when including a named section show the heading
                     );
    }
    function allow_undeclared_arg($name, $value) {
    	// either just allow it or you can store it here away also. 
    	$this->vars[$name] = $value;
    	return $name != 'action';
    }
    // TODO: check if page can really be pulled from the args, or if it is just the basepage. 
    function getWikiPageLinks($argstr, $basepage) {
        $args = $this->getArgs($argstr);
	$page = @$args['page'];
        if ($page) {
            // Expand relative page names.
            $page = new WikiPageName($page, $basepage);
        }
        if (!$page or !$page->name)
            return false;
        return array(array('linkto' => $page->name, 'relation' => 0));
    }
                
    function run($dbi, $argstr, &$request, $basepage) {
    	$this->vars = array();
        $args = $this->getArgs($argstr, $request);
	$vars = $args['vars'] ? $args['vars'] : $this->vars;
	$page = $args['page'];
        if ($page) {
            // Expand relative page names.
            $page = new WikiPageName($page, $basepage);
            $page = $page->name;
        }
        if (!$page) {
            return $this->error(_("no page specified"));
        }

        // Protect from recursive inclusion. A page can include itself once
        static $included_pages = array();
        if (in_array($page, $included_pages)) {
            return $this->error(sprintf(_("recursive inclusion of page %s"),
                                        $page));
        }

        $p = $dbi->getPage($page);
        if ($args['rev']) {
            $r = $p->getRevision($args['rev']);
            if (!$r) {
                return $this->error(sprintf(_("%s(%d): no such revision"),
                                            $page, $args['rev']));
            }
        } else {
            $r = $p->getCurrentRevision();
        }
        $initial_content = $r->getPackedContent();
        $c = explode("\n", $initial_content);

        if ($args['section']) {
            $c = extractSection($args['section'], $c, $page, $quiet, $args['sectionhead']);
            $initial_content = implode("\n", $c);
        }

        if (preg_match('/<noinclude>.+<\/noinclude>/s', $initial_content)) {
            $initial_content = preg_replace("/<noinclude>.+?<\/noinclude>/s", "", 
                                            $initial_content);
        }
	$this->doVariableExpansion($initial_content, $vars, $basepage, $request);

        array_push($included_pages, $page);

        include_once('lib/BlockParser.php');
        $content = TransformText($initial_content, $r->get('markup'), $page);

        array_pop($included_pages);

        return HTML::div(array('class' => 'template'), $content);
    }

    /**
     * Expand template variables. Used by the TemplatePlugin and the CreatePagePlugin
     */
    function doVariableExpansion(&$content, $vars, $basepage, &$request) {
        if (preg_match('/%%\w+%%/', $content)) // need variable expansion
        {
            $dbi =& $request->_dbi;
            $var = array();
            if (is_string($vars) and !empty($vars)) {
                foreach (split("&",$vars) as $pair) {
                    list($key,$val) = split("=",$pair);
                    $var[$key] = $val;
                }
            } elseif (is_array($vars)) {
		$var =& $vars;
	    }
            $thispage = $dbi->getPage($basepage);
            // pagename and userid are not overridable
            $var['PAGENAME'] = $thispage->getName();
            if (preg_match('/%%USERID%%/', $content))
		$var['USERID'] = $request->_user->getId();
            if (empty($var['MTIME']) and preg_match('/%%MTIME%%/', $content)) {
                $thisrev  = $thispage->getCurrentRevision(false);
                $var['MTIME'] = $GLOBALS['WikiTheme']->formatDateTime($thisrev->get('mtime'));
            }
            if (empty($var['CTIME']) and preg_match('/%%CTIME%%/', $content)) {
                if ($first = $thispage->getRevision(1,false))
                    $var['CTIME'] = $GLOBALS['WikiTheme']->formatDateTime($first->get('mtime'));
            }
            if (empty($var['AUTHOR']) and preg_match('/%%AUTHOR%%/', $content))
                $var['AUTHOR'] = $thispage->getAuthor();
            if (empty($var['OWNER']) and preg_match('/%%OWNER%%/', $content))
                $var['OWNER'] = $thispage->getOwner();
            if (empty($var['CREATOR']) and preg_match('/%%CREATOR%%/', $content))
                $var['CREATOR'] = $thispage->getCreator();
            foreach (array("SERVER_URL", "DATA_PATH", "SCRIPT_NAME", "PHPWIKI_BASE_URL") as $c) {
                // constants are not overridable
                if (preg_match('/%%'.$c.'%%/', $content))
                    $var[$c] = constant($c);
            }
            if (preg_match('/%%BASE_URL%%/', $content))
                $var['BASE_URL'] = PHPWIKI_BASE_URL;

            foreach ($var as $key => $val) {
                //$content = preg_replace("/%%".preg_quote($key,"/")."%%/", $val, $content);
                $content = str_replace("%%".$key."%%", $val, $content);
            }
        }
	return $content;
    }
};

// $Log: Template.php,v $
// Revision 1.9  2007/03/04 14:09:13  rurban
// silence missing page warning
//
// Revision 1.8  2007/01/25 07:42:29  rurban
// Changed doVariableExpansion API. Uppercase default vars. Use str_replace.
//
// Revision 1.7  2007/01/04 16:42:41  rurban
// Improve vars passing. Use new method allow_undeclared_arg to allow arbitrary args for the template. Fix doVariableExpansion: use a ref. Fix pagename. Put away \b in regex.
//
// Revision 1.6  2007/01/03 21:24:06  rurban
// protect page in links. new doVariableExpansion() for CreatePage. preg_quote custom vars.
//
// Revision 1.5  2006/04/17 17:28:21  rurban
// honor getWikiPageLinks change linkto=>relation
//
// Revision 1.4  2005/09/11 13:30:22  rurban
// improve comments
//
// Revision 1.3  2005/09/10 20:43:19  rurban
// support <noinclude>
//
// Revision 1.2  2005/09/10 20:07:16  rurban
// fix BASE_URL
//
// Revision 1.1  2005/09/10 19:59:38  rurban
// Parametrized page inclusion ala mediawiki
//

// For emacs users
// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
