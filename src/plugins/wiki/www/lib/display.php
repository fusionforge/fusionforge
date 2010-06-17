<?php
// display.php: fetch page or get default content
rcs_id('$Id: display.php 6184 2008-08-22 10:33:41Z vargenau $');

require_once('lib/Template.php');

/**
 * Extract keywords from Category* links on page. 
 */
function GleanKeywords ($page) {
    if (!defined('KEYWORDS')) return '';
    require_once("lib/TextSearchQuery.php");
    $search = new TextSearchQuery(KEYWORDS, true);
    $KeywordLinkRegexp = $search->asRegexp();
    // iterate over the pagelinks (could be a large number) [15ms on PluginManager]
    // or do a titleSearch and check the categories if they are linked?
    $links = $page->getPageLinks();
    $keywords[] = SplitPagename($page->getName());
    while ($link = $links->next()) {
    	$m = array();
        if (preg_match($KeywordLinkRegexp, $link->getName(), $m))
            $keywords[] = SplitPagename($m[0]);
    }
    $keywords[] = WIKI_NAME;
    return join(', ', $keywords);
}

/** Make a link back to redirecting page.
 *
 * @param $pagename string  Name of redirecting page.
 * @return XmlContent Link to the redirecting page.
 */
function RedirectorLink($pagename) {
    $url = WikiURL($pagename, array('redirectfrom' => ''));
    return HTML::a(array('class' => 'redirectfrom wiki',
                         'href' => $url),
                   $pagename);
}

    
function actionPage(&$request, $action) {
    global $WikiTheme;

    $pagename = $request->getArg('pagename');
    $version = $request->getArg('version');

    $page = $request->getPage();
    $revision = $page->getCurrentRevision();

    $dbi = $request->getDbh();
    $actionpage = $dbi->getPage($action);
    $actionrev = $actionpage->getCurrentRevision();

    $pagetitle = HTML(fmt("%s: %s", 
                          $actionpage->getName(),
                          $WikiTheme->linkExistingWikiWord($pagename, false, $version)));

    $request->setValidators(array('pageversion' => $revision->getVersion(),
                                  '%mtime' => $revision->get('mtime')));
    $request->appendValidators(array('pagerev' => $revision->getVersion(),
                                     '%mtime' => $revision->get('mtime')));
    $request->appendValidators(array('actionpagerev' => $actionrev->getVersion(),
                                     '%mtime' => $actionrev->get('mtime')));

    $transformedContent = $actionrev->getTransformedContent();
 
   /* Optionally tell google (and others) not to take notice of action pages.
      RecentChanges or AllPages might be an exception.
   */
    $args = array();
    if (GOOGLE_LINKS_NOFOLLOW)
	$args = array('ROBOTS_META' => "noindex,nofollow");

    /* Handle other formats: So far we had html only.
       xml is requested by loaddump, rss is handled by recentchanges, 
       pdf is a special action, but should be a format to dump multiple pages
       if the actionpage plugin returns a pagelist.
       rdf and owl are handled by SemanticWeb.
    */
    $format = $request->getArg('format');
    
    /* At first the single page formats: html, xml */
    if ($pagename == _("LinkDatabase")) {
        $template = Template('browse', array('CONTENT' => $transformedContent));
	GeneratePage($template, $pagetitle, $revision, $args);
    } elseif (!$format or $format == 'html' or $format == 'sidebar' or $format == 'contribs') {
	$template = Template('browse', array('CONTENT' => $transformedContent));
	GeneratePage($template, $pagetitle, $revision, $args);
    } elseif ($format == 'xml') {
	$template = new Template('browse', $request,
                                 array('revision' => $revision,
                                       'CONTENT'  => $transformedContent,
				       ));
	$html = GeneratePageAsXML($template, $pagename, $revision /*, 
				  array('VALID_LINKS' => $args['VALID_LINKS'])*/);
	header("Content-Type: application/xhtml+xml; charset=" . $GLOBALS['charset']);
	echo $html;
    } else {
    	$pagelist = null;
    	require_once('lib/WikiPlugin.php');
	// Then the multi-page formats
	// rss (if not already handled by RecentChanges)
	// Need the pagelist from the first plugin
	foreach($transformedContent->_content as $cached_element) {
	    if (is_a($cached_element, "Cached_PluginInvocation")) {
	        $loader = new WikiPluginLoader;
	        $markup = null;
	        // return the first found pagelist
	        $pagelist = $loader->expandPI($cached_element->_pi, $request, 
	                                      $markup, $pagename);
	        if (is_a($pagelist, 'PageList'))
	            break;
	    }
	}
        $args['VALID_LINKS'] = array($pagename);
        if (!$pagelist or !is_a($pagelist, 'PageList')) {
	    if (!in_array($format, array("rss91","rss2","rss","atom","rdf")))
		trigger_error(sprintf("Format %s requires an actionpage returning a pagelist.", 
				      $format)
			      ."\n".("Fall back to single page mode"), E_USER_WARNING);
	    require_once('lib/PageList.php');
	    $pagelist = new PageList();
	    $pagelist->addPage($page);
	} else {
            foreach ($pagelist->_pages as $page) {
            	$name = $page->getName();
            	if ($name != $pagename and $page->exists())
                    $args['VALID_LINKS'][] = $name;
            }
	}
	if ($format == 'pdf') {
	    require_once("lib/pdf.php");
	    ConvertAndDisplayPdfPageList($request, $pagelist, $args);
	}
	elseif ($format == 'ziphtml') { // need to fix links
	    require_once('lib/loadsave.php');
	    $request->setArg('zipname', FilenameForPage($pagename).".zip");
	    $request->setArg('pages', $args['VALID_LINKS']);
	    MakeWikiZipHtml($request);
	} // time-sorted RDF á la RecentChanges 
	elseif (in_array($format, array("rss91","rss2","rss","atom"))) {
            $args = $request->getArgs();
            if ($pagename == _("RecentChanges")) {
                $template->printExpansion($args);
	    } else {
	        require_once("lib/plugin/RecentChanges.php");
	        $plugin = new WikiPlugin_RecentChanges();
                return $plugin->format($plugin->getChanges($request->_dbi, $args), $args);
	    }
	} elseif ($format == 'rdf') { // all semantic relations and attributes
	    require_once("lib/SemanticWeb.php");
	    $rdf = new RdfWriter($request, $pagelist);
	    $rdf->format();
	} elseif ($format == 'rdfs') {
	    require_once("lib/SemanticWeb.php");
	    $rdf = new RdfsWriter($request, $pagelist);
	    $rdf->format();
	} elseif ($format == 'owl') { // or daml?
	    require_once("lib/SemanticWeb.php");
	    $rdf = new OwlWriter($request, $pagelist);
	    $rdf->format();
	} else {
	    if (!in_array($pagename, array(_("LinkDatabase"))))
		trigger_error(sprintf(_("Unsupported argument: %s=%s"),"format",$format),
	    	              E_USER_WARNING);
	    $template = Template('browse', array('CONTENT' => $transformedContent));
	    GeneratePage($template, $pagetitle, $revision, $args);
	}
    }
    $request->checkValidators();
    flush();
    return '';
}

