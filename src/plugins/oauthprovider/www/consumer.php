<?php

/**
 * Manage OAuth consumers
 * 
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

oauthprovider_CheckSiteAdmin();

$user = session_get_user(); // get the session user
$t_consumers = OauthAuthzConsumer::load_all();

$t_can_manage = false;
if(forge_check_global_perm ('forge_admin')) $t_can_manage = true;

// FIXME : use $HTML->boxTop() and likes bellow
if(count($t_consumers)>0)	{	
	echo $HTML->boxTop(_('OAuth consumers'));

	echo $HTML->listTableTop(array(_('Consumer'), _('URL'), _('Description'), _('Email'), _('Key'), _('Secret'), '', ''));
	
	$i = 0;
	foreach( $t_consumers as $t_consumer ) { ?>
	<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
    <td><?php echo ( $t_consumer->getName() ) ?></td>
    <td class="center"><?php echo ( $t_consumer->getURL() ) ?></td>
	<td class="center"><?php echo ( $t_consumer->getDesc() ) ?></td>
	<td class="center"><?php echo ( $t_consumer->getEmail() ) ?></td>
	<td><?php echo ( substr($t_consumer->key, 0, 10).'...' ) ?></td>
    <td><?php 
	//if ( $t_can_manage ) {
	  echo ( substr($t_consumer->secret, 0, 10).'...' );
	/*}
	else {
	  echo '**************';
	}*/ ?></td>
	<td class="center">
      <?php 
	if ( $t_can_manage ) {	  
	  print util_make_link('/plugins/'.$pluginname.'/consumer_manage.php?consumer_id=' . $t_consumer->getId() , _('Manage'));
	}
      ?>
    </td>
    <td class="center">
      <?php 
	if ( $t_can_manage ) {
	  print util_make_link('/plugins/'.$pluginname.'/consumer_delete.php?consumer_id=' . $t_consumer->getId() . '&plugin_oauthprovider_consumer_delete_token='.form_generate_key(), _('Delete'));
	}
    } 
    echo $HTML->listTableBottom();
    
echo $HTML->boxBottom();

}
else {
	echo '<p>'. _('There are currently no OAuth consumers registered in the database').'</p>';
}

if ( $t_can_manage ) { 

$f_consumer_name = getStringFromPost( 'consumer_name' );
$f_consumer_url = getStringFromPost( 'consumer_url' );
$f_consumer_desc = getStringFromPost( 'consumer_desc' );
$f_consumer_email = getStringFromPost( 'consumer_email' );
	
	?>
<br/>
<form action="consumer_create.php" method="post">
<?php echo '<input type="hidden" name="plugin_oauthprovider_consumer_create_token" value="'.form_generate_key().'"/>' ?>
<table class="width50" align="center" cellspacing="1">

<tr>
<td class="form-title" colspan="2"><?php echo _('Create Consumer') ?></td>
</tr>

<tr>
<td class="category"><?php echo _('Name') ?></td>
<td><input name="consumer_name" maxlength="128" size="40" value="<?php echo $f_consumer_name ?>"/></td>
</tr>

<tr>
<td class="category"><?php echo _('URL') ?></td>
<td><input name="consumer_url" maxlength="250" size="40" value="<?php echo $f_consumer_url ?>"/></td>
</tr>

<tr>
<td class="category"><?php echo _('Description') ?></td>
<td><input name="consumer_desc" maxlength="250" size="40" value="<?php echo $f_consumer_desc ?>"/></td>
</tr>

<tr>
<td class="category"><?php echo _('Email') ?></td>
<td><input name="consumer_email" maxlength="250" size="40" value="<?php echo $f_consumer_email ?>"/></td>
</tr>



<tr>
<td class="center" colspan="2"><input type="submit" value="<?php echo _('Create Consumer') ?>"/></td>
</tr>

</table>
</form>
<?php }
	
	
	site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
