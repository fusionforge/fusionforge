<?php
// rcs_id('$Id: IniConfig.php 7693 2010-09-17 11:33:14Z rurban $');
/**
 * A configurator intended to read its config from a PHP-style INI file,
 * instead of a PHP file.
 *
 * Pass a filename to the IniConfig() function and it will read all its
 * definitions from there, all by itself, and proceed to do a mass-define
 * of all valid PHPWiki config items.  In this way, we can hopefully be
 * totally backwards-compatible with the old index.php method, while still
 * providing a much tastier on-going experience.
 *
 * @author: Joby Walker, Reini Urban, Matthew Palmer
 */
/*
 * Copyright 2004,2005,2006,2007 $ThePhpWikiProgrammingTeam
 * Copyright 2008-2010 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * DONE:
 * - Convert the value lists to provide defaults, so that every "if
 *      (defined())" and "if (!defined())" can fuck off to the dismal hole
 *      it belongs in.
 * - config.ini => config.php dumper for faster startup. (really faster? to time)
 *
 * TODO:
 * - Old-style index.php => config/config.ini converter.
 *
 * - Don't use too much globals for easier integration into other projects
 *   (namespace pollution). (FusionForge, phpnuke, postnuke, phpBB2, carolina, ...)
 *   Use one global $phpwiki object instead which holds the cfg vars, constants
 *   and all other globals.
 *     (global $FieldSeparator, $charset, $WikiNameRegexp, $KeywordLinkRegexp;
 *      global $DisabledActions, $DBParams, $LANG, $AllActionPages)
 *
 * - Resurrect the larger "config object" code (in config/) so it'll aid the
 *   GUI config writers, and allow us to do proper validation and default
 *   value handling.
 *
 * - Get rid of WikiNameRegexp and KeywordLinkRegexp as globals by finding
 *   everywhere that uses them as variables and modify the code to use
 *   them as constants.
 */

include_once (dirname(__FILE__)."/config.php");
include_once (dirname(__FILE__)."/FileFinder.php");

/**
 * Speed-up iniconfig loading.
 *
 * Dump the static parts of the parsed config/config.ini settings to a fast-loadable config.php file.
 * The dynamic parts are then evaluated as before.
 * Requires write-permissions to config/config.php
 */
function save_dump($file) {
    $vars =& $GLOBALS; // copy + unset not possible
    $ignore = array();
    foreach (array("SERVER","ENV","GET","POST","REQUEST","COOKIE","FILES") as $key) {
        $ignore["HTTP_".$key."_VARS"]++;
        $ignore["_".$key]++;
    }
    foreach (array("HTTP_POST_FILES","GLOBALS","RUNTIMER","ErrorManager",'LANG',
    		   'HOME_PAGE','request','SCRIPT_NAME','VIRTUAL_PATH','SCRIPT_FILENAME') as $key)
    	$ignore[$key]++;
    $fp = fopen($file, "wb");
    fwrite($fp,"<?php\n");
    fwrite($fp,"function wiki_configrestore(){\n");
    //TODO: optimize this by removing ignore, big serialized array and merge into existing GLOBALS
    foreach ($vars as $var => $val) {
    	if (!$ignore[$var])
            fwrite($fp, "\$GLOBALS['".$var."']=unserialize(\""
            		    .addslashes(serialize($val))."\");\n");
    }
    // cannot be optimized, maybe leave away predefined consts somehow
    foreach (get_defined_constants() as $var => $val) {
    	if (substr($var,0,4) != "PHP_" and substr($var,0,2) != "E_"
    	    and substr($var,0,2) != "T_"  and substr($var,0,2) != "M_")
            fwrite($fp, "if(!defined('".$var."')) define('".$var."',unserialize(\""
            	        .addslashes(serialize($val))."\"));\n");
    }
    fwrite($fp, "return 'noerr';}");
    fwrite($fp,"?>");
    fclose($fp);
}

function _check_int_constant(&$c) {
  // if int value == string value, force int type
  if (sprintf("%d",(int)$c) === $c) { // DEBUG & _DEBUG_bla
    $c = (int)$c;
  }
}

