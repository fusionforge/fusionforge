<?php
/** A-Select class for Gforge
 *
 * This file is part of Gforge
 *
 * This class, like Gforge, is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */


class ASelect {
	var $username;
	var $organization;
	var $attributes=array();
	var $ticket;
	function ASelect () {

		if(isset($_COOKIE['aselectuid'])){
		   $this->username = $_COOKIE['aselectuid'];
		} else {
   		 $this->username = '';
		}

		if(isset($_COOKIE['aselectorganization'])){
			$this->organization = $_COOKIE['aselectorganization'];
		} else {
   		$this->organization = '';
		}

		if(isset($_COOKIE['aselectattributes'])){
			$a = explode('&', $_COOKIE['aselectattributes']);
			$i = 0;
			while ($i < count($a)) {
   			$b = split('=', $a[$i]);
   			$this->$attributes[htmlspecialchars(urldecode($b[0]))] = htmlspecialchars(urldecode($b[1]));
   			$i++;
			}
			} else {
   		//nothing to be done here yet.
			}

		if(isset($_COOKIE['aselectticket'])){
			$this->ticket = $_COOKIE['aselectticket'];
		} else {
   		$this->ticket = '';
		}
	}

	function getUserName () {
		return $this->username;
	}

	function getOrganization () {
		return $this->organization;
	}

	function getAttributes () {
		return $this->attributes;
	}

	function getAttribute ($attribute) {
		return $this->attributes[$attribute];
	}

	function getTicket () {
		return $this->ticket;
	}


}
?>
