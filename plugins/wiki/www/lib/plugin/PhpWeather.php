<?php // -*-php-*-
// $Id: PhpWeather.php 8071 2011-05-18 14:56:14Z vargenau $
/**
 * Copyright 1999, 2000, 2001, 2002 $ThePhpWikiProgrammingTeam
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
 * This plugin requires a separate program called PhpWeather. For more
 * information and to download PhpWeather, see:
 *
 *   http://sourceforge.net/projects/phpweather/
 *
 * Usage:
 *
 * <<PhpWeather >>
 * <<PhpWeather menu=true >>
 * <<PhpWeather icao=KJFK >>
 * <<PhpWeather language=en >>
 * <<PhpWeather units=only_metric >>
 * <<PhpWeather icao||=CYYZ cc||=CA language||=en menu=true >>
 *
 * If you want a menu, and you also want to change the default station
 * or language, then you have to use the ||= form, or else the user
 * wont be able to change the station or language.
 *
 * The units argument should be one of only_metric, only_imperial,
 * both_metric, or both_imperial.
 */

// We require the base class from PHP Weather. Try some default directories.
// Better define PHPWEATHER_BASE_DIR to the directory on your server:
if (!defined('PHPWEATHER_BASE_DIR')) {
    /* PhpWeather has not been loaded before. We include the base
     * class from PhpWeather, adjust this to match the location of
     * PhpWeather on your server: */
    if (!isset($_SERVER))
        $_SERVER =& $GLOBALS['HTTP_SERVER_VARS'];
    @include_once($_SERVER['DOCUMENT_ROOT'] . '/phpweather-2.2.1/phpweather.php');
    if (!defined('PHPWEATHER_BASE_DIR'))
        @include_once($_SERVER['DOCUMENT_ROOT'] . '/phpweather/phpweather.php');
}

class WikiPlugin_PhpWeather
extends WikiPlugin
{
    function getName () {
        return _("PhpWeather");
    }

    function getDescription () {
        return _("The PhpWeather plugin provides weather reports from the Internet.");
    }

    function getDefaultArguments() {
        global $LANG;
        return array('icao'  => 'EKAH',
                     'cc'    => 'DK',
                     'language'  => 'en',
                     'menu'  => false,
                     'units' => 'both_metric');
    }

    function run($dbi, $argstr, &$request, $basepage) {
        // When 'phpweather/phpweather.php' is not installed then
        // PHPWEATHER_BASE_DIR will be undefined.
        if (!defined('PHPWEATHER_BASE_DIR'))
            return $this->error(_("You have to define PHPWEATHER_BASE_DIR before use. (config/config.ini)")); //early return

        require_once(PHPWEATHER_BASE_DIR . '/output/pw_images.php');
        require_once(PHPWEATHER_BASE_DIR . '/pw_utilities.php');

        extract($this->getArgs($argstr, $request));
        $html = HTML();

        $w = new phpweather(); // Our weather object

        if (!empty($icao)) {
            /* We assign the ICAO to the weather object: */
            $w->set_icao($icao);
            if (!$w->get_country_code()) {
                /* The country code couldn't be resolved, so we
                 * shouldn't use the ICAO: */
                trigger_error(sprintf(_("The ICAO '%s' wasn't recognized."),
                                      $icao), E_USER_NOTICE);
                $icao = '';
            }
        }

        if (!empty($icao)) {

            /* We check and correct the language if necessary: */
            //if (!in_array($language, array_keys($w->get_languages('text')))) {
            if (!in_array($language, array_keys(get_languages('text')))) {
                trigger_error(sprintf(_("%s does not know about the language '%s', using 'en' instead."),
                                      $this->getName(), $language),
                              E_USER_NOTICE);
                $language = 'en';
            }

            $class = "pw_text_$language";
            require_once(PHPWEATHER_BASE_DIR . "/output/$class.php");

            $t = new $class($w);
            $t->set_pref_units($units);
            $i = new pw_images($w);

            $i_temp = HTML::img(array('src' => $i->get_temp_image()));
            $i_wind = HTML::img(array('src' => $i->get_winddir_image()));
            $i_sky  = HTML::img(array('src' => $i->get_sky_image()));

            $m = $t->print_pretty();

            $m_td = HTML::td(HTML::p(new RawXml($m)));

            $i_tr = HTML::tr();
            $i_tr->pushContent(HTML::td($i_temp));
            $i_tr->pushContent(HTML::td($i_wind));

            $i_table = HTML::table($i_tr);
            $i_table->pushContent(HTML::tr(HTML::td(array('colspan' => '2'),
                                                    $i_sky)));

            $tr = HTML::tr();
            $tr->pushContent($m_td);
            $tr->pushContent(HTML::td($i_table));

            $html->pushContent(HTML::table($tr));

        }

        /* We make a menu if asked to, or if $icao is empty: */
        if ($menu || empty($icao)) {

            $form_arg = array('action' => $request->getURLtoSelf(),
                              'method' => 'get');

            /* The country box is always part of the menu: */
            $p1 = HTML::p(new RawXml(get_countries_select($w, $cc)));

            /* We want to save the language: */
            $p1->pushContent(HTML::input(array('type'  => 'hidden',
                                               'name'  => 'language',
                                               'value' => $language)));
            /* And also the ICAO: */
            $p1->pushContent(HTML::input(array('type'  => 'hidden',
                                               'name'  => 'icao',
                                               'value' => $icao)));

            $caption = (empty($cc) ? _("Submit country") : _("Change country"));
            $p1->pushContent(HTML::input(array('type'  => 'submit',
                                               'value' => $caption)));

            $html->pushContent(HTML::form($form_arg, $p1));

            if (!empty($cc)) {
                /* We have selected a country, now display a list with
                 * the available stations in that country: */
                $p2 = HTML::p();

                /* We need the country code after the form is submitted: */
                $p2->pushContent(HTML::input(array('type'  => 'hidden',
                                                   'name'  => 'cc',
                                                   'value' => $cc)));

                $p2->pushContent(new RawXml(get_stations_select($w, $cc, $icao)));
                $p2->pushContent(new RawXml(get_languages_select($language)));
                $p2->pushContent(HTML::input(array('type'  => 'submit',
                                                   'value' => _("Submit location"))));

                $html->pushContent(HTML::form($form_arg, $p2));

            }

        }

        return $html;
    }
};

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
