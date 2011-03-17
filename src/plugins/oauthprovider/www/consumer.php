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

$user = session_get_user(); // get the session user
$t_consumers = OauthAuthzConsumer::load_all();

$t_can_manage = false;
if(forge_check_global_perm ('forge_admin')) $t_can_manage = true;

// FIXME : use $HTML->boxTop() and likes bellow
if(count($t_consumers)>0)	{	
?>

<br/>
<table align="center" cellspacing="1">

  <tr>
  <td class="form-title"><?php echo $plugin_oauthprovider_consumers ?></td>
  </tr>

  <tr class="row-category">
  <td><?php echo $plugin_oauthprovider_consumer ?></td>
  <td><?php echo $plugin_oauthprovider_url ?></td>
  <td><?php echo $plugin_oauthprovider_desc ?></td>
  <td><?php echo $plugin_oauthprovider_email ?></td>
  <td><?php echo $plugin_oauthprovider_key ?></td>
  <td><?php echo $plugin_oauthprovider_secret ?></td>  
  <td></td>
  <td></td>
  </tr>

<?php
			
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
	  
	  print util_make_link('/plugins/'.$pluginname.'/consumer_manage.php?type='.$type.'&id='.$id.'&pluginname='.$pluginname . '&consumer_id=' . $t_consumer->getId() , $plugin_oauthprovider_manage);
	}
      ?>
    </td>
    <td class="center">
      <?php 
	if ( $t_can_manage ) {
	  print util_make_link('/plugins/'.$pluginname.'/consumer_delete.php?type='.$type.'&id='.$id.'&pluginname='.$pluginname . '&consumer_id=' . $t_consumer->getId() . '&plugin_oauthprovider_consumer_delete_token='.form_generate_key(), $plugin_oauthprovider_delete);
	  
	}
      ?>
    </td>    
  </tr>
<?php } ?>

</table>

<?php
}
else {
	echo '<p>There are currently no customers in the database.</p>';
}

if ( $t_can_manage ) { ?>
<br/>
<form action="<?php echo 'consumer_create.php?type='.$type.'&id='.$id.'&pluginname='.$pluginname ?>" method="post">
<?php echo '<input type="hidden" name="plugin_oauthprovider_consumer_create_token" value="'.form_generate_key().'"/>' ?>
<table class="width50" align="center" cellspacing="1">

<tr>
<td class="form-title" colspan="2"><?php echo $plugin_oauthprovider_create_consumer ?></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo $plugin_oauthprovider_name ?></td>
<td><input name="consumer_name" maxlength="128" size="40"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo $plugin_oauthprovider_url ?></td>
<td><input name="consumer_url" maxlength="250" size="40"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo $plugin_oauthprovider_desc ?></td>
<td><input name="consumer_desc" maxlength="250" size="40"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo $plugin_oauthprovider_email ?></td>
<td><input name="consumer_email" maxlength="250" size="40"/></td>
</tr>



<tr>
<td class="center" colspan="2"><input type="submit" value="<?php echo $plugin_oauthprovider_create_consumer ?>"/></td>
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
