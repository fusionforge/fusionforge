<?php

/**
 * This file is (c) Copyright 2010 by Olivier BERGER, Madhumita DHAR, Institut TELECOM
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * This program has been developed in the frame of the COCLICO
 * project with financial support of its funders.
 *
 */

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';

require_once 'checks.php';

$pluginname = 'oauthprovider';

/*if($type!=admin)
{
	exit_error("Only the Project Admin can access this page.", 'oauthprovider');
}*/

if(forge_check_global_perm ('forge_admin'))	$admin_access=true;

if($admin_access)	{
	oauthprovider_CheckSiteAdmin();
	$t_tokens = OauthAuthzRequestToken::load_all();
}else {
	oauthprovider_CheckUser();
	$t_tokens = OauthAuthzRequestToken::load_all(user_getid());
}

$headers = array(
	_('Consumer name'),
	_('Key'),
	_('Secret'),
	_('Authorized'),
	_('Role'),
	_('User'),
	_('Authorized on'),
	'DELETE'
	);

echo $HTML->boxTop(_('Request Tokens'));
echo $HTML->listTableTop($headers);

$i=0;
foreach( $t_tokens as $t_token ) {
	$consumer = OauthAuthzConsumer::load($t_token->getConsumerId());
	echo '<tr '.$HTML->boxGetAltRowStyle($i).'>';
	if($admin_access)	{
		echo '<td>'.util_make_link('/plugins/'.$pluginname.'/consumer_manage.php?consumer_id=' . $t_token->getConsumerId(), $consumer->getName()).'</td>';
	}else {
		echo '<td>'.$consumer->getName().'</td>';
	}
	echo '<td>'.$t_token->key.'</td>';
	echo '<td>'.$t_token->secret.'</td>';
	if($t_token->getAuthorized()==1)	$auth = 'Yes';
	else $auth = 'No';
	echo '<td>'.$auth.'</td>';
	$role_id =$t_token->getRole();
	if($role_id!=0)	{
		//echo 'Roleid: '.$role_id;
		$role = RBACEngine::getInstance()->getRoleById($role_id);
		//print_r($role);
		echo '<td>'.$role->getName().'</td>';
	}else {
		echo '<td>'.'---'.'</td>';
	}
	if($t_token->getUserId() > 0 ) {
		$user_object =& user_get_object($t_token->getUserId());
          $user = $user_object->getRealName().' ('.$user_object->getUnixName().')';
	}	else {
	  $user = "-";
	}
	echo '<td>'.$user.'</td>';
	echo '<td>'.date(DATE_RFC822, $t_token->gettime_stamp()) .'</td>';
	echo '<td>'.util_make_link('/plugins/'.$pluginname.'/token_delete.php?token_id=' . $t_token->getId() . '&token_type=request' . '&plugin_oauthprovider_token_delete_token='.form_generate_key(), _('Delete')).'</td>';
	echo '</tr>';
	$i++;

}

echo $HTML->listTableBottom();
echo $HTML->boxBottom();

//html_page_bottom1( __FILE__ );
site_project_footer(array());
