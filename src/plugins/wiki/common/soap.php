<?php
/**
 * SOAP wiki Include - this file contains wrapper functions for the SOAP interface
 *
 * Copyright 2015 (c) Alcatel-Lucent
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once 'FFError.class.php';

// Add wiki in include path
$include_path = explode(PATH_SEPARATOR, get_include_path());
$plugins = '';
for ($i=0; $i<count($include_path); $i++) {
	if (preg_match('/plugins/', $include_path[$i])) {
		$plugins = $include_path[$i];
		break;
	}
}
set_include_path(get_include_path().PATH_SEPARATOR.$plugins.'/wiki/www');

define ("WIKI_SOAP", true);
define ("PHPWIKI_NOMAIN", true);
require_once 'lib/prepend.php';
require_once 'g';

function checkCredentials(&$server, $access, $pagename)
{
	$user = session_get_user();
	$username = $user->getRealName();

	global $request;
	$request->_user = WikiUser($username);

	if (!mayAccessPage($access, $pagename)) {
		$server->fault(401, "no permission, "
						  . "access=$access, "
						  . "pagename=$pagename, "
						  . "username=$username"
						  );
	}
}

$server->wsdl->addComplexType(
	'PageName',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'pagename' => array('name'=>'pagename', 'type' => 'xsd:string'),
	)
);

$server->wsdl->addComplexType(
	'ArrayOfPageNames',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType', 'wsdl:arrayType'=>'tns:PageName[]')),
	'tns:PageName'
);

$server->wsdl->addComplexType(
	'PageMetadata',
	'complexType',
	'struct',
	'all',
	'',
	array(
	'hits' => array('name'=>'hits', 'type' => 'xsd:int'),
	'date' => array('name'=>'date', 'type' => 'xsd:int'),
	'locked' => array('name'=>'locked', 'type' => 'xsd:string'),
	)
);

$server->register(
	'doSavePage',
	array(
		'pagename'=>'xsd:string',
		'content'=>'xsd:string',
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('doSavePageResponse'=>'xsd:string'),
		$uri, $uri.'#doSavePage', 'rpc', 'encoded'
);

$server->register(
	'getPageContent',
	array(
		'pagename'=>'xsd:string',
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('getPageContentResponse'=>'xsd:string'),
		$uri, $uri.'#getPageContent', 'rpc', 'encoded'
);

$server->register(
	'getPageRevision',
	array(
		'pagename'=>'xsd:string',
		'revision'=>'xsd:int',
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('getPageRevisionResponse'=>'xsd:string'),
		$uri, $uri.'#getPageRevision', 'rpc', 'encoded'
);

$server->register(
	'getCurrentRevision',
	array(
		'pagename'=>'xsd:string',
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('getCurrentRevisionResponse'=>'xsd:string'),
		$uri, $uri.'#getCurrentRevision', 'rpc', 'encoded'
);

$server->register(
	'getPageMeta',
	array(
		'pagename'=>'xsd:string',
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('getPageMetaResponse'=>'tns:PageMetadata'),
		$uri, $uri.'#getPageMeta', 'rpc', 'encoded'
);

$server->register(
	'getAllPagenames',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('getAllPagenamesResponse'=>'tns:ArrayOfPageNames'),
		$uri, $uri.'#getAllPagenames', 'rpc', 'encoded'
);

$server->register(
	'getBacklinks',
	array(
		'pagename'=>'xsd:string',
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('getBacklinksResponse'=>'xsd:string'),
		$uri, $uri.'#getBacklinks', 'rpc', 'encoded'
);

$server->register(
	'doTitleSearch',
	array(
		's'=>'xsd:string',
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('doTitleSearchResponse'=>'tns:ArrayOfPageNames'),
		$uri, $uri.'#doTitleSearch', 'rpc', 'encoded'
);

$server->register(
	'doFullTextSearch',
	array(
		's'=>'xsd:string',
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('doFullTextSearchResponse'=>'tns:ArrayOfPageNames'),
		$uri, $uri.'#doFullTextSearch', 'rpc', 'encoded'
);

$server->register(
	'getRecentChanges',
	array(
		'limit'=>'xsd:int',
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('getRecentChangesResponse'=>'tns:ArrayOfPageNames'),
		$uri, $uri.'#getRecentChanges', 'rpc', 'encoded'
);

$server->register(
	'listLinks',
	array(
		'pagename'=>'xsd:string',
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('listLinksResponse'=>'tns:ArrayOfPageNames'),
		$uri, $uri.'#listLinks', 'rpc', 'encoded'
);

$server->register(
	'listPlugins',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('listPluginsResponse'=>'tns:ArrayOfPageNames'),
		$uri, $uri.'#listPlugins', 'rpc', 'encoded'
);

$server->register(
	'getPluginSynopsis',
	array(
		'pluginname'=>'xsd:string',
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('getPluginSynopsisResponse'=>'xsd:string'),
		$uri, $uri.'#getPluginSynopsis', 'rpc', 'encoded'
);

$server->register(
	'listRelations',
	array(
		'option'=>'xsd:int',
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('listRelationsResponse'=>'tns:ArrayOfPageNames'),
		$uri, $uri.'#listRelations', 'rpc', 'encoded'
);

function init_soap($session_ser, $group_id)

{
	continue_session($session_ser);
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault('', '', 'Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault('', '', $grp->getErrorMessage());
	}

	global $page_prefix;
	$page_prefix = '_g'.$group_id.'_';
}

function doSavePage($pagename, $content, $session_ser, $group_id)
{
	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server, 'edit', $pagename);
	$dbi = WikiDB::open($GLOBALS['DBParams']);
	$page = $dbi->getPage($pagename);
	$current = $page->getCurrentRevision();
	$version = $current->getVersion();
	$user = session_get_user();
	$username = $user->getRealName();
	$summary = sprintf(_("SOAP Request by %s"), $username);
	$meta = array('author' => $username,
				  'author_id' => $username,
				  'summary' => $summary,
				  'mtime' => time(),
				  'pagetype' => 'wikitext'
				 );
	$ret = $page->save($content, $version + 1, $meta);
	if ($ret === false) {
		return "Failed";
	} else {
		return "Done";
	}
}

// require 'view' access
function getPageContent($pagename, $session_ser, $group_id)
{
	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server, 'view', $pagename);
	$dbi = WikiDB::open($GLOBALS['DBParams']);
	$page = $dbi->getPage($pagename);
	$rev = $page->getCurrentRevision();
	$text = $rev->getPackedContent();
	return $text;
}

// require 'view' access
function getPageRevision($pagename, $revision, $session_ser, $group_id)
{
	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server, 'view', $pagename);
	$dbi = WikiDB::open($GLOBALS['DBParams']);
	$page = $dbi->getPage($pagename);
	$rev = $page->getRevision($revision);
	$text = $rev->getPackedContent();
	return $text;
}

// require 'view' access
function getCurrentRevision($pagename, $session_ser, $group_id)
{
	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server,'view', $pagename);
	$dbi = WikiDB::open($GLOBALS['DBParams']);
	$page = $dbi->getPage($pagename);
	$version = $page->getVersion();
	return (double)$version;
}

// require 'change' or 'view' access ?
function getPageMeta($pagename, $session_ser, $group_id)
{
	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server, 'view', $pagename);
	$dbi = WikiDB::open($GLOBALS['DBParams']);
	$page = $dbi->getPage($pagename);
	return $page->getMetaData();
}

// require 'view' access to AllPages
function getAllPagenames($session_ser, $group_id)
{
	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server, 'view', _("AllPages"));
	$dbi = WikiDB::open($GLOBALS['DBParams']);
	$page_iter = $dbi->getAllPages();
	$pages = array();
	while ($page = $page_iter->next()) {
		$pages[] = array('pagename' => $page->_pagename);
	}
	sort($pages);
	return $pages;
}

// require 'view' access
function getBacklinks($pagename, $session_ser, $group_id)
{
	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server, 'view', $pagename);
	$dbi = WikiDB::open($GLOBALS['DBParams']);
	$backend = &$dbi->_backend;
	$result = $backend->get_links($pagename);
	$page_iter = new WikiDB_PageIterator($dbi, $result);
	$pages = array();
	while ($page = $page_iter->next()) {
		$pages[] = array('pagename' => $page->getName());
	}
	return $pages;
}

// require 'view' access to TitleSearch
function doTitleSearch($s, $session_ser, $group_id)
{
	require_once 'lib/TextSearchQuery.php';

	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server, 'view', _("TitleSearch"));
	$dbi = WikiDB::open($GLOBALS['DBParams']);
	$query = new TextSearchQuery($s);
	$page_iter = $dbi->titleSearch($query);
	$pages = array();
	while ($page = $page_iter->next()) {
		$pages[] = array('pagename' => $page->getName());
	}
	return $pages;
}

// require 'view' access to FullTextSearch
function doFullTextSearch($s, $session_ser, $group_id)
{
	require_once 'lib/TextSearchQuery.php';

	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server, 'view', _("FullTextSearch"));
	$dbi = WikiDB::open($GLOBALS['DBParams']);
	$query = new TextSearchQuery($s);
	$page_iter = $dbi->fullSearch($query);
	$pages = array();
	while ($page = $page_iter->next()) {
		$pages[] = array('pagename' => $page->getName());
	}
	return $pages;
}

// require 'view' access to RecentChanges
function getRecentChanges($limit = 20, $session_ser, $group_id)
{
	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server, 'view', _("RecentChanges"));
	$dbi = WikiDB::open($GLOBALS['DBParams']);
	$params = array('limit' => $limit, 'since' => false,
		'include_minor_revisions' => false);
	$page_iter = $dbi->mostRecent($params);
	$pages = array();
	while ($page = $page_iter->next()) {
		$pages[] = array('pagename' => $page->getName());
	}
	return $pages;
}

// require 'view' access
function listLinks($pagename, $session_ser, $group_id)
{
	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server, 'view', $pagename);
	$dbi = WikiDB::open($GLOBALS['DBParams']);
	$page = $dbi->getPage($pagename);
	$linkiterator = $page->getPageLinks();
	$links = array();
	while ($currentpage = $linkiterator->next()) {
		if ($currentpage->exists())
			$links[] = array('pagename' => $currentpage->getName());
	}
	return $links;
}

function listPlugins($session_ser, $group_id)
{
	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server, 'change', _("HomePage"));
	$plugin_dir = 'lib/plugin';
	if (defined('PHPWIKI_DIR'))
		$plugin_dir = PHPWIKI_DIR . "/$plugin_dir";
	$pd = new fileSet($plugin_dir, '*.php');
	$plugins = $pd->getFiles();
	unset($pd);
	sort($plugins);
	$RetArray = array();
	if (!empty($plugins)) {
		require_once 'lib/WikiPlugin.php';
		$w = new WikiPluginLoader();
		foreach ($plugins as $plugin) {
			$pluginName = str_replace(".php", "", $plugin);
			$p = $w->getPlugin($pluginName, false); // second arg?
			// trap php files which aren't WikiPlugin~s: wikiplugin + wikiplugin_cached only
			if (strtolower(substr(get_parent_class($p), 0, 10)) == 'wikiplugin') {
				$RetArray[] = array('pagename' => $pluginName);
			}
		}
	}
	return $RetArray;
}

function getPluginSynopsis($pluginname, $session_ser, $group_id)
{
	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server, 'change', "Help/" . $pluginname . "Plugin");
	require_once 'lib/WikiPlugin.php';
	$w = new WikiPluginLoader();
	$synopsis = '';
	$p = $w->getPlugin($pluginname, false); // second arg?
	// trap php files which aren't WikiPlugin~s: wikiplugin + wikiplugin_cached only
	if (strtolower(substr(get_parent_class($p), 0, 10)) == 'wikiplugin') {
		$plugin_args = '';
		$desc = $p->getArgumentsDescription();
		$desc = str_replace("<br />", ' ', $desc->asXML());
		if ($desc)
			$plugin_args = ' ' . $desc;
		$synopsis = "<<" . $pluginname . $plugin_args . ">>";
	}
	return $synopsis;
}

/*
 * array listRelations([ Integer option = 1 ])
 *
 * Returns an array of all available relation names.
 *   option: 1 relations only ( with 0 also )
 *   option: 2 attributes only
 *   option: 3 both, all names of relations and attributes
 *   option: 4 unsorted, this might be added as bitvalue: 7 = 4+3. default: sorted
 * For some semanticweb autofill methods.
 *
 * @author: Reini Urban
 */
function listRelations($option = 1, $session_ser, $group_id)
{
	init_soap($session_ser, $group_id);
	global $server;
	checkCredentials($server, 'view', _("HomePage"));
	$dbi = WikiDB::open($GLOBALS['DBParams']);
	$also_attributes = $option & 2;
	$only_attributes = $option & 2 and !($option & 1);
	$sorted = !($option & 4);
	$relations = $dbi->listRelations($also_attributes, $only_attributes, $sorted);
	sort($relations);
	$relations = array_unique($relations);
	$pagelist = array();
	foreach ($relations as $relation) {
		$pagelist[] = array('pagename' => $relation);
	}
	return $pagelist;
}
