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

oauthprovider_CheckSiteAdmin();

session_require_global_perm('forge_admin');

$f_consumer_id = getIntFromGet( 'consumer_id' );

$t_consumer = OauthAuthzConsumer::load( $f_consumer_id );


$i=0;
?>

<br/>
<form action="consumer_update.php" method="post">
<?php echo '<input type="hidden" name="plugin_oauthprovider_consumer_update_token" value="'.form_generate_key().'"/>' ?>
<input type="hidden" name="consumer_id" value="<?php echo $t_consumer->getId() ?>"/>
<table class="width60" align="center" cellspacing="1">

<tr>
<td class="form-title"><?php echo _('Update Consumer') ?></td>
<td class="right"><?php print util_make_link("/plugins/".$pluginname.'/consumer_manage.php?consumer_id=' . $t_consumer->getId(), _('Cancel') ); ?></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo _('Name') ?></td>
<td><input name="consumer_name" maxlength="128" size="40" value="<?php echo ( $t_consumer->getName() ) ?>"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo _('URL') ?></td>
<td><input name="consumer_url" maxlength="250" size="40" value="<?php echo ( $t_consumer->getUrl() ) ?>"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo _('Description') ?></td>
<td><input name="consumer_desc" maxlength="250" size="40" value="<?php echo ( $t_consumer->getDesc() ) ?>"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo _('Email') ?></td>
<td><input name="consumer_email" maxlength="250" size="40" value="<?php echo ( $t_consumer->getEmail() ) ?>"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo _('Key') ?></td>
<td><input name="consumer_key" readonly="readonly" maxlength="250" size="40" value="<?php echo ( $t_consumer->key ) ?>"/></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo _('Secret') ?></td>
<td><input name="consumer_secret" readonly="readonly" maxlength="250" size="40" value="<?php echo ( $t_consumer->secret ) ?>"/></td>
</tr>

<tr>
<td class="center" colspan="1"><input type="submit" name="update" value="<?php echo  _('Update Consumer') ?>"/></td>
<td class="center" colspan="1"><input type="submit" name="keys_update" value="<?php echo  _('Regenerate keys & Update Consumer') ?>"/></td>
</tr>
</table>
</form>


<?php
//html_page_bottom1( __FILE__ );
site_project_footer(array());
