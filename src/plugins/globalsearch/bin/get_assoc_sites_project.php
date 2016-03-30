#! /usr/bin/php
<?php
/**
 * Copyright 2016, Franck Villaume - TrivialDev
 *
 * This file is a part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge. If not, see <http://www.gnu.org/licenses/>.
 */

require dirname(__FILE__).'/../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'common/include/utils.php';

$res = db_query_params('SELECT assoc_site_id, title, link, onlysw, status_id, rank
			FROM plugin_globalsearch_assoc_site WHERE enabled=$1 ORDER BY rank', array('t'));

if ($res && db_numrows($res)) {
	while ($arr = db_fetch_array($res)) {
		$link = $arr['link'];
		$filename = $link.'/export/rss_sfprojects.php?showall=1';
		$simpleXmlLoadedFile = simplexml_load_file($filename);
		$res2 = db_query_params('DELETE FROM plugin_globalsearch_assoc_site_project WHERE assoc_site_id=$1', array($arr['assoc_site_id']));
		if ($simpleXmlLoadedFile !== false) {
			$xmlObjectsArray = $simpleXmlLoadedFile->channel->item;
			foreach ($xmlObjectsArray as $key => $xmlObject) {
				$title = (string)$xmlObject->title;
				$projectsubLink = (string)$xmlObject->link;
				if ($projectsubLink[0] == '/') {
					$projectsubLink = substr($projectsubLink, 1);
				}
				$projectLink = $link.$projectsubLink;
				$description = htmlentities((string)$xmlObject->description);
				$res2 = db_query_params('INSERT INTO plugin_globalsearch_assoc_site_project (assoc_site_id, project_title, project_link, project_description) '.
							'VALUES ($1, $2, $3, $4)', array($arr['assoc_site_id'], $title, $projectLink, $description));
			}
			$res2 = db_query_params('UPDATE plugin_globalsearch_assoc_site SET status_id=$1 WHERE assoc_site_id=$2', array(2, $arr['assoc_site_id']));
		} else {
			$res2 = db_query_params('UPDATE plugin_globalsearch_assoc_site SET status_id=$1 WHERE assoc_site_id=$2', array(4, $arr['assoc_site_id']));
		}
	}
}
