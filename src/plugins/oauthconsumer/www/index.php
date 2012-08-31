<?php

// TODO : copyright header missing

require_once('../../env.inc.php');
require_once 'checks.php';

oauthconsumer_CheckUser();

$userid = session_get_user()->getID();
$providers = OAuthProvider::get_all_oauthproviders();
if(count($providers)>0)	{
	echo '<p>'. _('This OAuth Consumer plugin allows a user to connect to different oauth enabled services.').'</p>';
	echo '<h4>'._('Accessing resources with OAuth').'</h4>';
	?>
	<form action="index.php" method="post">
	<?php echo _('<b>Providers</b>');
	$f_provider_id = getStringFromPost('providers');?>
	<select name=providers>
	<?php foreach ($providers as $provider) 	{
		echo '<option value="'.$provider->get_id().'" ';
		if($provider->get_id()==$f_provider_id)	{
			echo 'SELECTED';
		}
		echo '>'.$provider->get_name().'</option>';
	}?>
		</select>
		<input type="submit" value="<?php echo _('Select') ?>"/>
		</form>
	<?php
	
	if($f_provider_id)	{
		$access_tokens = OAuthAccessToken::get_all_access_tokens_by_provider($f_provider_id, $userid);
		if(count($access_tokens)>0)	{
			?>
			<form action="response.php" method="post">
			<table class="width50" align="center" cellspacing="1">
							
			<tr>
			<td class="category"><?php echo _('Access Tokens');?></td>
			<td><select name=tokens>
			<?php foreach ($access_tokens as $token) 	{
				echo '<option value="'.$token->get_id().'">'.$token->get_token_key().'</option>';
			}?>
			</select></td>
			</tr>
			
			<tr>
			<td class="category"><?php echo _('Resource URL') ?></td>
			<td><input name="resource_url" maxlength="250" size="60" value=""/></td>
			</tr>
			
			<tr>
			<td class="category"><?php echo _('HTTP Request');?></td>
			<td><select name=http>
				<option value="get">GET</option>
				<option value="post">POST</option>
			</select></td>
			</tr>
			
			<tr>
			<td class="category"><?php echo _('POST data') ?></td>
			<td><input name="post_data" maxlength="250" size="60" value=""/></td>
			</tr>
			
			<tr>
			<td class="center" colspan="2"><input type="submit" value="<?php echo _('Go') ?>"/></td>
			</tr>
			
			</table>			
			</form>
			
			<br><br>
			<?php 
			
		}else {
			echo '<p>'. _('No access tokens have been created for this provider').'</p>';
		}
	}
}else 	{
	echo '<p>'. _('There are no OAuth Providers registered in the database currently. Please ask your forge administer to create one.').'</p>';
}

echo'<br><br><p>'._("If no OAuth Providers or Access Tokens have been created yet, follow the links below to get started").'</p>';

echo util_make_link('/plugins/'.$pluginname.'/providers.php', _('OAuth Providers')). ' <br />';
echo util_make_link('/plugins/'.$pluginname.'/get_access_token.php', _('Get Access tokens')).'<br /> ';
echo util_make_link('/plugins/'.$pluginname.'/access_tokens.php', _('Access tokens')).'<br /> ';

site_user_footer(array());