function IniConfig($file) {

    // Optionally check config/config.php dump for faster startup
    $dump = substr($file, 0, -3)."php";
    if (isWindows($dump)) $dump = str_replace("/","\\",$dump);
    if (file_exists($dump) and is_readable($dump) and filesize($dump) > 0 and sort_file_mtime($dump, $file) < 0) {
        @include($dump) or die("Error including " . $dump);
        if (function_exists('wiki_configrestore') and (wiki_configrestore() === 'noerr')) {
            fixup_dynamic_configs();
            return;
        }
    }

    // First-time installer detection here...
    // Similar to SetupWiki()
    if (!file_exists($file)) {
        // We need to DATA_PATH for configurator, or pass the posted values
        // somewhow to the script
        $GLOBALS['charset'] = 'utf-8';
        include_once(dirname(__FILE__)."/install.php");
        run_install("_part1");
        if (!defined("_PHPWIKI_INSTALL_RUNNING"))
            trigger_error("Datasource file '$file' does not exist", E_USER_ERROR);
        exit();
    }

    // List of all valid config options to be define()d which take "values" (not
    // booleans). Needs to be categorised, and generally made a lot tidier.
    $_IC_VALID_VALUE = array
        ('WIKI_NAME', 'ADMIN_USER', 'ADMIN_PASSWD',
         'DEFAULT_DUMP_DIR', 'HTML_DUMP_DIR',
         'HTML_DUMP_SUFFIX', 'MAX_UPLOAD_SIZE', 'MINOR_EDIT_TIMEOUT',
         'ACCESS_LOG', 'CACHE_CONTROL', 'CACHE_CONTROL_MAX_AGE',
         'COOKIE_EXPIRATION_DAYS', 'COOKIE_DOMAIN',
         'PASSWORD_LENGTH_MINIMUM', 'USER_AUTH_POLICY',
         'GROUP_METHOD',
         'EDITING_POLICY', 'THEME', 'CHARSET',
         'WIKI_PGSRC', 'DEFAULT_WIKI_PGSRC',
         'ALLOWED_PROTOCOLS', 'INLINE_IMAGES', 'SUBPAGE_SEPARATOR', /*'KEYWORDS',*/
         // extra logic:
         //'DATABASE_PREFIX', 'DATABASE_DSN', 'DATABASE_TYPE', 'DATABASE_DBHANDLER',
	 'DATABASE_OPTIMISE_FREQUENCY',
         'INTERWIKI_MAP_FILE', 'COPYRIGHTPAGE_TITLE', 'COPYRIGHTPAGE_URL',
         'AUTHORPAGE_TITLE', 'AUTHORPAGE_URL',
         'WIKI_NAME_REGEXP',
         'PLUGIN_CACHED_DATABASE', 'PLUGIN_CACHED_FILENAME_PREFIX',
         'PLUGIN_CACHED_HIGHWATER', 'PLUGIN_CACHED_LOWWATER', 'PLUGIN_CACHED_MAXLIFETIME',
         'PLUGIN_CACHED_MAXARGLEN', 'PLUGIN_CACHED_IMGTYPES',
         'WYSIWYG_BACKEND', 'PLUGIN_MARKUP_MAP',
         // extra logic:
         'SERVER_NAME','SERVER_PORT','SCRIPT_NAME', 'DATA_PATH', 'PHPWIKI_DIR', 'VIRTUAL_PATH',
	 'EXTERNAL_HTML2PDF_PAGELIST', 'PLUGIN_CACHED_CACHE_DIR'
         );

    // Optional values which need to be defined.
    // These are not defined in config-default.ini and empty if not defined.
    $_IC_OPTIONAL_VALUE = array
        (
         'DEBUG', 'TEMP_DIR', 'DEFAULT_LANGUAGE',
         'LDAP_AUTH_HOST','LDAP_SET_OPTION','LDAP_BASE_DN', 'LDAP_AUTH_USER',
         'LDAP_AUTH_PASSWORD','LDAP_SEARCH_FIELD','LDAP_OU_GROUP','LDAP_OU_USERS',
         'AUTH_USER_FILE','DBAUTH_AUTH_DSN',
         'IMAP_AUTH_HOST', 'POP3_AUTH_HOST',
         'AUTH_USER_FILE', 'AUTH_GROUP_FILE', 'AUTH_SESS_USER', 'AUTH_SESS_LEVEL',
         'GOOGLE_LICENSE_KEY','FORTUNE_DIR',
         'DISABLE_GETIMAGESIZE','DBADMIN_USER','DBADMIN_PASSWD',
         'SESSION_SAVE_PATH',
         'TOOLBAR_PAGELINK_PULLDOWN', 'TOOLBAR_TEMPLATE_PULLDOWN', 'TOOLBAR_IMAGE_PULLDOWN',
         'EXTERNAL_LINK_TARGET', 'ACCESS_LOG_SQL', 'USE_EXTERNAL_HTML2PDF',
	 'LOGIN_LOG','LDAP_SEARCH_FILTER'
         );

    // List of all valid config options to be define()d which take booleans.
    $_IC_VALID_BOOL = array
        ('ENABLE_USER_NEW', 'ENABLE_PAGEPERM', 'ENABLE_EDIT_TOOLBAR', 'JS_SEARCHREPLACE',
         'ENABLE_XHTML_XML', 'ENABLE_DOUBLECLICKEDIT', 'ENABLE_LIVESEARCH', 'ENABLE_ACDROPDOWN',
         'USECACHE', 'WIKIDB_NOCACHE_MARKUP',
         'ENABLE_REVERSE_DNS', 'ENCRYPTED_PASSWD', 'ZIPDUMP_AUTH',
         'ENABLE_RAW_HTML', 'ENABLE_RAW_HTML_LOCKEDONLY', 'ENABLE_RAW_HTML_SAFE',
         'STRICT_MAILABLE_PAGEDUMPS', 'COMPRESS_OUTPUT',
         'ALLOW_ANON_USER', 'ALLOW_ANON_EDIT',
         'ALLOW_BOGO_LOGIN', 'ALLOW_USER_PASSWORDS',
         'AUTH_USER_FILE_STORABLE', 'ALLOW_HTTP_AUTH_LOGIN',
         'ALLOW_USER_LOGIN', 'ALLOW_LDAP_LOGIN', 'ALLOW_IMAP_LOGIN',
         'WARN_NONPUBLIC_INTERWIKIMAP', 'USE_PATH_INFO',
         'DISABLE_HTTP_REDIRECT',
         'PLUGIN_CACHED_USECACHE', 'PLUGIN_CACHED_FORCE_SYNCMAP',
         'BLOG_DEFAULT_EMPTY_PREFIX', 'DATABASE_PERSISTENT',
         'FUSIONFORGE',
         'ENABLE_DISCUSSION_LINK', 'ENABLE_CAPTCHA',
         'ENABLE_WYSIWYG', 'WYSIWYG_DEFAULT_PAGETYPE_HTML',
         'DISABLE_MARKUP_WIKIWORD', 'ENABLE_MARKUP_COLOR', 'ENABLE_MARKUP_TEMPLATE',
         'ENABLE_MARKUP_MEDIAWIKI_TABLE',
         'ENABLE_MARKUP_DIVSPAN', 'USE_BYTEA', 'UPLOAD_USERDIR', 'DISABLE_UNITS',
	 'ENABLE_SEARCHHIGHLIGHT', 'DISABLE_UPLOAD_ONLY_ALLOWED_EXTENSIONS',
         'ENABLE_AUTH_OPENID', 'INSECURE_ACTIONS_LOCALHOST_ONLY',
         'ENABLE_MAILNOTIFY', 'ENABLE_RECENTCHANGESBOX', 'ENABLE_PAGE_PUBLIC',
         'ENABLE_AJAX', 'ENABLE_EXTERNAL_PAGES',
         'READONLY'
         );

    $rs = @parse_ini_file($file);
    $rsdef = @parse_ini_file(dirname(__FILE__)."/../config/config-default.ini");
    foreach ($rsdef as $k => $v) {
    	if (defined($k)) {
    	    $rs[$k] = constant($k);
    	} elseif (!isset($rs[$k])) {
    	    $rs[$k] = $v;
    	}
    }
    unset($k); unset($v);

    foreach ($_IC_VALID_VALUE as $item) {
        if (defined($item)) {
            unset($rs[$item]);
            continue;
        }
        if (array_key_exists($item, $rs)) {
            _check_int_constant($rs[$item]);
            define($item, $rs[$item]);
            unset($rs[$item]);
        //} elseif (array_key_exists($item, $rsdef)) {
        //    define($item, $rsdef[$item]);
        // calculate them later or not at all:
        } elseif (in_array($item,
                           array('DATABASE_PREFIX', 'SERVER_NAME', 'SERVER_PORT',
                                 'SCRIPT_NAME', 'DATA_PATH', 'PHPWIKI_DIR', 'VIRTUAL_PATH',
                                 'LDAP_AUTH_HOST','IMAP_AUTH_HOST','POP3_AUTH_HOST',
                                 'PLUGIN_CACHED_CACHE_DIR','EXTERNAL_HTML2PDF_PAGELIST')))
        {
            ;
        } elseif (!defined("_PHPWIKI_INSTALL_RUNNING")) {
            trigger_error(sprintf("missing config setting for %s",$item));
        }
    }
    unset($item);

    // Boolean options are slightly special - if they're set to any of
    // '', 'false', '0', or 'no' (all case-insensitive) then the value will
    // be a boolean false, otherwise if there is anything set it'll
    // be true.
    foreach ($_IC_VALID_BOOL as $item) {
        if (defined($item)) {
            unset($rs[$item]);
            continue;
        }
        if (array_key_exists($item, $rs)) {
            $val = $rs[$item];
        //} elseif (array_key_exists($item, $rsdef)) {
        //    $val = $rsdef[$item];
        } else {
            $val = false;
            //trigger_error(sprintf("missing boolean config setting for %s",$item));
        }

        // calculate them later: old or dynamic constants
        if (!array_key_exists($item, $rs) and
            in_array($item, array('USE_PATH_INFO', 'USE_DB_SESSION',
                                  'ALLOW_HTTP_AUTH_LOGIN', 'ALLOW_LDAP_LOGIN',
                                  'ALLOW_IMAP_LOGIN', 'ALLOW_USER_LOGIN',
                                  'REQUIRE_SIGNIN_BEFORE_EDIT',
                                  'WIKIDB_NOCACHE_MARKUP',
                                  'COMPRESS_OUTPUT', 'USE_BYTEA', 'READONLY',
                                  )))
        {
            ;
        }
        elseif (!$val) {
            define($item, false);
        }
        elseif (strtolower($val) == 'false' ||
                strtolower($val) == 'no' ||
                $val == '' ||
                $val == false ||
                $val == '0') {
            define($item, false);
        }
        else {
            define($item, true);
        }
        unset($rs[$item]);
    }
    unset($item);

    // Database
    global $DBParams;
    foreach (array('DATABASE_TYPE' 	=> 'dbtype',
    		   'DATABASE_DSN'  	=> 'dsn',
    		   'DATABASE_SESSION_TABLE' => 'db_session_table',
    		   'DATABASE_DBA_HANDLER'   => 'dba_handler',
    	           'DATABASE_DIRECTORY' => 'directory',
    	           'DATABASE_TIMEOUT'   => 'timeout',
    	           'DATABASE_PREFIX'    => 'prefix')
             as $item => $k)
    {
        if (defined($item)) {
            $DBParams[$k] = constant($item);
            unset($rs[$item]);
        } elseif (array_key_exists($item, $rs)) {
            $DBParams[$k] = $rs[$item];
            define($item, $rs[$item]);
            unset($rs[$item]);
        } elseif (array_key_exists($item, $rsdef)) {
            $DBParams[$k] = $rsdef[$item];
            define($item, $rsdef[$item]);
            unset($rsdef[$item]);
        }
    }
    $valid_database_types = array('SQL','ADODB','PDO','dba','file','flatfile','cvs','cvsclient');
    if (!in_array(DATABASE_TYPE, $valid_database_types))
        trigger_error(sprintf("Invalid DATABASE_TYPE=%s. Choose one of %s",
                              DATABASE_TYPE, join(",", $valid_database_types)),
                      E_USER_ERROR);
    unset($valid_database_types);
    if (DATABASE_TYPE == 'PDO') {
        if (!check_php_version(5))
            trigger_error("Invalid DATABASE_TYPE=PDO. PDO requires at least php-5.0!",
                          E_USER_ERROR);
        // try to load it dynamically (unix only)
        if (!loadPhpExtension("pdo")) {
            echo $GLOBALS['php_errormsg'], "<br>\n";
            trigger_error(sprintf("dl() problem: Required extension '%s' could not be loaded!",
                                  "pdo"),
                          E_USER_ERROR);
        }
    }
    // Detect readonly database, e.g. system mounted read-only for maintenance
    // via dbh->readonly later. Unfortunately not possible as constant.

    // USE_DB_SESSION default logic:
    if (!defined('USE_DB_SESSION')) {
        if ($DBParams['db_session_table']
            and in_array($DBParams['dbtype'], array('SQL','ADODB','PDO','dba'))) {
            define('USE_DB_SESSION', true);
        } else {
            define('USE_DB_SESSION', false);
        }
    }
    unset($item); unset($k);

    // Expiry stuff
    global $ExpireParams;
    foreach (array('major','minor','author') as $major) {
    	foreach (array('max_age','min_age','min_keep','keep','max_keep') as $max) {
    	    $item = strtoupper($major) . '_'. strtoupper($max);
            if (defined($item)) $val = constant($item);
            elseif (array_key_exists($item, $rs))
                $val = $rs[$item];
    	    elseif (array_key_exists($item, $rsdef))
                $val = $rsdef[$item];
            if (!isset($ExpireParams[$major]))
                $ExpireParams[$major] = array();
            $ExpireParams[$major][$max] = $val;
            unset($rs[$item]);
    	}
    }
    unset($item); unset($major); unset($max);

    // User authentication
    if (!isset($GLOBALS['USER_AUTH_ORDER'])) {
        if (isset($rs['USER_AUTH_ORDER']))
            $GLOBALS['USER_AUTH_ORDER'] = preg_split('/\s*:\s*/',
                                                     $rs['USER_AUTH_ORDER']);
        else
            $GLOBALS['USER_AUTH_ORDER'] = array("PersonalPage");
    }

    // Now it's the external DB authentication stuff's turn
    if (in_array('Db', $GLOBALS['USER_AUTH_ORDER']) && empty($rs['DBAUTH_AUTH_DSN'])) {
        $rs['DBAUTH_AUTH_DSN'] = $DBParams['dsn'];
    }

    global $DBAuthParams;
    $DBAP_MAP = array('DBAUTH_AUTH_DSN' => 'auth_dsn',
                      'DBAUTH_AUTH_CHECK' => 'auth_check',
                      'DBAUTH_AUTH_USER_EXISTS' => 'auth_user_exists',
                      'DBAUTH_AUTH_CRYPT_METHOD' => 'auth_crypt_method',
                      'DBAUTH_AUTH_UPDATE' => 'auth_update',
                      'DBAUTH_AUTH_CREATE' => 'auth_create',
                      'DBAUTH_PREF_SELECT' => 'pref_select',
                      'DBAUTH_PREF_INSERT' => 'pref_insert',
                      'DBAUTH_PREF_UPDATE' => 'pref_update',
                      'DBAUTH_IS_MEMBER' => 'is_member',
                      'DBAUTH_GROUP_MEMBERS' => 'group_members',
                      'DBAUTH_USER_GROUPS' => 'user_groups'
                      );
    foreach ($DBAP_MAP as $rskey => $apkey) {
        if (defined($rskey)) {
            $DBAuthParams[$apkey] = constant($rskey);
        } elseif (isset($rs[$rskey])) {
            $DBAuthParams[$apkey] = $rs[$rskey];
            define($rskey, $rs[$rskey]);
        } elseif (isset($rsdef[$rskey])) {
            $DBAuthParams[$apkey] = $rsdef[$rskey];
            define($rskey, $rsdef[$rskey]);
        }
        unset($rs[$rskey]);
    }
    unset($rskey); unset($apkey);

    // TODO: Currently unsupported on non-SQL. Nice to have for RhNavPlugin
    // CHECKME: PDO
    if (array_key_exists('ACCESS_LOG_SQL', $rs)) {
    	// WikiDB_backend::isSql() not yet loaded
        if (!in_array(DATABASE_TYPE, array('SQL','ADODB','PDO')))
            // override false config setting on no SQL WikiDB database.
            define('ACCESS_LOG_SQL', 0);
    }
    // SQL defaults to ACCESS_LOG_SQL = 2
    else {
        define('ACCESS_LOG_SQL',
               in_array(DATABASE_TYPE, array('SQL','ADODB','PDO')) ? 2 : 0);
    }

    global $PLUGIN_MARKUP_MAP;
    $PLUGIN_MARKUP_MAP = array();
    if (defined('PLUGIN_MARKUP_MAP') and trim(PLUGIN_MARKUP_MAP) != "") {
	$_map = preg_split('/\s+/', PLUGIN_MARKUP_MAP);
	foreach ($_map as $v) {
	    list($xml,$plugin) = explode(':', $v);
	    if (!empty($xml) and !empty($plugin))
	        $PLUGIN_MARKUP_MAP[$xml] = $plugin;
	}
	unset($_map); unset($xml); unset($plugin); unset($v);
    }

    if (empty($rs['TEMP_DIR'])) {
	$rs['TEMP_DIR'] = "/tmp";
	if (getenv("TEMP"))
	    $rs['TEMP_DIR'] = getenv("TEMP");
    }
    // optional values will be set to '' to simplify the logic.
    foreach ($_IC_OPTIONAL_VALUE as $item) {
        if (defined($item)) {
            unset($rs[$item]);
            continue;
        }
        if (array_key_exists($item, $rs)) {
	    _check_int_constant($rs[$item]);
            define($item, $rs[$item]);
            unset($rs[$item]);
        } else
            define($item, '');
    }

    if (USE_EXTERNAL_HTML2PDF) {
	$item = 'EXTERNAL_HTML2PDF_PAGELIST';
        if (defined($item)) {
            unset($rs[$item]);
        } elseif (array_key_exists($item, $rs)) {
            define($item, $rs[$item]);
            unset($rs[$item]);
        } elseif (array_key_exists($item, $rsdef)) {
            define($item, $rsdef[$item]);
	}
    }
    unset($item);

    // LDAP bind options
    global $LDAP_SET_OPTION;
    if (defined('LDAP_SET_OPTION') and LDAP_SET_OPTION) {
        $optlist = preg_split('/\s*:\s*/', LDAP_SET_OPTION);
        foreach ($optlist as $opt) {
            $bits = preg_split('/\s*=\s*/', $opt, 2);
            if (count($bits) == 2) {
                if (is_string($bits[0]) and defined($bits[0]))
                    $bits[0] = constant($bits[0]);
                $LDAP_SET_OPTION[$bits[0]] = $bits[1];
            }
            else {
                // Possibly throw some sort of error?
            }
        }
        unset($opt); unset($bits);
    }

    // Default Wiki pages to force loading from pgsrc
    global $GenericPages;
    $GenericPages = preg_split('/\s*:\s*/', @$rs['DEFAULT_WIKI_PAGES']);

    // Wiki name regexp:  Should be a define(), but might needed to be changed at runtime
    // (different LC_CHAR need different posix classes)
    global $WikiNameRegexp;
    $WikiNameRegexp = constant('WIKI_NAME_REGEXP');
    if (!trim($WikiNameRegexp))
       $WikiNameRegexp = '(?<![[:alnum:]])(?:[[:upper:]][[:lower:]]+){2,}(?![[:alnum:]])';

    // Got rid of global $KeywordLinkRegexp by using a TextSearchQuery instead
    // of "Category:Topic"
    if (!isset($rs['KEYWORDS'])) $rs['KEYWORDS'] = @$rsdef['KEYWORDS'];
    if (!isset($rs['KEYWORDS'])) $rs['KEYWORDS'] = "Category* OR Topic*";
    if ($rs['KEYWORDS'] == 'Category:Topic') $rs['KEYWORDS'] = "Category* OR Topic*";
    if (!defined('KEYWORDS')) define('KEYWORDS', $rs['KEYWORDS']);
    //if (empty($keywords)) $keywords = array("Category","Topic");
    //$KeywordLinkRegexp = '(?<=' . implode('|^', $keywords) . ')[[:upper:]].*$';

    // TODO: can this be a constant?
    global $DisabledActions;
    if (!array_key_exists('DISABLED_ACTIONS', $rs)
        and array_key_exists('DISABLED_ACTIONS', $rsdef))
        $rs['DISABLED_ACTIONS'] = @$rsdef['DISABLED_ACTIONS'];
    if (array_key_exists('DISABLED_ACTIONS', $rs))
        $DisabledActions = preg_split('/\s*:\s*/', $rs['DISABLED_ACTIONS']);

    global $PLUGIN_CACHED_IMGTYPES;
    $PLUGIN_CACHED_IMGTYPES = preg_split('/\s*[|:]\s*/', PLUGIN_CACHED_IMGTYPES);

    if (!defined('PLUGIN_CACHED_CACHE_DIR')) {
        if (empty($rs['PLUGIN_CACHED_CACHE_DIR']) and !empty($rsdef['PLUGIN_CACHED_CACHE_DIR']))
            $rs['PLUGIN_CACHED_CACHE_DIR'] = $rsdef['PLUGIN_CACHED_CACHE_DIR'];
        if (empty($rs['PLUGIN_CACHED_CACHE_DIR'])) {
            if (!empty($rs['INCLUDE_PATH'])) {
                @ini_set('include_path', $rs['INCLUDE_PATH']);
                $GLOBALS['INCLUDE_PATH'] = $rs['INCLUDE_PATH'];
            }
            $rs['PLUGIN_CACHED_CACHE_DIR'] = TEMP_DIR . '/cache';
            if (!FindFile($rs['PLUGIN_CACHED_CACHE_DIR'], 1)) { // [29ms]
                FindFile(TEMP_DIR, false, 1);            // TEMP must exist!
                mkdir($rs['PLUGIN_CACHED_CACHE_DIR'], 0777);
            }
            // will throw an error if not exists.
            define('PLUGIN_CACHED_CACHE_DIR', FindFile($rs['PLUGIN_CACHED_CACHE_DIR'],false,1));
        } else {
            define('PLUGIN_CACHED_CACHE_DIR', $rs['PLUGIN_CACHED_CACHE_DIR']);
            // will throw an error if not exists.
            FindFile(PLUGIN_CACHED_CACHE_DIR);
        }
    }

    // process the rest of the config.ini settings:
    foreach ($rs as $item => $v) {
        if (defined($item)) {
            continue;
        } else {
	    _check_int_constant($v);
            define($item, $v);
        }
    }
    unset($item); unset($v);

    unset($rs);
    unset($rsdef);

    fixup_static_configs($file); //[1ms]
    // Dump all globals and constants
    // The question is if reading this is faster then doing IniConfig() + fixup_static_configs()
    if (is_writable($dump)) {
        save_dump($dump);
    }
    // store locale[] in config.php? This is too problematic.
    fixup_dynamic_configs($file); // [100ms]
}

