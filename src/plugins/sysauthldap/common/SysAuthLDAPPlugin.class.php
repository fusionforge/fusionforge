<?php
/**
 * FusionForge sysauthldap plugin
 *
 * Copyright 2012, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

forge_define_config_item('enabled', 'sysauthldap', 'no');
forge_set_config_item_bool('enabled', 'sysauthldap');

forge_define_config_item('ldap_host', 'sysauthldap', '$core/web_host');
forge_define_config_item('ldap_port', 'sysauthldap', '389');
forge_define_config_item('ldap_version', 'sysauthldap', '3');
forge_define_config_item('base_dn', 'sysauthldap', 'fromhost:$core/web_host');
forge_define_config_item('bind_dn', 'sysauthldap', 'cn=admin,$sysauthldap/base_dn');
forge_define_config_item('password', 'sysauthldap', '');

setconfigfromenv ('sysauthldap', 'ldap_password',
			 'GForgePluginSysAuthLdapPasswd', NULL) ;

class SysAuthLDAPPlugin extends SysAuthPlugin {
	function SysAuthLDAPPlugin () {
		$this->SysAuthPlugin() ;
		$this->name = "sysauthldap" ;
		$this->text = _("System authentication via LDAP");
		$this->pkg_desc =
_("This plugin maintains data about users, groups and memberships in an
LDAP directory that can be used for NSS/PAM system authentication (or
for other uses).");
		$this->ldap_conn = NULL;
		$this->user_suffix = "Users";
		$this->group_suffix = "Projects";
	}

	function setError($msg, $code=1) {
		error_log($msg);
		parent::setError($msg,$code);
	}

	function _connect() {
		if ($this->ldap_conn) {
			return true;
		}

		$this->clearError();
		$ldap_conn = @ldap_connect(forge_get_config('ldap_host', $this->name),forge_get_config('ldap_port', $this->name));
		if (!$ldap_conn) {
			$this->setError('ERROR: Cannot connect to LDAP server<br />');
			return false;
		}
		if (forge_get_config('ldap_version', $this->name)) {
			if (!ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, forge_get_config('ldap_version', $this->name))) {
				return false;
			}
		}

		$this->basedn = $this->_parse_fromhost(forge_get_config('base_dn', $this->name));
		$this->binddn = $this->_parse_fromhost(forge_get_config('bind_dn', $this->name));

		if (!ldap_bind($ldap_conn,$this->binddn,forge_get_config('ldap_password', $this->name))) {
			return false;
		}
		$this->ldap_conn = $ldap_conn;
		return true;
	}

	function _parse_fromhost($s) {
		$result = $s;
		if (preg_match ('/\b(fromhost:[^,]+)/', $s, $matches)) {
			$m = preg_replace ('/^fromhost:/','',$matches[0]);

			$r = array();
			foreach (explode ('.', $m) as $i) {
				$r[] = 'dc='.$i;
			}
			$result = preg_replace ('/\b(fromhost:[^,]+)/',
						      implode(',',$r),
						      $s);
		}
		return $result;
	}

	function user_update($params) {
		if (!forge_get_config('enabled',$this->name)) return true;
		if (!$this->_connect()) { exit_error("Error connecting to LDAP"); }

		$user = $params['user'];

		if (! $user->isActive()) {
			return $this->_user_delete($user);
		}

		$entry = $this->_get_user_entry($user);
		$dn = $this->_get_user_dn($user);

		if (!$this->_exists($dn)) {
			if (@ldap_add($this->ldap_conn,$dn,$entry)) {
				return true;
			}

			$this->_create_user_suffix();
			if (ldap_add($this->ldap_conn,$dn,$entry)) {
				return true;
			}
			$this->setError("ERROR: cannot add LDAP user entry '".
					$user->getUnixName()."' ($dn): ".ldap_error($this->ldap_conn));
			return false;
		} else {
			if (@ldap_modify($this->ldap_conn,$dn,$entry)) {
				return true;
			}
			$this->setError("ERROR: cannot modify LDAP user entry '".
					$user->getUnixName()."' ($dn): ".ldap_error($this->ldap_conn));
			return false;
		}
	}

	function _user_delete($user) {
		$dn = $this->_get_user_dn($user);

		if (!$this->_exists($dn)) {
			return true;
		}

		if (ldap_delete($this->ldap_conn,$dn)) {
			return true;
		}
		$this->setError("ERROR: cannot delete LDAP user entry '".
				$user->getUnixName()."' ($dn): ".ldap_error($this->ldap_conn));
		return false;
	}

	function user_delete($params) {
		if (!forge_get_config('enabled',$this->name)) return true;
		if (!$this->_connect()) { exit_error("Error connecting to LDAP"); }

		$user = $params['user'];

		return $this->_user_delete($user);
	}

	function _group_update_standard($group) {
		$entry = $this->_get_group_entry($group);
		$dn = $this->_get_group_dn($group);

		foreach ($group->getUsers(false) as $u) {
			$entry['memberUid'][] = $u->getUnixName();
		}

		if (!$this->_exists($dn)) {
			if (@ldap_add($this->ldap_conn,$dn,$entry)) {
				return true;
			}

			$this->_create_group_suffix();
			if (ldap_add($this->ldap_conn,$dn,$entry)) {
				return true;
			}

			$this->setError("ERROR: cannot add LDAP group entry '".
					$group->getUnixName()."' ($dn): ".ldap_error($this->ldap_conn));
			return false;
		} else {
			if (@ldap_modify($this->ldap_conn,$dn,$entry)) {
				return true;
			}

			$this->setError("ERROR: cannot modify LDAP group entry '".
					$group->getUnixName()."' ($dn): ".ldap_error($this->ldap_conn));
			return false;
		}
	}

	function _group_update_scm($group) {
		if (! $group->usesSCM()) {
			return $this->_group_delete_prefix($group,'scm_');
		}

		$entry = $this->_get_group_entry($group, 'scm_');
		$dn = $this->_get_group_dn($group, 'scm_');

		foreach ($group->getUsers(false) as $u) {
			if (forge_check_perm_for_user($u,'scm',$group->getID(),'write')) {
				$entry['memberUid'][] = $u->getUnixName();
			}
		}

		if (!$this->_exists($dn)) {
			if (@ldap_add($this->ldap_conn,$dn,$entry)) {
				return true;
			}

			$this->_create_group_suffix();
			if (ldap_add($this->ldap_conn,$dn,$entry)) {
				return true;
			}

			$this->setError("ERROR: cannot add LDAP group entry '".
					$group->getUnixName()."' ($dn): ".ldap_error($this->ldap_conn));
			return false;
		} else {
			if (@ldap_modify($this->ldap_conn,$dn,$entry)) {
				return true;
			}

			$this->setError("ERROR: cannot modify LDAP group entry '".
					$group->getUnixName()."' ($dn): ".ldap_error($this->ldap_conn));
			return false;
		}
	}

	function _group_delete_prefix($group,$prefix='') {
		$dn = $this->_get_group_dn($group,$prefix);

		if (!$this->_exists($dn)) {
			return true;
		}

		if (ldap_delete($this->ldap_conn,$dn)) {
			return true;
		}

		$this->setError("ERROR: cannot delete LDAP group entry '".
				$group->getUnixName()."' ($dn): ".ldap_error($this->ldap_conn));
		return false;
	}

	function group_update($params) {
		if (!forge_get_config('enabled',$this->name)) return true;
		if (!$this->_connect()) { exit_error("Error connecting to LDAP"); }

		$group = $params['group'];

		if (! $group->isActive()) {
			return $this->_group_delete_prefix($group) &&
				$this->_group_delete_prefix($group,'scm_');
		}

		return $this->_group_update_standard($group)
			&& $this->_group_update_scm($group);
	}

	function group_delete($params) {
		if (!forge_get_config('enabled',$this->name)) return true;
		if (!$this->_connect()) { exit_error("Error connecting to LDAP"); }

		$group = $params['group'];

		return $this->_group_delete_prefix($group,'') &&
			$this->_group_delete_prefix($group,'scm_');
	}

	function _get_user_dn_suffix() {
		return 'ou='.$this->user_suffix.','.$this->basedn;
	}

	function _create_user_suffix() {
		$dn = $this->_get_user_dn_suffix();
		if (!$this->_exists($dn)) {
			$entry = array();
			$entry['objectClass'] = 'organizationalUnit';
			$entry['ou'] = $this->user_suffix;
			ldap_add($this->ldap_conn,$dn,$entry);
		}
	}

	function _get_user_dn($user) {
		return 'uid='.$user->getUnixName().','.$this->_get_user_dn_suffix();
	}

	function _get_user_entry($user) {
		$entry = array();

		$entry['objectClass'][0]='top';
		$entry['objectClass'][1]='account';
		$entry['objectClass'][2]='posixAccount';
		$entry['objectClass'][3]='shadowAccount';
		$entry['objectClass'][4]='debGforgeAccount';
		$entry['uid']=$user->getUnixName();
		$entry['cn']=$user->getRealName();
		$entry['gecos']=preg_replace("/[\x80-\xff]/","?",$user->getRealName());
		$entry['userPassword']='{crypt}'.$user->getUnixPasswd();
		$entry['homeDirectory'] = account_user_homedir($user->getUnixName());
		$entry['loginShell']=$user->getShell();
		$entry['debGforgeCvsShell']="/bin/cvssh";
		$entry['debGforgeForwardEmail']=$user->getEmail();
		$entry['uidNumber']=$user->getUnixUID();
		$entry['gidNumber']=$user->getUnixGID();
		$entry['shadowLastChange']=1;
		$entry['shadowMax']=99999;
		$entry['shadowWarning']=7;

		return $entry;
	}

	function _get_group_dn_suffix() {
		return 'ou='.$this->group_suffix.','.$this->basedn;
	}

	function _create_group_suffix() {
		$dn = $this->_get_group_dn_suffix();
		if (!$this->_exists($dn)) {
			$entry = array();
			$entry['objectClass'] = 'organizationalUnit';
			$entry['ou'] = $this->group_suffix;
			ldap_add($this->ldap_conn,$dn,$entry);
		}
	}

	function _get_group_dn($group, $prefix='') {
		return 'cn='.$prefix.$group->getUnixName().','.$this->_get_group_dn_suffix();
	}

	function _get_group_entry($group, $prefix='') {
		$entry = array();

		$entry['objectClass'][0]='top';
		$entry['objectClass'][1]='posixGroup';
		$entry['cn']=$prefix.$group->getUnixName();
		$entry['userPassword']='{crypt}x';
		$entry['gidNumber']=$group->getID()+10000;

		return $entry;
	}

	function _exists($dn) {
		$sdn = $this->basedn;
		$t = ldap_explode_dn($dn,0);
		$t = $t[0];
		$filter = "($t)";
		$sr = ldap_search($this->ldap_conn, $sdn, $filter);
		if (!$sr) {
			return false;
		}
		$e = ldap_get_entries($this->ldap_conn, $sr);
		for ($i = 0; $i < $e['count']; $i++) {
			if ($e[$i]['dn'] == $dn) {
				return true;
			}
		}
		return false;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
