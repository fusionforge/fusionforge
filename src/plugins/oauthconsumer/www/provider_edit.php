<?php

require_once('../../env.inc.php');
require_once 'checks.php';

oauthconsumer_CheckForgeAdminExit();

$provider_id = isset($f_provider_id)?$f_provider_id:getIntFromGet( 'provider_id' );
$provider = OAuthProvider::get_provider($provider_id);

$f_provider_name = getStringFromPost( 'provider_name' );
$f_provider_desc = getStringFromPost( 'provider_desc' );
$f_consumer_key = getStringFromPost( 'consumer_key' );
$f_consumer_secret = getStringFromPost( 'consumer_secret' );
$f_request_token_url = getStringFromPost( 'request_token_url' );
$f_authorize_url = getStringFromPost( 'authorize_url' );
$f_access_token_url = getStringFromPost( 'access_token_url' );

$i = 0;
?>

<br/>
<form action="provider_update.php" method="post">
<?php echo '<input type="hidden" name="plugin_oauthconsumer_provider_update_token" value="'.form_generate_key().'"/>' ?>
<input type="hidden" name="provider_id" value="<?php echo $provider->get_id() ?>"/>
<table class="width75" align="center" cellspacing="1">

<tr>
<td class="form-title" colspan="2"><?php echo _('<b>Edit OAuth Provider</b>') ?></td>
<td class="right"><?php print util_make_link("/plugins/".$pluginname.'/providers.php', _('Cancel') ); ?></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo _('Name') ?></td>
<td><input name="provider_name" maxlength="128" size="60" value="<?php echo ($f_provider_name)?$f_provider_name:$provider->get_name() ?>"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo _('Description') ?></td>
<td><input name="provider_desc" maxlength="250" size="60" value="<?php echo ($f_provider_desc)?$f_provider_desc:$provider->get_description() ?>"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo _('Consumer Key') ?></td>
<td><input name="consumer_key" maxlength="250" size="60" value="<?php echo ($f_consumer_key)?$f_consumer_key:$provider->get_consumer_key() ?>"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo _('Consumer Secret') ?></td>
<td><input name="consumer_secret" maxlength="250" size="60" value="<?php echo ($f_consumer_secret)?$f_consumer_secret:$provider->get_consumer_secret() ?>"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo _('Request Token URL') ?></td>
<td><input name="request_token_url" maxlength="250" size="60" value="<?php echo ($f_request_token_url)?$f_request_token_url:$provider->get_request_token_url() ?>"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo _('Authorization URL') ?></td>
<td><input name="authorize_url" maxlength="250" size="60" value="<?php echo ($f_authorize_url)?$f_authorize_url:$provider->get_authorize_url() ?>"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo _('Access Token URL') ?></td>
<td><input name="access_token_url" maxlength="250" size="60" value="<?php echo ($f_access_token_url)?$f_access_token_url:$provider->get_access_token_url() ?>"/></td>
</tr>

<tr>
<td class="center" colspan="1"><input type="submit" name="update" value="<?php echo  _('Update Provider') ?>"/></td>
</tr>
</table>
</form>

<?php

echo'<br><br>';

echo util_make_link('/plugins/'.$pluginname.'/providers.php', _('OAuth Providers')). ' <br />';
echo util_make_link('/plugins/'.$pluginname.'/get_access_token.php', _('Get Access tokens')).'<br /> ';
echo util_make_link('/plugins/'.$pluginname.'/access_tokens.php', _('Access tokens')).'<br /> ';

site_user_footer(array());
