<?php

require_once('../../env.inc.php');
require_once 'checks.php';

oauthconsumer_CheckUser();

$userid = session_get_user()->getID();
$access_tokens = OAuthAccessToken::get_all_access_tokens($userid);

if(count($access_tokens)>0)	{	
	echo $HTML->boxTop(_('OAuth Access Tokens'));
	echo $HTML->listTableTop(array(_('Provider'), _('Consumer Key'), _('Consumer Secret'), '', ''));	
	$i = 0;
	foreach( $access_tokens as $token ) { ?>
		<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
		<td class="center"><?php echo ( OAuthProvider::get_provider($token->get_provider_id())->get_name() ) ?></td>
	    <td class="center"><?php echo ( $token->get_token_key() ) ?></td>
		<td class="center"><?php echo ( $token->get_token_secret() ) ?></td>
		<td class="center">
			<?php print util_make_link('/plugins/'.$pluginname.'/access_token_delete.php?token_id=' . $token->get_id() . '&plugin_oauthconsumer_delete_access_token='.form_generate_key(), _('Delete')); ?>
		</td></tr>		
		<?php 
	} 
	echo $HTML->listTableBottom();
	echo $HTML->boxBottom();
	echo util_make_link('/plugins/'.$pluginname.'/get_access_token.php', _('Get more access tokens')).'<br /> ';
}
else {
	echo '<p>'. _('You have no OAuth Access Tokens registered in the database currently').'</p>';
}


site_user_footer(array());