function _ignore_unknown_charset_warning(&$error) {
    //htmlspecialchars(): charset `iso-8859-2' not supported, assuming iso-8859-1
    if (preg_match('/^htmlspecialchars\(\): charset \`.+\' not supported, assuming iso-8859-1/',
                   $error->errstr)) {
        $error->errno = 0;
        return true;  // Ignore error
    }
    return false;
}

// moved from lib/config.php [1ms]
function fixup_static_configs($file) {
    global $FieldSeparator, $charset, $WikiNameRegexp, $AllActionPages;
    global $HTTP_SERVER_VARS, $DBParams, $LANG, $ErrorManager;
    // init FileFinder to add proper include paths
    FindFile("lib/interwiki.map",true);

    // "\x80"-"\x9f" (and "\x00" - "\x1f") are non-printing control
    // chars in iso-8859-*
    // $FieldSeparator = "\263"; // this is a superscript 3 in ISO-8859-1.
    // $FieldSeparator = "\xFF"; // this byte should never appear in utf-8
    // Get rid of constant. pref is dynamic and language specific
    $charset = CHARSET;
    // Disabled: Let the admin decide which charset.
    //if (isset($LANG) and in_array($LANG,array('zh')))
    //    $charset = 'utf-8';
    if (strtolower($charset) == 'utf-8')
        $FieldSeparator = "\xFF";
    else
        $FieldSeparator = "\x81";

    // Some exotic charsets are not supported by htmlspecialchars, which just prints an E_WARNING.
    // Even on simple 8bit charsets, where just <>& need to be replaced. For iso-8859-[2-4] e.g.
    // See <php-src>/ext/standard/html.c
    // For performance reasons we require a magic constant to ignore this warning.
    if (defined('IGNORE_CHARSET_NOT_SUPPORTED_WARNING')
        and IGNORE_CHARSET_NOT_SUPPORTED_WARNING)
    {
        $ErrorManager->pushErrorHandler
            (new WikiFunctionCb('_ignore_unknown_charset_warning'));
    }

    // All pages containing plugins of the same name as the filename
    $ActionPages = explode(':',
      'AllPages:AllUsers:AppendText:AuthorHistory:'
      .'BackLinks:'
      .'CreatePage:'
      .'FullTextSearch:FuzzyPages:'
      .'LikePages:LinkDatabase:LinkSearch:ListRelations:'
      .'ModeratedPage:MostPopular:'
      .'NewPagesPerUser:'
      .'OrphanedPages:'
      .'PageDump:PageHistory:PageInfo:PluginManager:'
      .'RateIt:' // RateIt works only in wikilens derived themes
      .'RandomPage:RecentChanges:RelatedChanges:RecentEdits:'
      .'SearchHighlight:SemanticRelations:SemanticSearch:SystemInfo:'
      .'TitleSearch:'
      .'UpLoad:UserPreferences:'
      .'UserRatings:' // UserRatings works only in wikilens derived themes
      .'WantedPages:WatchPage:WhoIsOnline:WikiAdminSelect');

    // The FUSIONFORGE theme omits them
    if (!FUSIONFORGE) {
       // Add some some action pages depending on configuration
       if (DEBUG) {
          $ActionPages[] = 'DebugInfo';
          $ActionPages[] = 'EditMetaData';
          $ActionPages[] = 'SpellCheck'; // SpellCheck does not work
       }
       $ActionPages[] = 'BlogArchives';
       $ActionPages[] = 'BlogJournal';
       $ActionPages[] = 'InterWikiSearch';
       $ActionPages[] = 'LdapSearch';
       $ActionPages[] = 'PasswordReset';
       $ActionPages[] = 'RecentComments';
       $ActionPages[] = 'TranslateText';
       $ActionPages[] = 'UriResolver';
       $ActionPages[] = 'WikiBlog';
    }

    global $AllAllowedPlugins;
    $AllAllowedPlugins = $ActionPages;
    // Add plugins that have no corresponding action page
    $AllAllowedPlugins[] = 'AsciiSVG';
    $AllAllowedPlugins[] = 'AtomFeed';
    $AllAllowedPlugins[] = 'BoxRight';
    $AllAllowedPlugins[] = 'CalendarList';
    $AllAllowedPlugins[] = 'Calendar';
    $AllAllowedPlugins[] = 'CategoryPage';
    $AllAllowedPlugins[] = 'Chart';
    $AllAllowedPlugins[] = 'Comment';
    $AllAllowedPlugins[] = 'CreateBib';
    $AllAllowedPlugins[] = 'CreateToc';
    $AllAllowedPlugins[] = 'CurrentTime';
    $AllAllowedPlugins[] = 'DeadEndPages';
    $AllAllowedPlugins[] = 'Diff';
    $AllAllowedPlugins[] = 'DynamicIncludePage';
    $AllAllowedPlugins[] = 'ExternalSearch';
    $AllAllowedPlugins[] = 'FacebookLike';
    $AllAllowedPlugins[] = 'FileInfo';
    $AllAllowedPlugins[] = 'GoogleMaps';
    $AllAllowedPlugins[] = 'GooglePlugin';
    $AllAllowedPlugins[] = 'GoTo';
    $AllAllowedPlugins[] = 'HelloWorld';
    $AllAllowedPlugins[] = 'IncludePage';
    $AllAllowedPlugins[] = 'IncludePages';
    $AllAllowedPlugins[] = 'IncludeSiteMap';
    $AllAllowedPlugins[] = 'IncludeTree';
    $AllAllowedPlugins[] = 'ListPages';
    $AllAllowedPlugins[] = 'ListSubpages';
    $AllAllowedPlugins[] = 'MediawikiTable';
    $AllAllowedPlugins[] = 'NoCache';
    $AllAllowedPlugins[] = 'OldStyleTable';
    $AllAllowedPlugins[] = 'PageGroup';
    $AllAllowedPlugins[] = 'PageTrail';
    $AllAllowedPlugins[] = 'PhotoAlbum';
    $AllAllowedPlugins[] = 'PhpHighlight';
    $AllAllowedPlugins[] = 'PopularTags';
    $AllAllowedPlugins[] = 'PopUp';
    $AllAllowedPlugins[] = 'PrevNext';
    $AllAllowedPlugins[] = 'Processing';
    $AllAllowedPlugins[] = 'RawHtml';
    $AllAllowedPlugins[] = 'RecentChangesCached';
    $AllAllowedPlugins[] = 'RecentReferrers';
    $AllAllowedPlugins[] = 'RedirectTo';
    $AllAllowedPlugins[] = 'RichTable';
    $AllAllowedPlugins[] = 'RssFeed';
    $AllAllowedPlugins[] = 'SemanticSearchAdvanced';
    $AllAllowedPlugins[] = 'SiteMap';
    $AllAllowedPlugins[] = 'SyncWiki';
    $AllAllowedPlugins[] = 'SyntaxHighlighter';
    $AllAllowedPlugins[] = 'Template';
    $AllAllowedPlugins[] = 'Transclude';
    $AllAllowedPlugins[] = 'UnfoldSubpages';
    $AllAllowedPlugins[] = 'Video';
    $AllAllowedPlugins[] = 'WikiAdminChown';
    $AllAllowedPlugins[] = 'WikiAdminPurge';
    $AllAllowedPlugins[] = 'WikiAdminRemove';
    $AllAllowedPlugins[] = 'WikiAdminRename';
    $AllAllowedPlugins[] = 'WikiAdminSearchReplace';
    $AllAllowedPlugins[] = 'WikiAdminSetAcl';
    $AllAllowedPlugins[] = 'WikiAdminSetAclSimple';
    $AllAllowedPlugins[] = 'WikiAdminUtils';
    $AllAllowedPlugins[] = 'WikicreoleTable';
    $AllAllowedPlugins[] = 'WikiForm';
    $AllAllowedPlugins[] = 'WikiFormRich';
    $AllAllowedPlugins[] = 'WikiPoll';
    $AllAllowedPlugins[] = 'YouTube';

    // The FUSIONFORGE theme omits them
    if (!FUSIONFORGE) {
        $AllAllowedPlugins[] = 'AddComment';
        $AllAllowedPlugins[] = 'AnalyseAccessLogSql';
        $AllAllowedPlugins[] = 'AsciiMath';
        $AllAllowedPlugins[] = '_AuthInfo';
        $AllAllowedPlugins[] = '_BackendInfo';
        $AllAllowedPlugins[] = 'CacheTest';
        $AllAllowedPlugins[] = 'CategoryPage';
        $AllAllowedPlugins[] = 'FoafViewer';
        $AllAllowedPlugins[] = 'FrameInclude';
        $AllAllowedPlugins[] = 'GraphViz';
        $AllAllowedPlugins[] = '_GroupInfo';
        $AllAllowedPlugins[] = 'HtmlConverter';
        $AllAllowedPlugins[] = 'Imdb';
        $AllAllowedPlugins[] = 'JabberPresence';
        $AllAllowedPlugins[] = 'ListPages';
        $AllAllowedPlugins[] = 'PhpWeather';
        $AllAllowedPlugins[] = 'Ploticus';
        $AllAllowedPlugins[] = 'PopularNearby';
        $AllAllowedPlugins[] = 'PreferenceApp';
        $AllAllowedPlugins[] = '_PreferencesInfo';
        $AllAllowedPlugins[] = '_Retransform';
        $AllAllowedPlugins[] = 'SqlResult';
        $AllAllowedPlugins[] = 'TeX2png';
        $AllAllowedPlugins[] = 'text2png';
        $AllAllowedPlugins[] = 'TexToPng';
        $AllAllowedPlugins[] = 'VisualWiki';
        $AllAllowedPlugins[] = 'WantedPagesOld';
        $AllAllowedPlugins[] = 'WikiAdminChmod';
        $AllAllowedPlugins[] = 'WikiAdminMarkup';
        $AllAllowedPlugins[] = 'WikiForum';
        $AllAllowedPlugins[] = '_WikiTranslation';
    }

    // Used by SetupWiki to pull in required pages, if not translated, then in English.
    // Also used by _WikiTranslation. Really important are only those which return pagelists
    // or contain basic functionality.
    $AllActionPages = $ActionPages;
    $AllActionPages[] = 'AllPagesCreatedByMe';
    $AllActionPages[] = 'AllPagesLastEditedByMe';
    $AllActionPages[] = 'AllPagesOwnedByMe';
    $AllActionPages[] = 'AllPagesByAcl';
    $AllActionPages[] = 'AllUserPages';
    $AllActionPages[] = 'FullRecentChanges';
    $AllActionPages[] = 'LeastPopular';
    $AllActionPages[] = 'LockedPages';
    $AllActionPages[] = 'MyRatings'; // MyRatings works only in wikilens-derived themes
    $AllActionPages[] = 'MyRecentEdits';
    $AllActionPages[] = 'MyRecentChanges';
    $AllActionPages[] = 'PhpWikiAdministration';
    $AllActionPages[] = 'PhpWikiAdministration/Chown';
    $AllActionPages[] = 'PhpWikiAdministration/Purge';
    $AllActionPages[] = 'PhpWikiAdministration/Remove';
    $AllActionPages[] = 'PhpWikiAdministration/Rename';
    $AllActionPages[] = 'PhpWikiAdministration/SearchReplace';
    $AllActionPages[] = 'PhpWikiAdministration/SetAcl';
    $AllActionPages[] = 'PhpWikiAdministration/SetAclSimple';
    $AllActionPages[] = 'RecentChangesMyPages';
    $AllActionPages[] = 'RecentEdits';
    $AllActionPages[] = 'RecentNewPages';
    $AllActionPages[] = 'SetGlobalAccessRights';
    $AllActionPages[] = 'SetGlobalAccessRightsSimple';
    $AllActionPages[] = 'UserContribs';

    // The FUSIONFORGE theme omits them
    if (!FUSIONFORGE) {
       // Add some some action pages depending on configuration
       if (DEBUG) {
          $AllActionPages[] = 'PhpWikiAdministration/Chmod';
       }
       $AllActionPages[] = 'PhpWikiAdministration/Markup';
    }

    if (FUSIONFORGE) {
       if (ENABLE_EXTERNAL_PAGES) {
          $AllAllowedPlugins[] = 'WikiAdminSetExternal';
          $AllActionPages[] = 'ExternalPages';
       }
    }

    // If user has not defined PHPWIKI_DIR, and we need it
    if (!defined('PHPWIKI_DIR') and !file_exists("themes/default")) {
    	$themes_dir = FindFile("themes");
        define('PHPWIKI_DIR', dirname($themes_dir));
    }

    // If user has not defined DATA_PATH, we want to use relative URLs.
    if (!defined('DATA_PATH')) {
        // fix similar to the one suggested by jkalmbach for
        // installations in the webrootdir, like "http://phpwiki.org/HomePage"
        if (!defined('SCRIPT_NAME'))
            define('SCRIPT_NAME', deduce_script_name());
        $temp = dirname(SCRIPT_NAME);
        if ( ($temp == '/') || ($temp == '\\') )
            $temp = '';
        define('DATA_PATH', $temp);
        /*
        if (USE_PATH_INFO)
            define('DATA_PATH', '..');
        */
    }

    //////////////////////////////////////////////////////////////////
    // Select database
    //
    if (empty($DBParams['dbtype']))
        $DBParams['dbtype'] = 'dba';

    if (!defined('THEME'))
        define('THEME', 'default');

    /*$configurator_link = HTML(HTML::br(), "=>",
                              HTML::a(array('href'=>DATA_PATH."/configurator.php"),
    								  _("Configurator")));*/
    // check whether the crypt() function is needed and present
    if (defined('ENCRYPTED_PASSWD') && !function_exists('crypt')) {
        $error = sprintf("Encrypted passwords cannot be used: %s.",
                         "'function crypt()' not available in this version of php");
        trigger_error($error, E_USER_WARNING);
        if (!preg_match("/config\-dist\.ini$/", $file)) { // protect against recursion
            include_once(dirname(__FILE__)."/install.php");
            run_install("_part1");
            exit();
        }
    }

    // Basic configurator validation
    if (!defined('ADMIN_USER') or ADMIN_USER == '') {
    	$error = sprintf("%s may not be empty. Please update your configuration.",
       			 "ADMIN_USER");
        // protect against recursion
        if (!preg_match("/config\-(dist|default)\.ini$/", $file)
            and !defined("_PHPWIKI_INSTALL_RUNNING"))
        {
            include_once(dirname(__FILE__)."/install.php");
            run_install("_part1");
            trigger_error($error, E_USER_ERROR);
            exit();
        } elseif ($HTTP_SERVER_VARS["REQUEST_METHOD"] == "POST") {
            $GLOBALS['HTTP_GET_VARS']['show'] = '_part1';
            trigger_error($error, E_USER_WARNING);
        }
    }
    if (!defined('ADMIN_PASSWD') or ADMIN_PASSWD == '') {
    	$error = sprintf("%s may not be empty. Please update your configuration.",
       			 "ADMIN_PASSWD");
    	// protect against recursion
        if (!preg_match("/config\-(dist|default)\.ini$/", $file)
           and !defined("_PHPWIKI_INSTALL_RUNNING"))
        {
            include_once(dirname(__FILE__)."/install.php");
            run_install("_part1");
            trigger_error($error, E_USER_ERROR);
            exit();
        } elseif ($HTTP_SERVER_VARS["REQUEST_METHOD"] == "POST") {
            $GLOBALS['HTTP_GET_VARS']['show'] = '_part1';
            trigger_error($error, E_USER_WARNING);
        }
    }

    if (defined('USE_DB_SESSION') and USE_DB_SESSION) {
        if (! $DBParams['db_session_table'] ) {
            $DBParams['db_session_table'] = @$DBParams['prefix'] . 'session';
            trigger_error(sprintf("DATABASE_SESSION_TABLE configuration set to %s.",
                                  $DBParams['db_session_table']),
                          E_USER_ERROR);
        }
    }
    // legacy:
    if (!defined('ENABLE_USER_NEW')) define('ENABLE_USER_NEW',true);
    if (!defined('ALLOW_USER_LOGIN'))
        define('ALLOW_USER_LOGIN', defined('ALLOW_USER_PASSWORDS') && ALLOW_USER_PASSWORDS);
    if (!defined('ALLOW_ANON_USER')) define('ALLOW_ANON_USER', true);
    if (!defined('ALLOW_ANON_EDIT')) define('ALLOW_ANON_EDIT', false);
    if (!defined('REQUIRE_SIGNIN_BEFORE_EDIT')) define('REQUIRE_SIGNIN_BEFORE_EDIT', ! ALLOW_ANON_EDIT);
    if (!defined('ALLOW_BOGO_LOGIN')) define('ALLOW_BOGO_LOGIN', true);
    if (!ENABLE_USER_NEW) {
      if (!defined('ALLOW_HTTP_AUTH_LOGIN'))
          define('ALLOW_HTTP_AUTH_LOGIN', false);
      if (!defined('ALLOW_LDAP_LOGIN'))
          define('ALLOW_LDAP_LOGIN', function_exists('ldap_connect') and defined('LDAP_AUTH_HOST'));
      if (!defined('ALLOW_IMAP_LOGIN'))
          define('ALLOW_IMAP_LOGIN', function_exists('imap_open') and defined('IMAP_AUTH_HOST'));
    }

    if (ALLOW_USER_LOGIN and !empty($DBAuthParams) and empty($DBAuthParams['auth_dsn'])) {
        if (isset($DBParams['dsn']))
            $DBAuthParams['auth_dsn'] = $DBParams['dsn'];
    }
}

