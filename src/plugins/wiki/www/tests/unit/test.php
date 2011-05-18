<?php // #!/usr/local/bin/php -Cq
/* Copyright (C) 2004 Dan Frankowski <dfrankow@cs.umn.edu>
 * Copyright (C) 2004,2005,2006 Reini Urban <rurban@x-ray.at>
 * $Id: test.php 8071 2011-05-18 14:56:14Z vargenau $
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
 * Unit tests for PhpWiki.
 *
 * You must have PEAR's PHPUnit package <http://pear.php.net/package/PHPUnit>.
 * These tests are unrelated to test/maketest.pl, which do not use PHPUnit.
 * These tests run from the command-line as well as from the browser.
 * Use the argv (from cli) or tests (from browser) params to run only certain tests.
 *
 * $ tests.php test=<testname1> test=<testname2> ... db=dba debug=9 level=10
 */
/****************************************************************
   User definable options
*****************************************************************/
// common cfg options are taken from config/config.ini

//TODO: let the user decide which constants to use: define="x=y"
//define('USE_DB_SESSION', false);
//define('ENABLE_USER_NEW', false);

// memory usage: (8MB limit on certain servers)
// setupwiki
// cli:  Mem16712 => Mem16928
// web:  Mem21216 => Mem26332 (5MB)

// dumphtml:
// cli: Mem20696 => Mem31240  (with USECACHE)    (10MB)
// cli: Mem20240 => Mem30212  (without USECACHE) (10MB)
// web: Mem29424 => Mem35400  (without USECACHE) (6MB)
//define('USECACHE', false);

//##################################################################
//
// Preamble needed to get the tests to run.
//
//##################################################################

$cur_dir = getcwd();
// Add root dir to the path
if (substr(PHP_OS,0,3) == 'WIN')
    $cur_dir = str_replace("\\","/", $cur_dir);
$rootdir = $cur_dir . '/../../';
$ini_sep = substr(PHP_OS,0,3) == 'WIN' ? ';' : ':';
$include_path = ini_get('include_path') . $ini_sep
              . $rootdir . $ini_sep . $rootdir . "lib/pear";
ini_set('include_path', $include_path);
define('DEFAULT_LANGUAGE','en'); // don't use browser detection
$LANG='en';
if (!isset($HTTP_SERVER_VARS)) {
   $HTTP_SERVER_VARS =& $_SERVER;
   $HTTP_GET_VARS    =& $_GET;
   $HTTP_POST_VARS   =& $_POST;
}

if (!empty($HTTP_SERVER_VARS) and $HTTP_SERVER_VARS["SERVER_NAME"] == 'phpwiki.sourceforge.net') {
    ini_set('include_path', ini_get('include_path') . ":/usr/share/pear");
    //define('ENABLE_PAGEPERM',false); // costs nothing
    define('USECACHE',false); // really?
    //define('WIKIDB_NOCACHE_MARKUP',1);
}

// available database backends to test:
$database_backends = array(
                           'file',
                           'flatfile', // not yet committed
                           'dba',
                           'SQL',   // default backend defined in the config.ini DSN
                           'ADODB', // same backend as defined in the config.ini DSN
			   // specific backends (need to be setup as db=test_phpwiki)
			   'PearDB_pgsql', 'PearDB_sqlite', 'PearDB_mysql',
			   //'PearDB_oci8','PearDB_mssql',
			   'ADODB_postgres7', 'ADODB_sqlite', 'ADODB_mysql',
			   //'ADODB_oci8', 'ADODB_mssql',
                           // 'cvs'
                           );
if ((int)substr(phpversion(), 1) >= 5)
    array_push($database_backends, 'PDO_pqsql', 'PDO_sqlite', 'PDO_mysql');
                                   //'PDO_oci', 'PDO_odbc'

//TODO: convert cvs test
// For "cvs" see the seperate tests/unit_test_backend_cvs.php (cvs is experimental)
//TODO: read some database values from config.ini, just use the "test_" prefix
// "flatfile" testing occurs in "tests/unit/.testbox/flatfile"
// "dba" needs the DATABASE_DBA_HANDLER, also in the .textbox directory
//$database_dba_handler = (substr(PHP_OS,0,3) == 'WIN') ? "db3" : "gdbm";
// "SQL" and "ADODB" need delete permissions to the test db
//  You have to create that database beforehand with our schema
//$database_dsn = "mysql://wikiuser:@localhost/phpwiki";
$database_prefix = "test_";

