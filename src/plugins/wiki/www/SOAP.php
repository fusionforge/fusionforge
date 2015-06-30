<?php
/**
 * SOAP server
 * Taken from http://www.wlug.org.nz/archive/
 * Please see http://phpwiki.sourceforge.net/phpwiki/PhpWiki.wdsl
 * for the wdsl discussion.
 *
 * Todo:
 * checkCredentials: set the $GLOBALS['request']->_user object for
 *                   mayAccessPage
 * server url:
 *   Installer helper which changes server url of the default PhpWiki.wdsl
 *   Or do it dynamically in the soap class? No, the client must connect to us.
 *
 * @author: Reini Urban
 * @author: Marc-Etienne Vargenau
 *          Rewrite with native PHP 5 SOAP
 */
define ("WIKI_SOAP", true);
define ("PHPWIKI_NOMAIN", true);

require_once 'lib/prepend.php';
require_once 'lib/IniConfig.php';
IniConfig('config/config.ini');
require_once 'lib/main.php';

function checkCredentials(&$server, &$credentials, $access, $pagename)
{
    // check the "Authorization: Basic '.base64_encode("$this->username:$this->password").'\r\n'" header
    if (isset($server->header['Authorization'])) {
        $line = base64_decode(str_replace("Basic ", "", trim($server->header['Authorization'])));
        list($username, $password) = explode(':', $line);
    } elseif ($credentials && is_string($credentials) && base64_decode($credentials, true)) {
        list($username, $password) = explode(':', base64_decode($credentials));
    } else {
        if (!isset($_SERVER))
            $_SERVER =& $GLOBALS['HTTP_SERVER_VARS'];
            // TODO: where in the header is the client IP
            if (!isset($username)) {
                if (isset($_SERVER['REMOTE_ADDR']))
                    $username = $_SERVER['REMOTE_ADDR'];
                elseif (isset($GLOBALS['REMOTE_ADDR']))
                    $username = $GLOBALS['REMOTE_ADDR'];
                else
                    $username = $server->host;
            }
    }
    if (!isset($password))
        $password = '';

    global $request;
    $request->_user = WikiUser($username);
    $request->_user->AuthCheck(array('userid' => $username, 'passwd' => $password));

    if (!mayAccessPage($access, $pagename)) {
        $server->fault(401, "no permission, "
                          . "access=$access, "
                          . "pagename=$pagename, "
                          . "username=$username"
                          );
    }
    $credentials = array('username' => $username, 'password' => $password);
}

