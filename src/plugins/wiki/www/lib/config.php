<?php
rcs_id('$Id: config.php 6468 2009-01-31 12:13:51Z vargenau $');
/*
 * NOTE: The settings here should probably not need to be changed.
 * The user-configurable settings have been moved to IniConfig.php
 * The run-time code has been moved to lib/IniConfig.php:fix_configs()
 */
 
if (!defined("LC_ALL")) {
    // Backward compatibility (for PHP < 4.0.5)
    if (!check_php_version(4,0,5)) {
        define("LC_ALL",   "LC_ALL");
        define("LC_CTYPE", "LC_CTYPE");
    } else {
        define("LC_ALL",   0);
        define("LC_CTYPE", 2);
    }
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

/*
// copy some $_ENV vars to $_SERVER for CGI compatibility. php does it automatically since when?
if (isCGI()) {
    foreach (explode(':','SERVER_SOFTWARE:SERVER_NAME:GATEWAY_INTERFACE:SERVER_PROTOCOL:SERVER_PORT:REQUEST_METHOD:HTTP_ACCEPT:PATH_INFO:PATH_TRANSLATED:SCRIPT_NAME:QUERY_STRING:REMOTE_HOST:REMOTE_ADDR:REMOTE_USER:AUTH_TYPE:CONTENT_TYPE:CONTENT_LENGTH') as $key) {
        $GLOBALS['HTTP_SERVER_VARS'][$key] = &$GLOBALS['HTTP_ENV_VARS'][$key];
    }
}
*/

// essential internal stuff
set_magic_quotes_runtime(0);

/** 
 * Browser Detection Functions
 *
 * Current Issues:
 *  NS/IE < 4.0 doesn't accept < ? xml version="1.0" ? >
 *  NS/IE < 4.0 cannot display PNG
 *  NS/IE < 4.0 cannot display all XHTML tags
 *  NS < 5.0 needs textarea wrap=virtual
 *  IE55 has problems with transparent PNG's
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
    return strstr(strtolower(browserAgent()), strtolower($match));
}
// returns a similar number for Netscape/Mozilla (gecko=5.0)/IE/Opera features.
function browserVersion() {
    $agent = browserAgent();
    if (strstr($agent, "Mozilla/4.0 (compatible; MSIE"))
        return (float) substr($agent, 30);
    elseif (strstr($agent, "Mozilla/5.0 (compatible; Konqueror/"))
        return (float) substr($agent, 36);
    else
        return (float) substr($agent, 8);
}
function isBrowserIE() {
    return (browserDetect('Mozilla/') and 
            browserDetect('MSIE'));
}
// problem with transparent PNG's
function isBrowserIE55() {
    return (isBrowserIE() and 
            browserVersion() > 5.1 and browserVersion() < 6.0);
}
// old Netscape prior to Mozilla
function isBrowserNetscape($version = false) {
    $agent = (browserDetect('Mozilla/') and 
            ! browserDetect('Gecko/') and
            ! browserDetect('MSIE'));
    if ($version) return $agent and browserVersion() >= $version; 
    else return $agent;
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
    $found = browserDetect('spoofer') or browserDetect('applewebkit');
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
        // ignore possible "_<territory>" and codeset "ja.utf8"
        /*
        require_once("lib/WikiTheme.php");
        $languages = listAvailableLanguages();
        if (defined('DEFAULT_LANGUAGE') and in_array(DEFAULT_LANGUAGE, $languages))
        {
            // remove duplicates
            if ($i = array_search(DEFAULT_LANGUAGE, $languages) !== false) {
                array_splice($languages, $i, 1);
            }
            array_unshift($languages, DEFAULT_LANGUAGE);
            foreach ($languages as $lang) {
                $arr = FileFinder::locale_versions($lang);
                $languages = array_merge($languages, $arr);
            }
        }
        */
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
            if (strstr($locale, '_')) list ($lang) = split('_', $locale);
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
        list ($lang) = split('_', $locale);
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
    // $LANG or DEFAULT_LANGUAGE is too less information, at least on unix for
    // setlocale(), for bindtextdomain() to succeed.
    $setlocale = guessing_setlocale(LC_ALL, $loc); // [56ms]
    if (!$setlocale) { // system has no locale for this language, so gettext might fail
        $setlocale = FileFinder::_get_lang();
        list ($setlocale,) = split('_', $setlocale, 2);
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

/** string pcre_fix_posix_classes (string $regexp)
*
* Older version (pre 3.x?) of the PCRE library do not support
* POSIX named character classes (e.g. [[:alnum:]]).
*
* This is a helper function which can be used to convert a regexp
* which contains POSIX named character classes to one that doesn't.
*
* All instances of strings like '[:<class>:]' are replaced by the equivalent
* enumerated character class.
*
* Implementation Notes:
*
* Currently we use hard-coded values which are valid only for
* ISO-8859-1.  Also, currently on the classes [:alpha:], [:alnum:],
* [:upper:] and [:lower:] are implemented.  (The missing classes:
* [:blank:], [:cntrl:], [:digit:], [:graph:], [:print:], [:punct:],
* [:space:], and [:xdigit:] could easily be added if needed.)
*
* This is a hack.  I tried to generate these classes automatically
* using ereg(), but discovered that in my PHP, at least, ereg() is
* slightly broken w.r.t. POSIX character classes.  (It includes
* "\xaa" and "\xba" in [:alpha:].)
*
* So for now, this will do.  --Jeff <dairiki@dairiki.org> 14 Mar, 2001
*/
function pcre_fix_posix_classes ($regexp) {
    global $charset;
    if (!isset($charset))
        $charset = CHARSET; // get rid of constant. pref is dynamic and language specific
    if (in_array($GLOBALS['LANG'], array('zh')))
        $charset = 'utf-8';
    if (strstr($GLOBALS['LANG'],'.utf-8'))
        $charset = 'utf-8';
    elseif (strstr($GLOBALS['LANG'],'.euc-jp'))
        $charset = 'euc-jp';
    elseif (in_array($GLOBALS['LANG'], array('ja')))
        //$charset = 'utf-8';
        $charset = 'euc-jp';

    if (strtolower($charset) == 'utf-8') { // thanks to John McPherson
        // until posix class names/pcre work with utf-8
	if (preg_match('/[[:upper:]]/', '\xc4\x80'))
            return $regexp;    
        // utf-8 non-ascii chars: most common (eg western) latin chars are 0xc380-0xc3bf
        // we currently ignore other less common non-ascii characters
        // (eg central/east european) latin chars are 0xc432-0xcdbf and 0xc580-0xc5be
        // and indian/cyrillic/asian languages
        
        // this replaces [[:lower:]] with utf-8 match (Latin only)
        $regexp = preg_replace('/\[\[\:lower\:\]\]/','(?:[a-z]|\xc3[\x9f-\xbf]|\xc4[\x81\x83\x85\x87])',
                               $regexp);
        // this replaces [[:upper:]] with utf-8 match (Latin only)
        $regexp = preg_replace('/\[\[\:upper\:\]\]/','(?:[A-Z]|\xc3[\x80-\x9e]|\xc4[\x80\x82\x84\x86])',
                               $regexp);
    } elseif (preg_match('/[[:upper:]]/', 'Ä')) {
        // First check to see if our PCRE lib supports POSIX character
        // classes.  If it does, there's nothing to do.
        return $regexp;
    }
    static $classes = array(
                            'alnum' => "0-9A-Za-z\xc0-\xd6\xd8-\xf6\xf8-\xff",
                            'alpha' => "A-Za-z\xc0-\xd6\xd8-\xf6\xf8-\xff",
                            'upper' => "A-Z\xc0-\xd6\xd8-\xde",
                            'lower' => "a-z\xdf-\xf6\xf8-\xff"
                            );
    $keys = join('|', array_keys($classes));
    return preg_replace("/\[:($keys):]/e", '$classes["\1"]', $regexp);
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

// >= php-4.1.0
if (!function_exists('array_key_exists')) { // lib/IniConfig.php, sqlite, adodb, ...
    function array_key_exists($item, $array) {
        return isset($array[$item]);
    }
}

// => php-4.0.5
if (!function_exists('is_scalar')) { // lib/stdlib.php:wikihash()
    function is_scalar($x) {
        return is_numeric($x) or is_string($x) or is_float($x) or is_bool($x); 
    }
}

// => php-4.2.0. pear wants to break old php's! DB uses it now.
if (!function_exists('is_a')) {
    function is_a($item,$class) {
        return isa($item,$class); 
    }
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

/**
 * array_diff_assoc() returns an array containing all the values from array1 that are not
 * present in any of the other arguments. Note that the keys are used in the comparison 
 * unlike array_diff(). In core since php-4.3.0
 * Our fallback here supports only hashes and two args.
 * $array1 = array("a" => "green", "b" => "brown", "c" => "blue");
 * $array2 = array("a" => "green", "y" => "yellow", "r" => "red");
 * => b => brown, c => blue
 */
if (!function_exists('array_diff_assoc')) {
    function array_diff_assoc($a1, $a2) {
    	$result = array();
    	foreach ($a1 as $k => $v) {
    	    if (!isset($a2[$k]) or !$a2[$k])
    	        $result[$k] = $v;	
    	}
    	return $result;
    }
}

/** 
 * wordwrap() might crash between 4.1.2 and php-4.3.0RC2, fixed in 4.3.0
 * See http://bugs.php.net/bug.php?id=20927 and 
 * http://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2002-1396
 * Improved version of wordwrap2() in the comments at http://www.php.net/wordwrap
 */
function safe_wordwrap($str, $width=80, $break="\n", $cut=false) {
    if (check_php_version(4,3))
        return wordwrap($str, $width, $break, $cut);
    elseif (!check_php_version(4,1,2))
        return wordwrap($str, $width, $break, $cut);
    else {
        $len = strlen($str);
        $tag = 0; $result = ''; $wordlen = 0;
        for ($i = 0; $i < $len; $i++) {
            $chr = $str[$i];
            // don't break inside xml tags
            if ($chr == '<') {
                $tag++;
            } elseif ($chr == '>') {
                $tag--;
            } elseif (!$tag) {
                if (!function_exists('ctype_space')) {
                    if (preg_match('/^\s$/', $chr))
                        $wordlen = 0;
                    else
                        $wordlen++;
                }
                elseif (ctype_space($chr)) {
                    $wordlen = 0;
                } else {
                    $wordlen++;
                }
            }
            if ((!$tag) && ($wordlen) && (!($wordlen % $width))) {
                $chr .= $break;
            }
            $result .= $chr;
        }
        return $result;
        /*
        if (isset($str) && isset($width)) {
            $ex = explode(" ", $str); // wrong: must use preg_split \s+
            $rp = array();
            for ($i=0; $i<count($ex); $i++) {
                // $word_array = preg_split('//', $ex[$i], -1, PREG_SPLIT_NO_EMPTY);
                // delete #&& !is_numeric($ex[$i])# if you want force it anyway
                if (strlen($ex[$i]) > $width && !is_numeric($ex[$i])) {
                    $where = 0;
                    $rp[$i] = "";
                    for($b=0; $b < (ceil(strlen($ex[$i]) / $width)); $b++) {
                        $rp[$i] .= substr($ex[$i], $where, $width).$break;
                        $where += $width;
                    }
                } else {
                    $rp[$i] = $ex[$i];
                }
            }
            return implode(" ",$rp);
        }
        return $text;
        */
    }
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

/**
 * htmlspecialchars doesn't support some special 8bit charsets, which we do want to support.
 * Well it just prints a warning which we could circumvent.
 * Note: unused, since php htmlspecialchars does the same, just prints a warning which we silence
 */
/*
function htmlspecialchars_workaround($str, $quote=ENT_COMPAT, $charset='iso-8859-1') {
    if (in_array(strtolower($charset), 
                 array('iso-8859-2', 'iso8859-2', 'latin-2', 'latin2'))) 
    {
        if (! ($quote & ENT_NOQUOTES)) {
            $str = str_replace("\"", "&quot;",
                               $str);
        }
        if ($quote & ENT_QUOTES) {
            $str = str_replace("\'", "&#039;",
                               $str);
        }
        return str_replace(array("<", ">", "&"),
                           array("&lt;", "&gt;", "&amp;"), $str);
    }
    else {
        return htmlspecialchars($str, $quote, $charset);
    }
}
*/

/**
 * htmlspecialchars doesn't support some special 8bit charsets, which we do want to support.
 * Well it just prints a warning which we could circumvent.
 * Note: unused, since php htmlspecialchars does the same, just prints a warning which we silence
 */
/*
function htmlspecialchars_workaround($str, $quote=ENT_COMPAT, $charset='iso-8859-1') {
    if (in_array(strtolower($charset), 
                 array('iso-8859-2', 'iso8859-2', 'latin-2', 'latin2'))) 
    {
        if (! ($quote & ENT_NOQUOTES)) {
            $str = str_replace("\"", "&quot;",
                               $str);
        }
        if ($quote & ENT_QUOTES) {
            $str = str_replace("\'", "&#039;",
                               $str);
        }
        return str_replace(array("<", ">", "&"),
                           array("&lt;", "&gt;", "&amp;"), $str);
    }
    else {
        return htmlspecialchars($str, $quote, $charset);
    }
}
*/

// For emacs users
// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
