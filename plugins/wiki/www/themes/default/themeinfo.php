<?php
// Avoid direct call to this file.
// PHPWIKI_VERSION is defined in lib/prepend.php
if (!defined('PHPWIKI_VERSION')) {
    header("Location: /");
    exit;
}

// $Id: themeinfo.php 7968 2011-03-07 13:39:47Z vargenau $

/*
 * This file defines the default appearance ("theme") of PhpWiki.
 */

require_once('lib/WikiTheme.php');

$WikiTheme = new WikiTheme('default');

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
