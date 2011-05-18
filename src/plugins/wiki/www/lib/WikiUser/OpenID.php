<?php //-*-php-*-
// $Id: OpenID.php 7956 2011-03-03 17:08:31Z vargenau $
/*
 * Copyright (C) 2010 ReiniUrban
 * Zend_OpenId_Consumer parts from Zend licensed under
 * http://framework.zend.com/license/new-bsd
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
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * This is not yet finished. We do not want to use zend extensions.
 *
 * See http://openid.net/specs/openid-authentication-1_1.html
 */

// requires the openssl extension
require_once("lib/HttpClient.php");

class _OpenIDPassUser
extends _PassUser
/**
 * Preferences are handled in _PassUser
 */
{
    /**
     * Verifies authentication response from OpenID server.
     *
     * This is the second step of OpenID authentication process.
     * The function returns true on successful authentication and false on
     * failure.
     *
     * @param array $params HTTP query data from OpenID server
     * @param string &$identity this argument is set to end-user's claimed
     *  identifier or OpenID provider local identifier.
     * @param mixed $extensions extension object or array of extensions objects
     * @return bool
     */
    function verify($params, &$identity = "", $extensions = null) {
        $version = 1.1;
        $this->_setError("");
        if (isset($params['openid_ns']) &&
            $params['openid_ns'] == $NS_2_0) { // global session var
            $version = 2.0;
        }
        if (isset($params["openid_claimed_id"])) {
            $identity = $params["openid_claimed_id"];
        } else if (isset($params["openid_identity"])){
            $identity = $params["openid_identity"];
        } else {
            $identity = "";
        }

        if ($version < 2.0 && !isset($params["openid_claimed_id"])) {
            global $request;
            $session = $request->getSessionVar('openid');
            if (!$session) {
                $request->setSessionVar('openid', array());
            }
            if ($session['identity'] == $identity) {
                $identity = $session['claimed_id'];
            }
        }
        if (empty($params['openid_return_to'])) {
            $this->_setError("Missing openid.return_to");
            return false;
        }
        if (empty($params['openid_signed'])) {
            $this->_setError("Missing openid.signed");
            return false;
        }
        if (empty($params['openid_sig'])) {
            $this->_setError("Missing openid.sig");
            return false;
        }
        if (empty($params['openid_mode'])) {
            $this->_setError("Missing openid.mode");
            return false;
        }
        if ($params['openid_mode'] != 'id_res') {
            $this->_setError("Wrong openid.mode '".$params['openid_mode']."' != 'id_res'");
            return false;
        }
        if (empty($params['openid_assoc_handle'])) {
            $this->_setError("Missing openid.assoc_handle");
            return false;
        }
    }

    /**
     * Performs check of OpenID identity.
     *
     * This is the first step of OpenID authentication process.
     * On success the function does not return (it does HTTP redirection to
     * server and exits). On failure it returns false.
     *
     * @param bool $immediate enables or disables interaction with user
     * @param string $id OpenID identity
     * @param string $returnTo HTTP URL to redirect response from server to
     * @param string $root HTTP URL to identify consumer on server
     * @param mixed $extensions extension object or array of extensions objects
     * @param Zend_Controller_Response_Abstract $response an optional response
     *  object to perform HTTP or HTML form redirection
     * @return bool
     */
    function _checkId($immediate, $id, $returnTo=null, $root=null,
                      $extensions=null, $response = null) {
        $this->_setError('');

        /*if (!Zend_OpenId::normalize($id)) {
            $this->_setError("Normalisation failed");
            return false;
        }*/
        $claimedId = $id;

        if (!$this->_discovery($id, $server, $version)) {
            $this->_setError("Discovery failed");
            return false;
        }
        if (!$this->_associate($server, $version)) {
            $this->_setError("Association failed");
            return false;
        }
        if (!$this->_getAssociation(
                $server,
                $handle,
                $macFunc,
                $secret,
                $expires)) {
            /* Use dumb mode */
            unset($handle);
            unset($macFunc);
            unset($secret);
            unset($expires);
        }

        $params = array();
        if ($version >= 2.0) {
            //$params['openid.ns'] = Zend_OpenId::NS_2_0;
        }

        $params['openid.mode'] = $immediate ?
            'checkid_immediate' : 'checkid_setup';

        $params['openid.identity'] = $id;

        $params['openid.claimed_id'] = $claimedId;

        if ($version <= 2.0) {
            global $request;
            $session = $request->getSessionVar('openid');
            $request->setSessionVar('identity', $id);
            $request->setSessionVar('claimed_id', $claimedId);
        }

        if (isset($handle)) {
            $params['openid.assoc_handle'] = $handle;
        }

        //$params['openid.return_to'] = Zend_OpenId::absoluteUrl($returnTo);

        // See lib/WikiUser/FaceBook.php how to handle http requests
        $web = new HttpClient("$server", 80);
        if (DEBUG & _DEBUG_LOGIN) $web->setDebug(true);

        if (empty($root)) {
            //$root = Zend_OpenId::selfUrl();
            if ($root[strlen($root)-1] != '/') {
                $root = dirname($root);
            }
        }
        if ($version >= 2.0) {
            $params['openid.realm'] = $root;
        } else {
            $params['openid.trust_root'] = $root;
        }

        /*if (!Zend_OpenId_Extension::forAll($extensions, 'prepareRequest', $params)) {
            $this->_setError("Extension::prepareRequest failure");
            return false;
        }
        */

        //Zend_OpenId::redirect($server, $params, $response);
        return true;
    }

    function _setError($message) {
        $this->_error = $message;
    }

    function checkPass($password) {
        $userid = $this->_userid;
        if (!loadPhpExtension('openssl')) {
            trigger_error(
                sprintf(_("The PECL %s extension cannot be loaded."), "openssl")
                 . sprintf(_(" %s AUTH ignored."), 'OpenID'),
                 E_USER_WARNING);
            return $this->_tryNextUser();
        }

        $retval = $this->_checkId(false, $id, $returnTo, $root, $extensions, $response);
        $this->_authmethod = 'OpenID';
        if (DEBUG & _DEBUG_LOGIN) trigger_error(get_class($this)."::checkPass => $retval",
                                                E_USER_WARNING);
        if ($retval) {
            $this->_level = WIKIAUTH_USER;
        } else {
            $this->_level = WIKIAUTH_ANON;
        }
        return $this->_level;
    }

    /* do nothing. the login/redirect is done in checkPass */
    function userExists() {
        if (!$this->isValidName($this->_userid)) {
            return $this->_tryNextUser();
        }
        if (!loadPhpExtension('openssl')) {
            trigger_error
                (sprintf(_("The PECL %s extension cannot be loaded."), "openssl")
                 . sprintf(_(" %s AUTH ignored."), 'OpenID'),
                 E_USER_WARNING);
            return $this->_tryNextUser();
        }
        if (DEBUG & _DEBUG_LOGIN)
            trigger_error(get_class($this)."::userExists => true (dummy)", E_USER_WARNING);
        return true;
    }

    // no quotes and shorter than 128
    function isValidName() {
        if (!$this->_userid) return false;
        return !preg_match('/[\"\']/', $this->_userid) and strlen($this->_userid) < 128;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