// Quiet warnings in IniConfig.php
$HTTP_SERVER_VARS['REMOTE_ADDR'] = '127.0.0.1';
$HTTP_SERVER_VARS['HTTP_USER_AGENT'] = "PHPUnit";

function printMemoryUsage($msg = '') {
    static $mem = 0;
    static $initmem = 0;
    if ($msg) echo $msg,"\n";
    if ((defined('DEBUG') and (DEBUG & 8)) or !defined('DEBUG')) {
        require_once("lib/stdlib.php");
        echo "-- MEMORY USAGE: ";
        $oldmem = $mem;
        $mem = getMemoryUsage();
        if (!$initmem) $initmem = $mem;
        // old libc on sf.net server doesn't understand "%+4d"
        echo sprintf("%8d\t[%s%4d]\t[+%4d]\n", $mem, $mem > $oldmem ? "+" : ($mem == $oldmem ? " " : ""),
                     $mem - $oldmem, $mem - $initmem);
        // TODO: print time
        flush();
    }
}
/* // now in stdlib.php
function printSimpleTrace($bt) {
    //print_r($bt);
    echo "Traceback:\n";
    foreach ($bt as $i => $elem) {
        if (!array_key_exists('file', $elem)) {
            continue;
        }
        print "  " . $elem['file'] . ':' . $elem['line'] . "\n";
    }
}
*/
// Show lots of detail when an assert() in the code fails
function assert_callback( $script, $line, $message ) {
   echo "assert failed: script ", $script," line ", $line," :";
   echo "$message";
   echo "Traceback:\n";
   printSimpleTrace(debug_backtrace());
   exit;
}
$foo = assert_options( ASSERT_CALLBACK, 'assert_callback');

//
// Get error reporting to call back, too
//
// set the error reporting level for this script
if (defined('E_STRICT') and (E_ALL & E_STRICT)) // strict php5?
    error_reporting(E_ALL & ~E_STRICT); 	// exclude E_STRICT
else
    error_reporting(E_ALL); // php4

// This is too strict, fails on every notice and warning.
/*
function myErrorHandler$errno, $errstr, $errfile, $errline) {
   echo "$errfile: $errline: error# $errno: $errstr\n";
   echo "Traceback:\n";
   printSimpleTrace(debug_backtrace());
}
// The ErrorManager version
function _ErrorHandler_CB(&$error) {
   echo "Traceback:\n";
   printSimpleTrace(debug_backtrace());
   if ($error->isFatal()) {
        $error->errno = E_USER_WARNING;
        return true; // ignore error
   }
   return true;
}
// set to the user defined error handler
// $old_error_handler = set_error_handler("myErrorHandler");
// This is already done via _DEBUG_TRACE
//$ErrorManager->pushErrorHandler(new WikiFunctionCb('_ErrorHandler_CB'));
*/

function purge_dir($dir) {
    static $finder;
    if (!isset($finder)) {
        $finder = new FileFinder;
    }
    $fileSet = new fileSet($dir);
    assert(!empty($dir));
    foreach ($fileSet->getFiles() as $f) {
    	unlink("$dir/$f");
    }
}

function purge_testbox() {
    global $DBParams;
    if (isset($GLOBALS['request'])) {
        $dbi = $GLOBALS['request']->getDbh();
    }
    $dir = $DBParams['directory'];
    switch ($DBParams['dbtype']) {
    case 'file':
    case 'flatfile':
        assert(!empty($dir));
        foreach (array('latest_ver','links','page_data','ver_data') as $d) {
            purge_dir("$dir/$d");
        }
        break;
    case 'SQL':
    case 'ADODB':
    case 'PDO':
        foreach (array_reverse($dbi->_backend->_table_names) as $table) {
            $dbi->genericSqlQuery("DELETE FROM $table");
        }
        break;
    case 'dba':
        purge_dir($dir);
        break;
    }
    if (isset($dbi)) {
        $dbi->_cache->close();
        $dbi->_backend->_latest_versions = array();
    }
}

function printConstant($v) {
    echo "$v=";
    if (defined($v)) {
        if (constant($v) or constant($v)===0 or constant($v)==='0') echo constant($v);
        else echo "false";
    } else echo "undefined";
    echo "\n";
}
/**
 * via the HTML sapi interface print a form to easily change the current cmdline settings.
 */
