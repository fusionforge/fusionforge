<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
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

/* deleteCategory action page */
global $mantisbt;
global $mantisbtConf;
global $username;
global $password;

$deleteCategory = $_POST['deleteCategory'];

if ($deleteCategory) {
    try {
	    $clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
	    $clientSOAP->__soapCall('mc_project_delete_category', array("username" => $username, "password" => $password, "p_project_id" => $mantisbtConf['id_mantisbt'], "p_category_name" => $deleteCategory));
    } catch (SoapFault $soapFault) {
        $msg = _('Error:').' '.$soapFault->faultstring;
        session_redirect('plugins/mantisbt/?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&error_msg='.urlencode($msg));
    }
    $feedback = sprintf(_('Category %s deleted successfully'), $deleteCategory);
    session_redirect('plugins/mantisbt/?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&feedback='.urlencode($feedback));
} else {
    $warning = _('Missing parameters to delete category');
    session_redirect('plugins/mantisbt/?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&warning_msg='.urlencode($warning));
}
?>