/**
 * Define constants which are client or request specific and should not be dumped statically.
 * Such as the language, and the virtual and server paths, which might be overridden
 * by startup scripts for wiki farms.
 */
function fixup_dynamic_configs($file) {
    global $WikiNameRegexp;
    global $HTTP_SERVER_VARS, $DBParams, $LANG;

    if (defined('INCLUDE_PATH') and INCLUDE_PATH) {
        @ini_set('include_path', INCLUDE_PATH);
        $GLOBALS['INCLUDE_PATH'] = INCLUDE_PATH;
    }
    if (defined('SESSION_SAVE_PATH') and SESSION_SAVE_PATH)
        @ini_set('session.save_path', SESSION_SAVE_PATH);
    if (!defined('DEFAULT_LANGUAGE'))   // not needed anymore
        define('DEFAULT_LANGUAGE', ''); // detect from client

    // FusionForge hack
    if (!FUSIONFORGE) {
        // Disable update_locale because Zend Debugger crash
        if(! extension_loaded('Zend Debugger')) {
            update_locale(isset($LANG) ? $LANG : DEFAULT_LANGUAGE);
        }
    }

    if (empty($LANG)) {
        if (!defined("DEFAULT_LANGUAGE") or !DEFAULT_LANGUAGE) {
            // TODO: defer this to WikiRequest::initializeLang()
            $LANG = guessing_lang();
            guessing_setlocale (LC_ALL,$LANG);
        }
        else
            $LANG = DEFAULT_LANGUAGE;
    }

    // Set up (possibly fake) gettext()
    // Todo: this could be moved to fixup_static_configs()
    // Bug #1381464 with php-5.1.1
    if (!function_exists ('bindtextdomain')
        and !function_exists ('gettext')
        and !function_exists ('_'))
    {
        $locale = array();

        function gettext ($text) {
            global $locale;
            if (!empty ($locale[$text]))
                return $locale[$text];
            return $text;
        }
        function _ ($text) {
            return gettext($text);
        }
    }
    else {
        $chback = 0;
    	if ($LANG != 'en') {

            // Working around really weird gettext problems: (4.3.2, 4.3.6 win)
            // bindtextdomain() in returns the current domain path.
            // 1. If the script is not index.php but something like "de", on a different path
            //    then bindtextdomain() fails, but after chdir to the correct path it will work okay.
            // 2. But the weird error "Undefined variable: bindtextdomain" is generated then.
            $bindtextdomain_path = FindFile("locale", false, true);
            if (isWindows())
                $bindtextdomain_path = str_replace("/", "\\", $bindtextdomain_path);
            $bindtextdomain_real = @bindtextdomain("phpwiki", $bindtextdomain_path);
            if (realpath($bindtextdomain_real) != realpath($bindtextdomain_path)) {
                // this will happen with virtual_paths. chdir and try again.
                chdir($bindtextdomain_path);
                $chback = 1;
                $bindtextdomain_real = @bindtextdomain("phpwiki", $bindtextdomain_path);
            }
        }
        // tell gettext not to use unicode. PHP >= 4.2.0. Thanks to Kai Krakow.
        if (defined('CHARSET') and function_exists('bind_textdomain_codeset'))
            @bind_textdomain_codeset("phpwiki", CHARSET);
        if ($LANG != 'en')
            textdomain("phpwiki");
        if ($chback) { // change back
            chdir($bindtextdomain_real . (isWindows() ? "\\.." : "/.."));
        }
    }

    // language dependent updates:
    if (!defined('CATEGORY_GROUP_PAGE'))
        define('CATEGORY_GROUP_PAGE',_("CategoryGroup"));
    if (!defined('WIKI_NAME'))
        define('WIKI_NAME', _("An unnamed PhpWiki"));
    if (!defined('HOME_PAGE'))
        define('HOME_PAGE', _("HomePage"));


    //////////////////////////////////////////////////////////////////
    // Autodetect URL settings:
    //
    foreach (array('SERVER_NAME','SERVER_PORT') as $var) {
        //FIXME: for CGI without _SERVER
        if (!defined($var) and !empty($HTTP_SERVER_VARS[$var]))
            // IPV6 fix by matt brown, #1546571
            // An IPv6 address must be surrounded by square brackets to form a valid server name.
            if ($var == 'SERVER_NAME' &&
                    strstr($HTTP_SERVER_VARS[$var], ':')) {
                define($var, '[' . $HTTP_SERVER_VARS[$var] . ']');
            } else {
                define($var, $HTTP_SERVER_VARS[$var]);
            }
    }
    if (!defined('SERVER_NAME')) define('SERVER_NAME', '127.0.0.1');
    if (!defined('SERVER_PORT')) define('SERVER_PORT', 80);
    if (!defined('SERVER_PROTOCOL')) {
        if (empty($HTTP_SERVER_VARS['HTTPS']) || $HTTP_SERVER_VARS['HTTPS'] == 'off')
            define('SERVER_PROTOCOL', 'http');
        else
            define('SERVER_PROTOCOL', 'https');
    }

    if (!defined('SCRIPT_NAME'))
        define('SCRIPT_NAME', deduce_script_name());

    if (!defined('USE_PATH_INFO')) {
        if (isCGI())
            define('USE_PATH_INFO', false);
        else {
            /*
             * If SCRIPT_NAME does not look like php source file,
             * or user cgi we assume that php is getting run by an
             * action handler in /cgi-bin.  In this case,
             * I think there is no way to get Apache to pass
             * useful PATH_INFO to the php script (PATH_INFO
             * is used to the the php interpreter where the
             * php script is...)
             */
            switch (php_sapi_name()) {
            case 'apache':
            case 'apache2handler':
                define('USE_PATH_INFO', true);
                break;
            case 'cgi':
            case 'apache2filter':
                define('USE_PATH_INFO', false);
                break;
            default:
                define('USE_PATH_INFO', ereg('\.(php3?|cgi)$', SCRIPT_NAME));
                break;
            }
        }
    }

    if (SERVER_PORT
        && SERVER_PORT != (SERVER_PROTOCOL == 'https' ? 443 : 80)) {
        define('SERVER_URL',
               SERVER_PROTOCOL . '://' . SERVER_NAME . ':' . SERVER_PORT);
    }
    else {
        define('SERVER_URL',
               SERVER_PROTOCOL . '://' . SERVER_NAME);
    }

    if (!defined('VIRTUAL_PATH')) {
        // We'd like to auto-detect when the cases where apaches
        // 'Action' directive (or similar means) is used to
        // redirect page requests to a cgi-handler.
        //
        // In cases like this, requests for e.g. /wiki/HomePage
        // get redirected to a cgi-script called, say,
        // /path/to/wiki/index.php.  The script gets all
        // of /wiki/HomePage as it's PATH_INFO.
        //
        // The problem is:
        //   How to detect when this has happened reliably?
        //   How to pick out the "virtual path" (in this case '/wiki')?
        //
        // (Another time an redirect might occur is to a DirectoryIndex
        // -- the requested URI is '/wikidir/', the request gets
        // passed to '/wikidir/index.php'.  In this case, the
        // proper VIRTUAL_PATH is '/wikidir/index.php', since the
        // pages will appear at e.g. '/wikidir/index.php/HomePage'.
        //

        $REDIRECT_URL = &$HTTP_SERVER_VARS['REDIRECT_URL'];
        if (USE_PATH_INFO and isset($REDIRECT_URL)
            and ! IsProbablyRedirectToIndex()) {
            // FIXME: This is a hack, and won't work if the requested
            // pagename has a slash in it.
            $temp = strtr(dirname($REDIRECT_URL . 'x'),"\\",'/');
            if ( ($temp == '/') || ($temp == '\\') )
                $temp = '';
            define('VIRTUAL_PATH', $temp);
        } else {
            define('VIRTUAL_PATH', SCRIPT_NAME);
        }
    }

    if (VIRTUAL_PATH != SCRIPT_NAME) {
        // Apache action handlers are used.
        define('PATH_INFO_PREFIX', VIRTUAL_PATH . '/');
    }
    else
        define('PATH_INFO_PREFIX', '/');

    define('PHPWIKI_BASE_URL',
           SERVER_URL . (USE_PATH_INFO ? VIRTUAL_PATH . '/' : SCRIPT_NAME));

    // Detect PrettyWiki setup (not loading index.php directly)
    // $SCRIPT_FILENAME should be the same as __FILE__ in index.php
    if (!isset($SCRIPT_FILENAME))
        $SCRIPT_FILENAME = @$HTTP_SERVER_VARS['SCRIPT_FILENAME'];
    if (!isset($SCRIPT_FILENAME))
        $SCRIPT_FILENAME = @$HTTP_ENV_VARS['SCRIPT_FILENAME'];
    if (!isset($SCRIPT_FILENAME))
        $SCRIPT_FILENAME = dirname(__FILE__.'/../') . '/index.php';
    if (isWindows())
        $SCRIPT_FILENAME = str_replace('\\\\','\\',strtr($SCRIPT_FILENAME, '/', '\\'));
    define('SCRIPT_FILENAME', $SCRIPT_FILENAME);

    // Get remote host name, if apache hasn't done it for us
    if (empty($HTTP_SERVER_VARS['REMOTE_HOST'])
        and !empty($HTTP_SERVER_VARS['REMOTE_ADDR'])
        and ENABLE_REVERSE_DNS)
        $HTTP_SERVER_VARS['REMOTE_HOST'] = gethostbyaddr($HTTP_SERVER_VARS['REMOTE_ADDR']);

}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
