<?php

/**
 * Copyright (C) 1999, 2000, 2001, 2002 $ThePhpWikiProgrammingTeam
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
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Usage: <<SystemInfo all >>
 *        or <<SystemInfo pagestats cachestats discspace hitstats >>
 *        or <<SystemInfo version >>
 *        or <<SystemInfo current_theme >>
 *        or <<SystemInfo PHPWIKI_DIR >>
 *
 * Provide access to phpwiki's lower level system information.
 *
 *   version, pagestats, SERVER_NAME, database, discspace,
 *   cachestats, userstats, linkstats, accessstats, hitstats,
 *   revisionstats, interwikilinks, imageextensions, wikiwordregexp,
 *   availableplugins, downloadurl  or any other predefined CONSTANT
 *
 * In spirit to http://www.ecyrd.com/JSPWiki/SystemInfo.jsp
 *
 * Done: Some calculations are heavy (~5-8 secs), so we should cache
 *       the result. In the page or with WikiPluginCached?
 */

require_once 'lib/WikiPluginCached.php';
class WikiPlugin_SystemInfo
    extends WikiPluginCached
{
    function getPluginType()
    {
        return PLUGIN_CACHED_HTML;
    }

    function getDescription()
    {
        return _("Provide access to PhpWiki's lower level system information.");
    }

    /* From lib/WikiPlugin.php:
     * If the plugin can deduce a modification time, or equivalent
     * sort of tag for it's content, then the plugin should
     * call $request->appendValidators() with appropriate arguments,
     * and should override this method to return true.
     */
    function managesValidators()
    {
        return true;
    }

    function getExpire($dbi, $argarray, $request)
    {
        return '+1800'; // 30 minutes
    }

    function getHtml($dbi, $argarray, $request, $basepage)
    {
        $loader = new WikiPluginLoader();
        return $loader->expandPI('<<SystemInfo '
            . WikiPluginCached::glueArgs($argarray) // all
            . ' ?>', $request, $this, $basepage);
    }

    function getDefaultArguments()
    {
        return array( // 'seperator' => ' ', // on multiple args
        );
    }

    function database()
    {
        $s = "DATABASE_TYPE: " . DATABASE_TYPE . ", ";
        switch (DATABASE_TYPE) {
            case 'SQL': // pear
            case 'ADODB':
            case 'PDO':
                $dsn = DATABASE_DSN;
                $s .= "DATABASE BACKEND:" . " ";
                $s .= (DATABASE_TYPE == 'SQL') ? 'PearDB' : 'ADODB';
                if (preg_match('/^(\w+):/', $dsn, $m)) {
                    $backend = $m[1];
                    $s .= " $backend";
                }
                $s .= ", DATABASE_PREFIX: \"" . DATABASE_PREFIX . "\", ";
                break;
            case 'dba':
                $s .= "DATABASE_DBA_HANDLER: " . DATABASE_DBA_HANDLER . ", ";
                $s .= "DATABASE_DIRECTORY: \"" . DATABASE_DIRECTORY . "\", ";
                break;
            case 'cvs':
                $s .= "DATABASE_DIRECTORY: \"" . DATABASE_DIRECTORY . "\", ";
                // $s .= "cvs stuff: , ";
                break;
            case 'flatfile':
                $s .= "DATABASE_DIRECTORY: " . DATABASE_DIRECTORY . ", ";
                break;
        }
        // hack: suppress error when using sql, so no timeout
        @$s .= "DATABASE_TIMEOUT: " . DATABASE_TIMEOUT;
        return $s;
    }

    function cachestats()
    {
        if (!defined('USECACHE') or !USECACHE)
            return _("no cache used");
        $dbi =& $this->_dbi;
        $cache = $dbi->_cache;
        $s = _("cached pagedata:") . " " . count($cache->_pagedata_cache);
        $s .= ", " . _("cached versiondata:");
        $s .= " " . count($cache->_versiondata_cache);
        //$s .= ", glv size: " . count($cache->_glv_cache);
        //$s .= ", cache hits: ?";
        //$s .= ", cache misses: ?";
        return $s;
    }

    function ExpireParams()
    {
        global $ExpireParams;
        $s = sprintf(_("Keep up to %d major edits, but keep them no longer than %d days."),
            $ExpireParams['major']['keep'],
            $ExpireParams['major']['max_age']);
        $s .= sprintf(_(" Keep up to %d minor edits, but keep them no longer than %d days."),
            $ExpireParams['minor']['keep'],
            $ExpireParams['minor']['max_age']);
        $s .= sprintf(_(" Keep the latest contributions of the last %d authors up to %d days."),
            $ExpireParams['author']['keep'], $ExpireParams['author']['max_age']);
        $s .= sprintf(_(" Additionally, try to keep the latest contributions of all authors in the last %d days (even if there are more than %d of them,) but in no case keep more than %d unique author revisions."),
            $ExpireParams['author']['min_age'],
            $ExpireParams['author']['keep'],
            $ExpireParams['author']['max_keep']);
        return $s;
    }

    function pagestats()
    {
        global $request;
        $dbi = $request->getDbh();
        $s = sprintf(_("%d pages"), $dbi->numPages(true));
        $s .= ", " . sprintf(_("%d not-empty pages"), $dbi->numPages(false));
        // more bla....
        // $s  .= ", " . sprintf(_("earliest page from %s"), $earliestdate);
        // $s  .= ", " . sprintf(_("latest page from %s"), $latestdate);
        // $s  .= ", " . sprintf(_("latest pagerevision from %s"), $latestrevdate);
        return $s;
    }

    //What kind of link statistics?
    //  total links in, total links out, mean links per page, ...
    //  Any useful numbers similar to a VisualWiki interestmap?
    function linkstats()
    {
        $s = _("not yet");
        return $s;
    }

    // number of homepages: easy
    // number of anonymous users?
    //   calc this from accesslog info?
    // number of anonymous edits?
    //   easy. related to the view/edit rate in accessstats.
    function userstats()
    {
        $dbi =& $this->_dbi;
        $h = 0;
        $page_iter = $dbi->getAllPages(true);
        while ($page = $page_iter->next()) {
            if ($page->isUserPage(true)) // check if the admin is there. if not add him to the authusers.
                $h++;
        }
        $s = sprintf(_("%d homepages"), $h);
        // $s  .= ", " . sprintf(_("%d anonymous users"), $au); // ??
        // $s  .= ", " . sprintf(_("%d anonymous edits"), $ae); // see recentchanges
        // $s  .= ", " . sprintf(_("%d authenticated users"), $auth); // users with password set
        // $s  .= ", " . sprintf(_("%d externally authenticated users"), $extauth); // query AuthDB?
        return $s;
    }

    //only from logging info possible. = hitstats per time.
    // total hits per day/month/year
    // view/edit rate
    // TODO: see WhoIsOnline hit stats, and sql accesslogs
    function accessstats()
    {
        $s = _("not yet");
        return $s;
    }

    // numeric array
    private function get_stats($hits, $treshold = 10.0)
    {
        sort($hits);
        reset($hits);
        $n = count($hits);
        $max = 0;
        $min = 9999999999999;
        $sum = 0;
        foreach ($hits as $h) {
            $sum += $h;
            $max = max($h, $max);
            $min = min($h, $min);
        }
        $mean = $n ? $sum / $n : 0;
        $median = $hits[ (int)($n / 2) ];
        $mintreshold = $max * $treshold / 100.0; // lower than 10% of the hits
        reset($hits);
        $nmin = $hits[0] < $mintreshold ? 1 : 0;
        while (next($hits) < $mintreshold)
            $nmin++;
        $maxtreshold = $max - $mintreshold; // more than 90% of the hits
        end($hits);
        $nmax = 1;
        while (prev($hits) > $maxtreshold)
            $nmax++;
        return array('n' => $n,
            'sum' => $sum,
            'min' => $min,
            'max' => $max,
            'mean' => $mean,
            'median' => $median,
            'stddev' => stddev($hits, $sum),
            'treshold' => $treshold,
            'nmin' => $nmin,
            'mintreshold' => $mintreshold,
            'nmax' => $nmax,
            'maxtreshold' => $maxtreshold);
    }

    // only absolute numbers, not for any time interval. see accessstats
    //  some useful number derived from the curve of the hit stats.
    //  total, max, mean, median, stddev;
    //  %d pages less than 3 hits (<10%)    <10% percent of the leastpopular
    //  %d pages more than 100 hits (>90%)  >90% percent of the mostpopular
    function hitstats()
    {
        $dbi =& $this->_dbi;
        $hits = array();
        $page_iter = $dbi->getAllPages(true);
        while ($page = $page_iter->next()) {
            if (($current = $page->getCurrentRevision())
                && (!$current->hasDefaultContents())
            ) {
                $hits[] = $page->get('hits');
            }
        }
        $treshold = 10.0;
        $stats = $this->get_stats($hits, $treshold);

        $s = sprintf(_("total hits: %d"), $stats['sum']);
        $s .= ", " . sprintf(_("max: %d"), $stats['max']);
        $s .= ", " . sprintf(_("mean: %2.3f"), $stats['mean']);
        $s .= ", " . sprintf(_("median: %d"), $stats['median']);
        $s .= ", " . sprintf(_("stddev: %2.3f"), $stats['stddev']);
        $s .= "; " . sprintf(_("%d pages with less than %d hits (<%d%%)."),
            $stats['nmin'], $stats['mintreshold'], $treshold);
        $s .= " " . sprintf(_("%d page(s) with more than %d hits (>%d%%)."),
            $stats['nmax'], $stats['maxtreshold'], 100 - $treshold);
        return $s;
    }

    /* not yet ready
     */
    function revisionstats()
    {
        global $LANG;

        include_once 'lib/WikiPluginCached.php';
        $cache = WikiPluginCached::newCache();
        $id = $cache->generateId('SystemInfo::revisionstats_' . $LANG);
        $cachedir = 'plugincache';
        $content = $cache->get($id, $cachedir);

        if (!empty($content))
            return $content;

        $dbi =& $this->_dbi;
        $stats = array();
        $page_iter = $dbi->getAllPages(true);
        $stats['empty'] = $stats['latest']['major'] = $stats['latest']['minor'] = 0;
        while ($page = $page_iter->next()) {
            if (!$page->exists()) {
                $stats['empty']++;
                continue;
            }
            $current = $page->getCurrentRevision();
            // is the latest revision a major or minor one?
            //   latest revision: numpages 200 (100%) / major (60%) / minor (40%)
            if ($current->get('is_minor_edit'))
                $stats['latest']['major']++;
            else
                $stats['latest']['minor']++;
            /*
                        // FIXME: This needs much too long to be acceptable.
                        // overall:
                        //   number of revisions: all (100%) / major (60%) / minor (40%)
                        // revs per page:
                        //   per page: mean 20 / major (60%) / minor (40%)
                        $rev_iter = $page->getAllRevisions();
                        while ($rev = $rev_iter->next()) {
                            if ($rev->get('is_minor_edit'))
                                $stats['page']['major']++;
                            else
                                $stats['page']['minor']++;
                        }
                        $rev_iter->free();
                        $stats['page']['all'] = $stats['page']['major'] + $stats['page']['minor'];
                        $stats['perpage'][]       = $stats['page']['all'];
                        $stats['perpage_major'][] = $stats['page']['major'];
                        $stats['sum']['all'] += $stats['page']['all'];
                        $stats['sum']['major'] += $stats['page']['major'];
                        $stats['sum']['minor'] += $stats['page']['minor'];
                        $stats['page'] = array();
            */
        }
        $page_iter->free();
        $stats['numpages'] = $stats['latest']['major'] + $stats['latest']['minor'];
        $stats['latest']['major_perc'] = $stats['latest']['major'] * 100.0 / $stats['numpages'];
        $stats['latest']['minor_perc'] = $stats['latest']['minor'] * 100.0 / $stats['numpages'];
        $empty = sprintf("empty pages: %d (%02.1f%%) / %d (100%%)\n",
            $stats['empty'], $stats['empty'] * 100.0 / $stats['numpages'],
            $stats['numpages']);
        $latest = sprintf("latest revision: major %d (%02.1f%%) / minor %d (%02.1f%%) / all %d (100%%)\n",
            $stats['latest']['major'], $stats['latest']['major_perc'],
            $stats['latest']['minor'], $stats['latest']['minor_perc'], $stats['numpages']);
        /*
                $stats['sum']['major_perc'] = $stats['sum']['major'] * 100.0 / $stats['sum']['all'];
                $stats['sum']['minor_perc'] = $stats['sum']['minor'] * 100.0 / $stats['sum']['all'];
                $sum = sprintf("number of revisions: major %d (%02.1f%%) / minor %d (%02.1f%%) / all %d (100%%)\n",
                               $stats['sum']['major'], $stats['sum']['major_perc'],
                               $stats['sum']['minor'], $stats['sum']['minor_perc'], $stats['sum']['all']);

                $stats['perpage']       = $this->get_stats($stats['perpage']);
                $stats['perpage_major'] = $this->get_stats($stats['perpage_major']);
                $stats['perpage']['major_perc'] = $stats['perpage_major']['sum'] * 100.0 / $stats['perpage']['sum'];
                $stats['perpage']['minor_perc'] = 100 - $stats['perpage']['major_perc'];
                $stats['perpage_minor']['sum']  = $stats['perpage']['sum'] - $stats['perpage_major']['sum'];
                $stats['perpage_minor']['mean'] = $stats['perpage_minor']['sum'] / ($stats['perpage']['n'] - $stats['perpage_major']['n']);
                $perpage = sprintf("revisions per page: all %d, mean %02.1f / major %d (%02.1f%%) / minor %d (%02.1f%%)\n",
                                   $stats['perpage']['sum'], $stats['perpage']['mean'],
                                   $stats['perpage_major']['mean'], $stats['perpage']['major_perc'],
                                   $stats['perpage_minor']['mean'], $stats['perpage']['minor_perc']);
                $perpage .= sprintf("  %d page(s) with less than %d revisions (<%d%%)\n",
                                    $stats['perpage']['nmin'], $stats['perpage']['maintreshold'], $treshold);
                $perpage .= sprintf("  %d page(s) with more than %d revisions (>%d%%)\n",
                                    $stats['perpage']['nmax'], $stats['perpage']['maxtreshold'], 100 - $treshold);
                $content = $empty . $latest . $sum . $perpage;
        */
        $content = $empty . $latest;

        // regenerate cache every 30 minutes
        $cache->save($id, $content, '+1800', $cachedir);
        return $content;
    }

    // Size of databases/files/cvs are possible plus the known size of the app.
    // Cache this costly operation.
    // Even if the whole plugin call is stored internally, we cache this
    // separately with a separate key.
    function discspace()
    {
        global $DBParams;

        include_once 'lib/WikiPluginCached.php';
        $cache = WikiPluginCached::newCache();
        $id = $cache->generateId('SystemInfo::discspace');
        $cachedir = 'plugincache';
        $content = $cache->get($id, $cachedir);

        if (empty($content)) {
            $dir = defined('PHPWIKI_DIR') ? PHPWIKI_DIR : '.';
            //TODO: windows only (no cygwin)
            $appsize = `du -s $dir | cut -f1`;

            if (in_array($DBParams['dbtype'], array('SQL', 'ADODB'))) {
                //TODO: where is the data is actually stored? see phpMyAdmin
                $pagesize = 0;
            } elseif ($DBParams['dbtype'] == 'dba') {
                $pagesize = 0;
                $dbdir = $DBParams['directory'];
                if ($DBParams['dba_handler'] == 'db3')
                    $pagesize = filesize($DBParams['directory']
                        . "/wiki_pagedb.db3") / 1024;
                // if issubdirof($dbdir, $dir) $appsize -= $pagesize;
            } else { // flatfile, cvs
                $dbdir = $DBParams['directory'];
                $pagesize = `du -s $dbdir`;
                // if issubdirof($dbdir, $dir) $appsize -= $pagesize;
            }
            $content = array('appsize' => $appsize,
                'pagesize' => $pagesize);
            // regenerate cache every 30 minutes
            $cache->save($id, $content, '+1800', $cachedir);
        } else {
            $appsize = $content['appsize'];
            $pagesize = $content['pagesize'];
        }

        $s = sprintf(_("Application size: %d KiB"), $appsize);
        if ($pagesize)
            $s .= ", " . sprintf(_("Pagedata size: %d KiB", $pagesize));
        return $s;
    }

    function inlineimages()
    {
        return implode(' ', explode('|', INLINE_IMAGES));
    }

    function wikinameregexp()
    {
        return $GLOBALS['WikiNameRegexp'];
    }

    function allowedprotocols()
    {
        return implode(' ', explode('|', ALLOWED_PROTOCOLS));
    }

    function available_plugins()
    {
        $fileset = new FileSet(FindFile('lib/plugin'), '*.php');
        $list = $fileset->getFiles();
        natcasesort($list);
        reset($list);
        return sprintf(_("Total %d plugins: "), count($list))
            . implode(', ', array_map(create_function('$f',
                    'return substr($f,0,-4);'),
                $list));
    }

    function supported_languages()
    {
        $available_languages = listAvailableLanguages();
        natcasesort($available_languages);

        return sprintf(_("Total of %d languages: "),
            count($available_languages))
            . implode(', ', $available_languages) . ". "
            . _("Current language") . _(": ") . $GLOBALS['LANG']
            . ((DEFAULT_LANGUAGE != $GLOBALS['LANG'])
                ? ". " . sprintf(_("Default language: “%s”"), DEFAULT_LANGUAGE)
                : '');
    }

    function supported_themes()
    {
        global $WikiTheme;
        $available_themes = listAvailableThemes();
        natcasesort($available_themes);
        return sprintf(_("Total of %d themes: "), count($available_themes))
            . implode(', ', $available_themes) . ". "
            . _("Current theme") . _(": ") . $WikiTheme->_name
            . ((THEME != $WikiTheme->_name)
                ? ". " . sprintf(_("Default theme: “%s”"), THEME)
                : '');
    }

    function call($arg, &$availableargs)
    {
        if (!empty($availableargs[$arg]))
            return $availableargs[$arg]();
        elseif (method_exists($this, $arg)) // any defined SystemInfo->method()
            return call_user_func_array(array(&$this, $arg), array()); elseif (defined($arg) && // any defined constant
            !in_array($arg, array('ADMIN_PASSWD', 'DATABASE_DSN', 'DBAUTH_AUTH_DSN'))
        )
            return constant($arg); else
            return $this->error(sprintf(_("unknown argument “%s” to SystemInfo"), $arg));
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        // don't parse argstr for name=value pairs. instead we use just 'name'
        //$args = $this->getArgs($argstr, $request);
        $this->_dbi =& $dbi;
        $args['seperator'] = ' ';
        $availableargs = // name => callback + 0 args
            array('appname' => create_function('', "return 'PhpWiki';"),
                'version' => create_function('', "return sprintf('%s', PHPWIKI_VERSION);"),
                'LANG' => create_function('', 'return $GLOBALS["LANG"];'),
                'LC_ALL' => create_function('', 'return setlocale(LC_ALL, 0);'),
                'current_language' => create_function('', 'return $GLOBALS["LANG"];'),
                'system_language' => create_function('', 'return DEFAULT_LANGUAGE;'),
                'current_theme' => create_function('', 'return $GLOBALS["WikiTheme"]->_name;'),
                'system_theme' => create_function('', 'return THEME;'),
                // more here or as method.
                '' => create_function('', "return 'dummy';")
            );
        // split the argument string by any number of commas or space
        // characters, which include " ", \r, \t, \n and \f
        $allargs = preg_split("/[\s,]+/", $argstr, -1, PREG_SPLIT_NO_EMPTY);
        if (in_array('all', $allargs) || in_array('table', $allargs)) {
            $allargs = array('appname' => _("Application name"),
                'version' => _("PhpWiki engine version"),
                'database' => _("Database"),
                'cachestats' => _("Cache statistics"),
                'pagestats' => _("Page statistics"),
                //'revisionstats'    => _("Page revision statistics"),
                //'linkstats'        => _("Link statistics"),
                'userstats' => _("User statistics"),
                //'accessstats'      => _("Access statistics"),
                'hitstats' => _("Hit statistics"),
                'discspace' => _("Harddisc usage"),
                'expireparams' => _("Expiry parameters"),
                'wikinameregexp' => _("Wikiname regexp"),
                'allowedprotocols' => _("Allowed protocols"),
                'inlineimages' => _("Inline images"),
                'available_plugins' => _("Available plugins"),
                'supported_languages' => _("Supported languages"),
                'supported_themes' => _("Supported themes"),
//                           '' => _(""),
                '' => ""
            );
            $table = HTML::table(array('class' => 'bordered'));
            foreach ($allargs as $arg => $desc) {
                if (!$arg)
                    continue;
                if (!$desc)
                    $desc = _($arg);
                $table->pushContent(HTML::tr(HTML::th(array('style' => "white-space:nowrap"), $desc),
                    HTML::td(HTML($this->call($arg, $availableargs)))));
            }
            return $table;
        } else {
            $output = '';
            foreach ($allargs as $arg) {
                $o = $this->call($arg, $availableargs);
                if (is_object($o))
                    return $o;
                else
                    $output .= ($o . $args['seperator']);
            }
            // if more than one arg, remove the trailing seperator
            if ($output) $output = substr($output, 0,
                -strlen($args['seperator']));
            return HTML($output);
        }
    }
}

function median($hits)
{
    sort($hits);
    reset($hits);
    $n = count($hits);
    $median = (int)($n / 2);
    if (!($n % 2)) // proper rounding on even length
        return ($hits[$median] + $hits[$median - 1]) * 0.5;
    else
        return $hits[$median];
}

function rsum($a, $b)
{
    $a += $b;
    return $a;
}

function mean(&$hits, $total = false)
{
    $n = count($hits);
    if (!$total)
        $total = array_reduce($hits, 'rsum');
    return (float)$total / ($n * 1.0);
}

function stddev(&$hits, $total = false)
{
    $n = count($hits);
    if (!$total) $total = array_reduce($hits, 'rsum');
    $GLOBALS['mean'] = $total / $n;
    $r = array_map(create_function('$i', 'global $mean; return ($i-$mean)*($i-$mean);'),
        $hits);
    unset($GLOBALS['mean']);
    return (float)sqrt(mean($r, $total) * ($n / (float)($n - 1)));
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