function displayPage(&$request, $template=false) {
    global $WikiTheme;
    $pagename = $request->getArg('pagename');
    $version = $request->getArg('version');
    $page = $request->getPage();
    if ($version) {
        $revision = $page->getRevision($version);
        if (!$revision)
            NoSuchRevision($request, $page, $version);
        /* Tell Google (and others) to ignore old versions of pages */
	$toks['ROBOTS_META'] = "noindex,nofollow";
    }
    else {
        $revision = $page->getCurrentRevision();
    }

    if (isSubPage($pagename)) {
        $pages = explode(SUBPAGE_SEPARATOR, $pagename);
        $last_page = array_pop($pages); // deletes last element from array as side-effect
        $pageheader = HTML::span(HTML::a(array('href' => WikiURL($pages[0]),
                                               'class' => 'pagetitle'
                                              ),
                                        $WikiTheme->maybeSplitWikiWord($pages[0] . SUBPAGE_SEPARATOR)));
        $first_pages = $pages[0] . SUBPAGE_SEPARATOR;
        array_shift($pages);
        foreach ($pages as $p)  {
            $pageheader->pushContent(HTML::a(array('href' => WikiURL($first_pages . $p),
                                                  'class' => 'backlinks'),
                                            $WikiTheme->maybeSplitWikiWord($p . SUBPAGE_SEPARATOR)));
            $first_pages .= $p . SUBPAGE_SEPARATOR;
        }
        $backlink = HTML::a(array('href' => WikiURL($pagename,
                                                    array('action' => _("BackLinks"))),
                                  'class' => 'backlinks'),
                            $WikiTheme->maybeSplitWikiWord($last_page));
        $backlink->addTooltip(sprintf(_("BackLinks for %s"), $pagename));
        $pageheader->pushContent($backlink);
    } else {
        $pageheader = HTML::a(array('href' => WikiURL($pagename,
                                                     array('action' => _("BackLinks"))),
                                   'class' => 'backlinks'),
                             $WikiTheme->maybeSplitWikiWord($pagename));
        $pageheader->addTooltip(sprintf(_("BackLinks for %s"), $pagename));
        if ($request->getArg('frame'))
            $pageheader->setAttr('target', '_top');
    }

    $pagetitle = SplitPagename($pagename);
    if (($redirect_from = $request->getArg('redirectfrom'))) {
        $redirect_message = HTML::span(array('class' => 'redirectfrom'),
                                       fmt("(Redirected from %s)",
                                           RedirectorLink($redirect_from)));
    // abuse the $redirected template var for some status update notice                                       
    } elseif ($request->getArg('errormsg')) { 
        $redirect_message = $request->getArg('errormsg');
        $request->setArg('errormsg', false);
    }

    $request->appendValidators(array('pagerev' => $revision->getVersion(),
                                     '%mtime' => $revision->get('mtime')));
/*
    // FIXME: This is also in the template...
    if ($request->getArg('action') != 'pdf' and !headers_sent()) {
      // FIXME: enable MathML/SVG/... support
      if (ENABLE_XHTML_XML
             and (!isBrowserIE()
                  and strstr($request->get('HTTP_ACCEPT'),'application/xhtml+xml')))
            header("Content-Type: application/xhtml+xml; charset=" . $GLOBALS['charset']);
        else
            header("Content-Type: text/html; charset=" . $GLOBALS['charset']);
    }
*/

    $toks['TITLE'] = $pagetitle;   // <title> tag
    $toks['HEADER'] = $pageheader; // h1 with backlink
    $toks['revision'] = $revision;

    // On external searchengine (google) referrer, highlight the searchterm and 
    // pass through the Searchhighlight actionpage.
    if ($result = isExternalReferrer($request)) {
    	if (!empty($result['query'])) {
    	    if (ENABLE_SEARCHHIGHLIGHT) {
                $request->_searchhighlight = $result;
                $request->appendValidators(array('%mtime' => time())); // force no cache(?)
                // Should be changed to check the engine and search term only
                // $request->setArg('nocache', 1); 
                $page_content = new TransformedText($revision->getPage(),
                                                    $revision->getPackedContent(),
                                                    $revision->getMetaData());
		/* Now add the SearchHighlight plugin to the top of the page, in memory only.
		   You can parametrize this by changing the SearchHighlight action page.
		*/
                if ($actionpage = $request->findActionPage('SearchHighlight')) {
                    $actionpage = $request->getPage($actionpage);
                    $actionrev = $actionpage->getCurrentRevision();
                    $pagetitle = HTML(fmt("%s: %s", 
                                          $actionpage->getName(),
                                          $WikiTheme->linkExistingWikiWord($pagename, false, $version)));
                    $request->appendValidators(array('actionpagerev' => $actionrev->getVersion(),
                                                     '%mtime' => $actionrev->get('mtime')));
                    $toks['SEARCH_ENGINE'] = $result['engine'];
                    $toks['SEARCH_ENGINE_URL'] = $result['engine_url'];
                    $toks['SEARCH_TERM'] = $result['query'];
		    //$toks['HEADER'] = HTML($actionpage->getName(),": ",$pageheader); // h1 with backlink
                    $actioncontent = new TransformedText($actionrev->getPage(),
                                                         $actionrev->getPackedContent(),
                                                         $actionrev->getMetaData());
		    // prepend the actionpage in front of the hightlighted content
	            $toks['CONTENT'] = HTML($actioncontent, $page_content);
                }
	    }
	} else {
            $page_content = $revision->getTransformedContent();
	}
    } else {
        $page_content = $revision->getTransformedContent();
    }
   
    /* Check for special pagenames, which are no actionpages. */
    /*
    if ( $pagename == _("RecentVisitors")) {
        $toks['ROBOTS_META']="noindex,follow";
    } else
    */
    if ($pagename == _("SandBox")) {
        $toks['ROBOTS_META']="noindex,nofollow";
    } else if (!isset($toks['ROBOTS_META'])) {
        $toks['ROBOTS_META'] = "index,follow";
    }
    if (!isset($toks['CONTENT']))
        $toks['CONTENT'] = new Template('browse', $request, $page_content);
    if (!empty($redirect_message))
        $toks['redirected'] = $redirect_message;

    // Massive performance problem parsing at run-time into all xml objects 
    // looking for p's. Should be optional, if not removed at all.
    //$toks['PAGE_DESCRIPTION'] = $page_content->getDescription();
    $toks['PAGE_KEYWORDS'] = GleanKeywords($page);
    if (!$template)
        $template = new Template('html', $request);

    // Handle other formats: So far we had html only.
    // xml is requested by loaddump, rss is handled by RecentChanges, 
    // pdf is a special action, but should be a format to dump multiple pages
    // if the actionpage plugin returns a pagelist.
    // rdf, owl, kbmodel, daml, ... are handled by SemanticWeb.
    $format = $request->getArg('format');
    /* Only single page versions. rss only if not already handled by RecentChanges.
     */
    if (!$format or $format == 'html' or $format == 'sidebar' or $format == 'contribs') {
	$template->printExpansion($toks);
    } elseif ($format == 'xml') {
        $template = new Template('htmldump', $request);
	$template->printExpansion($toks);
    } else {
	// No pagelist here. Single page version only
	require_once("lib/PageList.php");
	$pagelist = new PageList();
	$pagelist->addPage($page);
	if ($format == 'pdf') {
	    require_once("lib/pdf.php");
	    ConvertAndDisplayPdfPageList($request, $pagelist);
	// time-sorted rdf a la RecentChanges
	} elseif (in_array($format, array("rss91","rss2","rss","atom"))) {
            if ($pagename == _("RecentChanges"))
                $template->printExpansion($toks);
            else {    
	        require_once("lib/plugin/RecentChanges.php");
	        $plugin = new WikiPlugin_RecentChanges();
                $args = $request->getArgs();
                return $plugin->format($plugin->getChanges($request->_dbi, $args), $args);
            }
	} elseif ($format == 'rdf') { // all semantic relations and attributes
	    require_once("lib/SemanticWeb.php");
	    $rdf = new RdfWriter($request, $pagelist);
	    $rdf->format();
	} elseif ($format == 'owl') { // or daml?
	    require_once("lib/SemanticWeb.php");
	    $rdf = new OwlWriter($request, $pagelist);
	    $rdf->format();
	} else {
	    if (!in_array($pagename, array(_("LinkDatabase"))))
		trigger_error(sprintf(_("Unsupported argument: %s=%s"),"format",$format),
	    	              E_USER_WARNING);
	    $template->printExpansion($toks);
	}
    }
    
    $page->increaseHitCount();

    if ($request->getArg('action') != 'pdf')
        $request->checkValidators();
    flush();
    return '';
}

