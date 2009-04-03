<?php // -*-php-*-
rcs_id('$Id: CreatePage.php 6185 2008-08-22 11:40:14Z vargenau $');
/**
 Copyright 2004,2007 $ThePhpWikiProgrammingTeam

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
 * This allows you to create a page getting the new pagename from a 
 * forms-based interface, and optionally with the initial content from 
 * some template, plus expansion of some variables via %%variable%% statements 
 * in the template.
 *
 * Put <?plugin-form CreatePage ?> at some page, browse this page, 
 * enter the name of the page to create, then click the button.
 *
 * Usage: <?plugin-form CreatePage template=SomeTemplatePage vars="year=2004&name=None" ?>
 * @authors: Dan Frankowski, Reini Urban
 */

include_once("lib/plugin/Template.php");

class WikiPlugin_CreatePage
extends WikiPlugin_Template
{
    function getName() {
        return _("CreatePage");
    }

    function getDescription() {
        return _("Create a Wiki page by the provided name.");
    }

    function getVersion() {
        return preg_replace("/[Revision: $]/", '',
                            "\$Revision: 6185 $");
    }

    function getDefaultArguments() {
        return array('s'            => false,
                     'initial_content' => '',
                     'template'     => false,
                     'vars'         => false,
                     'overwrite'    => false,
                     //'buttontext' => false,
                     //'method'     => 'POST'
                     );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        extract($this->getArgs($argstr, $request));
        // Prevent spaces at the start and end of a page name
        $s = trim($s);
        if (!$s) {
            return $this->error(_("Cannot create page with empty name!"));
	}
	// TODO: javascript warning if "/" or SUBPAGE_SEPARATOR in s

        $param = array('action' => 'edit');
        if ($template and $dbi->isWikiPage($template)) {
            $param['template'] = $template;
        } elseif (!empty($initial_content)) { 
            // Warning! Potential URI overflow here on the GET redirect. Better use template.
            $param['initial_content'] = $initial_content;
        }
        // If the initial_content is too large, pre-save the content in the page 
        // and redirect without that argument.
        // URI length limit:
        //   http://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.2.1
        $url = WikiURL($s, $param, 'absurl');
        // FIXME: expand vars in templates here.
        if (strlen($url) > 255 
            or ($param['template'])
            or preg_match('/%%\w+%%/', $initial_content)) // need variable expansion
        {
            unset($param['initial_content']);
            $url = WikiURL($s, $param, 'absurl');
            $page = $dbi->getPage($s);
            $current = $page->getCurrentRevision();
            $version = $current->getVersion();
	    // overwrite empty (deleted) pages
            if ($version and !$current->hasDefaultContents() and !$overwrite) {
                return $this->error(fmt("%s already exists", WikiLink($s)));
            } else {
                $user = $request->getUser();
                $meta = array('markup' => 2.0,
                              'author' => $user->getId());
                if (!empty($param['template']) and !$initial_content) {
                    $tmplpage = $dbi->getPage($template);
                    $currenttmpl = $tmplpage->getCurrentRevision();
                    $initial_content = $currenttmpl->getPackedContent();
                    $meta['markup'] = $currenttmpl->_data['markup'];

		    if (preg_match('/<noinclude>.+<\/noinclude>/s', $initial_content)) {
			$initial_content = preg_replace("/<noinclude>.+?<\/noinclude>/s", "", 
							$initial_content);
		    }
                }
                $meta['summary'] = _("Created by CreatePage");
		$content = $this->doVariableExpansion($initial_content, $vars, $s, $request);

		if ($content !== $initial_content) {
                    // need to destroy the template so that editpage doesn't overwrite it.
                    unset($param['template']);
                    $url = WikiURL($s, $param, 'absurl');
                }

                $page->save($content, $version+1, $meta);
            }
        }
        return HTML($request->redirect($url, true));
    }
};

// $Log: not supported by cvs2svn $
// Revision 1.12  2007/08/10 22:03:34  rurban
// Cannot create page with empty name
//
// Revision 1.11  2007/01/27 21:36:52  rurban
// Overwrite empty or deleted pages
//
// Revision 1.10  2007/01/25 07:42:16  rurban
// Changed doVariableExpansion API.
//
// Revision 1.9  2007/01/04 16:42:23  rurban
// Expand even if no vars are given. They may be defaults, i.e %%pagename%%
//
// Revision 1.8  2007/01/03 21:23:32  rurban
// Derive from Template. Use same variable expansion. Support <noinclude> as in Template.
//
// Revision 1.7  2004/09/06 10:22:15  rurban
// oops, forgot global request
//
// Revision 1.6  2004/09/06 08:35:32  rurban
// support template variables (not yet working)
//
// Revision 1.5  2004/07/08 20:30:07  rurban
// plugin->run consistency: request as reference, added basepage.
// encountered strange bug in AllPages (and the test) which destroys ->_dbi
//
// Revision 1.4  2004/04/21 16:14:50  zorloc
// Prevent spaces at the start and end of a created page name -- submitted by Dan Frankowski (dfrankow).
//
// Revision 1.3  2004/03/24 19:41:04  rurban
// fixed the name
//
// Revision 1.2  2004/03/17 15:37:41  rurban
// properly support initial_content and template with URI length overflow workaround
//
// Revision 1.3  2004/03/16 16:25:05  dfrankow
// Support initial_content parameter
//
// Revision 1.2  2004/03/09 16:28:45  dfrankow
// Merge the RATING branch onto the main line
//
// Revision 1.1  2004/03/08 18:57:59  rurban
// Allow WikiForm overrides, such as method => POST, targetpage => [pagename]
// in the plugin definition.
// New simple CreatePage plugin by dfrankow.
//
// Revision 1.1.2.2  2004/02/23 21:22:29  dfrankow
// Add a little doc
//
// Revision 1.1.2.1  2004/02/21 15:29:19  dfrankow
// Allow a CreatePage edit box, as GUI syntactic sugar
//
// Revision 1.1.1.1  2004/01/29 14:30:28  dfrankow
// Right out of the 1.3.7 package
//

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
