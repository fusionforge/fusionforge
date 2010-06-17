<?php // -*-php-*-
rcs_id('$Id: Comment.php 6185 2008-08-22 11:40:14Z vargenau $');
/**
 * A WikiPlugin for putting comments in WikiPages
 *
 * Usage:
 * <?plugin Comment
 *
 * !!! My Secret Text
 *
 * This is some WikiText that won't show up on the page.
 *
 * ?>
 */

class WikiPlugin_Comment
extends WikiPlugin
{
    // Five required functions in a WikiPlugin.

    function getName() {
        return _("Comment");
    }

    function getDescription() {
        return _("Embed hidden comments in WikiPages.");

    }

    function getVersion() {
        return preg_replace("/[Revision: $]/", '',
                            "\$Revision: 6185 $");
    }

    // No arguments here.
    function getDefaultArguments() {
        return array();
    }

    function run($dbi, $argstr, &$request, $basepage) {
    }

    // function handle_plugin_args_cruft(&$argstr, &$args) {
    // }

};

// $Log: not supported by cvs2svn $
// Revision 1.1  2003/01/28 17:57:15  carstenklapp
// Martin Geisler's clever Comment plugin.
//

// For emacs users
// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
