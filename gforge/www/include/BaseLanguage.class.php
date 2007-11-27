<?php

/**
 * GForge Localization Facility
 *
 * Portions Copyright 1999-2000 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id$
 */

/*

	Tim Perdue, September 7, 2000

	Base class for adding multilingual support to SF.net

	Contains variables which can be overridden optionally by other
	language files.

	Base language is english - an english class will extend this one,
	but won't override anything

	As new languages are added, they can override what they wish, and
		as we extend our class, other languages can follow suit
		as they are translated without holding up our progress
*/

class BaseLanguage {

	/**
	 * associative array to hold the string value
	 *
	 * @var array $textArray
	 */
	var $textArray ;
	
	/**
	 * selected language
	 *
	 * @var string $lang
	 */
	var $lang;
	
	/**
	 * name of the current language
	 *
	 * @var string $name
	 */
	var $name;
	
	/**
	 * language id
	 *
	 * @var int $id
	 */
	var $id;
	
	/**
	 * language code
	 *
	 * @var string $code
	 */
	var $code;
	
	/**
	 * result set handle for supported languages
	 *
	 * @var resource $languagesRes
	 */
	var $languagesRes;
	
	/**
	 * array containing dependencies of the cache file
	 *
	 * @var array $cacheDependencies
	 */
	var $cacheDependencies = array();

	/**
	 * array containing the plugins which are loaded
	 *
	 * @var array $pluginDependencies
	 */
	var $pluginDependencies = array();

	/**
	 * Constructor
	 */
	function BaseLanguage() {
		// disable localization caching system if configuration is wrong
		if(!isset($GLOBALS['sys_localization_cache_path']) || !is_writable($GLOBALS['sys_localization_cache_path'])) {
			$GLOBALS['sys_localization_enable_caching'] = false;
		}
	}

	/**
	 * getText - get a localized string
	 *
	 * @param string $pagename name of the current page
	 * @param string $category key
	 * @param mixed $args array which will replace the $1, $2, etc before it is returned
	 */
	function getText($pagename, $category, $args = '') {
		if ($args) {
			for ($i=1, $max = sizeof($args)+1; $i <= $max; $i++) {
				$patterns[] = '/\$'.$i.'/';
			}
			$tstring = preg_replace($patterns, $args, $this->textArray[$pagename][$category]);
		} else {
			$tstring = $this->textArray[$pagename][$category];
		}
		return $tstring;
	}

	/**
	 * getLanguages - returns database result of supported languages
	 *
	 * @return resource supported languages
	 */
	function getLanguages() {
		if (!isset($this->languagesRes)) {
			$this->languagesRes = db_query('SELECT * FROM supported_languages ORDER BY name ASC');
		}
		return $this->languagesRes;
	}

	/**
	 * getLanguageId - returns the language id corresponding to the language name
	 *
	 * @return int language id
	 */
	function getLanguageId() {
		if (!$this->id) {
			$this->id = db_result(db_query("SELECT language_id FROM supported_languages WHERE classname='".$this->lang."'"), 0, 0) ;
		}
		return $this->id ;
	}

	/**
	 * getLanguageName - returns the language name corresponding to the language id
	 *
	 * @return string language name
	 */
	function getLanguageName() {
		if (!$this->name) {
			$id = $this->getLanguageId () ;
			$this->name = db_result(db_query("SELECT name FROM supported_languages WHERE language_id='$id'"), 0, 0) ;
		}
		return $this->name ;
	}
}

/**
 * getLanguageClassName - get the classname for a language id
 * 
 * @param string $acceptedLanguages HTTP_ACCEPT_LANGUAGE header string
 * @return string the language class name.
 */
