<?php
/**
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
 * Copyright 2012-2014, Franck Villaume - TrivialDev
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The provided file ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

//
//	This page contains a form with a file-upload button
//	so a user can choose a file to upload a .csv file and store it in task mgr
//

global $ath;
global $group_id;

$ath->header(array('atid'=>$ath->getID(), 'title'=>$ath->getName()));

$default = array('headers' => 1, 'sep' => ',');

if (session_loggedin()) {
	$u = session_get_user();
	$pref = $u->getPreference('csv');
	if ($pref) {
		$default = unserialize($pref);
	}
}

$headers = getIntFromRequest('headers', $default['headers']);
$sep = getStringFromRequest('sep', $default['sep']);

if (session_loggedin()) {
	if ( ($sep !== $default['sep']) || ($headers !== $default['headers']) ) {
		$pref = array_merge( $default, array('headers' => $headers, 'sep' => $sep));
		$u->setPreference('csv', serialize($pref));
	}
}

$url_set_format = '/tracker/?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;func=format_csv&amp;sep='.urlencode($sep).'&amp;headers='.$headers;

$url_export = '/tracker/?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;func=downloadcsv&amp;sep='.urlencode($sep).'&amp;headers='.$headers;

$format = $headers ? ' with headers' : ' without headers';
$format .= " using '$sep' as separator.";
?>
<p><?php echo _('This page allows you to export the items using a CSV (<a href="http://en.wikipedia.org/wiki/Comma-separated_values">Comma Separated Values</a>) File. This format can be used to view your entries using your favorite spreadsheet software.'); ?></p>
<h2><?php echo _('Export as a CSV file'); ?></h2>

<strong><?php echo _('Selected CSV Format:'); ?></strong> CSV<?php echo $format ?> <a href="<?php echo $url_set_format ?>">(Change)</a>

<p><a href="<?php echo $url_export ?>"><?php echo _('Download CSV file'); ?></a></p>

<p ></p>
<?php
$ath->footer(array());