function html_option_form() {
    global $debug_level, $user_level, $start_debug;

    $form = HTML();
    $option = HTML::div(array('class' => 'option'),
                        HTML::span(array('title' => 'doubleclick to (un)select all', 'style'=>'font-weight: bold; padding: 1px; border: 2px outset;','onDblClick'=>'flipAll(\'test[\')'),
                                   ' test: '),
                        HTML::br());
    $i = 0;
    foreach ($GLOBALS['alltests'] as $s) {
        $id = preg_replace("/\W/", "", $s) . $i++;
        $input = array('type' => 'checkbox', 'name' => 'test['.$s.']', 'value' => '1', 'id' => $id);
        if (in_array($s,$GLOBALS['runtests'])) $input['checked'] = 'checked';
        $option->pushContent(HTML::input($input), HTML::label(array('for' => $id), $s), HTML::br());
    }
    $form->pushContent(HTML::td($option));

    $option = HTML::div(array('class' => 'option'),
                        HTML::span(array('title' => 'doubleclick to (un)select all', 'style'=>'font-weight: bold; padding: 1px; border: 2px outset;', 'onDblClick'=>'flipAll(\'db[\')'),
                                   ' db: '),
                        HTML::br());
    foreach ($GLOBALS['database_backends'] as $s) {
        $id = preg_replace("/\W/", "", $s) . $i++;
        $input = array('type' => 'checkbox', 'name' => 'db['.$s.']', 'value' => '1', 'id' => $id);
        if (in_array($s,$GLOBALS['run_database_backends'])) $input['checked'] = 'checked';
        $option->pushContent(HTML::input($input), HTML::label(array('for' => $id), $s), HTML::br());
    }
    $form->pushContent(HTML::td($option));

    $js = JavaScript(
"function flipAll(formName) {
  var isFirstSet = -1;
  formObj = document.forms[0];
  for (var i=0; i < formObj.length; i++) {
      fldObj = formObj.elements[i];
      if ((fldObj.type == 'checkbox') && (fldObj.name.substring(0,formName.length) == formName)) {
         if (isFirstSet == -1)
           isFirstSet = (fldObj.checked) ? true : false;
         fldObj.checked = (isFirstSet) ? false : true;
       }
   }
}
function updateDebugEdit(formObj) {
  val=0;
  for (var i=0; i < formObj.length; i++) {
      fldObj = formObj.elements[i];
      if ((fldObj.type == 'checkbox') && (fldObj.name.substring(0,7) == '_debug[')) {
         if (fldObj.checked) val = val + parseInt(fldObj.value);
       }
   }
   formObj.debug.value = val;
}
function updateLevelEdit(formObj) {
  for (var i=0; i < formObj.length; i++) {
      fldObj = formObj.elements[i];
      if ((fldObj.type == 'radio') && (fldObj.name.substring(0,7) == '_level[')) {
         if (fldObj.checked) {
            formObj.level.value = fldObj.value;
            return;
         }
      }
   }
}");
    $option = HTML::div(array('class' => 'option'),
                        HTML::span(array('title' => 'doubleclick to (un)select all', 'style'=>'font-weight: bold; padding: 1px; border: 2px outset;',
                                         'onDblClick'=>'flipAll(\'_debug[\')'),
                                   ' debug: '),' ',
                        HTML::input(array('name'=>'debug','id'=>'debug',
                                          'value'=>$debug_level,'size'=>5)),
                        HTML::br());
    foreach (array('VERBOSE' 	=> 1,
                   'PAGELINKS' 	=> 2,
                   'PARSER' 	=> 4,
                   'TRACE' 	=> 8,
                   'INFO' 	=> 16,
                   'APD' 	=> 32,
                   'LOGIN' 	=> 64,
                   'SQL' 	=> 128,
                   'REMOTE' 	=> 256,
                   ) as $s => $v) {
        $id = preg_replace("/\W/", "", $s) . $i++;
        $input = array('type' => 'checkbox', 'name' => '_debug[]', 'value' => $v, 'id' => $id,
                       'onClick' => 'updateDebugEdit(this.form)');
        if ($debug_level & $v) $input['checked'] = 'checked';
        $option->pushContent(HTML::input($input), HTML::label(array('for' => $id), "_DEBUG_".$s), HTML::br());
    }
    $form->pushContent(HTML::td($option));

    $option = HTML::div(array('class' => 'option'),
                        HTML::span(array('style'=>'font-weight: bold;'), "level: "),
                        HTML::input(array('name'=>'level','id'=>'level',
                                          'value'=>$user_level,'size'=>5)),
                        HTML::br());
    foreach (array('FORBIDDEN' 	=> -1,
                   'ANON' 	=> 0,
                   'BOGO' 	=> 1,
                   'USER' 	=> 2,
                   'ADMIN' 	=> 10,
                   'UNOBTAINABLE'=> 100,
                   ) as $s => $v) {
        $id = preg_replace("/\W/", "", $s) . $i++;
        $input = array('type' => 'radio', 'name' => '_level[]', 'value' => $v, 'id' => $id,
                       'onClick' => 'updateLevelEdit(this.form)');
        if ($user_level & $v) $input['checked'] = 'checked';
        $option->pushContent(HTML::input($input), HTML::label(array('for' => $id), "WIKIAUTH_".$s), HTML::br());
    }
    $form->pushContent(HTML::td($option));

    unset($input);
    $option = HTML::div(array('class' => 'option'), 'defines: ', HTML::br());
    if (!empty($GLOBALS['define']))
      foreach ($GLOBALS['define'] as $s) {
        if (defined($s)) {
            $input = array('type' => 'edit', 'name' => $s, 'value' => constant($s));
            $option->pushContent(HTML::input($input), HTML::label(array('for' => $id), $s), HTML::br());
        }
    }
    if (!empty($input))
        $form->pushContent(HTML::td($option));
    $table = HTML::form(array('action' => $_SERVER['PHP_SELF'],
                                          'method' => 'GET',
                              'accept-charset' => $GLOBALS['charset']),
                        $js,
                        HTML::table(HTML::tr(array('valign'=>'top'), $form)),
                        HTML::input(array('type' => 'submit')),
                        HTML::input(array('type' => 'reset')));
    if ($start_debug)
        $table->pushContent(HiddenInputs(array('start_debug' => $start_debug)));
    return $table->printXml();
}

