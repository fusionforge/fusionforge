<?php

require_once '../../env.inc.php';
require_once 'checks.php';

oauthconsumer_CheckUser();

$providers = OAuthProvider::get_all_oauthproviders();
$admin_access = false;
if(forge_check_global_perm ('forge_admin')) $admin_access = true;

if(count($providers)>0)	{	
	echo $HTML->boxTop(_('OAuth Providers'));
	echo $HTML->listTableTop(array(_('Name'), _('Description'), _('Consumer Key'), _('Consumer Secret'), _('Request Token Url'), _('Authorization Url'), _('Access Token Url'), '', ''));	
	$i = 0;
	foreach( $providers as $provider ) { ?>
		<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
		<td class="center"><?php echo ( $provider->get_name() ) ?></td>
	    <td class="center"><?php echo ( $provider->get_description() ) ?></td>
		<td class="center"><?php echo ( $provider->get_consumer_key() ) ?></td>
		<?php if($admin_access)	{ ?>
			<td class="center"><?php echo ( $provider->get_consumer_secret() ) ?></td>
		<?php }else 	{ ?>
			 <td class="center">*****************</td>
		<?php } ?>
		<td class="center"><?php echo ( $provider->get_request_token_url() ) ?></td>
		<td class="center"><?php echo ( $provider->get_authorize_url() ) ?></td>
		<td class="center"><?php echo ( $provider->get_access_token_url() ) ?></td>
		<?php 
		if ($admin_access) { ?>
			<td class="center">
			<?php print util_make_link('/plugins/'.$pluginname.'/provider_edit.php?provider_id=' . $provider->get_id() , _('Edit'));?>
			</td><?php
		}
	
		if ($admin_access) { ?>
			<td class="center">
			<?php print util_make_link('/plugins/'.$pluginname.'/provider_delete.php?provider_id=' . $provider->get_id() . '&plugin_oauthconsumer_provider_delete_token='.form_generate_key(), _('Delete')); ?>
			</td><?php
		}?>
		</tr>		
		<?php 
	} 
	echo $HTML->listTableBottom();
	echo $HTML->boxBottom();
	
}
else {
	echo '<p>'. _('There are currently no OAuth Providers registered in the database').'</p>';
}

if ($admin_access) {

	$f_provider_name = getStringFromPost( 'provider_name' );
	$f_provider_desc = getStringFromPost( 'provider_desc' );
	$f_consumer_key = getStringFromPost( 'consumer_key' );
	$f_consumer_secret = getStringFromPost( 'consumer_secret' );
	$f_request_token_url = getStringFromPost( 'request_token_url' );
	$f_authorize_url = getStringFromPost( 'authorize_url' );
	$f_access_token_url = getStringFromPost( 'access_token_url' );

	?>
	<br/>
	<form action="provider_add.php" method="post">
	<?php echo '<input type="hidden" name="plugin_oauthconsumer_provider_create_token" value="'.form_generate_key().'"/>' ?>
	<table class="width50" align="center" cellspacing="1">
	
	<tr>
	<td class="form-title" colspan="2"><?php echo _('<b>Add a new OAuth provider</b>') ?></td>
	</tr>
	
	<tr>
	<td class="category"><?php echo _('Name') ?></td>
	<td><input name="provider_name" maxlength="128" size="40" value="<?php echo $f_provider_name ?>"/></td>
	</tr>
	
	<tr>
	<td class="category"><?php echo _('Description') ?></td>
	<td><input name="provider_desc" maxlength="250" size="40" value="<?php echo $f_provider_desc ?>"/></td>
	</tr>
	
	<tr>
	<td class="category"><?php echo _('Consumer Key') ?></td>
	<td><input name="consumer_key" maxlength="250" size="40" value="<?php echo $f_consumer_key ?>"/></td>
	</tr>
	
	<tr>
	<td class="category"><?php echo _('Consumer Secret') ?></td>
	<td><input name="consumer_secret" maxlength="250" size="40" value="<?php echo $f_consumer_secret ?>"/></td>
	</tr>
	
	<tr>
	<td class="category"><?php echo _('Request Token URL') ?></td>
	<td><input name="request_token_url" maxlength="250" size="40" value="<?php echo $f_request_token_url ?>"/></td>
	</tr>
	
	<tr>
	<td class="category"><?php echo _('Authorization URL') ?></td>
	<td><input name="authorize_url" maxlength="250" size="40" value="<?php echo $f_authorize_url ?>"/></td>
	</tr>
	
	<tr>
	<td class="category"><?php echo _('Access Token URL') ?></td>
	<td><input name="access_token_url" maxlength="250" size="40" value="<?php echo $f_access_token_url ?>"/></td>
	</tr>
	
	<tr>
	<td class="center" colspan="2"><input type="submit" value="<?php echo _('Add provider') ?>"/></td>
	</tr>
	
	</table>
	</form>
	<?php
}

echo'<br><br>';

echo util_make_link('/plugins/'.$pluginname.'/get_access_token.php', _('Get Access tokens')).'<br /> ';
echo util_make_link('/plugins/'.$pluginname.'/access_tokens.php', _('Access tokens')).'<br /> ';


site_user_footer(array());
