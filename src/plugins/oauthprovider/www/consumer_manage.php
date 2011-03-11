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

session_require_global_perm('forge_admin');

$f_consumer_id = getIntFromGet( 'consumer_id' );

$t_consumer = OauthAuthzConsumer::load( $f_consumer_id );

$i = 0;
?>

<br/>
<table class="width75" align="center" cellspacing="1">

<tr>
<td class="form-title" colspan="2"><?php echo $plugin_oauthprovider_manage_consumer ?></td>
<td class="right">
<?php
	print util_make_link('/plugins/'.$pluginname.'/index.php?type='.$type.'&id='.$id.'&pluginname='.$pluginname , $plugin_oauthprovider_back);
?>
</td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo $plugin_oauthprovider_name ?></td>
<td colspan="2"><?php echo ( $t_consumer->getName() ) ?></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo $plugin_oauthprovider_url ?></td>
<td colspan="2"><?php echo ( $t_consumer->getUrl() ) ?></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo $plugin_oauthprovider_desc ?></td>
<td colspan="2"><?php echo ( $t_consumer->getDesc() ) ?></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo $plugin_oauthprovider_email ?></td>
<td colspan="2"><?php echo ( $t_consumer->getEmail() ) ?></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo $plugin_oauthprovider_key ?></td>
<td colspan="2"><?php echo ( $t_consumer->key ) ?></td>
</tr>

<tr <?php echo $HTML->boxGetAltRowStyle($i++) ?>>
<td class="category"><?php echo $plugin_oauthprovider_secret ?></td>
<td colspan="2"><?php echo ( $t_consumer->secret ) ?></td>
</tr>

<tr>
<td width="30%"></td>
<td width="20%"></td>
<td width="50%"></td>
</tr>

<tr>
<td colspan="1">
<form action="<?php echo 'consumer_update_page.php?type='.$type.'&id='.$id.'&pluginname='.$pluginname . '&consumer_id=' . $t_consumer->getId() ?>" method="post">
	<input type="submit" value="<?php echo $plugin_oauthprovider_update_consumer ?>"/>
</form>
</td>
<td colspan="1">
<form action="<?php echo 'consumer_delete.php?type='.$type.'&id='.$id.'&pluginname='.$pluginname . '&consumer_id=' . $t_consumer->getId() ?>" method="post">
	<?php echo '<input type="hidden" name="plugin_oauthprovider_consumer_delete_token" value="'.form_generate_key().'"/>' ?>
	<input type="submit" value="<?php echo $plugin_oauthprovider_delete_consumer ?>"/>
</form>
</td>
</tr>

</table>

<?php
//html_page_bottom1( __FILE__ );
site_project_footer(array());


