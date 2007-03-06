<?php
# Copyright (C) 2004 Brion Vibber <brion@pobox.com>
# http://www.mediawiki.org/
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or 
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
# http://www.gnu.org/copyleft/gpl.html

/**
 * Authentication plugin interface. Instantiate a subclass of AuthPlugin
 * and set $wgAuth to it to authenticate against some external tool.
 *
 * The default behavior is not to do anything, and use the local user
 * database for all authentication. A subclass can require that all
 * accounts authenticate externally, or use it only as a fallback; also
 * you can transparently create internal wiki accounts the first time
 * someone logs in who can be authenticated externally.
 *
 * This interface is new, and might change a bit before 1.4.0 final is
 * done...
 *
 * @package MediaWiki
 */

#
# LdapAuthentication.php 
# Infos availible at http://bugzilla.wikipedia.org/show_bug.cgi?id=814
#
# Version 1.0f / 07.10.2005
# including the fixes describend in comment #50 #51 and #52
#

require_once( 'AuthPlugin.php' );

class LdapAuthenticationPlugin extends AuthPlugin {
	var $email, $lang, $realname, $nickname, $SearchType;
	/**
	 * Check whether there exists a user account with the given name.
	 * The name will be normalized to MediaWiki's requirements, so
	 * you might need to munge it (for instance, for lowercase initial
	 * letters).
	 *
	 * @param string $username
	 * @return bool
	 * @access public
	 */
	function userExists( $username ) {
                global $wgLDAPAddLDAPUsers;

		$this->printDebug("Entering userExists",1);

		//If we can't add LDAP users, we don't really need to check
		//if the user exists, the authenticate method will do this for
		//us. This will decrease hits to the LDAP server.
		if (!$wgLDAPAddLDAPUsers) {
			return true;
		}
	
		$ldapconn = $this->connect();
		if ($ldapconn) {
			$this->printDebug("Successfully connected",1);
			$searchstring = $this->getSearchString($ldapconn,$username);

			//Search for the entry.
                        $entry = @ldap_read($ldapconn, $searchstring, "objectclass=*");

			//getSearchString is going to bind, but will not unbind
			//Let's clean up
			@ldap_unbind();
			if (!$entry) {
				$this->printDebug("Did not find a matching user in LDAP",1);
				//user wasn't found
				return false;
			} else {
				$this->printDebug("Found a matching user in LDAP",1);
				return true;
			}
		} else {
			$this->printDebug("Failed to connect",1);
			return false;
		}
		
	}
	
