<?php

require_once('../../env.inc.php');
require_once 'checks.php';

oauthconsumer_CheckUser();

echo util_make_link('/plugins/'.$pluginname.'/providers.php', _('OAuth Providers')). ' <br />';
echo util_make_link('/plugins/'.$pluginname.'/get_access_token.php', _('Get Access tokens')).'<br /> ';
echo util_make_link('/plugins/'.$pluginname.'/access_tokens.php', _('Access tokens')).'<br /> ';


site_user_footer(array());