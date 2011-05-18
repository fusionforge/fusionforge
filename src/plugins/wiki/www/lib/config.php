<?php
// $Id: config.php 7964 2011-03-05 17:05:30Z vargenau $
/*
 * NOTE: The settings here should probably not need to be changed.
 * The user-configurable settings have been moved to IniConfig.php
 * The run-time code has been moved to lib/IniConfig.php:fix_configs()
 */

if (!defined("LC_ALL")) {
    define("LC_ALL",   0);
    define("LC_CTYPE", 2);
}
// debug flags:
define ('_DEBUG_VERBOSE',   1); // verbose msgs and add validator links on footer
define ('_DEBUG_PAGELINKS', 2); // list the extraced pagelinks at the top of each pages
define ('_DEBUG_PARSER',    4); // verbose parsing steps
define ('_DEBUG_TRACE',     8); // test php memory usage, prints php debug backtraces
define ('_DEBUG_INFO',     16);
define ('_DEBUG_APD',      32); // APD tracing/profiling
define ('_DEBUG_LOGIN',    64); // verbose login debug-msg (settings and reason for failure)
define ('_DEBUG_SQL',     128); // force check db, force optimize, print some debugging logs
define ('_DEBUG_REMOTE',  256); // remote debug into subrequests (xmlrpc, ajax, wikiwyg, ...)
                // or test local SearchHighlight.
                // internal links have persistent ?start_debug=1

function isCGI() {
    return (substr(php_sapi_name(),0,3) == 'cgi' and
            isset($GLOBALS['HTTP_ENV_VARS']['GATEWAY_INTERFACE']) and
            @preg_match('/CGI/',$GLOBALS['HTTP_ENV_VARS']['GATEWAY_INTERFACE']));
}

// essential internal stuff
if (!check_php_version(5,3)) {
    set_magic_quotes_runtime(0);
}

/**
 * Browser Detection Functions
 *
 * @author: ReiniUrban
 */
function browserAgent() {
    static $HTTP_USER_AGENT = false;
    if ($HTTP_USER_AGENT !== false) return $HTTP_USER_AGENT;
    if (!$HTTP_USER_AGENT)
        $HTTP_USER_AGENT = @$GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'];
    if (!$HTTP_USER_AGENT) // CGI
        $HTTP_USER_AGENT = @$GLOBALS['HTTP_ENV_VARS']['HTTP_USER_AGENT'];
    if (!$HTTP_USER_AGENT) // local CGI testing
        $HTTP_USER_AGENT = 'none';
    return $HTTP_USER_AGENT;
}
function browserDetect($match) {
    return (strpos(strtolower(browserAgent()), strtolower($match)) !== false);
}
// returns a similar number for Netscape/Mozilla (gecko=5.0)/IE/Opera features.
function browserVersion() {
    $agent = browserAgent();
    if (strstr($agent, "Mozilla/4.0 (compatible; MSIE"))
        return (float)substr($agent, 30);
    elseif (strstr($agent, "Mozilla/5.0 (compatible; Konqueror/"))
        return (float)substr($agent, 36);
    elseif (strstr($agent, "AppleWebKit/"))
        return (float)substr($agent, strpos($agent, "AppleWebKit/") + 12);
    else
        return (float)substr($agent, 8);
}
function isBrowserIE() {
    return (browserDetect('Mozilla/') and
            browserDetect('MSIE'));
}
// must omit display alternate stylesheets: konqueror 3.1.4
// http://sourceforge.net/tracker/index.php?func=detail&aid=945154&group_id=6121&atid=106121
function isBrowserKonqueror($version = false) {
    if ($version) return browserDetect('Konqueror/') and browserVersion() >= $version;
    return browserDetect('Konqueror/');
}
// MacOSX Safari has certain limitations. Need detection and patches.
// * no <object>, only <embed>
function isBrowserSafari($version = false) {
    $found = browserDetect('Spoofer/');
    $found = browserDetect('AppleWebKit/') or $found;
    if ($version) return $found and browserVersion() >= $version;
    return $found;
}
function isBrowserOpera($version = false) {
    if ($version) return browserDetect('Opera/') and browserVersion() >= $version;
    return browserDetect('Opera/');
}

/**
 * If $LANG is undefined:
 * Smart client language detection, based on our supported languages
 * HTTP_ACCEPT_LANGUAGE="de-at,en;q=0.5"
 *   => "de"
 * We should really check additionally if the i18n HomePage version is defined.
 * So must defer this to the request loop.
 */
