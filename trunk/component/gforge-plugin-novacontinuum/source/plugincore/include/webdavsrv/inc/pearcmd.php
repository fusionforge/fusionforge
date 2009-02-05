<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Stig Bakken <ssb@php.net>                                   |
// |          Tomas V.V.Cox <cox@idecnet.com>                             |
// |                                                                      |
// +----------------------------------------------------------------------+
//
// $Id: pearcmd.php,v 1.13.2.2 2004/12/30 05:43:18 cellog Exp $

ob_end_clean();
/**
 * @nodep Gtk
 */
if ('@include_path@' != '@'.'include_path'.'@') {
    ini_set('include_path', '@include_path@');
}
ini_set('allow_url_fopen', true);
if (!ini_get('safe_mode')) {
    @set_time_limit(0);
}
ob_implicit_flush(true);
ini_set('track_errors', true);
ini_set('html_errors', false);
ini_set('magic_quotes_runtime', false);
set_error_handler('error_handler');

$pear_package_version = "5.0.4";

require_once 'PEAR.php';
require_once 'PEAR/Config.php';
require_once 'PEAR/Command.php';
require_once 'Console/Getopt.php';

PEAR_Command::setFrontendType('CLI');
$all_commands = PEAR_Command::getCommands();

$argv = Console_Getopt::readPHPArgv();
/* $progname = basename($argv[0]); */
$progname = 'pear';
if (in_array('getopt2', get_class_methods('Console_Getopt'))) {
    array_shift($argv);
    $options = Console_Getopt::getopt2($argv, "c:C:d:D:Gh?sSqu:vV");
} else {
    $options = Console_Getopt::getopt($argv, "c:C:d:D:Gh?sSqu:vV");
}
if (PEAR::isError($options)) {
    usage($options);
}

$opts = $options[0];

$fetype = 'CLI';
if ($progname == 'gpear' || $progname == 'pear-gtk') {
    $fetype = 'Gtk';
} else {
    foreach ($opts as $opt) {
        if ($opt[0] == 'G') {
            $fetype = 'Gtk';
        }
    }
}
PEAR_Command::setFrontendType($fetype);
$ui = &PEAR_Command::getFrontendObject();
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ui, "displayFatalError"));
if (ini_get('safe_mode')) {
    $ui->outputData('WARNING: running in safe mode requires that all files created ' .
        'be the same uid as the current script.  PHP reports this script is uid: ' .
        @getmyuid() . ', and current user is: ' . @get_current_user());
}

$pear_user_config = '';
$pear_system_config = '';
$store_user_config = false;
$store_system_config = false;
$verbose = 1;

foreach ($opts as $opt) {
    switch ($opt[0]) {
        case 'c':
            $pear_user_config = $opt[1];
            break;
        case 'C':
            $pear_system_config = $opt[1];
            break;
    }
}

$config = &PEAR_Config::singleton($pear_user_config, $pear_system_config);
$verbose = $config->get("verbose");
$cmdopts = array();

foreach ($opts as $opt) {
    $param = !empty($opt[1]) ? $opt[1] : true;
    switch ($opt[0]) {
        case 'd':
            list($key, $value) = explode('=', $param);
            $config->set($key, $value, 'user');
            break;
        case 'D':
            list($key, $value) = explode('=', $param);
            $config->set($key, $value, 'system');
            break;
        case 's':
            $store_user_config = true;
            break;
        case 'S':
            $store_system_config = true;
            break;
        case 'u':
            $config->remove($param, 'user');
            break;
        case 'v':
            $config->set('verbose', $config->get('verbose') + 1);
            break;
        case 'q':
            $config->set('verbose', $config->get('verbose') - 1);
            break;
        case 'V':
            usage(null, 'version');
        default:
            // all non pear params goes to the command
            $cmdopts[$opt[0]] = $param;
            break;
    }
}

if ($store_system_config) {
    $config->store('system');
}

if ($store_user_config) {
    $config->store('user');
}

$command = (isset($options[1][0])) ? $options[1][0] : null;

if (empty($command) && ($store_user_config || $store_system_config)) {
    exit;
}