// $Log: not supported by cvs2svn $
// Revision 1.78  2008/02/14 18:48:42  rurban
// VALID_LINKS only for existing pages
//
// Revision 1.77  2007/09/12 19:32:29  rurban
// link only VALID_LINKS with pagelist HTML_DUMP
//
// Revision 1.76  2007/08/10 21:59:27  rurban
// fix missing PageList dependency
// add format=rdfs
//
// Revision 1.75  2007/07/01 09:17:45  rurban
// add ATOM support, a very questionable format
//
// Revision 1.74  2007/06/07 17:01:27  rurban
// actionPage has no toks: fix format=rss* on actionpages
//
// Revision 1.73  2007/05/30 20:43:31  rurban
// added MonoBook UserContribs
//
// Revision 1.72  2007/05/13 18:13:12  rurban
// LinkDatabase format exceptions
//
// Revision 1.71  2007/02/17 22:39:05  rurban
// format=rss overhaul
//
// Revision 1.70  2007/01/22 23:43:06  rurban
// Add RecentChanges format=sidebar
//
// Revision 1.69  2007/01/20 15:53:51  rurban
// Rewrite of SearchHighlight: through ActionPage and InlineParser
//
// Revision 1.68  2007/01/20 11:25:19  rurban
// actionPage: request is already global
//
// Revision 1.67  2007/01/07 18:44:20  rurban
// Support format handlers for single- and multi-page: pagelists from actionpage plugins. Use USE_SEARCHHIGHLIGHT. Fix InlineHighlight (still experimental).
//
// Revision 1.66  2006/03/19 14:26:29  rurban
// sf.net patch by Matt Brown: Add rel=nofollow to more actions
//
// Revision 1.65  2005/05/05 08:54:40  rurban
// fix pagename split for title and header
//
// Revision 1.64  2005/04/23 11:21:55  rurban
// honor theme-specific SplitWikiWord in the HEADER
//
// Revision 1.63  2004/11/30 17:48:38  rurban
// just comments
//
// Revision 1.62  2004/11/30 09:51:35  rurban
// changed KEYWORDS from pageprefix to search term. added installer detection.
//
// Revision 1.61  2004/11/21 11:59:19  rurban
// remove final \n to be ob_cache independent
//
// Revision 1.60  2004/11/19 19:22:03  rurban
// ModeratePage part1: change status
//
// Revision 1.59  2004/11/17 20:03:58  rurban
// Typo: call SearchHighlight not SearchHighLight
//
// Revision 1.58  2004/11/09 17:11:16  rurban
// * revert to the wikidb ref passing. there's no memory abuse there.
// * use new wikidb->_cache->_id_cache[] instead of wikidb->_iwpcache, to effectively
//   store page ids with getPageLinks (GleanDescription) of all existing pages, which
//   are also needed at the rendering for linkExistingWikiWord().
//   pass options to pageiterator.
//   use this cache also for _get_pageid()
//   This saves about 8 SELECT count per page (num all pagelinks).
// * fix passing of all page fields to the pageiterator.
// * fix overlarge session data which got broken with the latest ACCESS_LOG_SQL changes
//
// Revision 1.57  2004/11/01 10:43:57  rurban
// seperate PassUser methods into seperate dir (memory usage)
// fix WikiUser (old) overlarge data session
// remove wikidb arg from various page class methods, use global ->_dbi instead
// ...
//
// Revision 1.56  2004/10/14 13:44:14  rurban
// fix lib/display.php:159: Warning[2]: Argument to array_reverse() should be an array
//
// Revision 1.55  2004/09/26 14:58:35  rurban
// naive SearchHighLight implementation
//
// Revision 1.54  2004/09/17 14:19:41  rurban
// disable Content-Type header for now, until it is fixed
//
// Revision 1.53  2004/06/25 14:29:20  rurban
// WikiGroup refactoring:
//   global group attached to user, code for not_current user.
//   improved helpers for special groups (avoid double invocations)
// new experimental config option ENABLE_XHTML_XML (fails with IE, and document.write())
// fixed a XHTML validation error on userprefs.tmpl
//
// Revision 1.52  2004/06/14 11:31:37  rurban
// renamed global $Theme to $WikiTheme (gforge nameclash)
// inherit PageList default options from PageList
//   default sortby=pagename
// use options in PageList_Selectable (limit, sortby, ...)
// added action revert, with button at action=diff
// added option regex to WikiAdminSearchReplace
//
// Revision 1.51  2004/05/18 16:23:39  rurban
// rename split_pagename to SplitPagename
//
// Revision 1.50  2004/05/04 22:34:25  rurban
// more pdf support
//
// Revision 1.49  2004/04/18 01:11:52  rurban
// more numeric pagename fixes.
// fixed action=upload with merge conflict warnings.
// charset changed from constant to global (dynamic utf-8 switching)
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
