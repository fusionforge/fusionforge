<?php
/**
 * Copyright 2010 (c) Mélanie Le Bail
 * http://fusionforge.org
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

$GLOBALS['mailman_bin_dir'] =  '/usr/lib/mailman/bin';
$GLOBALS['mailman_lib_dir'] = '/var/lib/mailman';
$GLOBALS['forumml_arch'] = '/var/lib/mailman/archives';
$GLOBALS['forumml_tmp'] = '/var/spool/forumml';
$GLOBALS['forumml_dir'] = '/var/lib/gforge/forumml';
$GLOBALS['sys_lf'] = "\n";

function isLogged(){

        return session_loggedin();
}

function htmlRedirect($url) {
        session_redirect($url);
}
function htmlIframe($url,$poub) {
        if (isset($poub['id'])) {
                $id=$poub['id'];
        }
        else {
                $id='';
        }
        echo ('<iframe src= "'.$url.'" id="'.$id.'" width=100% height=500px></iframe>');
}

function helpButton($help) {

}
function getIcon($url,$w=16,$h=16,$args=array()) {
        echo html_image($url,$w,$h,$args);
}
function getImage($img) {
        echo util_make_url($GLOBALS['HTML']->imgroot.$img);

}
function get_server_url() {
        return util_make_url();
}
?>