//####################################################################
//
// End of preamble, run the test suite ..
//
//####################################################################

ob_start();

if (isset($HTTP_SERVER_VARS['REQUEST_METHOD']))
    echo "<pre>\n";
elseif (!empty($HTTP_SERVER_VARS['argv']))
    $argv = $HTTP_SERVER_VARS['argv'];
elseif (!ini_get("register_argc_argv"))
    echo "Could not read cmd args (register_argc_argv=Off?)\n";
// purge the testbox

$debug_level = 1; //was 9, _DEBUG_VERBOSE | _DEBUG_TRACE
//if (defined('E_STRICT')) $debug_level = 5; // add PARSER flag on php5
$user_level  = 1; // BOGO (conflicts with RateIt)
// use argv (from cli) or tests (from browser) params to run only certain tests
// avoid pear: Console::Getopt
$alltests = array(/* valid tests without clean virgin setup */
                  'InlineParserTest','HtmlParserTest',
                  'PageListTest','ListPagesTest','XmlRpcTest',
                  /* virgin setup */
                  'SetupWiki',
                  /* valid tests only with clean virgin setup */
                  'AllPagesTest','AllUsersTest','OrphanedPagesTest',
                  'WantedPagesTest','TextSearchTest','IncludePageTest',
                  'AtomParserTest','AtomFeedTest',
                  /* final tests which require all valid pages and consumes > 32MB */
                  'DumpHtml');
// support db=file db=dba test=SetupWiki test=DumpHtml debug=num -dconstant=value
// or  db=file,dba test=SetupWiki,DumpHtml debug=num -dconstant=value
if (isset($HTTP_SERVER_VARS['REQUEST_METHOD'])) {
    $argv = array();
    foreach ($HTTP_GET_VARS as $key => $val) {
    	if (is_array($val))
    	    foreach ($val as $k => $v) $argv[] = $key."=".$k;
    	elseif (strstr($val,",") and in_array($key, array("test","db")))
    	    foreach (explode(",",$val) as $v) $argv[] = $key."=".$v;
    	else
            $argv[] = $key."=".$val;
    }
} elseif (!empty($argv) and preg_match("/test\.php$/", $argv[0])) {
    array_shift($argv);
}
if (!empty($argv)) {
    $runtests = array();
    $define = array();
    $run_database_backends = array();
    $m = array();
    foreach ($argv as $arg) {
        if (preg_match("/^test=(.+)$/",$arg,$m) and in_array($m[1], $alltests))
            $runtests[] = $m[1];
        elseif (preg_match("/^db=(.+)$/",$arg,$m) and in_array($m[1], $database_backends))
            $run_database_backends[] = $m[1];
        elseif (preg_match("/^debug=(\d+)$/",$arg,$m))
            $debug_level = $m[1];
        elseif (preg_match("/^level=(\d+)$/",$arg,$m))
            $user_level = $m[1];
        elseif (preg_match("/^\-d(\w+)=(.+)$/",$arg,$m)) {
            $define[$m[1]] = $m[2];
            if ($m[2] == 'true') $m[2] = true;
            elseif ($m[2] == 'false') $m[2] = false;
            if (!defined($m[1])) define($m[1], $m[2]);
        } elseif (in_array($arg, $alltests))
            $runtests[] = $arg;
        elseif ($debug_level & 1)
            echo "ignored arg: ", $arg, "\n";
    }
}