if ($fetype == 'Gtk') {
    Gtk::main();
} else do {
    if ($command == 'help') {
        usage(null, @$options[1][1]);
    }

    PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
    $cmd = PEAR_Command::factory($command, $config);
    PEAR::popErrorHandling();
    if (PEAR::isError($cmd)) {
        usage(null, @$options[1][1]);
    }

    $short_args = $long_args = null;
    PEAR_Command::getGetoptArgs($command, $short_args, $long_args);
    if (in_array('getopt2', get_class_methods('Console_Getopt'))) {
        array_shift($options[1]);
        $tmp = Console_Getopt::getopt2($options[1], $short_args, $long_args);
    } else {
        $tmp = Console_Getopt::getopt($options[1], $short_args, $long_args);
    }
    if (PEAR::isError($tmp)) {
        break;
    }
    list($tmpopt, $params) = $tmp;
    $opts = array();
    foreach ($tmpopt as $foo => $tmp2) {
        list($opt, $value) = $tmp2;
        if ($value === null) {
            $value = true; // options without args
        }
        if (strlen($opt) == 1) {
            $cmdoptions = $cmd->getOptions($command);
            foreach ($cmdoptions as $o => $d) {
                if (@$d['shortopt'] == $opt) {
                    $opts[$o] = $value;
                }
            }
        } else {
            if (substr($opt, 0, 2) == '--') {
                $opts[substr($opt, 2)] = $value;
            }
        }
    }
    $ok = $cmd->run($command, $opts, $params);
    if ($ok === false) {
        PEAR::raiseError("unknown command `$command'");
    }
} while (false);

// {{{ usage()

function usage($error = null, $helpsubject = null)
{
    global $progname, $all_commands;
    $stderr = fopen('php://stderr', 'w');
    if (PEAR::isError($error)) {
        fputs($stderr, $error->getMessage() . "\n");
    } elseif ($error !== null) {
        fputs($stderr, "$error\n");
    }
    if ($helpsubject != null) {
        $put = cmdHelp($helpsubject);
    } else {
        $put =
            "Commands:\n";
        $maxlen = max(array_map("strlen", $all_commands));
        $formatstr = "%-{$maxlen}s  %s\n";
        ksort($all_commands);
        foreach ($all_commands as $cmd => $class) {
            $put .= sprintf($formatstr, $cmd, PEAR_Command::getDescription($cmd));
        }
        $put .=
            "Usage: $progname [options] command [command-options] <parameters>\n".
            "Type \"$progname help options\" to list all options.\n".
            "Type \"$progname help shortcuts\" to list all command shortcuts.\n".
            "Type \"$progname help <command>\" to get the help for the specified command.";
    }
    fputs($stderr, "$put\n");
    fclose($stderr);
    exit;
}

function cmdHelp($command)
{
    global $progname, $all_commands, $config;
    if ($command == "options") {
        return
        "Options:\n".
        "     -v         increase verbosity level (default 1)\n".
        "     -q         be quiet, decrease verbosity level\n".
        "     -c file    find user configuration in `file'\n".
        "     -C file    find system configuration in `file'\n".
        "     -d foo=bar set user config variable `foo' to `bar'\n".
        "     -D foo=bar set system config variable `foo' to `bar'\n".
        "     -G         start in graphical (Gtk) mode\n".
        "     -s         store user configuration\n".
        "     -S         store system configuration\n".
        "     -u foo     unset `foo' in the user configuration\n".
        "     -h, -?     display help/usage (this message)\n".
        "     -V         version information\n";
    } elseif ($command == "shortcuts") {
        $sc = PEAR_Command::getShortcuts();
        $ret = "Shortcuts:\n";
        foreach ($sc as $s => $c) {
            $ret .= sprintf("     %-8s %s\n", $s, $c);
        }
        return $ret;

    } elseif ($command == "version") {
        return "PEAR Version: ".$GLOBALS['pear_package_version'].
               "\nPHP Version: ".phpversion().
               "\nZend Engine Version: ".zend_version().
               "\nRunning on: ".php_uname();

    } elseif ($help = PEAR_Command::getHelp($command)) {
        if (is_string($help)) {
            return "$progname $command [options] $help\n";
        }
        if ($help[1] === null) {
            return "$progname $command $help[0]";
        } else {
            return "$progname $command [options] $help[0]\n$help[1]";
        }
    }
    return "Command '$command' is not valid, try 'pear help'";
}

// }}}

function error_handler($errno, $errmsg, $file, $line, $vars) {
    if ((defined('E_STRICT') && $errno & E_STRICT) || !error_reporting()) {
        return; // @silenced error
    }
    $errortype = array (
        E_ERROR   =>  "Error",
        E_WARNING   =>  "Warning",
        E_PARSE   =>  "Parsing Error",
        E_NOTICE   =>  "Notice",
        E_CORE_ERROR  =>  "Core Error",
        E_CORE_WARNING  =>  "Core Warning",
        E_COMPILE_ERROR  =>  "Compile Error",
        E_COMPILE_WARNING =>  "Compile Warning",
        E_USER_ERROR =>  "User Error",
        E_USER_WARNING =>  "User Warning",
        E_USER_NOTICE =>  "User Notice"
    );
    $prefix = $errortype[$errno];
    $file = basename($file);
    print "\n$prefix: $errmsg in $file on line $line\n";
}


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * mode: php
 * End:
 */
// vim600:syn=php

?>
