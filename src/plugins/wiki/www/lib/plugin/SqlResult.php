<?php

/*
 * Copyright 2004 $ThePhpWikiProgrammingTeam
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
 * This plugin displays results of arbitrary SQL select statements
 * in table form.
 * The database definition, the DSN, must be defined in the local file
 * config/SqlResult.ini
 *   A simple textfile with alias = dsn lines.
 *
 * Optional template file to format the result and handle some logic.
 * Template vars: %%where%%, %%sortby%%, %%limit%%
 * TODO: paging
 *
 * Usage:
 *   <<SqlResult alias=mysql
 *            SELECT 'mysql password for string "xx":',
 *                   PASSWORD('xx')
 *   >>
 *   <<SqlResult alias=videos template=videos
 *            SELECT rating,title,date
 *                   FROM video
 *                   ORDER BY rating DESC
 *                   LIMIT 5
 *   >>
 *   <<SqlResult alias=imdb template=imdbmovies where||="Davies, Jeremy%"
 *   SELECT m.title, m.date, n.name, c.role
 *     FROM movies as m, names as n, jobs as j, characters as c
 *     WHERE n.name LIKE "%%where%%"
 *     AND m.title_id = c.title_id
 *     AND n.name_id = c.name_id
 *     AND c.job_id = j.job_id
 *     AND j.description = 'Actor'
 *     ORDER BY m.date DESC
 *   >>
 *
 * @author: ReiniUrban
 */

require_once 'lib/PageList.php';

class WikiPlugin_SqlResult
    extends WikiPlugin
{
    public $_args;

    function getDescription()
    {
        return _("Display arbitrary SQL result tables.");
    }

    function getDefaultArguments()
    {
        return array(
            'alias' => false, // DSN database specification
            'ordered' => false, // if to display as <ol> list: single col only without template
            'template' => false, // use a custom <theme>/template.tmpl
            'where' => false, // custom filter for the query
            'sortby' => false, // for paging, default none
            'limit' => "0,50", // for paging, default: only the first 50
        );
    }

    function getDsn($alias)
    {
        $ini = parse_ini_file(FindFile("config/SqlResult.ini"));
        return $ini[$alias];
    }

    /** Get the SQL statement from the rest of the lines
     */
    function handle_plugin_args_cruft($argstr, $args)
    {
        $this->_sql = str_replace("\n", " ", $argstr);
        return;
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        global $DBParams;
        //$request->setArg('nocache','1');
        extract($this->getArgs($argstr, $request));
        if (!$alias)
            return $this->error(_("No DSN alias for SqlResult.ini specified"));
        $sql = $this->_sql;

        // apply custom filters
        if ($where and strstr($sql, "%%where%%"))
            $sql = str_replace("%%where%%", $where, $sql);
        // TODO: use a SQL construction library?
        if ($limit) {
            $pagelist = new PageList();
            $limit = $pagelist->limit($limit);
            if (strstr($sql, "%%limit%%"))
                $sql = str_replace("%%limit%%", $limit, $sql);
            else {
                if (strstr($sql, "LIMIT"))
                    $sql = preg_replace("/LIMIT\s+[\d,]+\s+/m", "LIMIT " . $limit . " ", $sql);
            }
        }
        if (strstr($sql, "%%sortby%%")) {
            if (!$sortby)
                $sql = preg_replace("/ORDER BY .*%%sortby%%\s/m", "", $sql);
            else
                $sql = str_replace("%%sortby%%", $sortby, $sql);
        } elseif (PageList::sortby($sortby, 'db')) { // add sorting: support paging sortby links
            if (preg_match("/\sORDER\s/", $sql))
                $sql = preg_replace("/ORDER BY\s\S+\s/m", "ORDER BY " . PageList::sortby($sortby, 'db'), $sql);
            else
                $sql .= " ORDER BY " . PageList::sortby($sortby, 'db');
        }

        $inidsn = $this->getDsn($alias);
        if (!$inidsn)
            return $this->error(sprintf(_("No DSN for alias %s in SqlResult.ini found"),
                $alias));
        // adodb or pear? adodb as default, since we distribute per default it.
        // for pear there may be overrides.
        // TODO: native PDO support (for now we use ADODB)
        if ($DBParams['dbtype'] == 'SQL') {
            $dbh = DB::connect($inidsn);
            $all = $dbh->getAll($sql);
            if (DB::isError($all)) {
                return $this->error($all->getMessage() . ' ' . $all->userinfo);
            }
        } else { // unless PearDB use the included ADODB, regardless if dba, file or PDO, ...
            if ($DBParams['dbtype'] != 'ADODB') {
                require_once 'lib/WikiDB/backend/ADODB.php';
            }
            $parsed = parseDSN($inidsn);
            $dbh = &ADONewConnection($parsed['phptype']);
            $conn = $dbh->Connect($parsed['hostspec'], $parsed['username'],
                $parsed['password'], $parsed['database']);
            if (!$conn)
                return $this->error($dbh->errorMsg());
            $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
            $dbh->SetFetchMode(ADODB_FETCH_ASSOC);

            $all = $dbh->getAll($sql);

            $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_NUM;
            $dbh->SetFetchMode(ADODB_FETCH_NUM);
            if (!$all)
                return $this->error($dbh->errorMsg());
        }
        $args = array();
        if ($limit) { // fill paging vars (see PageList)
            $args = $pagelist->pagingTokens(count($all), count($all[0]), $limit);
            if (!$args) $args = array();
        }

        if ($template) {
            $args = array_merge(
                array('SqlResult' => $all, // the resulting array of rows
                    'ordered' => $ordered, // whether to display as <ul>/<dt> or <ol>
                    'where' => $where,
                    'sortby' => $sortby,
                    'limit' => $limit),
                $args); // paging params override given params
            return Template($template, $args);
        } else {
            if ($ordered) {
                $html = HTML::ol(array('class' => 'sqlresult'));
                if ($all)
                    foreach ($all as $row) {
                        $html->pushContent(HTML::li(array('class' => $i++ % 2 ? 'evenrow' : 'oddrow'), $row[0]));
                    }
            } else {
                $html = HTML::table(array('class' => 'sqlresult'));
                $i = 0;
                if ($all)
                    foreach ($all as $row) {
                        $tr = HTML::tr(array('class' => $i++ % 2 ? 'evenrow' : 'oddrow'));
                        if ($row)
                            foreach ($row as $col) {
                                $tr->pushContent(HTML::td($col));
                            }
                        $html->pushContent($tr);
                    }
            }
        }
        // do paging via pagelink template
        if (!empty($args['NUMPAGES'])) {
            $paging = Template("pagelink", $args);
            $html = $table->pushContent(HTML::thead($paging),
                HTML::tbody($html),
                HTML::tfoot($paging));
        }
        if (0 and DEBUG) { // test deferred error/warning/notice collapsing
            trigger_error("test notice", E_USER_NOTICE);
            trigger_error("test warning", E_USER_WARNING);
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
