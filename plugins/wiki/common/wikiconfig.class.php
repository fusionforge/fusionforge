<?php
/**
 * WikiPlugin Class
 *
 * This file is part of Fusionforge.
 *
 * Copyright 2010 (c) Marc-Etienne Vargenau, Alcatel-Lucent
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class WikiConfig extends Error {
    var $group_id;

    var $default_config = array(
        'DISABLE_MARKUP_WIKIWORD' => true,
        'NUM_SPAM_LINKS' => false,
        'ENABLE_RATEIT' => false,
    );

    var $default_desc = array(
    );

    function WikiConfig($group_id=false)
    {
        $this->default_desc['DISABLE_MARKUP_WIKIWORD']
          = _("Check to disable automatic linking of camelcase words to pages. Internal page links must be forced with [[pagename]] then.");
        $this->default_desc['NUM_SPAM_LINKS']
          = _("Check to enable spam prevention. If a non-admin adds more than 20 external links, it will be rejected as spam.");
        $this->default_desc['ENABLE_RATEIT']
          = _("Check to enable page rating. Logged users will be able to rate wiki pages.");
        $this->group_id = (int)$group_id;
        return true;
    }

    function getWikiConfigNames()
    {
       return array_keys($this->default_config);
    }

    function getWikiConfigDescription($config_name)
    {
       return $this->default_desc[$config_name];
    }

    function getWikiConfig($config_name)
    {
        if (!isset($this->default_config[$config_name])) {
            $this->setError('getWikiConfig: illegal config name');
            return false;
        }
        $res = db_query_params('SELECT config_value FROM plugin_wiki_config WHERE group_id=$1 AND config_name=$2', array($this->group_id, $config_name));
        if (db_numrows($res) > 0) {
            return(db_result($res, 0, 'config_value'));
        } else {
            return $this->default_config[$config_name];
        }
    }

    function updateWikiConfig($config_name, $config_value)
    {
        if (!isset($this->default_config[$config_name])) {
            $this->setError('updateWikiConfig: illegal config name');
            return false;
        }
        if (!is_numeric($config_value)) {
            $this->setError('updateWikiConfig: value should be numeric');
            return false;
        }
        $res = db_query_params('SELECT count(*) as c FROM plugin_wiki_config WHERE group_id=$1 AND config_name=$2',
            array($this->group_id, $config_name));
        if (db_result($res, 0, 'c') > 0) {
            $res = db_query_params('UPDATE plugin_wiki_config SET config_value=$3 WHERE group_id=$1 AND config_name=$2',
                array($this->group_id, $config_name, $config_value));
        } else {
            $res = db_query_params('INSERT INTO plugin_wiki_config (group_id, config_name, config_value) VALUES ($1, $2, $3)',
                array($this->group_id, $config_name, $config_value));
        }
        if (!$res) {
            $this->setError('WikiConfig::updateWikiConfig():: '.db_error());
            return false;
        }
        return true;
    }
}
?>
