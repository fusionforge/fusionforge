<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('Widget.class.php');

/**
 * Widget_Rss
 *
 * Rss reader
 */
/* abstract */ class Widget_Rss extends Widget {
	var $rss_title;
	var $rss_url;
	function Widget_Rss($id, $owner_id, $owner_type) {
		$this->Widget($id);
		$this->setOwner($owner_id, $owner_type);
	}
	function getTitle() {
		$hp = Codendi_HTMLPurifier::instance();
		return $this->rss_title ?  $hp->purify($this->rss_title, CODENDI_PURIFIER_CONVERT_HTML)  : 'RSS Reader';
	}
	function getContent() {
		$hp = Codendi_HTMLPurifier::instance();
		$content = '';
		if ($this->rss_url) {
			if (function_exists('idn_to_utf8()')) {
				function idn_to_utf8($param) {
					return idn_to_unicode($param);
				}
			}
			require_once('common/rss/simplepie.inc');
			if (!is_dir(forge_get_config('sys_var_path') .'/rss')) {
				mkdir(forge_get_config('sys_var_path') .'/rss');
			}
			$rss = new SimplePie($this->rss_url, forge_get_config('sys_var_path') .'/rss', null, forge_get_config('sys_proxy'));
			$max_items = 10;
			$items = array_slice($rss->get_items(), 0, $max_items);
			$content .= '<table width="100%">';
			$i = 0;
			foreach($items as $item){
				if ($i % 2 == 0) {
					$class="bgcolor-white";
				}
				else {
					$class="bgcolor-grey";
				}

				$i=$i+1;

				$content .= '<tr class="'. $class .'"><td WIDTH="99%">';
				if ($image = $item->get_link(0, 'image')) {
					//hack to display twitter avatar
					$content .= '<img src="'.  $hp->purify($image, CODENDI_PURIFIER_CONVERT_HTML)  .'" style="float:left; margin-right:1em;" />';
				}
				$content .= '<a href="'. $item->get_link() .'">'. $item->get_title() .'</a>';  //Trust SimplePie for purifying
				if ($item->get_date()) {
					$content .= '<span style="color:#999;" title="'. date(_("Y-m-d H:i"), $item->get_date('U')) .'"> - '. $this->_date_ago($item->get_date('U'),time()) .'</span>';
				}
				$content .= '</td></tr>';
			}
			$content .= '</table>';
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
		$prefs  = '';
		$prefs .= '<table><tr><td>Title:</td><td><input type="text" class="textfield_medium" name="rss[title]" value="'. $hp->purify($this->rss_title, CODENDI_PURIFIER_CONVERT_HTML) .'" /></td></tr>';
		$prefs .= '<tr><td>Url:</td><td><input type="text" class="textfield_medium" name="rss[url]" value="'. $hp->purify($this->rss_url, CODENDI_PURIFIER_CONVERT_HTML) .'" /></td></tr>';
		$prefs .= '</table>';
		return $prefs;
	}
	function getInstallPreferences() {
		$prefs  = '';
		$prefs .= '<table>';
		$prefs .= '<tr><td>Url:</td><td><input type="text" class="textfield_medium" name="rss[url]" value="'. _("http://search.twitter.com/search.atom?q=fusionforge&amp;show_user=1") .'" /></td></tr>';
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
	function create(&$request) {
		$content_id = false;
		$vUrl = new Valid_String('url');
		$vUrl->setErrorMessage("Can't add empty rss url");
		$vUrl->required();
		if($request->validInArray('rss', $vUrl)) {
			$rss = $request->get('rss');
			$vTitle = new Valid_String('title');
			$vTitle->required();
			if (!$request->validInArray('rss', $vTitle)) {
				if (function_exists('idn_to_utf8()')) {
					require_once('simplepie/simplepie.inc');
				}
				else {
					require_once('common/rss/simplepie.inc');
				}
				if (!is_dir(forge_get_config('sys_var_path') .'/rss')) {
					mkdir(forge_get_config('sys_var_path') .'/rss');
				}
				$rss_reader = new SimplePie($rss['url'], forge_get_config('sys_var_path') .'/rss', null, forge_get_config('sys_proxy'));
				$rss['title'] = $rss_reader->get_title();
			}
			$sql = 'INSERT INTO widget_rss (owner_id, owner_type, title, url) VALUES ($1,$2,$3,$4)';
			$res = db_query_params($sql,array($this->owner_id,$this->owner_type,$rss['title'],$rss['url']));
			$content_id = db_insertid($res, 'widget_rss', 'id');
		}
		return $content_id;
	}
	function updatePreferences(&$request) {
		$done = false;
		$vContentId = new Valid_UInt('content_id');
		$vContentId->required();
		if (($rss = $request->get('rss')) && $request->valid($vContentId)) {
			$vUrl = new Valid_String('url');
			if($request->validInArray('rss', $vUrl)) {
				$url =  $rss['url'] ;
			} else {
				$url = '';
			}

			$vTitle = new Valid_String('title');
			if($request->validInArray('rss', $vTitle)) {
				$title =  $rss['title'] ;
			} else {
				$title = '';
			}

			if ($url || $title) {
				$sql = "UPDATE widget_rss SET title=$1 , url=$2  WHERE owner_id =$3 AND owner_type = $4 AND id = $5";
				$res = db_query_params($sql,array($title,$url,$this->owner_id,$this->owner_type,(int)$request->get('content_id')));
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
	function _date_ago($from_time, $to_time, $include_seconds = false) {
		$distance_in_minutes = round((abs($to_time - $from_time))/60);
		$distance_in_seconds = round(abs($to_time - $from_time));

		if ($distance_in_minutes <= 1) {
			return ($distance_in_minutes == 0) ? _('less than 1 minute') : _('1 minute');
		} else if ($distance_in_minutes <= 44) {
			return vsprintf(_('%s minutes ago'), $distance_in_minutes);
		} else if ($distance_in_minutes <= 89) {
			return _('About one hour') ;
		} else if ($distance_in_minutes <= 1439) {
			return vsprintf(_('about %s hours'), round($distance_in_minutes/60));
		} else if ($distance_in_minutes <= 2879) {
			return _('About one day') ;
		} else if ($distance_in_minutes <= 43199) {
			return vsprintf(_('%s days ago'), round($distance_in_minutes/1440));
		} else if ($distance_in_minutes <= 86399) {
			return _('About one month') ;
		} else if ($distance_in_minutes <= 525959) {
			return vsprintf(_('%s months ago'), round($distance_in_minutes/43200));
		} else if ($distance_in_minutes <= 1051919) {
			return _('About one year') ;
		} else {
			return vsprintf(_('over %s years'), round($distance_in_minutes/525960));
		}
	}

}
?>
