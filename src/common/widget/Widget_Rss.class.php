<?php
/**
 * Generic RSS Widget Class
 *
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2012,2014,2019,2021, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is a part of Fusionforge.
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
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';

/**
 * Widget_Rss
 *
 * Rss reader
 */
/* abstract */ class Widget_Rss extends Widget {
	var $rss_title;
	var $rss_url;
	
	function __construct($id, $owner_id, $owner_type) {
		parent::__construct($id);
		$this->setOwner($owner_id, $owner_type);
	}

	function getTitle() {
		$hp = Codendi_HTMLPurifier::instance();
		return $this->rss_title ?  $hp->purify($this->rss_title, CODENDI_PURIFIER_CONVERT_HTML)  : _('RSS Reader');
	}

	function getContent() {
		global $HTML;
		$hp = Codendi_HTMLPurifier::instance();
		$content = '';
		if ($this->rss_url) {
			if (function_exists('idn_to_utf8()')) {
				function idn_to_utf8($param) {
					return idn_to_unicode($param);
				}
			}
			if (!(include_once 'simplepie/autoloader.php'))  // vendor, debian
				if (!(include_once 'php-simplepie/autoloader.php'))  // fedora
					exit_error(_('Could not load the SimplePie PHP library.'));
			if (!is_dir(forge_get_config('data_path') .'/rss')) {
				if (!mkdir(forge_get_config('data_path') .'/rss')) {
					$content .= $HTML->error_msg(_('Cannot create backend directory. Contact forge administrator.'));
				}
			}
			$rss = new SimplePie();
			$rss->set_feed_url($this->rss_url);
			$rss->set_cache_location(forge_get_config('data_path') .'/rss');
			$rss->init();
			$rss->handle_content_type();
			$max_items = 10;
			$items = array_slice($rss->get_items(), 0, $max_items);
			if (count($items)) {
				$content .= $HTML->listTableTop();
				foreach($items as $key => $item){
					$content .= '<tr><td style="width:99%">';
					if ($image = $item->get_link(0, 'image')) {
						//hack to display twitter avatar
						$content .= '<img src="'.  $hp->purify($image, CODENDI_PURIFIER_CONVERT_HTML)  .'" style="float:left; margin-right:1em;" />';
					}
					/* Do not trust SimplePie for purifying. */
					$content .= html_e('a', array(
						'href' => util_unconvert_htmlspecialchars($item->get_link()),
					), util_html_secure($item->get_title()));
					if ($item->get_date()) {
						$content .= '<span style="color:#999;" title="'. date(_("Y-m-d H:i"), $item->get_date('U')) .'"> - '. $this->_date_ago($item->get_date('U'),time()) .'</span>';
					}
					$content .= '</td></tr>';
				}
				$content .= $HTML->listTableBottom();
			} else {
				$content = $HTML->information(_('No element to display'));
			}
		}
		return $content;
	}

	function isAjax() {
		return true;
	}

	function hasPreferences() {
		return true;
	}

	function getPreferences() {
		$hp = Codendi_HTMLPurifier::instance();
		$prefs = '<table>';
		$prefs .= '<tr>';
		$prefs .= '<td>';
		$prefs .= _('Title')._(':');
		$prefs .= '</td>';
		$prefs .= '<td>';
		$prefs .= '<input type="text" class="textfield_medium" name="rss[title]" value="'. $hp->purify($this->rss_title, CODENDI_PURIFIER_CONVERT_HTML) .'" /></td></tr>';
		$prefs .= '<tr>';
		$prefs .= '<td>';
		$prefs .= 'URL'._(':');
		$prefs .= '</td>';
		$prefs .= '<td>';
		$prefs .= '<input type="url" class="textfield_medium" name="rss[url]" value="'. $hp->purify($this->rss_url, CODENDI_PURIFIER_CONVERT_HTML) .'" /></td></tr>';
		$prefs .= '</table>';
		return $prefs;
	}

	function getInstallPreferences() {
		$prefs = '<table>';
		$prefs .= '<tr>';
		$prefs .= '<td>';
		$prefs .= _('Title')._(':');
		$prefs .= '</td>';
		$prefs .= '<td>';
		$prefs .= '<input type="text" class="textfield_medium" name="rss[title]" value="" placeholder="'. _('Set your RSS Title here.') .'" /></td></tr>';
		$prefs .= '<tr>';
		$prefs .= '<td>';
		$prefs .= 'URL'._(':');
		$prefs .= '</td>';
		$prefs .= '<td>';
		$prefs .= '<input type="url" class="textfield_medium" name="rss[url]" value="" placeholder="'._('Set your RSS URL here.').'" />';
		$prefs .= '</td>';
		$prefs .= '</tr>';
		$prefs .= '</table>';
		return $prefs;
	}

	function cloneContent($id, $owner_id, $owner_type) {
		$sql = "INSERT INTO widget_rss (owner_id, owner_type, title, url)
			SELECT $1, $2, title, url
			FROM widget_rss
			WHERE owner_id = $3 AND owner_type = $4";
		$res = db_query_params($sql,array($owner_id,$owner_type,$this->owner_id,$this->owner_type));
		return db_insertid($res,'widget_rss','id');
	}

	function loadContent($id) {
		$sql = "SELECT * FROM widget_rss WHERE owner_id = $1 AND owner_type = $2 AND id = $3";
		$res = db_query_params($sql,array($this->owner_id,$this->owner_type,$id));
		if ($res && db_numrows($res)) {
			$data = db_fetch_array($res);
			$this->rss_title = $data['title'];
			$this->rss_url   = $data['url'];
			$this->content_id = $id;
		}
	}

	function create() {
		$rss = getArrayFromRequest('rss');
		if (!(include_once 'simplepie/simplepie.inc'))  // vendor, debian
			if (!(include_once 'php-simplepie/autoloader.php'))  // fedora
				exit_error(_('Could not load the SimplePie PHP library.'));
		if (!is_dir(forge_get_config('data_path') .'/rss')) {
			mkdir(forge_get_config('data_path') .'/rss');
		}
		$rss_reader = new SimplePie($rss['url'], forge_get_config('data_path') .'/rss', null, forge_get_config('sys_proxy'));
		if ($rss_reader) {
			//TODO: why ??? We set the title in preference.
			$rss['title'] = $rss_reader->get_title();
		} else {
			return false;
		}
		$sql = 'INSERT INTO widget_rss (owner_id, owner_type, title, url) VALUES ($1,$2,$3,$4)';
		$res = db_query_params($sql,array($this->owner_id,$this->owner_type,$rss['title'],$rss['url']));
		$content_id = db_insertid($res, 'widget_rss', 'id');
		return $content_id;
	}

	function updatePreferences() {
		$done = false;
		if ($rss = getArrayFromRequest('rss')) {
			$url =  $rss['url'] ;
			$title =  $rss['title'] ;
			if ($url || $title) {
				$sql = "UPDATE widget_rss SET title=$1 , url=$2  WHERE owner_id =$3 AND owner_type = $4 AND id = $5";
				db_query_params($sql, array($title, $url, $this->owner_id, $this->owner_type, getIntFromRequest('content_id')));
				$done = true;
			}
		}
		return $done;
	}

	function destroy($id) {
		$sql = 'DELETE FROM widget_rss WHERE id = $1 AND owner_id = $2 AND owner_type = $3';
		db_query_params($sql,array($id,$this->owner_id,$this->owner_type));
	}

	function isUnique() {
		return false;
	}

	function _date_ago($from_time, $to_time) {
		$distance_in_minutes = round((abs($to_time - $from_time))/60);

		if ($distance_in_minutes <= 1) {
			return ($distance_in_minutes == 0) ? _('less than 1 minute') : _('1 minute');
		} elseif ($distance_in_minutes <= 44) {
			return sprintf(_('%d minutes ago'), $distance_in_minutes);
		} elseif ($distance_in_minutes <= 89) {
			return _('About one hour') ;
		} elseif ($distance_in_minutes <= 1439) {
			return sprintf(_('about %s hours'), round($distance_in_minutes/60));
		} elseif ($distance_in_minutes <= 2879) {
			return _('About one day') ;
		} elseif ($distance_in_minutes <= 43199) {
			return sprintf(_('%s days ago'), round($distance_in_minutes/1440));
		} elseif ($distance_in_minutes <= 86399) {
			return _('About one month') ;
		} elseif ($distance_in_minutes <= 525959) {
			return sprintf(_('%s months ago'), round($distance_in_minutes/43200));
		} elseif ($distance_in_minutes <= 1051919) {
			return _('About one year') ;
		} else {
			return sprintf(_('over %s years'), round($distance_in_minutes/525960));
		}
	}
}