if (empty($run_database_backends))
    $run_database_backends = $database_backends;
if (empty($runtests))
    $runtests = $alltests;
if ($debug_level & 1) {
    //echo "\n";
    echo "PHP_SAPI=",php_sapi_name(), "\n";
    echo "PHP_OS=",PHP_OS, "\n";
    echo "PHP_VERSION=",PHP_VERSION, "\n";
    echo "test=", join(",",$runtests),"\n";
    echo "db=", join(",",$run_database_backends),"\n";
    echo "debug=", $debug_level,"\n";
    echo "level=", $user_level,"\n";
    if (!empty($define)) {
    	foreach ($define as $k => $v) printConstant($k);
    }
    if ($debug_level & 8) {
    	echo "pid=",getmypid(),"\n";
    }
    echo "\n";
}
flush();

if (!defined('DEBUG'))
    define('DEBUG', $debug_level);
// override defaults:
if (!defined('RATING_STORAGE'))
   define('RATING_STORAGE', 'WIKIPAGE');
if (!defined('GROUP_METHOD'))
    define('GROUP_METHOD', 'NONE');

if (DEBUG & 8)
    printMemoryUsage("beforePEAR");

if (DEBUG & 8)
    printMemoryUsage("beforePhpWiki");

define('PHPWIKI_NOMAIN', true);
// Other needed files
require_once $rootdir.'index.php';
require_once $rootdir.'lib/main.php';

// init filefinder for pear path fixup.
FindFile ('PHPUnit.php', 'missing_okay');
// PEAR library (requires version ??)
require_once 'PHPUnit.php';

ob_end_flush();

if ($debug_level & 1) {
    //echo "\n";
    echo "PHPWIKI_VERSION=",PHPWIKI_VERSION,
        strstr(PHPWIKI_VERSION,"pre") ? strftime(" / %Y%m%d") : "","\n";
    if ($debug_level & 9) {
        // which constants affect memory?
        foreach (explode(",","USECACHE,WIKIDB_NOCACHE_MARKUP,"
                            ."ENABLE_USER_NEW,ENABLE_PAGEPERM") as $v) {
            printConstant($v);
        }
    }
    echo "\n";
}

global $ErrorManager;
$ErrorManager->setPostponedErrorMask(EM_FATAL_ERRORS|EM_WARNING_ERRORS|EM_NOTICE_ERRORS);
// FIXME: ignore cached requests (if-modified-since) from cli
class MockRequest extends WikiRequest {
    function MockRequest($dbparams) {
        $this->_dbi = WikiDB::open($dbparams);
        $this->_user = new MockUser("a_user", $GLOBALS['user_level']);
        $this->_group = new GroupNone();
        $this->_args = array('pagename' => 'HomePage', 'action' => 'browse');
        $this->Request();
    }
    function getGroup() {
    	if (is_object($this->_group))
            return $this->_group;
        else // FIXME: this is set to "/f:" somewhere.
            return new GroupNone();
    }
}

if (ENABLE_USER_NEW) {
    class MockUser extends _WikiUser {
        function MockUser($name, $level) {
            $this->_userid = $name;
            $this->_isSignedIn = $level > 1;
            $this->_level = $level;
        }
        function isSignedIn() {
            return $this->_isSignedIn;
        }
    }
} else {
    class MockUser extends WikiUser {
        function MockUser($name, $level) {
            $this->_userid = $name;
            $this->_isSignedIn = $level > 1;
            $this->_level = $level;
        }
        function isSignedIn() {
            return $this->_isSignedIn;
        }
    }
}

