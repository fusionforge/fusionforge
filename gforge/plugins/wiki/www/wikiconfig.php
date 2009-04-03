<?php

/*
 * Copyright (C) 2008 Alcatel-Lucent
 *
 * This file is part of PhpWiki.
 *
 * PhpWiki is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * PhpWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The Configuration File ("Contribution") has not been tested and/or
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

define('GFORGE', true);

define('PATH_INFO_PREFIX', '/'.$group_name . '/');
define('USE_PATH_INFO', true);

define('WIKI_NAME', $group_name);

define('UPLOAD_FILE_PATH', '/opt/groups/'.WIKI_NAME.'/www/uploads/');
define('UPLOAD_DATA_PATH', '/www/'.WIKI_NAME.'/uploads/');
 
// Do not use a directory per user but only one (per project)
define('UPLOAD_USERDIR', false);

// GForge is UTF-8, so use the same.
define('CHARSET', 'UTF-8');

// Disable access log (already in apache & gforge).
define('ACCESS_LOG_SQL', 0);

// define('DEBUG', true);
// define('_DEBUG_LOGIN', true);

// Disable VACUUM (they are performed every night)
define('DATABASE_OPTIMISE_FREQUENCY', 0);

// It is not used by it is required by libs.
define('ADMIN_USER', 'Project Administrators');
define('ADMIN_PASSWD', 'xxx');
        
// Allow ".jpeg" as extension
define('INLINE_IMAGES', 'png|jpg|jpeg|gif');

// Allow template with MediaWiki syntax
define('ENABLE_MARKUP_TEMPLATE', true);

// Allow tables with MediaWiki syntax
define('ENABLE_MARKUP_MEDIAWIKI_TABLE', true);

// Allow parsing of headers for CreateToc
define('TOC_FULL_SYNTAX', true);

// Allow <div> and <span> in wiki code
define('ENABLE_MARKUP_DIVSPAN', true);

// Disable ENABLE_ACDROPDOWN, it creates a <style> in the <body> (illegal)
define('ENABLE_ACDROPDOWN', false);

define('TOOLBAR_PAGELINK_PULLDOWN', false);
define('TOOLBAR_TEMPLATE_PULLDOWN', false);
define('TOOLBAR_IMAGE_PULLDOWN', true);

// Disable WYSIWYG
define('ENABLE_WYSIWYG', false);

// Which backend? Might need to be seperately installed. See lib/WysiwygEdit/
// Recommended is only Wikiwyg.
//
//  Wikiwyg     http://openjsan.org/doc/i/in/ingy/Wikiwyg/
//  tinymce     http://tinymce.moxiecode.com/
//  FCKeditor   http://fckeditor.net/
//  spaw        http://sourceforge.net/projects/spaw
//  htmlarea3
//  htmlarea2
define('WYSIWYG_BACKEND', 'tinymce');
//
// Store all WYSIWYG pages as HTML? Will loose most link and plugin options.
// Not recommended, but presented here to test several WYSIWYG backends.
define('WYSIWYG_DEFAULT_PAGETYPE_HTML', false);

// Disable public pages
define('ENABLE_PAGE_PUBLIC', false);

// Let all revisions be stored. Default since 1.3.11
define('MAJOR_MIN_KEEP', 2147483647); 
define('MINOR_MIN_KEEP', 2147483647);
define('MAJOR_MAX_AGE', 2147483647);
define('MAJOR_KEEP', 2147483647);
define('MINOR_MAX_AGE', 2147483647);
define('MINOR_KEEP', 2147483647);
define('AUTHOR_MAX_AGE', 2147483647);
define('AUTHOR_KEEP', 2147483647);
define('AUTHOR_MIN_AGE', 2147483647);
define('AUTHOR_MAX_KEEP', 2147483647);

//
// Define access rights for the wiki.
//

// Allow anonymous user to view the pages.
define('ALLOW_ANON_USER', true);

// Do not allow anon users to edit pages
define('ALLOW_ANON_EDIT', false);

// Do not allow fake user
define('ALLOW_BOGO_LOGIN', false);
define('ALLOW_USER_PASSWORDS', true);

// A dedicated auth has been created to get auth from GForge
$USER_AUTH_ORDER = array("GForge");
define('USER_AUTH_ORDER', 'GForge');
define('USER_AUTH_POLICY', 'strict');

define('EXTERNAL_LINK_TARGET', '_top');

// Override the default configuration for CONSTANTS before index.php
$LANG='en'; $LC_ALL='en_US';

// For Gforge, we create some specific pages, located in the theme
define('WIKI_PGSRC', 'themes/gforge/pgsrc/');

// We use a local interwiki map file
define('INTERWIKI_MAP_FILE', 'themes/gforge/interwiki.map');

define('DEFAULT_WIKI_PAGES', "");

define('ERROR_REPORTING', E_ERROR);

define('DBAUTH_AUTH_CHECK', "SELECT IF(passwd='\$password',1,0) as ok FROM plugin_wiki_pref WHERE userid='\$userid'");
define('DBAUTH_AUTH_USER_EXISTS', "SELECT userid FROM plugin_wiki_pref WHERE userid='\$userid'");
define('DBAUTH_AUTH_CREATE', "INSERT INTO plugin_wiki_pref (passwd,userid) VALUES ('\$password','\$userid')");
define('DBAUTH_PREF_SELECT', "SELECT prefs FROM plugin_wiki_pref WHERE userid='\$userid'");
define('DBAUTH_PREF_UPDATE', "UPDATE plugin_wiki_pref SET prefs='\$pref_blob' WHERE userid='\$userid'");
define('DBAUTH_PREF_INSERT', "INSERT INTO plugin_wiki_pref (prefs,userid) VALUES ('\$pref_blob','\$userid')");
define('DBAUTH_IS_MEMBER', "SELECT userid FROM plugin_wiki_pref WHERE userid='\$userid' AND groupname='\$groupname'");
define('DBAUTH_GROUP_MEMBERS', "SELECT userid FROM plugin_wiki_pref WHERE groupname='\$groupname'");
define('DBAUTH_USER_GROUPS', "SELECT groupname FROM plugin_wiki_pref WHERE userid='\$userid'");

define('USE_DB_SESSION', true);

if (isset($sys_use_selenium) && $sys_use_selenium) {
	// Temporary disabled for selenium based tests.
	define('ENABLE_EDIT_TOOLBAR', false);
}

// If the user is logged in, let the Wiki know
if (session_loggedin()){
    // let php do it's session stuff too!
    //ini_set('session.save_handler', 'files');
    // session_start();
    $user = session_get_user();

    if ($user && is_object($user) && !$user->isError() && $user->isActive()) {
        $user_name = $user->getRealName();
        $_SESSION['user_id'] = $user_name;
        $_SERVER['PHP_AUTH_USER'] = $user_name;
        $HTTP_SERVER_VARS['PHP_AUTH_USER'] = $user_name;
    }
} else {
    // clear out the globals, just in case... 
}

// Load the default configuration.
include dirname(__FILE__).'/index.php';

// Override the default configuration for VARIABLES after index.php:
// E.g. Use another DB:
$DBParams['dbtype'] = 'SQL';
$DBParams['dsn']    = 'pgsql://' . $sys_dbuser . ':' . 
    $sys_dbpasswd . '@' . $sys_dbhost .'/' . $sys_dbname;

$DBParams['prefix'] = "plugin_wiki_";

// Start the wiki
include dirname(__FILE__).'/lib/main.php';
?>