function guessing_lang ($languages=false) {
    if (!$languages) {
        // make this faster
        $languages = array("en","de","es","fr","it","ja","zh","nl","sv");
    }

    $accept = false;
    if (isset($GLOBALS['request'])) // in fixup-dynamic-config there's no request yet
        $accept = $GLOBALS['request']->get('HTTP_ACCEPT_LANGUAGE');
    elseif (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

    if ($accept) {
        $lang_list = array();
        $list = explode(",", $accept);
        for ($i=0; $i<count($list); $i++) {
            $pos = strchr($list[$i], ";") ;
            if ($pos === false) {
                // No Q it is only a locale...
                $lang_list[$list[$i]] = 100;
            } else {
                // Has a Q rating
                $q = explode(";",$list[$i]) ;
                $loc = $q[0] ;
                $q = explode("=",$q[1]) ;
                $lang_list[$loc] = $q[1]*100 ;
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
                if ( ($tail = strchr($lang, $sep)) ) {
                    $lang_short = substr($lang, 0, -strlen($tail));
                    if (in_array($lang_short, $languages))
                        return $lang_short;
                }
            }
            if ($pos = strchr($lang, "-") and in_array(substr($lang, 0, $pos), $languages))
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
function guessing_setlocale ($category, $locale) {
    $alt = array('en' => array('C', 'en_US', 'en_GB', 'en_AU', 'en_CA', 'english'),
                 'de' => array('de_DE', 'de_DE', 'de_DE@euro',
                               'de_AT@euro', 'de_AT', 'German_Austria.1252', 'deutsch',
                               'german', 'ge'),
                 'es' => array('es_ES', 'es_MX', 'es_AR', 'spanish'),
                 'nl' => array('nl_NL', 'dutch'),
                 'fr' => array('fr_FR', 'français', 'french'),
                 'it' => array('it_IT'),
                 'sv' => array('sv_SE'),
                 'ja.utf-8'  => array('ja_JP','ja_JP.utf-8','japanese'),
                 'ja.euc-jp' => array('ja_JP','ja_JP.eucJP','japanese.euc'),
                 'zh' => array('zh_TW', 'zh_CN'),
                 );
    if (!$locale or $locale=='C') {
        // do the reverse: return the detected locale collapsed to our LANG
        $locale = setlocale($category, '');
        if ($locale) {
            if (strstr($locale, '_')) list ($lang) = explode('_', $locale);
            else $lang = $locale;
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
        $try = $try . '.' . $GLOBALS['charset'];
        if ($res = setlocale($category, $try))
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
function update_locale($loc) {
    if ($loc == 'C' or $loc == 'en') return;
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
    if (!function_exists('bindtextdomain'))  {
        // Reinitialize translation array.
        global $locale;
        $locale = array();
        // do reinit to purge PHP's static cache [43ms]
        if ( ($lcfile = FindLocalizedFile("LC_MESSAGES/phpwiki.php", 'missing_ok', 'reinit')) ) {
            include($lcfile);
        }
    } else {
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
        $x = setlocale(LC_CTYPE, 'en_US.' . $GLOBALS['charset']);
    } else {
        $x = setlocale(LC_CTYPE, $setlocale);
    }

    return $loc;
}

function deduce_script_name() {
    $s = &$GLOBALS['HTTP_SERVER_VARS'];
    $script = @$s['SCRIPT_NAME'];
    if (empty($script) or $script[0] != '/') {
        // Some places (e.g. Lycos) only supply a relative name in
        // SCRIPT_NAME, but give what we really want in SCRIPT_URL.
        if (!empty($s['SCRIPT_URL']))
            $script = $s['SCRIPT_URL'];
    }
    return $script;
}

function IsProbablyRedirectToIndex () {
    // This might be a redirect to the DirectoryIndex,
    // e.g. REQUEST_URI = /dir/?some_action got redirected
    // to SCRIPT_NAME = /dir/index.php

    // In this case, the proper virtual path is still
    // $SCRIPT_NAME, since pages appear at
    // e.g. /dir/index.php/HomePage.

    $requri = preg_replace('/\?.*$/','',$GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI']);
    $requri = preg_quote($requri, '%');
    return preg_match("%^${requri}[^/]*$%", $GLOBALS['HTTP_SERVER_VARS']['SCRIPT_NAME']);
}

// needed < php5
// by bradhuizenga at softhome dot net from the php docs
if (!function_exists('str_ireplace')) {
  function str_ireplace($find, $replace, $string) {
      if (!is_array($find)) $find = array($find);
      if (!is_array($replace)) {
          if (!is_array($find))
              $replace = array($replace);
          else {
              // this will duplicate the string into an array the size of $find
              $c = count($find);
              $rString = $replace;
              unset($replace);
              for ($i = 0; $i < $c; $i++) {
                  $replace[$i] = $rString;
              }
          }
      }
      foreach ($find as $fKey => $fItem) {
          $between = explode(strtolower($fItem),strtolower($string));
          $pos = 0;
          foreach ($between as $bKey => $bItem) {
              $between[$bKey] = substr($string,$pos,strlen($bItem));
              $pos += strlen($bItem) + strlen($fItem);
          }
          $string = implode($replace[$fKey], $between);
      }
      return($string);
  }
}

// htmlspecialchars_decode exists for PHP >= 5.1
if (!function_exists('htmlspecialchars_decode')) {

  function htmlspecialchars_decode($text) {
      return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
  }

}

/**
 * safe php4 definition for clone.
 * php5 copies objects by reference, but we need to clone "deep copy" in some places.
 * (BlockParser)
 * We need to eval it as workaround for the php5 parser.
 * See http://www.acko.net/node/54
 */
if (!check_php_version(5)) {
    eval('
    function clone($object) {
      return $object;
    }
    ');
}

function getUploadFilePath() {

    if (defined('UPLOAD_FILE_PATH')) {
        // Force creation of the returned directory if it does not exist.
        if (!file_exists(UPLOAD_FILE_PATH)) {
            mkdir(UPLOAD_FILE_PATH, 0775);
        }
        if (string_ends_with(UPLOAD_FILE_PATH, "/")
            or string_ends_with(UPLOAD_FILE_PATH, "\\")) {
            return UPLOAD_FILE_PATH;
        } else {
            return UPLOAD_FILE_PATH."/";
        }
    }
    return defined('PHPWIKI_DIR')
        ? PHPWIKI_DIR . "/uploads/"
        : realpath(dirname(__FILE__) . "/../uploads/");
}
function getUploadDataPath() {
    if (defined('UPLOAD_DATA_PATH')) {
    return string_ends_with(UPLOAD_DATA_PATH, "/")
        ? UPLOAD_DATA_PATH : UPLOAD_DATA_PATH."/";
    }
    return SERVER_URL . (string_ends_with(DATA_PATH, "/") ? '' : "/")
     . DATA_PATH . '/uploads/';
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