	/**
	 * Connect to the external database.
	 *
	 * @return resource
	 * @access private
	 */
	function connect() {
                global $wgLDAPServerNames;
                global $wgLDAPUseSSL, $wgLDAPUseTLS;

		$this->printDebug("Entering Connect",1);

                if ( $wgLDAPUseSSL ) {
			$this->printDebug("Using SSL",3);
                        $serverpre = "ldaps://";
                } else {
			$this->printDebug("Not Using SSL",3);
                        $serverpre = "ldap://";
                }
		
		$servers = "";
                $tmpservers = $wgLDAPServerNames[$_SESSION['wsDomain']];
                $tok = strtok($tmpservers, " ");
                while ($tok) {
                        $servers = $servers . " " . $serverpre . $tok;
                        $tok = strtok(" ");
                }
                $servers = rtrim($servers);
		$this->printDebug("Using servers: $servers",2);
                $ldapconn = @ldap_connect( $servers );
		ldap_set_option( $ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option( $ldapconn, LDAP_OPT_REFERRALS, 0);
		if ($wgLDAPUseTLS) {
			$this->printDebug("Using TLS",3);
			ldap_start_tls($ldapconn);
		}
		return $ldapconn;
	}

	
	/**
	 * Check if a username+password pair is a valid login.
	 * The name will be normalized to MediaWiki's requirements, so
	 * you might need to munge it (for instance, for lowercase initial
	 * letters).
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool
	 * @access public
	 */
	function authenticate( $username, $password ) {
		global $wgLDAPRetrievePrefs;
		global $wgLDAPGroupDN, $wgLDAPRequiredGroups;
		global $wgLDAPGroupUseFullDN;
		global $wgLDAPRequireAuthAttribute, $wgLDAPAuthAttribute;

		$this->printDebug("Entering Connect",1);

		//We don't handle local authentication
		if ( 'local' == $_SESSION['wsDomain'] ) {
			$this->printDebug("User is using a local domain",2);
			return false;
		}
		
		if ( '' == $password ) {
			$this->printDebug("User used a blank password",1);
			return false;
		}

                $ldapconn = $this->connect();
                if ( $ldapconn ) {
			$this->printDebug("Connected successfully",1);
			$userdn = $this->getSearchString($ldapconn, $username);

			//It is possible that getSearchString will return an
			//empty string; if this happens, the bind will ALWAYS
			//return true, and will let anyone in!
			if ('' == $userdn) {
				$this->printDebug("User DN is blank",1);
				return false;
			}

			$this->printDebug("Binding as the user",1);
			//Let's see if the user can authenticate.
			$bind = $this->bindAs($ldapconn, $userdn, $password);
			if (!$bind) {
				return false;
			}
			$this->printDebug("Binded successfully",1);

			if ($wgLDAPRequireAuthAttribute) {
				$this->printDebug("Checking for auth attributes",1);
				$filter = "(" . $wgLDAPAuthAttribute[$_SESSION['wsDomain']] . ")";
				$attributes = array("dn");
				$entry = ldap_read($ldapconn, $userdn, $filter, $attributes);
				$info = ldap_get_entries($ldapconn, $entry);
				if ($info["count"] < 1) {
					$this->printDebug("Failed auth attribute check",1);
					return false;
				}
                        }

			//Old style groups, non-nestable and fairly limited on group type (full DN
			//versus username). DEPRECATED
			if ($wgLDAPGroupDN) {
				$this->printDebug("Checking for (old style) group membership",1);
				if (!$this->isMemberOfLdapGroup($ldapconn, $userdn, $wgLDAPGroupDN)) {
					$this->printDebug("Failed (old style) group membership check",1);

					//No point in going on if the user isn't in the required group
					return false;
				}
			}

			if ($wgLDAPRequiredGroups[$_SESSION['wsDomain']]) {
				$this->printDebug("Checking for (new style) group membership",1);

				if ($wgLDAPGroupUseFullDN[$_SESSION['wsDomain']]) {
					$inGroup = $this->isMemberOfRequiredLdapGroup($ldapconn, $userdn);
				} else {
					$inGroup = $this->isMemberOfRequiredLdapGroup($ldapconn, $username);
				}

				if (!$inGroup) {
					return false;
				}

			}

			if ($wgLDAPRetrievePrefs) {
				$this->printDebug("Retrieving preferences",1);

				$entry = @ldap_read($ldapconn, $userdn, "objectclass=*");
				$info = @ldap_get_entries($ldapconn, $entry);
				$this->email = $info[0]["mail"][0];
				$this->lang = $info[0]["preferredlanguage"][0];
				$this->nickname = $info[0]["displayname"][0];
				$this->realname = $info[0]["cn"][0];

				$this->printDebug("Retrieved: $this->email, $this->lang, $this->nickname, $this->realname",2);
			}

			// Lets clean up.
			@ldap_unbind();
                } else {
			$this->printDebug("Failed to connect",1);
                        return false;
                }
		$this->printDebug("Authentication passed",1);
		//We made it this far; the user authenticated and didn't fail any checks, so he/she gets in.
                return true;
	}

	/**
	 * Modify options in the login template.
	 * 
	 * @param UserLoginTemplate $template
	 * @access public
	 */
	function modifyUITemplate( &$template ) {
		global $wgLDAPDomainNames, $wgLDAPUseLocal;
		global $wgLDAPAddLDAPUsers;

		if ( !$wgLDAPAddLDAPUsers ) {
			$template->set( 'create', false );
		}

                $template->set( 'usedomain', true );
		$template->set( 'useemail', false );

                $tempDomArr = $wgLDAPDomainNames;
                if ( $wgLDAPUseLocal ) {
                        array_push( $tempDomArr, 'local' );
                }
                $template->set( 'domainnames', $tempDomArr );
	}

	/**
	 * Return true if the wiki should create a new local account automatically
	 * when asked to login a user who doesn't exist locally but does in the
	 * external auth database.
	 *
	 * This is just a question, and shouldn't perform any actions.
	 *
	 * @return bool
	 * @access public
	 */
	function autoCreate() {
		return true;
	}

	/**
	 * Set the given password in the authentication database.
	 * Return true if successful.
	 * 
	 * @param string $password
	 * @return bool
	 * @access public
	 */
	function setPassword( $user, &$password ) {
		global $wgLDAPUpdateLDAP, $wgLDAPWriterDN, $wgLDAPWriterPassword;

		$this->printDebug("Entering setPassword",1);

		if ($_SESSION['wsDomain'] == 'local') {
			$this->printDebug("User is using a local domain",1);

			//We don't set local passwords, but we don't want the wiki
			//to send the user a failure.		
			return true;
		} else if (!$wgLDAPUpdateLDAP) {
			$this->printDebug("Wiki is set to not allow updates",1);

			//We aren't allowing the user to change his/her own password
			return false;
		}

		if (!isset($wgLDAPWriterDN)) {
			$this->printDebug("Wiki doesn't have wgLDAPWriterDN set",1);

			//We can't change a user's password without an account that is
			//allowed to do it.
			return false;
		}

		$pass = $this->getPasswordHash($pass);

		$ldapconn = $this->connect();
		if ($ldapconn) {
			$this->printDebug("Connected successfully",1);
			$userdn = $this->getSearchString($ldapconn, $user->getName());

			$this->printDebug("Binding as the writerDN",1);
                        $bind = $this->bindAs( $ldapconn, $wgLDAPWriterDN, $wgLDAPWriterPassword );
                        if (!$bind) {
                                return false;
                        }

			$values["userpassword"] = $pass;

			//Blank out the password in the database. We don't want to save
			//domain credentials for security reasons.
			$password = '';

			$success = ldap_modify($ldapconn, $userdn, $values);
			@ldap_unbind();
			if ($success) {
				$this->printDebug("Successfully modified the user's password",1);
				return true;
			} else {
				$this->printDebug("Failed to modify the user's password",1);
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Update user information in the external authentication database.
	 * Return true if successful.
	 *
	 * @param User $user
	 * @return bool
	 * @access public
	 */	
        function updateExternalDB( $user ) {
		global $wgLDAPUpdateLDAP;
		global $wgLDAPWriterDN, $wgLDAPWriterPassword;

		$this->printDebug("Entering updateExternalDB",1);

		if (!$wgLDAPUpdateLDAP || $_SESSION['wsDomain'] == 'local') {
			$this->printDebug("Either the user is using a local domain, or the wiki isn't allowing updates",1);

			//We don't handle local preferences, but we don't want the
			//wiki to return an error.
			return true;
		}

		if (!isset($wgLDAPWriterDN)) {
			$this->printDebug("The wiki doesn't have wgLDAPWriterDN set",1);

			//We can't modify LDAP preferences if we don't have a user
			//capable of editing LDAP attributes.
			return false;
		}

		$this->email = $user->getEmail();
		$this->realname = $user->getRealName();
		$this->nickname = $user->getOption('nickname');
		$this->language = $user->getOption('language');

		$ldapconn = $this->connect();
		if ($ldapconn) {
			$this->printDebug("Connected successfully",1);
			$userdn = $this->getSearchString($ldapconn, $user->getName());

			$this->printDebug("Binding as the writerDN",1);
                        $bind = $this->bindAs( $ldapconn, $wgLDAPWriterDN, $wgLDAPWriterPassword );
                        if (!$bind) {
                                return false;
                        }

			if ('' != $this->email) { $values["mail"] = $this->email; }
			if ('' != $this->nickname) { $values["displayname"] = $this->nickname; }
			if ('' != $this->realname) { $values["cn"] = $this->realname; }
			if ('' != $this->language) { $values["preferredlanguage"] = $this->language; }

			if (0 != sizeof($values) && ldap_modify($ldapconn, $userdn, $values)) {
				$this->printDebug("Successfully modified the user's attributes",1);
				@ldap_unbind();
				return true;
			} else {
				$this->printDebug("Failed to modify the user's attributes",1);
				@ldap_unbind();
				return false;
			}
		} else {
			$this->printDebug("Failed to Connect",1);
			return false;
		}
        }

	function canCreateAccounts() {
		global $wgLDAPAddLDAPUsers;

                if ($wgLDAPAddLDAPUsers) {
                        return true;
                }
	}

	/**
	 * Add a user to the external authentication database.
	 * Return true if successful.
	 *
	 * @param User $user
	 * @param string $password
	 * @return bool
	 * @access public
	 */
        function addUser( $user, $password ) {
                global $wgLDAPAddLDAPUsers, $wgLDAPWriterDN, $wgLDAPWriterPassword;
		global $wgLDAPSearchAttributes;
		global $wgLDAPWriteLocation;
		global $wgLDAPRequiredGroups, $wgLDAPGroupDN;
		global $wgLDAPRequireAuthAttribute, $wgLDAPAuthAttribute;

		$this->printDebug("Entering addUser",1);

		if ($wgLDAPRequiredGroups || $wgLDAPGroupDN) {
			$this->printDebug("The wiki is requiring users to be in specific groups, cannot add users as this would be a security hole.",1);
			//It is possible that later we can add users into
			//groups, but since we don't support it, we don't want
			//to open holes!
			return false;
		}

		if (!$wgLDAPAddLDAPUsers || 'local' == $_SESSION['wsDomain']) {
			$this->printDebug("Either the user is using a local domain, or the wiki isn't allowing users to be added to LDAP",1);

			//Tell the wiki not to return an error.
			return true;
		}

		if (!isset($wgLDAPWriterDN)) {
			$this->printDebug("The wiki doesn't have wgLDAPWriterDN set",1);

			//We can't add users without an LDAP account capable of doing so.
			return false;
		}

                $this->email = $user->getEmail();
                $this->realname = $user->getRealName();
		$username = $user->getName();

		$pass = $this->getPasswordHash($password);

                $ldapconn = $this->connect();
                if ($ldapconn) {
			$this->printDebug("Successfully connected",1);
			$userdn = $this->getSearchString($ldapconn, $username);
			if ('' == $userdn) {
				$this->printDebug("userdn is blank, attempting to use wgLDAPWriteLocation",1);
				if (isset($wgLDAPWriteLocation[$_SESSION['wsDomain']])) {
					$this->printDebug("wgLDAPWriteLocation is set, using that",1);
					$userdn = $wgLDAPSearchAttributes[$_SESSION['wsDomain']] . "=" . 
						$username . $wgLDAPWriteLocation[$_SESSION['wsDomain']];
				} else {
					$this->printDebug("wgLDAPWriteLocation is not set, failing",1);
					return false;
				}
			}

			$this->printDebug("Binding as the writerDN",1);
                        $bind = $this->bindAs( $ldapconn, $wgLDAPWriterDN, $wgLDAPWriterPassword );
                        if (!$bind) {
                                return false;
                        }

			//Set up LDAP attributes
                        $values["uid"] = $username;
                        $values["sn"] = $username;
			if ('' != $this->email) { $values["mail"] = $this->email; }
                        if ('' != $this->realname) {$values["cn"] = $this->realname; }
				else { $values["cn"] = $username; }
                        $values["userpassword"] = $pass;
                        $values["objectclass"] = "inetorgperson";
                        
			if ($wgLDAPRequireAuthAttribute) {
				$values[$wgLDAPAuthAttribute[$_SESSION['wsDomain']]] = "true";
			}

			if (@ldap_add($ldapconn, $userdn, $values)) {
				$this->printDebug("Successfully added user",1);
				@ldap_unbind();
				return true;
			} else {
				$this->printDebug("Failed to add user",1);
				@ldap_unbind();
				return false;
			}
                } else {
                        return false;
                }
        }

	/**
	 * Set the domain this plugin is supposed to use when authenticating.
	 *
	 * @param string $domain
	 * @access public	
	 */
        function setDomain( $domain ) {
        	$_SESSION['wsDomain'] = $domain;
	}

	/**
	 * Check to see if the specific domain is a valid domain.
	 * 
	 * @param string $domain
	 * @return bool
	 * @access public
	 */
	function validDomain( $domain ) {
		global $wgLDAPDomainNames, $wgLDAPUseLocal;

		$this->printDebug("Entering validDomain",1);

		if (in_array($domain, $wgLDAPDomainNames) || ($wgLDAPUseLocal && 'local' == $domain)) {
			$this->printDebug("User is using a valid domain",1);
			return true;
		} else {
			$this->printDebug("User is not using a valid domain, failing",1);
			return false;
		}
	}

        /**
         * When a user logs in, optionally fill in preferences and such.
         * For instance, you might pull the email address or real name from the
         * external user database.
         *
         * The User object is passed by reference so it can be modified; don't
         * forget the & on your function declaration.
         *
         * @param User $user
         * @access public
         */
        function updateUser( &$user ) {
		$this->printDebug("Entering updateUser",1);

		if ('' != $this->lang) {
                	$user->setOption('language',$this->lang);
		}
		if ('' != $this->nickname) {
                	$user->setOption('nickname',$this->nickname);
		}
		if ('' != $this->realname) {
                	$user->setRealName($this->realname);
		}
		if ('' != $this->email) {
                	$user->setEmail($this->email);
		}
        }

	/**
	 * Return true to prevent logins that don't authenticate here from being
	 * checked against the local database's password fields.
	 *
	 * This is just a question, and shouldn't perform any actions.
	 *
	 * @return bool
	 * @access public
	 */
	function strict() {
		global $wgLDAPUseLocal, $wgLDAPMailPassword;
		if ($wgLDAPUseLocal || $wgLDAPMailPassword) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * When creating a user account, optionally fill in preferences and such.
	 * For instance, you might pull the email address or real name from the
	 * external user database.
	 *
	 * The User object is passed by reference so it can be modified; don't
	 * forget the & on your function declaration.
	 *
	 * @param User $user
	 * @access public
	 */
	function initUser( &$user ) {
		$this->printDebug("Entering updateUser",1);

		if ('local' == $_SESSION['wsDomain']) {
			$this->printDebug("User is using a local domain",1);
			return;
		}

		//We are creating an LDAP user, it is very important that we do
		//NOT set a local password because it could compromise the
		//security of our domain.
		$user->setPassword( '' );

                if ('' != $this->lang) {
                        $user->setOption('language',$this->lang);
                }
                if ('' != $this->nickname) {
                        $user->setOption('nickname',$this->nickname);
                }
                if ('' != $this->realname) {
                        $user->setRealName($this->realname);
                }
                if ('' != $this->email) {
                        $user->setEmail($this->email);
                }
	}

	function getCanonicalName( $username ) {
		$this->printDebug("Entering getCanonicalName",1);

		//Change username to lowercase so that multiple user accounts
		//won't be created for the same user.
		$username = strtolower($username);

		//The wiki considers an all lowercase name to be invalid; need to
		//uppercase the first letter
		$username[0] = strtoupper($username[0]);
		$this->printDebug("Munged username: $username",1);
		return $username;
	}

	function getSearchString($ldapconn, $username) {
                global $wgLDAPSearchStrings;

		$this->printDebug("Entering getSearchString",1);

		if (isset($wgLDAPSearchStrings[$_SESSION['wsDomain']])) {
			//This is a straight bind
			$this->printDebug("Doing a straight bind",1);
                	$tmpuserdn = $wgLDAPSearchStrings[$_SESSION['wsDomain']];
                	$userdn = str_replace("USER-NAME",$username,$tmpuserdn);
		} else {
			//This is a proxy bind, or an anonymous bind with a search
			$this->printDebug("Doing a proxy or anonymous bind",1);
			$userdn = $this->getUserDN($ldapconn, $username);
		}
		$this->printDebug("userdn is: $userdn",2);
		return $userdn;
	}

	function getUserDN($ldapconn, $username) {
		global $wgLDAPProxyAgent, $wgLDAPProxyAgentPassword;
		global $wgLDAPSearchAttributes;
		global $wgLDAPRequireAuthAttribute, $wgLDAPAuthAttribute;
		global $wgLDAPBaseDNs;

		$this->printDebug("Entering getUserDN",1);

		if (isset($wgLDAPProxyAgent)) {
			//This is a proxy bind
			$this->printDebug("Doing a proxy bind",1);
                	$bind = $this->bindAs( $ldapconn, $wgLDAPProxyAgent, $wgLDAPProxyAgentPassword );
		} else {
			//This is an anonymous bind
			$this->printDebug("Doing an anonymous bind",1);
			$bind = $this->bindAs( $ldapconn );
		}

		if (!$bind) {
			$this->printDebug("Failed to bind",1);
			return '';
		}

		//we need to do a subbase search for the entry

		//Why waste a bind later, if a user is missing an auth attribute
		//let's catch it here.
		if ($wgLDAPRequireAuthAttribute) {
			$auth_filter = "(" . $wgLDAPAuthAttribute[$_SESSION['wsDomain']] . ")";
			$srch_filter = "(" . $wgLDAPSearchAttributes[$_SESSION['wsDomain']] . "=$username)";
			$filter = "(&" . $srch_filter . $auth_filter . ")";
			$this->printDebug("Created an auth attribute filter: $filter",2);
		} else {
			$filter = "(" . $wgLDAPSearchAttributes[$_SESSION['wsDomain']] . "=$username)";
			$this->printDebug("Created a regular filter: $filter",2);
		}

		$attributes = array("dn");
                $base = $wgLDAPBaseDNs[$_SESSION['wsDomain']];
		$this->printDebug("Using base: $base",2);
		$entry = @ldap_search($ldapconn, $base, $filter, $attributes);
		if (!$entry) {
			$this->printDebug("Couldn't find an entry",1);
			return '';
		}

		$info = @ldap_get_entries($ldapconn, $entry);
		$userdn = $info[0]["dn"];
		return $userdn;
	}

	//DEPRECATED
	function isMemberOfLdapGroup( $ldapconn, $userDN, $groupDN ) {
		$this->printDebug("Entering isMemberOfLdapGroup (DEPRECATED)",1);

		//we need to do a subbase search for the entry
		$filter = "(member=".$userDN.")";
		$info=ldap_get_entries($ldapconn,@ldap_search($ldapconn, $groupDN, $filter));
		return ($info["count"]>=1);
	}

	function isMemberOfRequiredLdapGroup( $ldapconn, $userDN ) {
		global $wgLDAPRequiredGroups;
		global $wgLDAPGroupSearchNestedGroups;

                $this->printDebug("Entering isMemberOfRequiredLdapGroup",1);

		$reqgroups = $wgLDAPRequiredGroups[$_SESSION['wsDomain']];
		$searchnested = $wgLDAPGroupSearchNestedGroups[$_SESSION['wsDomain']];

		$this->printDebug("Required groups:" . implode(",",$reqgroups) . "",1);

		$groups = $this->getGroups($ldapconn, $userDN);

		if (count($groups) == 0) {
			//User isn't in any groups, so he/she obviously can't be in
			//a required one
                	$this->printDebug("Couldn't find the user in any groups (1).",1);
			return false;
		} else {
			//User is in groups, let's see if a required group is one of them
			foreach ($groups as $group) {
				if ( in_array( $group, $reqgroups ) ) {
                			$this->printDebug("Found user in a group.",1);
					return true;
				}
			}

			//We didn't find the user in the group, lets check nested groups
			if ( $searchnested ) {
				//No reason to go on if we aren't allowing nested group
				//searches
				if ( $this->searchNestedGroups($ldapconn, $groups) ) {
					return true;
				}
			} 
                	$this->printDebug("Couldn't find the user in any groups (2).",1);
			return false;
		}
	}

	function searchNestedGroups( $ldapconn, $groups, $checkedgroups = array() ) {
		global $wgLDAPRequiredGroups;

                $this->printDebug("Entering searchNestedGroups",1);

		//base case, no more groups left to check
		if (!$groups) {
			$this->printDebug("Couldn't find user in any nested groups.",1);
			return false;
		}

		$this->printDebug("Checking groups:" . implode(",",$groups) . "",2);

		$reqgroups = $wgLDAPRequiredGroups[$_SESSION['wsDomain']];

		$groupstocheck = array();
		foreach ( $groups as $group ) {
			$returnedgroups = $this->getGroups($ldapconn, $group);
			foreach ($returnedgroups as $checkme) {
				$checkme = strtolower($checkme);
                		$this->printDebug("Checking membership for: $checkme",2);
				if (in_array($checkme,$checkedgroups)) {
					//We already checked this, move on
					continue;
				} else if (in_array($checkme,$reqgroups)) {
					$this->printDebug("Found user in a nested group.",1);
					//Woohoo
					return true;
				} else {
					//We'll need to check this group's members now
					array_push($groupstocheck,$checkme);
				}
			}
		}
 
		$checkedgroups = array_unique(array_merge($groups, $checkedgroups));

		//Mmmmmm. Tail recursion. Tasty.
		if ( $this->searchNestedGroups($ldapconn, $groupstocheck, $checkedgroups) ) {
			return true;
		} else {
			return false;
		}
	}

	function getGroups( $ldapconn, $dn ) {
                global $wgLDAPBaseDNs;
                global $wgLDAPGroupObjectclass, $wgLDAPGroupAttribute;
		global $wgLDAPProxyAgent, $wgLDAPProxyAgentPassword;

                $base = $wgLDAPBaseDNs[$_SESSION['wsDomain']];
                $objectclass = $wgLDAPGroupObjectclass[$_SESSION['wsDomain']];
                $attribute = $wgLDAPGroupAttribute[$_SESSION['wsDomain']];

                $this->printDebug("Entering getGroups",1);

                //Search for the groups this user is in
                $filter = "(&($attribute=$dn)(objectclass=$objectclass))";

                $this->printDebug("Search string: $filter",2);

		if ( isset($wgLDAPProxyAgent) ) {
			//We'll try to bind as the proxyagent as the proxyagent should normally have more
			//rights than the user. If the proxyagent fails to bind, we will still be able
			//to search as the normal user (which is why we don't return on fail).
                	$this->printDebug("Binding as the proxyagentDN",1);
			$bind = $this->bindAs($ldapconn, $wgLDAPProxyAgent, $wgLDAPProxyAgentPassword);
		}

                $info=ldap_get_entries($ldapconn,@ldap_search($ldapconn, $base, $filter));

                //We need to shift because the first entry will be a count
                array_shift($info);

                $groups = array();
                foreach ($info as $i) {
                        $mem = strtolower($i['dn']);
                        array_push($groups,$mem);
                }

		$this->printDebug("Returned groups:" . implode(",",$groups) . "",2);

		return $groups;
	}

	function getPasswordHash( $password ) {
		global $wgLDAPPasswordHash;

		$this->printDebug("Entering getPasswordHash",1);

		//Set the password hashing based upon admin preference
		switch ($wgLDAPPasswordHash) {
			case 'crypt':
				$pass = '{CRYPT}' . crypt($password);
				break;
			case 'clear':
				$pass = $password;
				break;
			default:
				$pwd_md5 = base64_encode(pack('H*',sha1($password)));
				$pass = "{SHA}".$pwd_md5;
				break;
		}
		$this->printDebug("Password is $pass",2);
		return $pass;
	}

	function printDebug( $debugText, $debugVal ) {
		global $wgLDAPDebug;

		if ($wgLDAPDebug > $debugVal) {
			echo $debugText . "<br>";
		}
	}

	function bindAs( $ldapconn, $userdn=null, $password=null ) {
		//Let's see if the user can authenticate.
		if ($userdn == null || $password == null) {
			$bind = @ldap_bind($ldapconn);
		} else {
			$bind = @ldap_bind($ldapconn, $userdn, $password);
		}
		if (!$bind) {
			$this->printDebug("Failed to bind as $userdn",1);
			$this->printDebug("with password: $password",3);
			return false;
		}
		return true;
	}
}

?>
