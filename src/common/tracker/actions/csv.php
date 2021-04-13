<?php
/**
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
 * Copyright 2012-2014,2016, Franck Villaume - TrivialDev
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

global $ath;
global $group_id;
global $HTML;

$ath->header(array('atid'=>$ath->getID(), 'title'=>$ath->getName()));

$default = array('headers' => 1, 'sep' => ',');

if (session_loggedin()) {
	$u = session_get_user();
	$pref = $u->getPreference('csv');
	if ($pref) {
		$default = unserialize($pref);
	}
}

$bom = getIntFromRequest('bom', 0);
$encoding = getStringFromRequest('encoding', 'UTF-8');
$headers = getIntFromRequest('headers', $default['headers']);
$sep = getFilteredStringFromRequest('sep', '/^[,;]$/', $default['sep']);

if (session_loggedin()) {
	if ( ($sep !== $default['sep']) || ($headers !== $default['headers']) ) {
		$pref = array_merge( $default, array('headers' => $headers, 'sep' => $sep));
		$u->setPreference('csv', serialize($pref));
	}
}

echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[

TrackerCSVController = function(params)
{
	this.csvparams	= params;
	this.bindControls();
};

TrackerCSVController.prototype =
{
	/*! Binds the controls to the actions
	 */
	bindControls: function() {
		this.csvparams.buttonStartDate.click(jQuery.proxy(this, 'setStartDate'));
	},

	setStartDate: function() {
		if (this.csvparams.buttonStartDate.is(':checked')) {
			this.csvparams.datePickerStartDate.removeAttr('disabled');
			this.csvparams.datePickerStartDate.attr('required', 'required');
		} else {
			this.csvparams.datePickerStartDate.attr('disabled', 'disabled');
			this.csvparams.datePickerStartDate.removeAttr('required');
		}
	},
};

var controllerCSV;

jQuery(document).ready(function() {
	controllerCSV = new TrackerCSVController({
		buttonStartDate:	jQuery('#limitByLastModifiedDate'),
		datePickerStartDate:	jQuery('#datepicker_start'),
	});

	jQuery('#datepicker_start').datepicker({
		dateFormat: "<?php echo _('yy-mm-dd'); ?>"
	});
});

//]]>
<?php
echo html_ac(html_ap() - 1);

$url = '/tracker/?group_id='.$group_id.'&atid='.$ath->getID().'&sep='.urlencode($sep).'&headers='.$headers;
$format = $headers ? _(' with headers') : _(' without headers');
$format .= _(' using ')."'".htmlentities($sep)."'"._(' as separator.');
?>
<p><?php echo _('This page allows you to export the items using a CSV (<a href="http://en.wikipedia.org/wiki/Comma-separated_values">Comma Separated Values</a>) File. This format can be used to view your entries using your favorite spreadsheet software.'); ?></p>
<?php
echo $HTML->information(_('By default, export uses filter as setup in the browse page. To overwrite, please use Advanced Options'));
echo html_e('h2', array(), _('Export as a CSV file'));
echo html_e('strong', array(), _('Selected CSV Format')._(': ')).'CSV'.$format.' '.util_make_link($url.'&func=format_csv&encoding='.$encoding.'&bom='.$bom, $HTML->getConfigurePic(_('Modify this CSV format.')));
echo $HTML->openForm(array('action' => $url.'&func=downloadcsv', 'method' => 'post'));
echo html_e('input', array('type' => 'hidden', 'name' => 'bom', 'value' => $bom));
echo html_e('input', array('type' => 'hidden', 'name' => 'encoding', 'value' => $encoding));
echo html_ao('fieldset', array('id' => 'fieldset1_closed', 'class' => 'coolfieldset'));
echo html_e('legend', array(), _('Advanced Options'));
echo html_ao('div');
echo html_e('p', array(), _('Overwrite default filter. (No filtering)')._(': ').html_e('input', array('type' => 'checkbox', 'name' => 'overwrite_filter', 'value' => 'overwrite')));
$attrsInputLimitByStartDate = array('type' => 'checkbox', 'id' => 'limitByLastModifiedDate', 'name' => 'limitByLastModifiedDate', 'value' => 1, 'title' => _('Set last modified date limitation for this export. If not enable, not limitation.'));
$attrsDatePickerLimitByStartDate = array('id' => 'datepicker_start', 'name' => '_changed_from', 'size' => 10, 'maxlength' => 10, 'disabled' => 'disabled');
echo html_e('p', array(), _('Set dates')._(': ').html_e('br').
			_('From')._(': ').html_e('input', $attrsInputLimitByStartDate).html_e('input', $attrsDatePickerLimitByStartDate));
echo html_ac(html_ap() - 2);
echo html_e('p', array(), html_e('input', array('type' => 'submit', 'value' => _('Download CSV file'))));
echo $HTML->closeForm();

$ath->footer();