/*
if (ENABLE_USER_NEW)
    $request->_user = WikiUser('AnonUser');
else {
    $request->_user = new WikiUser($request, 'AnonUser');
    $request->_prefs = $request->_user->getPreferences();
}
*/
if (DEBUG & _DEBUG_TRACE)
    printMemoryUsage("PhpWikiLoaded");

// provide a nice input form for all options
if (isset($HTTP_SERVER_VARS['REQUEST_METHOD'])) {
    echo html_option_form();
    flush();
}

// save and restore all args for each test.
class phpwiki_TestCase extends PHPUnit_TestCase {
    function setUp() {
        global $request, $WikiTheme;
	include_once("themes/" . THEME . "/themeinfo.php");
        $this->_savedargs = $request->_args;
        $request->_args = array();
        if (DEBUG & 1) {
            echo $this->_name,"\n";
            flush();
        }
    }
    function tearDown() {
        global $request;
        $request->_args = $this->_savedargs;
        if (DEBUG & _DEBUG_TRACE)
            printMemoryUsage();
    }
}

// Test all db backends.
foreach ($run_database_backends as $dbtype) {
    global $request, $DBParams;
    //    if (DEBUG & _DEBUG_TRACE)
    //        printMemoryUsage("PHPUnitInitialized");
    $DBParams['dbtype'] = $dbtype;
    if (string_starts_with($dbtype, 'PearDB_')) {
	$DBParams['dbtype'] = 'SQL';
	$DBParams['dsn'] = preg_replace("/^([^:]+):/", substr($dbtype, 7).":", $DBParams['dsn']);
        echo "dsn: ",$DBParams['dsn'],"\n";
    }
    if (string_starts_with($dbtype, 'ADODB_')) {
	$DBParams['dbtype'] = 'ADODB';
	$DBParams['dsn'] = preg_replace("/^([^:]+):/", substr($dbtype, 6).":", $DBParams['dsn']);
        echo "dsn: ",$DBParams['dsn'],"\n";
    }
    if (string_starts_with($dbtype, 'PDO_')) {
	$DBParams['dbtype'] = 'PDO';
	$DBParams['dsn'] = preg_replace("/^([^:]+):/", substr($dbtype, 4).":", $DBParams['dsn']);
        echo "dsn: ",$DBParams['dsn'],"\n";
    }
    // sqlite fix:
    if (preg_match('/sqlite$/', $dbtype)) {
	$DBParams['dsn'] = preg_replace("/127\.0\.0\.1/", '', $DBParams['dsn']);
        echo "dsn: ",$DBParams['dsn'],"\n";
    }
    $DBParams['directory']            = $cur_dir . '/.testbox';
    if ($dbtype == 'flatfile')
        $DBParams['directory']        = $cur_dir . '/.testbox/flatfile';
    $DBParams['prefix']               = $database_prefix;
    // from config.ini
    //$DBParams['dba_handler']          = $database_dba_handler;

    echo "Testing DB Backend \"$dbtype\" ...\n";
    flush();
    $request = new MockRequest($DBParams);
    if ( ! ENABLE_USER_NEW ) {
        $request->_user->_request =& $request;
        $request->_user->_dbi =& $request->_dbi;
    }
    if (DEBUG & _DEBUG_TRACE)
        printMemoryUsage("PhpWikiInitialized");

    foreach ($runtests as $test) {
    	if (!@ob_get_level()) ob_start();
        $suite  = new PHPUnit_TestSuite("phpwiki");
        if (file_exists(dirname(__FILE__).'/lib/'.$test.'.php'))
            require_once dirname(__FILE__).'/lib/'.$test.'.php';
        else
            require_once dirname(__FILE__).'/lib/plugin/'.$test.'.php';
        $suite->addTest( new PHPUnit_TestSuite($test) );

        @set_time_limit(240);
        $result = PHPUnit::run($suite);
        echo "ran " . $result->runCount() . " tests, " . $result->failureCount() . " failures.\n";
        ob_end_flush();
        if ($result->failureCount() > 0) {
            echo "More detail:\n";
            echo $result->toString();
        }
    }

    $request->chunkOutput();
    $request->_dbi->close();
    unset($request->_user);
    unset($request->_dbi);
    unset($request);
    unset($suite);
    unset($result);
}

if (isset($HTTP_SERVER_VARS['REQUEST_METHOD']))
    echo "</pre>\n";

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
