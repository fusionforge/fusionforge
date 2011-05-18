<?php // -*-php-*-
// $Id: WikiBlog.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright 2002,2003,2007,2009 $ThePhpWikiProgrammingTeam
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
 * @author: MichaelVanDam, major refactor by JeffDairiki (as AddComment)
 * @author: Changed as baseclass to AddComment and WikiForum and EditToolbar integration by ReiniUrban.
 */

require_once('lib/TextSearchQuery.php');

/**
 * This plugin shows 'blogs' (comments/news) associated with a
 * particular page and provides an input form for adding a new blog.
 *
 * USAGE:
 * Add <<WikiBlog >> at your PersonalPage and BlogArchive and
 * BlogJournal will find the Blog entries automatically.
 *
 * Now it is also the base class for all attachable pagetypes:
 *    "wikiblog", "comment" and "wikiforum"
 *
 * HINTS/COMMENTS:
 *
 * To have the blog show up on a seperate page:
 * On TopPage, use
 *   <<WikiBlog mode=add>>
 * Create TopPage/Blog with this page as actionpage:
 *   <<WikiBlog pagename=TopPage mode=show>>
 *
 * To have the main ADMIN_USER Blog appear under Blog and not under WikiBlog/Blog
 * or UserName/Blog as for other users blogs,
 * define BLOG_DEFAULT_EMPTY_PREFIX=true
 * use the page Blog as basepage
 * and user="" (as default for ADMIN or current user) and pagename="Blog"
 * in the various blog plugins (BlogArchives, BlogJournal)
 *
 * TODO:
 *
 * It also works as an action-page if you create a page called 'WikiBlog'
 * containing this plugin.  This allows adding comments to any page
 * by linking "PageName?action=WikiBlog".  Maybe a nice feature in
 * lib/display.php would be to automatically check if there are
 * blogs for the given page, then provide a link to them somewhere on
 * the page.  Or maybe this just creates a huge mess...
 *
 * Maybe it would be a good idea to ENABLE blogging of only certain
 * pages by setting metadata or something...?  If a page is non-bloggable
 * the plugin is ignored (perhaps with a warning message).
 *
 * Should blogs be by default filtered out of RecentChanges et al???
 *
 * Think of better name for this module: Blog? WikiLog? WebLog? WikiDot?
 *
 * Have other 'styles' for the plugin?... e.g. 'quiet'.  Display only
 * 'This page has 23 associated comments. Click here to view / add.'
 *
 * For admin user, put checkboxes beside comments to allow for bulk removal.
 *
 * Permissions for who can add blogs?  Display entry box only if
 * user meets these requirements...?
 *
 * Code cleanup: break into functions, use templates (or at least remove CSS)
 */

