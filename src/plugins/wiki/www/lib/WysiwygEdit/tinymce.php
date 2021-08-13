<?php
/**
 * Copyright © 2005 Reini Urban
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
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 */

/**
 * Multiple browser support, currently Mozilla (PC, Mac and Linux),
 * MSIE (PC) and FireFox (PC, Mac and Linux) and some limited Safari support.
 *
 * Download: http://tinymce.moxiecode.com/
 * Suggested installation of the jscripts subdirectory
 *   tinymce/jscripts/tiny_mce/ into themes/default/tiny_mce/
 *
 * WARNING! Probably incompatible with ENABLE_XHTML_XML
 *
 * @package WysiwygEdit
 * @author Reini Urban
 */

require_once 'lib/WysiwygEdit.php';

class WysiwygEdit_tinymce extends WysiwygEdit
{

    function __construct()
    {
        $this->_transformer_tags = false;
        $this->BasePath = DATA_PATH . '/themes/default/tiny_mce/';
        $this->_htmltextid = "edit-content";
        $this->_wikitextid = "editareawiki";
    }

    function Head($name = 'edit[content]')
    {
        global $LANG, $WikiTheme;
        $WikiTheme->addMoreHeaders
        (JavaScript('', array('src' => $this->BasePath . 'tiny_mce.js',
            'language' => 'JavaScript')));
        return JavaScript("
tinyMCE.init({
    mode    : 'exact',
    elements: '$name',
        theme   : 'advanced',
        language: \"$LANG\",
        ask     : false,
    theme_advanced_toolbar_location : \"top\",
    theme_advanced_toolbar_align : \"left\",
    theme_advanced_path_location : \"bottom\",
    theme_advanced_buttons1 : \"bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,undo,redo,link,unlink\",
    theme_advanced_buttons2 : \"\",
    theme_advanced_buttons3 : \"\",
});");
        /*
        plugins : \"table,contextmenu,paste,searchreplace,iespell,insertdatetime\",
    extended_valid_elements : \"a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]\"
});
        */
    }

    // to be called after </textarea>
    // name ignored
    function Textarea($textarea, $wikitext, $name = 'edit[content]')
    {
        $out = HTML($textarea,
            HTML::div(array("id" => $this->_wikitextid,
                    'style' => 'display:none'),
                $wikitext), "\n");
        //TODO: maybe some more custom links
        return $out;
    }
}