class PhpWikiSoapServer
{
    //todo: check and set credentials
    // requiredAuthorityForPage($action);
    // require 'edit' access
    function doSavePage($pagename, $content, $credentials = false)
    {
        global $server;
        checkCredentials($server, $credentials, 'edit', $pagename);
        $dbi = WikiDB::open($GLOBALS['DBParams']);
        $page = $dbi->getPage($pagename);
        $current = $page->getCurrentRevision();
        $version = $current->getVersion();
        $userid = $credentials['username'];
        $summary = sprintf(_("SOAP Request by %s"), $userid);
        $meta = array('author' => $userid,
                      'author_id' => $userid,
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
    function getPageContent($pagename, $credentials = false)
    {
        global $server;
        checkCredentials($server, $credentials, 'view', $pagename);
        $dbi = WikiDB::open($GLOBALS['DBParams']);
        $page = $dbi->getPage($pagename);
        $rev = $page->getCurrentRevision();
        $text = $rev->getPackedContent();
        return $text;
    }

    // require 'view' access
    function getPageRevision($pagename, $revision, $credentials = false)
    {
        global $server;
        checkCredentials($server, $credentials, 'view', $pagename);
        $dbi = WikiDB::open($GLOBALS['DBParams']);
        $page = $dbi->getPage($pagename);
        $rev = $page->getRevision($revision);
        $text = $rev->getPackedContent();
        return $text;
    }

    // require 'view' access
    function getCurrentRevision($pagename, $credentials = false)
    {
        global $server;
        checkCredentials($server, $credentials, 'view', $pagename);
        $dbi = WikiDB::open($GLOBALS['DBParams']);
        $page = $dbi->getPage($pagename);
        $version = $page->getVersion();
        return (double)$version;
    }

    // require 'change' or 'view' access ?
    function getPageMeta($pagename, $credentials = false)
    {
        global $server;
        checkCredentials($server, $credentials, 'view', $pagename);
        $dbi = WikiDB::open($GLOBALS['DBParams']);
        $page = $dbi->getPage($pagename);
        return $page->getMetaData();
    }

    // require 'view' access to AllPages
    function getAllPagenames($credentials = false)
    {
        global $server;
        checkCredentials($server, $credentials, 'view', _("AllPages"));
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
    function getBacklinks($pagename, $credentials = false)
    {
        global $server;
        checkCredentials($server, $credentials, 'view', $pagename);
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
    function doTitleSearch($s, $credentials = false)
    {
        require_once 'lib/TextSearchQuery.php';

        global $server;
        checkCredentials($server, $credentials, 'view', _("TitleSearch"));
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
    function doFullTextSearch($s, $credentials = false)
    {
        require_once 'lib/TextSearchQuery.php';

        global $server;
        checkCredentials($server, $credentials, 'view', _("FullTextSearch"));
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
    function getRecentChanges($limit = false, $since = false, $include_minor = false, $credentials = false)
    {
        global $server;
        checkCredentials($server, $credentials, 'view', _("RecentChanges"));
        $dbi = WikiDB::open($GLOBALS['DBParams']);
        $params = array('limit' => $limit, 'since' => $since,
            'include_minor_revisions' => $include_minor);
        $page_iter = $dbi->mostRecent($params);
        $pages = array();
        while ($page = $page_iter->next()) {
            $pages[] = array('pagename' => $page->getName(),
                'lastModified' => $page->get('mtime'),
                'author' => $page->get('author'),
                'summary' => $page->get('summary'), // added with 1.3.13
                'version' => $page->getVersion()
            );
        }
        return $pages;
    }

    // require 'view' access
    function listLinks($pagename, $credentials = false)
    {
        global $server;
        checkCredentials($server, $credentials, 'view', $pagename);
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

    function listPlugins($credentials = false)
    {
        global $server;
        checkCredentials($server, $credentials, 'change', _("HomePage"));
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
                    $RetArray[] = $pluginName;
                }
            }
        }
        return $RetArray;
    }

    function getPluginSynopsis($pluginname, $credentials = false)
    {
        global $server;
        checkCredentials($server, $credentials, 'change', "Help/" . $pluginname . "Plugin");
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

    // only plugins returning pagelists will return something useful. so omit the html output
    function callPlugin($pluginname, $plugin_args, $credentials = false)
    {
        global $request;
        global $server;
        checkCredentials($server, $credentials, 'change', "Help/" . $pluginname . "Plugin");

        $dbi = WikiDB::open($GLOBALS['DBParams']);
        $basepage = '';
        require_once 'lib/WikiPlugin.php';
        $w = new WikiPluginLoader();
        $p = $w->getPlugin($pluginname, false); // second arg?
        $pagelist = $p->run($dbi, $plugin_args, $request, $basepage);
        $pages = array();
        if (is_object($pagelist) and is_a($pagelist, 'PageList')) {
            foreach ($pagelist->pageNames() as $name)
                $pages[] = array('pagename' => $name);
        }
        return $pages;
    }

    /**
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
    function listRelations($option = 1, $credentials = false)
    {
        global $server;
        checkCredentials($server, $credentials, 'view', _("HomePage"));
        $dbi = WikiDB::open($GLOBALS['DBParams']);
        $also_attributes = $option & 2;
        $only_attributes = $option & 2 and !($option & 1);
        $sorted = !($option & 4);
        $relations = $dbi->listRelations($also_attributes,
            $only_attributes,
            $sorted);
        return array_keys(array_flip($relations)); // Remove duplicates
    }

    // some basic semantic search
    function linkSearch($linktype, $search, $pages = "*", $relation = "*", $credentials = false)
    {
        global $server;
        checkCredentials($server, $credentials, 'view', _("HomePage"));
        $dbi = WikiDB::open($GLOBALS['DBParams']);
        require_once 'lib/TextSearchQuery.php';
        $pagequery = new TextSearchQuery($pages);
        $linkquery = new TextSearchQuery($search);
        if ($linktype == 'relation') {
            $relquery = new TextSearchQuery($relation);
            $links = $dbi->_backend->link_search($pagequery, $linkquery, $linktype, $relquery);
        } elseif ($linktype == 'attribute') { // only numeric search with attributes!
            $relquery = new TextSearchQuery($relation);
            require_once 'lib/SemanticWeb.php';
            // search: "population > 1 million and area < 200 km^2" relation="*" pages="*"
            $linkquery = new SemanticAttributeSearchQuery($search, $relation);
            $links = $dbi->_backend->link_search($pagequery, $linkquery, $linktype, $relquery);
        } else {
            // we already do have forward and backlinks as SOAP
            $links = $dbi->_backend->link_search($pagequery, $linkquery, $linktype);
        }
        return $links->asArray();
    }
}

$server = new SoapServer('PhpWiki.wsdl');
$server->setClass('PhpWikiSoapServer');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST') {
    $server->handle();
} elseif (isset($_SERVER['QUERY_STRING'])) {
    // Return the WSDL
    $wsdl = @implode('', @file('PhpWiki.wsdl'));
    if (strlen($wsdl) > 1) {
        header("Content-type: text/xml");
        echo $wsdl;
    } else {
        header("Status: 500 Internal Server Error");
        header("Content-type: text/plain");
        echo "HTTP/1.0 500 Internal Server Error";
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
