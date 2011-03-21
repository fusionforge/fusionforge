<?php


require_once ('../../../www/env.inc.php');
require_once $gfcommon.'include/pre.php';


session_require_login();

// get global users vars
$u =& user_get_object(user_getid());
if (!$u || !is_object($u)) {
	exit_error(_('Could Not Get User'));
} elseif ($u->isError()) {
	exit_error($u->getErrorMessage(),'my');
}

$openid_identity = htmlspecialchars(trim(getStringFromRequest('openid_identity', 'http://')));

print_r($_GET);
print_r($_POST);

if (getStringFromRequest('addidentity') != '') {
	if ($openid_identity == '' || $openid_identity == 'http://') {
		$error_msg = _('ERROR: Missing URL for the new identity');
	} else if (!util_check_url($openid_identity)) {
		$error_msg = _('ERROR: Malformed URL (only http, https and ftp allowed)');
	} else {
		$res = db_query_params('SELECT openid_identity FROM plugin_authopenid_user_identities WHERE openid_identity =$1',
					array($openid_identity));
		if ($res && db_numrows($res) > 0) {
			$error_msg = _('ERROR: identity already used by a forge user.');
		} else {
			$res = db_query_params('INSERT INTO plugin_authopenid_user_identities (user_id, openid_identity) VALUES ($1,$2)',
					       array ($u->getID(),
						      htmlspecialchars_decode($openid_identity))) ;
			if (!$res || db_affected_rows($res) < 1) {
				$error_msg = sprintf(_('Cannot insert new identity: %s'),
						     db_error());
			} else {
				$feedback = _('Identity successfully added');
				$openid_identity = 'http://';
			}
		}
	}
} elseif (getStringFromRequest('delete') != '') {
	$openid_identity = urldecode(htmlspecialchars_decode($openid_identity));
	echo 'delete ';
	print_r($openid_identity);
	$res = db_query_params('DELETE FROM plugin_authopenid_user_identities WHERE user_id=$1 AND openid_identity=$2',
				array($u->getID(), $openid_identity));
	if (!$res || db_affected_rows($res) < 1) {
		$error_msg = sprintf(_('Cannot delete identity: %s'), db_error());
	}
	else {
		$feedback = _('Identity successfully deleted');
		$openid_identity = 'http://';
	}
}

$title = sprintf(_('Manage OpenID identities for user %1$s'), $u->getUnixName());
site_user_header(array('title'=>$title));

echo $HTML->boxTop(_('My OpenID identities'));

?>
<h2><?php echo _('Add new identity'); ?></h2>

<p><?php echo _('You can add your own OpenID identities in the form below.') ?></p>

<form name="new_identity" action="<?php echo util_make_uri ('/plugins/authopenid/'); ?>" method="post">
<fieldset>
<legend><?php echo _('Add new identity'); ?></legend>
<p>
<input type="hidden" name="user_id" value="<?php echo $u->getID() ?>" />
<input type="hidden" name="addidentity" value="1" />
<strong><?php echo _('OpenID identity URL:') ?></strong><?php echo utils_requiredField(); ?>
<br />
<input type="text" size="150" name="openid_identity" value="<?php echo $openid_identity ?>" /><br />
</p>
<p>
<input type="submit" value="<?php echo _('Add identity') ?>" />
</p>
</fieldset>
</form>
<?php

echo $HTML->listTableTop(array(_('Identity'), ''));

$res = db_query_params('SELECT openid_identity FROM plugin_authopenid_user_identities WHERE user_id =$1',
							    array($u->getID()));
if($res) {
	$i = 0;

	while ($row = db_fetch_array($res)) {
		$openid_identity = 	$row['openid_identity'];
	
		echo '<tr '.$HTML->boxGetAltRowStyle($i).'>';
		echo '<td>'. $openid_identity .'</td>';
		echo '<td><a href="'.util_make_uri ('/plugins/authopenid/').'?openid_identity='. urlencode($openid_identity) .'&delete=1">delete</a></td>';
		echo '</tr>';
		$i++;
	}
}

echo $HTML->listTableBottom();

echo $HTML->boxBottom();


site_user_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
