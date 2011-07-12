<?php
/**
 * FusionForge localisation
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2003-2004, Guillaume Smet
 * Copyright 2007-2009, Roland Mas
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

	if (!isset($_SERVER['SERVER_SOFTWARE'])) {
		// In command-line scripts
		if (forge_get_config('default_language')) {
			return forge_get_config('default_language') ;
		}
		return "English";
	}

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
	$ranges = explode (',', getStringFromServer ('HTTP_ACCEPT_LANGUAGE')) ;
	$languages = array() ; $lcount = count ($ranges) ;
	if ($lcount > 0) {
		$delta = 0.009/$lcount ;
		$i = 0 ;

		foreach ($ranges as $p) {
			if (preg_match ('/(.*);q=(.*)/', $p, $matches)) {
				$l = $matches[1] ;
				$w = $matches[2] ;
				$languages[$l] = $w + $delta * ($lcount - $i) ;
			} else {
				$languages[$p] = 1 + $delta * ($lcount - $i) ;
			}
			$i++ ;
		}
		arsort($languages, SORT_NUMERIC);
		$languages = array_keys($languages);

		for( $i=0, $max = sizeof($languages); $i < $max; $i++){
			$languageCode = $languages[$i];
			$res = db_query_params ('select classname from supported_languages where language_code=$1', array ($languageCode)) ;
			if (db_numrows($res) > 0) {
				return db_result($res,0,'classname');
			}
			// If that didn't work, check if we have sublanguage specifier
			// If so, try to strip it and look for for main language only
			if (strstr($languageCode, '-')) {
				$languageCode = substr($languageCode, 0, 2);
				$res = db_query_params ('select classname from supported_languages where language_code=$1', array ($languageCode)) ;
				if (db_numrows($res) > 0) {
					return db_result($res,0,'classname');
				}
			}
		}
	}

	// Okay, let's use the site-wide default language
	if (forge_get_config('default_language')) {
		return forge_get_config('default_language') ;
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
	$res = db_query_params ('SELECT classname FROM supported_languages WHERE language_id=$1', array ($lang_id));
	return db_result($res, 0, 'classname');
}

function language_name_to_lang_id ($language) {
	$res = db_query_params ('SELECT language_id FROM supported_languages WHERE classname=$1', array ($language)) ;
	return db_result($res, 0, 'language_id');
}

function setup_gettext_from_context() {
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
	$locale[] = language_name_to_locale_code($lang).'.utf8';
	$locale[] = language_name_to_locale_code($lang).'.UTF-8';
	setup_gettext_from_locale ($locale) ;
}

function setup_gettext_from_sys_lang () {

	$lang = "English";
	if (forge_get_config('default_language')) {
		$lang = forge_get_config('default_language') ;
	}

	$locale[] = language_name_to_locale_code($lang).'.utf8';
	$locale[] = language_name_to_locale_code($lang).'.UTF-8';
	setup_gettext_from_locale ($locale) ;
}

/*
 * setup_gettext_from_locale() - call setlocales to set up language used by gettext
 *
 * @param	array	locales (.utf8 + .UTF-8)
 */
function setup_gettext_from_locale ($locale) {
	setlocale(LC_ALL, $locale);

	if (isset($GLOBALS['sys_gettext_path'])) {
		bindtextdomain('fusionforge', $GLOBALS['sys_gettext_path']);
	} else {
		bindtextdomain('fusionforge', '/usr/share/locale/');
	}
	textdomain('fusionforge');
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
