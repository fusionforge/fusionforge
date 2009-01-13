<?php

/**
 * GForge Localization Facility
 *
 * Uses the GNU gettext system
 *
 * Copyright 2007 Roland Mas <lolando@debian.org>
 *
 * Rewritten from previous works
 * Portions Copyright 1999-2000 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id$
 */

/**
 * choose_language_from_context - find the most appropriate language
 * 
 * @return string the language class name.
 */
function choose_language_from_context () {
	/*
		Determine which language to use

		It depends on whether the user has set a cookie or not using
		the account page or the left-hand nav or how their browser is
		set or whether they are logged in or not

		if logged in, use language from users table
		else check for cookie and use that value if valid
		if no cookie check browser preference and use that language if valid
		else just use default language as configured for the installation
	*/

	// Logged in -> use preferences
	if (session_loggedin()) {
		$user = session_get_user () ;
		return lang_id_to_language_name ($user->getLanguage()) ;
	}

	// Cookie present -> use that
	$cookie_language_id = getIntFromCookie ('cookie_language_id') ;
	if ($cookie_language_id) {
		return lang_id_to_language_name ($cookie_language_id) ;
	}

	// Try with the browser's preferred language
	$matches = array();
	preg_match_all('/([a-z]{2}(?:-[a-z]{2})?)(?:;q=([0-9\.]{1,4}))?/',
		       getStringFromServer ('HTTP_ACCEPT_LANGUAGE'),
		       $matches,
		       PREG_SET_ORDER);
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

	// Okay, let's use the site-wide default language
	if ($GLOBALS['sys_lang']) {
		return $GLOBALS['sys_lang'] ;
	}
	
	// Still no match?  Really?
	return "English";
}

function language_name_to_locale_code ($lang) {
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

function locale_code_to_language_name ($loc) {
	$localemap = array (
		'eu_ES' => 'Basque',
		'bg_BG' => 'Bulgarian',
		'ca_ES' => 'Catalan',
		'zh_TW' => 'Chinese',
		'nl_NL' => 'Dutch',
		'en_US' => 'English',
		'eo' 	=> 'Esperanto',
		'fr_FR' => 'French',
		'de_DE' => 'German',
		'el_GR' => 'Greek',
		'he_IL' => 'Hebrew',
		'id_ID' => 'Indonesian',
		'it_IT' => 'Italian',
		'ja_JP' => 'Japanese',
		'ko_KR' => 'Korean',
		'nb_NO' => 'Norwegian',
		'pl_PL' => 'Polish',
		'pt_BR' => 'PortugueseBrazilian',
		'pt_PT' => 'Portuguese',
		'ru_RU' => 'Russian',
		'zh_CN' => 'SimplifiedChinese',
		'es_ES' => 'Spanish',
		'sv_SE' => 'Swedish',
		'th_TH' => 'Thai',
		) ;
	return $localemap[$loc] ;
}

function lang_id_to_language_name ($lang_id) {
	$res = db_query('SELECT classname FROM supported_languages WHERE language_id=\''.$lang_id.'\'');
	return db_result($res, 0, 'classname');
}

function language_name_to_lang_id ($language) {
	$res = db_query('SELECT language_id FROM supported_languages WHERE classname=\''.$language.'\'');
	return db_result($res, 0, 'language_id');
}

function setup_gettext_from_browser() {
	setup_gettext_from_langname (choose_language_from_context ());
}

function setup_gettext_for_user ($user) {
	setup_gettext_from_lang_id ($user->getLanguage());
}

function setup_gettext_from_lang_id ($lang_id) {
	$lang = lang_id_to_language_name ($lang_id) ;
	setup_gettext_from_langname($lang) ;
}

function setup_gettext_from_langname ($lang) {
	$locale = language_name_to_locale_code($lang).'.utf8';
	setup_gettext_from_locale ($locale) ;
}

function setup_gettext_from_locale ($locale) {
	setlocale(LC_ALL, $locale);
	setlocale (LC_TIME, _('en_US'));
	
	if (isset($GLOBALS['sys_gettext_path'])) {
		bindtextdomain('gforge', $GLOBALS['sys_gettext_path']);
	} else {
		bindtextdomain('gforge', '/usr/share/locale/');
	}
	textdomain('gforge');
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
