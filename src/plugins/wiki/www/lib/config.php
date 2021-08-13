<?php
/**
 * Copyright © 2000-2001 Arno Hollosi
 * Copyright © 2000-2001 Steve Wainstead
 * Copyright © 2001-2003 Jeff Dairiki
 * Copyright © 2002-2002 Carsten Klapp
 * Copyright © 2002-2002 Lawrence Akka
 * Copyright © 2002,2004-2009 Reini Urban
 * Copyright © 2008-2014 Marc-Etienne Vargenau, Alcatel-Lucent
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
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 */

/*
 * NOTE: The settings here should probably not need to be changed.
 * The user-configurable settings have been moved to IniConfig.php
 */

if (!defined("LC_ALL")) {
    define("LC_ALL", 0);
    define("LC_CTYPE", 2);
}
// debug flags:
define ('_DEBUG_VERBOSE', 1); // verbose msgs and add validator links on footer
define ('_DEBUG_PAGELINKS', 2); // list the extraced pagelinks at the top of each pages
define ('_DEBUG_PARSER', 4); // verbose parsing steps
define ('_DEBUG_TRACE', 8); // test php memory usage, prints php debug backtraces
define ('_DEBUG_INFO', 16);
define ('_DEBUG_APD', 32); // APD tracing/profiling
define ('_DEBUG_LOGIN', 64); // verbose login debug-msg (settings and reason for failure)
define ('_DEBUG_SQL', 128); // force check db, force optimize, print some debugging logs
define ('_DEBUG_REMOTE', 256); // remote debug into subrequests (xmlrpc, ajax, wikiwyg, ...)
// or test local SearchHighlight.
// internal links have persistent ?start_debug=1

function isCGI()
{
    return (substr(php_sapi_name(), 0, 3) == 'cgi' and
        isset($GLOBALS['HTTP_ENV_VARS']['GATEWAY_INTERFACE']) and
            @preg_match('/CGI/', $GLOBALS['HTTP_ENV_VARS']['GATEWAY_INTERFACE']));
}

/**
 * If $LANG is undefined:
 * Smart client language detection, based on our supported languages
 * HTTP_ACCEPT_LANGUAGE="de-at,en;q=0.5"
 *   => "de"
 * We should really check additionally if the i18n HomePage version is defined.
 * So must defer this to the request loop.
 *
 * @return string
 */