function getLanguageClassName($acceptedLanguages) {
	global $cookie_language_id;

	/*
		Determine which language file to use

		It depends on whether the user has set a cookie or not using
		the account page or the left-hand nav or how their browser is
		set or whether they are logged in or not

		if logged in, use language from users table
		else check for cookie and use that value if valid
		if no cookie check browser preference and use that language if valid
		else just use default language as configured for the installation
	*/

	if ($cookie_language_id) {
		$lang=$cookie_language_id;
		$res=db_query("select classname from supported_languages where language_id='".addslashes($lang)."'");
		if (!$res || db_numrows($res) < 1) {
			return false; // we will use default language
		} else {
			return db_result($res,0,'classname');
		}
	} else {
		$matches = array();
		preg_match_all('/([a-z]{2}(?:-[a-z]{2})?)(?:;q=([0-9\.]{1,4}))?/', $acceptedLanguages, $matches, PREG_SET_ORDER);
		$languages = array();
		
		$languagesCount = count($matches);
		
		if($languagesCount > 0) {
			$delta = 0.009/$languagesCount;
		
			for($i = 0, $max = count($matches); $i < $max; $i++) {
				$languageCode = $matches[$i][1];
				$quality = (!isset($matches[$i][2]) || empty($matches[$i][2])) ? '1' : $matches[$i][2];
				$languages[$languageCode] = $quality + $delta * ($languagesCount - $i);
			}

			arsort($languages, SORT_NUMERIC);
			$languages = array_keys($languages);

			for( $i=0, $max = sizeof($languages); $i < $max; $i++){
				$languageCode = $languages[$i];
				$res = db_query("select classname from supported_languages where language_code = '".addslashes($languageCode)."'");
				if (db_numrows($res) > 0) {
					return db_result($res,0,'classname');
				}
				// If that didn't work, check if we have sublanguage specifier
				// If so, try to strip it and look for for main language only
				if (strstr($languageCode, '-')) {
					$languageCode = substr($languageCode, 0, 2);
					$res = db_query("select classname from supported_languages where language_code = '".addslashes($languageCode)."'");
					if (db_numrows($res) > 0) {
						return db_result($res,0,'classname');
					}
				}
			}
		}
		return false; // we will use default language
	}
}

function language_code_from_name ($lang) {
	$langmap = array (
		'Basque'              => 'eu_ES',
		'Bulgarian'           => 'bg_BG',
		'Catalan'             => 'ca_ES',
		'Chinese'             => 'zh_TW',
		'Dutch'               => 'nl_NL',
		'English'             => 'en_US',
		'Esperanto'           => 'eo',
		'French'              => 'fr_FR',
		'German'              => 'de_DE',
		'Greek'               => 'el_GR',
		'Hebrew'              => 'he_IL',
		'Indonesian'          => 'id_ID',
		'Italian'             => 'it_IT',
		'Japanese'            => 'ja_JP',
		'Korean'              => 'ko_KR',
		'Norwegian'           => 'nb_NO',
		'Polish'              => 'pl_PL',
		'PortugueseBrazilian' => 'pt_BR',
		'Portuguese'          => 'pt_PT',
		'Russian'             => 'ru_RU',
		'SimplifiedChinese'   => 'zh_CN',
		'Spanish'             => 'es_ES',
		'Swedish'             => 'sv_SE',
		'Thai'                => 'th_TH',
		) ;
	return $langmap[$lang] ;
}

function setup_gettext_from_browser() {
	global $sys_lang ;
	if (!$sys_lang) {
		$sys_lang="English";
	}
	if (session_loggedin()) {
		setup_gettext_for_user(session_get_user());
	} else {
		//if you aren't logged in, check your browser settings 
		//and see if we support that language
		//if we don't support it, just use default language
		if (getStringFromServer('HTTP_ACCEPT_LANGUAGE')) {
			$classname=getLanguageClassName(getStringFromServer('HTTP_ACCEPT_LANGUAGE'));
		} else {
			$classname=$sys_lang;
		}
		setup_gettext_from_langname($classname);
	}
}

function setup_gettext_for_user($user) {
	setup_gettext_from_lang_id($user->getLanguage());
}

function setup_gettext_from_lang_id($lang_id) {
	$res = db_query('SELECT classname FROM supported_languages WHERE language_id=\''.$lang_id.'\'');
	setup_gettext_from_langname(db_result($res, 0, 'classname'));
}

function setup_gettext_from_langname ($lang) {
	$locale = language_code_from_name($lang).'.utf8';
	setup_gettext_from_locale ($locale) ;
}

function setup_gettext_from_locale ($locale) {
	setlocale(LC_ALL, $locale);
	setlocale (LC_TIME, _('en_US'));
	bindtextdomain('gforge', '/usr/share/locale/');
	textdomain('gforge');
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