class WikiPlugin_WikiBlog
extends WikiPlugin
{
    function getName () {
        return _("WikiBlog");
    }

    function getDescription () {
        return sprintf(_("Show and add blogs for %s"),'[pagename]');
    }

    // Arguments:
    //  page - page which is blogged to (default current page)
    //
    //  order - 'normal' - place in chronological order
    //        - 'reverse' - place in reverse chronological order
    //
    //  mode - 'show' - only show old blogs
    //         'add' - only show entry box for new blog
    //         'show,add' - show old blogs then entry box
    //         'add,show' - show entry box followed by old blogs
    //
    // TODO:
    //
    // - arguments to allow selection of time range to display
    // - arguments to display only XX blogs per page (can this 'paging'
    //    co-exist with the wiki??  difficult)
    // - arguments to allow comments outside this range to be
    //    display as e.g. June 2002 archive, July 2002 archive, etc..
    // - captions for 'show' and 'add' sections


    function getDefaultArguments() {
        return array('pagename'   => '[pagename]',
                     'order'      => 'normal',
                     'mode'       => 'show,add',
                     'noheader'   => false
                    );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        // allow empty pagenames for ADMIN_USER style blogs: "Blog/day"
        //if (!$args['pagename'])
        //    return $this->error(_("No pagename specified"));

        // Get our form args.
        $blog = $request->getArg("edit");
        $request->setArg("edit", false);

        if ($request->isPost() and !empty($blog['save'])) {
            $this->add($request, $blog, 'wikiblog', $basepage); // noreturn
        }
        //TODO: preview

        // Now we display previous comments and/or provide entry box
        // for new comments
        $html = HTML();
        foreach (explode(',', $args['mode']) as $show) {
            if (!empty($seen[$show]))
                continue;
            $seen[$show] = 1;

            switch ($show) {
            case 'show':
                $html->pushContent($this->showAll($request, $args));
                break;
            case 'add':
                $html->pushContent($this->showForm($request, $args));
                break;
            default:
                return $this->error(sprintf("Bad mode ('%s')", $show));
            }
        }
        return $html;
    }

    /**
     * posted: required: pagename, content. optional: summary
     */
    function add (&$request, $posted, $type='wikiblog') {
        // This is similar to editpage. Shouldn't we use just this for preview?
        $parent = $posted['pagename'];
        if (empty($parent)) {
            $prefix = "";   // allow empty parent for default "Blog/day"
            $parent = HOME_PAGE;
        } elseif (($parent == 'Blog' or $parent == 'WikiBlog') and $type == 'wikiblog')
        { // avoid Blog/Blog/2003-01-11/14:03:02+00:00
            $prefix = "";
            $parent = ''; // 'Blog';
        } elseif ($parent == 'Comment' and $type == "comment")
        {
            $prefix = "";
            $parent = ''; // 'Comment';
        } elseif ($parent == 'Forum' and $type == "wikiforum")
        {
            $prefix = "";
            $parent = ''; // 'Forum';
        } else {
            $prefix = $parent . SUBPAGE_SEPARATOR;
        }
        //$request->finish(fmt("No pagename specified for %s",$type));

        $now = time();
        $dbi = $request->getDbh();
        $user = $request->getUser();

        /*
         * Page^H^H^H^H Blog meta-data
         * This method is reused for all attachable pagetypes: wikiblog, comment and wikiforum
         *
         * This is info that won't change for each revision.
         * Nevertheless, it's now stored in the revision meta-data.
         * Several reasons:
         *  o It's more convenient to have all information required
         *    to render a page revision in the revision meta-data.
         *  o We can avoid a race condition, since version meta-data
         *    updates are atomic with the version creation.
         */

        $blog_meta = array('ctime'      => $now,
                           'creator'    => $user->getId(),
                           'creator_id' => $user->getAuthenticatedId(),
                           );


        // Version meta-data
        $summary = trim($posted['summary']);
        // edit: private only
        $perm = new PagePermission();
        $perm->perm['edit'] = $perm->perm['remove'];
        $version_meta = array('author'    => $blog_meta['creator'],
                              'author_id' => $blog_meta['creator_id'],
                              'markup'    => 2.0,   // assume new markup
                              'summary'   => $summary ? $summary : _("New comment."),
                              'mtime'     => $now,
                              'pagetype'  => $type,
                              $type       => $blog_meta,
                              'perm'      => $perm->perm,
                              );
        if ($type == 'comment')
            unset($version_meta['summary']);

        // Comment body.
        $body = trim($posted['content']);

        $saved = false;
        if ($type != 'wikiforum')
            $pagename = $this->_blogPrefix($type);
        else {
            $pagename = substr($summary,0,12);
            if (empty($pagename)) {
                $saved = true;
                trigger_error("Empty title", E_USER_WARNING);
            }
        }
        while (!$saved) {
            // Generate the page name.  For now, we use the format:
            //   Rootname/Blog/2003-01-11/14:03:02+00:00
            // Rootname = $prefix, Blog = $pagename,
            // This gives us natural chronological order when sorted
            // alphabetically. "Rootname/" is optional.
            // Esp. if Rootname is named Blog, it is omitted.

            $time = Iso8601DateTime();
            // Check intermediate pages. If not existing they should RedirectTo the parent page.
            // Maybe add the BlogArchives plugin instead for the new interim subpage.
            $redirected = $prefix . $pagename;
            if (!$dbi->isWikiPage($redirected)) {
                    if (!$parent) $parent = HOME_PAGE;
                require_once('lib/loadsave.php');
                $pageinfo = array('pagename' => $redirected,
                                  'content'  => '<?plugin RedirectTo page="'.$parent.'" ?>',
                                  'pagedata' => array(),
                                  'versiondata' => array('author' => $blog_meta['creator'], 'is_minor_edit' => 1),
                                  );
                SavePage($request, $pageinfo, '', '');
            }
            $redirected = $prefix . $pagename . SUBPAGE_SEPARATOR . preg_replace("/T.*/", "", "$time");
            if (!$dbi->isWikiPage($redirected)) {
                    if (!$parent) $parent = HOME_PAGE;
                require_once('lib/loadsave.php');
                $pageinfo = array('pagename' => $redirected,
                                  'content'  => '<?plugin RedirectTo page="'.$parent.'" ?>',
                                  'pagedata' => array(),
                                  'versiondata' => array('author' => $blog_meta['creator'], 'is_minor_edit' => 1),
                                  );
                SavePage($request, $pageinfo, '', '');
            }

            $p = $dbi->getPage($prefix . $pagename . SUBPAGE_SEPARATOR
                               . str_replace("T", SUBPAGE_SEPARATOR, "$time"));
            $pr = $p->getCurrentRevision();

            // Version should be zero.  If not, page already exists
            // so increment timestamp and try again.
            if ($pr->getVersion() > 0) {
                $now++;
                continue;
            }

            // FIXME: there's a slight, but currently unimportant
            // race condition here.  If someone else happens to
            // have just created a blog with the same name,
            // we'll have locked it before we discover that the name
            // is taken.
            $saved = $p->save($body, 1, $version_meta);

            $now++;
        }

        $dbi->touch();
        $request->setArg("mode", "show");
        $request->redirect($request->getURLtoSelf()); // noreturn

        // FIXME: when submit a comment from preview mode,
        // adds the comment properly but jumps to browse mode.
        // Any way to jump back to preview mode???
    }

    function showAll (&$request, $args, $type="wikiblog") {
        // FIXME: currently blogSearch uses WikiDB->titleSearch to
        // get results, so results are in alphabetical order.
        // When PageTypes fully implemented, could have smarter
        // blogSearch implementation / naming scheme.

        $dbi = $request->getDbh();
        $basepage = $args['pagename'];
        $blogs = $this->findBlogs($dbi, $basepage, $type);
        $html = HTML();
        if ($blogs) {
            // First reorder
            usort($blogs, array("WikiPlugin_WikiBlog",
                                "cmp"));
            if ($args['order'] == 'reverse')
                $blogs = array_reverse($blogs);

            $name = $this->_blogPrefix($type);
            if (!$args['noheader'])
                $html->pushContent(HTML::h4(array('class' => "$type-heading"),
                                            fmt("%s on %s:", $name, WikiLink($basepage))));
            foreach ($blogs as $rev) {
                if (!$rev->get($type)) {
                    // Ack! this is an old-style blog with data ctime in page meta-data.
                    $content = $this->_transformOldFormatBlog($rev, $type);
                }
                else {
                    $content = $rev->getTransformedContent($type);
                }
                $html->pushContent($content);
            }

        }
        return $html;
    }

    // Subpage for the basepage. All Blogs/Forum/Comment entries are
    // Subpages under this pagename, to find them faster.
    function _blogPrefix($type='wikiblog') {
        if ($type == 'wikiblog')
            $basepage = "Blog";
        elseif ($type == 'comment')
            $basepage = "Comment";
        elseif ($type == 'wikiforum')
            $basepage = substr($summary,0,12);
            //$basepage = _("Message"); // FIXME: we use now the first 12 chars of the summary
        return $basepage;
    }

    function _transformOldFormatBlog($rev, $type='wikiblog') {
        $page = $rev->getPage();
        $metadata = array();
        foreach (array('ctime', 'creator', 'creator_id') as $key)
            $metadata[$key] = $page->get($key);
        if (empty($metadata) and $type != 'wikiblog')
            $metadata[$key] = $page->get('wikiblog');
        $meta = $rev->getMetaData();
        $meta[$type] = $metadata;
        return new TransformedText($page, $rev->getPackedContent(), $meta, $type);
    }

    function findBlogs (&$dbi, $basepage='', $type='wikiblog') {
        $prefix = (empty($basepage)
                   ? ""
                   :  $basepage . SUBPAGE_SEPARATOR) . $this->_blogPrefix($type);
        $pages = $dbi->titleSearch(new TextSearchQuery('"'.$prefix.'"', true, 'none'));

        $blogs = array();
        while ($page = $pages->next()) {
            if (!string_starts_with($page->getName(), $prefix))
                continue;
            $current = $page->getCurrentRevision();
            if ($current->get('pagetype') == $type) {
                $blogs[] = $current;
            }
        }
        return $blogs;
    }

    function cmp($a, $b) {
        return(strcmp($a->get('mtime'),
                      $b->get('mtime')));
    }

    function showForm (&$request, $args, $template='blogform') {
        // Show blog-entry form.
        $args = array('PAGENAME' => $args['pagename'],
                      'HIDDEN_INPUTS' =>
                      HiddenInputs($request->getArgs()));
        if (ENABLE_EDIT_TOOLBAR and !ENABLE_WYSIWYG and ($template != 'addcomment')) {
            include_once("lib/EditToolbar.php");
            $toolbar = new EditToolbar();
            $args = array_merge($args, $toolbar->getTokens());
        }
        return new Template($template, $request, $args);
    }

    // "2004-12" => "December 2004"
    function _monthTitle($month){
            if (!$month) $month = strftime("%Y-%m");
        //list($year,$mon) = explode("-",$month);
        return strftime("%B %Y", strtotime($month."-01"));
    }

    // "UserName/Blog/2004-12-13/12:28:50+01:00" => array('month' => "2004-12", ...)
    function _blog($rev_or_page) {
            $pagename = $rev_or_page->getName();
        if (preg_match("/^(.*Blog)\/(\d\d\d\d-\d\d)-(\d\d)\/(.*)/", $pagename, $m))
            list(,$prefix,$month,$day,$time) = $m;
        return array('pagename' => $pagename,
                     // page (list pages per month) or revision (list months)?
                     //'title' => isa($rev_or_page,'WikiDB_PageRevision') ? $rev_or_page->get('summary') : '',
                     //'monthtitle' => $this->_monthTitle($month),
                     'month'   => $month,
                     'day'     => $day,
                     'time'    => $time,
                     'prefix'  => $prefix);
    }

    function _nonDefaultArgs($args) {
            return array_diff_assoc($args, $this->getDefaultArguments());
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