function guessing_lang()
{
    $languages = array("en", "de", "es", "fr", "it", "ja", "zh", "nl", "sv");

    $accept = false;
    if (isset($GLOBALS['request'])) // in fixup-dynamic-config there's no request yet
        $accept = $GLOBALS['request']->get('HTTP_ACCEPT_LANGUAGE');
    elseif (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

    if ($accept) {
        $lang_list = array();
        $list = explode(",", $accept);
        for ($i = 0; $i < count($list); $i++) {
            $pos = strchr($list[$i], ";");
            if ($pos === false) {
                // No Q it is only a locale...
                $lang_list[$list[$i]] = 100;
            } else {
                // Has a Q rating
                $q = explode(";", $list[$i]);
                $loc = $q[0];
                $q = explode("=", $q[1]);
                $lang_list[$loc] = $q[1] * 100;
            }
        }

        // sort by q desc
        arsort($lang_list);

        // compare with languages, ignoring sublang and charset
        foreach ($lang_list as $lang => $q) {
            if (in_array($lang, $languages))
                return $lang;
            // de_DE.iso8859-1@euro => de_DE.iso8859-1, de_DE, de
            // de-DE => de-DE, de
            foreach (array('@', '.', '_') as $sep) {
                if (($tail = strchr($lang, $sep))) {
                    $lang_short = substr($lang, 0, -strlen($tail));
                    if (in_array($lang_short, $languages))
                        return $lang_short;
                }
            }
            if ($pos = strpos($lang, "-") and in_array(substr($lang, 0, $pos), $languages))
                return substr($lang, 0, $pos);
        }
    }
    return $languages[0];
}

/**
 * Smart setlocale().
 *
 * This is a version of the builtin setlocale() which is
 * smart enough to try some alternatives...
 *
 * @param mixed $category
 * @param string $locale
 * @return string The new locale, or <code>false</code> if unable
 *  to set the requested locale.
 * @see setlocale
 * [56ms]
 */
function guessing_setlocale($category, $locale)
{
    $alt = array(
        'de' => array('de_DE', 'de_AT', 'de_CH', 'deutsch', 'german'),
        'en' => array('en_US', 'en_GB', 'en_AU', 'en_CA', 'en_IE', 'english', 'C'),
        'es' => array('es_ES', 'es_MX', 'es_AR', 'spanish'),
        'fr' => array('fr_FR', 'fr_BE', 'fr_CA', 'fr_CH', 'fr_LU', 'français', 'french'),
        'it' => array('it_IT', 'it_CH', 'italian'),
        'ja' => array('ja_JP', 'japanese'),
        'nl' => array('nl_NL', 'nl_BE', 'dutch'),
        'sv' => array('sv_SE', 'sv_FI', 'swedish'),
        'zh' => array('zh_TW', 'zh_CN'),
    );
    if (!$locale or $locale == 'C') {
        // do the reverse: return the detected locale collapsed to our LANG
        $locale = setlocale($category, '');
        if ($locale) {
            if (strstr($locale, '_'))
                list ($lang) = explode('_', $locale);
            else
                $lang = $locale;
            if (strlen($lang) > 2) {
                foreach ($alt as $try => $locs) {
                    if (in_array($locale, $locs) or in_array($lang, $locs)) {
                        //if (empty($GLOBALS['LANG'])) $GLOBALS['LANG'] = $try;
                        return $try;
                    }
                }
            }
        }
    }
    if (strlen($locale) == 2)
        $lang = $locale;
    else
        list ($lang) = explode('_', $locale);
    if (!isset($alt[$lang]))
        return false;

    foreach ($alt[$lang] as $try) {
        if ($res = setlocale($category, $try))
            return $res;
        // Try with charset appended...
        $tryutf8 = $try . '.' . 'UTF-8';
        if ($res = setlocale($category, $tryutf8))
            return $res;
        $tryutf8 = $try . '.' . 'utf8';
        if ($res = setlocale($category, $tryutf8))
            return $res;
        foreach (array(".", '@', '_') as $sep) {
            if ($i = strpos($try, $sep)) {
                $try = substr($try, 0, $i);
                if (($res = setlocale($category, $try)))
                    return $res;
            }
        }
    }
    return false;
    // A standard locale name is typically of  the  form
    // language[_territory][.codeset][@modifier],  where  language is
    // an ISO 639 language code, territory is an ISO 3166 country code,
    // and codeset  is  a  character  set or encoding identifier like
    // ISO-8859-1 or UTF-8.
}

// [99ms]
function update_locale($loc)
{
    if ($loc == 'C' or $loc == 'en') {
        return '';
    }
    // $LANG or DEFAULT_LANGUAGE is too less information, at least on unix for
    // setlocale(), for bindtextdomain() to succeed.
    $setlocale = guessing_setlocale(LC_ALL, $loc); // [56ms]
    if (!$setlocale) { // system has no locale for this language, so gettext might fail
        $setlocale = FileFinder::_get_lang();
        list ($setlocale,) = explode('_', $setlocale, 2);
        $setlocale = guessing_setlocale(LC_ALL, $setlocale); // try again
        if (!$setlocale) $setlocale = $loc;
    }
    // Try to put new locale into environment (so any
    // programs we run will get the right locale.)
    if (function_exists('bindtextdomain')) {
        // If PHP is in safe mode, this is not allowed,
        // so hide errors...
        @putenv("LC_ALL=$setlocale");
        @putenv("LANG=$loc");
        @putenv("LANGUAGE=$loc");
    }

    // To get the POSIX character classes in the PCRE's (e.g.
    // [[:upper:]]) to match extended characters (e.g. GrüßGott), we have
    // to set the locale, using setlocale().
    //
    // The problem is which locale to set?  We would like to recognize all
    // upper-case characters in the iso-8859-1 character set as upper-case
    // characters --- not just the ones which are in the current $LANG.
    //
    // As it turns out, at least on my system (Linux/glibc-2.2) as long as
    // you setlocale() to anything but "C" it works fine.  (I'm not sure
    // whether this is how it's supposed to be, or whether this is a bug
    // in the libc...)
    //
    // We don't currently use the locale setting for anything else, so for
    // now, just set the locale to US English.
    //
    // FIXME: Not all environments may support en_US?  We should probably
    // have a list of locales to try.
    if (setlocale(LC_CTYPE, 0) == 'C') {
        setlocale(LC_CTYPE, 'en_US.UTF-8');
    } else {
        setlocale(LC_CTYPE, $setlocale);
    }

    return $loc;
}

function deduce_script_name()
{
    $s = &$_SERVER;
    $script = @$s['SCRIPT_NAME'];
    if (empty($script) or $script[0] != '/') {
        // Some places (e.g. Lycos) only supply a relative name in
        // SCRIPT_NAME, but give what we really want in SCRIPT_URL.
        if (!empty($s['SCRIPT_URL']))
            $script = $s['SCRIPT_URL'];
    }
    return $script;
}

function IsProbablyRedirectToIndex()
{
    // This might be a redirect to the DirectoryIndex,
    // e.g. REQUEST_URI = /dir/?some_action got redirected
    // to SCRIPT_NAME = /dir/index.php

    // In this case, the proper virtual path is still
    // $SCRIPT_NAME, since pages appear at
    // e.g. /dir/index.php/HomePage.

    $requri = preg_replace('/\?.*$/', '', $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI']);
    $requri = preg_quote($requri, '%');
    return preg_match("%^${requri}[^/]*$%", $GLOBALS['HTTP_SERVER_VARS']['SCRIPT_NAME']);
}

function getUploadFilePath()
{

    if (defined('UPLOAD_FILE_PATH')) {
        if (string_ends_with(UPLOAD_FILE_PATH, "/")
            or string_ends_with(UPLOAD_FILE_PATH, "\\")
        ) {
            return UPLOAD_FILE_PATH;
        } else {
            return UPLOAD_FILE_PATH . "/";
        }
    }
    return defined('PHPWIKI_DIR')
        ? PHPWIKI_DIR . "/uploads/"
        : realpath(dirname(__FILE__) . "/../uploads/")."/";
}

function getUploadDataPath()
{
    if (defined('UPLOAD_DATA_PATH')) {
        return string_ends_with(UPLOAD_DATA_PATH, "/")
            ? UPLOAD_DATA_PATH : UPLOAD_DATA_PATH . "/";
    }
    return SERVER_URL . (string_ends_with(DATA_PATH, "/") ? '' : "/")
        . DATA_PATH . '/uploads/';
}
