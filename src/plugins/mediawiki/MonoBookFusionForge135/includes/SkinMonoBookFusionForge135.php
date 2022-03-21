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
class SkinMonoBookFusionForge135 extends SkinTemplate {
	/** Using MonoBook. */
	public $skinname = 'monobookfusionforge135';
	public $stylename = 'MonoBookFusionForge135';
	public $template = 'MonoBookFusionForge135Template';

	/**
	 * @param OutputPage $out
	 */
	public function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );

		if ( $out->getUser()->getOption( 'monobookfusionforge135-responsive' ) ) {
			$out->addMeta( 'viewport',
				'width=device-width, initial-scale=1.0, ' .
				'user-scalable=yes, minimum-scale=0.25, maximum-scale=5.0'
			);
			$styleModule = 'skins.monobookfusionforge135.responsive';
			$out->addModules( [
				'skins.monobookfusionforge135.mobile'
			] );

			if ( ExtensionRegistry::getInstance()->isLoaded( 'Echo' ) && $out->getUser()->isLoggedIn() ) {
				$out->addModules( [ 'skins.monobookfusionforge135.mobile.echohack' ] );
			}
			if ( ExtensionRegistry::getInstance()->isLoaded( 'UniversalLanguageSelector' ) ) {
				$out->addModules( [ 'skins.monobookfusionforge135.mobile.uls' ] );
			}
		} else {
			$styleModule = 'skins.monobookfusionforge135.styles';
		}

		$out->addModuleStyles( [
			'mediawiki.skinning.content.externallinks',
			$styleModule
		] );

		// Force desktop styles in IE 8-; no support for @media widths
		// FIXME: Remove conditional comment dependency.
		$out->addStyle( $this->stylename . '/resources/screen-desktop.css', 'screen', 'lt IE 9' );
	}

	/**
	 * @param User $user
	 * @param array &$preferences
	 */
	public static function onGetPreferences( User $user, array &$preferences ) {
		$preferences['monobookfusionforge135-responsive'] = [
			'type' => 'toggle',
			'label-message' => 'monobookfusionforge135-responsive-label',
			'section' => 'rendering/skin/skin-prefs',
			// Only show this section when the Monobook skin is checked. The JavaScript client also uses
			// this state to determine whether to show or hide the whole section.
			'hide-if' => [ '!==', 'wpskin', 'monobookfusionforge135' ],
		];
	}

	/**
	 * Handler for ResourceLoaderRegisterModules hook
	 * Check if extensions are loaded
	 *
	 * @param ResourceLoader $resourceLoader
	 */
	public static function registerMobileExtensionStyles( ResourceLoader $resourceLoader ) {
		if ( ExtensionRegistry::getInstance()->isLoaded( 'Echo' ) ) {
			$resourceLoader->register( 'skins.monobookfusionforge135.mobile.echohack', [
				'localBasePath' => __DIR__ . '/..',
				'remoteSkinPath' => 'MonoBookFusionForge135',

				'targets' => [ 'desktop', 'mobile' ],
				'scripts' => [ 'resources/mobile-echo.js' ],
				'styles' => [ 'resources/mobile-echo.less' => [
					'media' => 'screen and (max-width: 550px)'
				] ],
				'dependencies' => [ 'oojs-ui.styles.icons-alerts', 'mediawiki.util' ],
				'messages' => [ 'monobookfusionforge135-notifications-link', 'monobookfusionforge135-notifications-link-none' ]
			] );
		}

		if ( ExtensionRegistry::getInstance()->isLoaded( 'UniversalLanguageSelector' ) ) {
			$resourceLoader->register( 'skins.monobookfusionforge135.mobile.uls', [
				'localBasePath' => __DIR__ . '/..',
				'remoteSkinPath' => 'MonoBookFusionForge135',

				'targets' => [ 'desktop' ],
				'scripts' => [ 'resources/mobile-uls.js' ],
				'dependencies' => [ 'ext.uls.interface' ],
			] );
		}
	}
}
