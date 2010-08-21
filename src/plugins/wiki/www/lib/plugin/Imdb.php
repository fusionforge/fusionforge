<?php // -*-php-*-
// rcs_id('$Id: Imdb.php 7638 2010-08-11 11:58:40Z vargenau $');
/*
 * Copyright 2004 $ThePhpWikiProgrammingTeam
 *
 * This file is (not yet) part of PhpWiki.
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * Query results from a local imdb copy.
 * see amdbfront for the conversion.
 * "imdb = mysql://user:pass@localhost/imdb" in lib/plugin/SqlResult.ini
 *
 * Queries:
 * <<Imdb query=movie_main title||="Sample Movie (2002)" >>
 * <<Imdb query=movie_combined title||="Sample Movie (2002)" >>
 * <<Imdb query=movie_full title||="Sample Movie (2002)" >>
 * <<Imdb query=movie_company_credits title||="Sample Movie (2002)" >>
 * <<Imdb query=name name||="Lastname, Firstname (I)" >>
 * More title queries:
 *  business, moviebudgets, colorinfo, mpaaratingsreasons,
 *  akatitles, alternateversions, miscellaneouscompanies, moviecountries,
 *  certificates, completecast, completecrew, crazycredits, genres, goofs,
 *  keywords, movielinks, plot, quotes, ratings, soundtracks, specialeffectscompanies,
 *  taglines, trivia, distributors, language, laserdisc, literature, locations,
 *  miscellaneouscompanies, productioncompanies, releasedates, runningtimes, soundmix,
 *  technical
 * More name queries:
 *   akanames, guestappearances, biographies
 *   job.descriptions
 *
 * @author: ReiniUrban
 */

include_once("lib/plugin/SqlResult.php");

class WikiPlugin_Imdb
extends WikiPlugin_SqlResult
{
    function getName () {
        return _("Imdb");
    }

    function getDescription () {
        return _("Query a local imdb database");
    }

    function getDefaultArguments() {
        return array(
                     'query'       => false, // what
                     'template'    => false, // TODO: use a custom <theme>/template.tmpl for the result
                     'where'       => false, // custom filter for the query
                     'title'       => false, // custom filter for the query
                     'name'        => false, // custom filter for the query
                     'sortby'      => false, // for paging, default none
                     'limit'       => false, // for paging, default: only the first 50
                    );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        extract($args);
        include_once("lib/imdb.php");
        $imdb = new imdb();

        if (method_exists($imdb, $query)) {
            $SqlResult = $imdb->$query($title ? $title : $name);
        } else {
            $SqlResult = array();
        }

        // if ($limit) ; // TODO: fill paging vars (see PageList)
        if ($ordered) {
            $html = HTML::ol(array('class'=>'sqlresult'));
            foreach ($SqlResult as $row) {
                $html->pushContent(HTML::li(array('class'=> $i++ % 2 ? 'evenrow' : 'oddrow'), $row[0]));
            }
        } else {
            $html = HTML::table(array('class'=>'sqlresult'));
            $i = 0;
            foreach ($SqlResult as $row) {
                $tr = HTML::tr(array('class'=> $i++ % 2 ? 'evenrow' : 'oddrow'));
                foreach ($row as $col) {
                    $tr->pushContent(HTML::td($col));
                }
                $html->pushContent($tr);
            }
        }
        // if ($limit) ; // do paging via pagelink template
        return $html;
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
