<?php
/**
 * MonoBook nouveau.
 *
 * Translated from gwicke's previous TAL template version to remove
 * dependency on PHPTAL.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Skins
 */

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @ingroup Skins
 */
class SkinMonoBookFusionForge125 extends SkinTemplate {
	/** Using MonoBook. */
	public $skinname = 'monobookfusionforge125';
	public $stylename = 'MonoBookFusionforge125';
	public $template = 'MonoBookFusionForge125Template';

	/**
	 * @param OutputPage $out
	 */
	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );

		/* add FusionForge styles */
		foreach ($GLOBALS['HTML']->stylesheets as $sheet) {
			$out->addStyle($sheet['css'], $sheet['media']);
		}

		$out->addModuleStyles( array(
			'mediawiki.skinning.interface',
			'mediawiki.skinning.content.externallinks',
			'skins.monobookfusionforge125.styles'
		) );

		// TODO: Migrate all of these
		$out->addStyle( $this->stylename . '/IE60Fixes.css', 'screen', 'IE 6' );
		$out->addStyle( $this->stylename . '/IE70Fixes.css', 'screen', 'IE 7' );
	}

	function setupTemplate( $classname, $repository = false, $cache_dir = false ) {
		$tc = new $classname();
		$tc->params = array();
		if ($tc->project = $project = group_get_object_by_name($GLOBALS['fusionforgeproject'])) {
			$tc->params['group'] = $GLOBALS['group_id'] = $project->getID();
			$tc->params['toptab'] = 'mediawiki';
		}
		return $tc;
	}
}
