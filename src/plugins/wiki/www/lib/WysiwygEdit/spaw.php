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
 * Download: http://sourceforge.net/projects/spaw
 * requires installation of spaw as lib/spaw
 * modify lib/spaw/config/spaw_control.config.php to your needs.
 *
 * @package WysiwygEdit
 * @author Reini Urban
 */

require_once 'lib/WysiwygEdit.php';

class WysiwygEdit_spaw extends WysiwygEdit
{

    function Head($name = 'edit[content]')
    {
        $basepath = DATA_PATH . '/lib/spaw/';
        $spaw_root = PHPWIKI_DIR . "/lib/spaw/";
        $spaw_base_url = "$basepath";
        $spaw_dir = "$basepath";
        $this->spaw_root =& $spaw_root;
        include_once($spaw_root . "spaw_control.class.php");
    }

    function Textarea($textarea, $wikitext, $name = 'edit[content]')
    {
        // global $LANG, $WikiTheme;
        $id = "spaw_editor";
        /*SPAW_Wysiwyg(
              $control_name='spaweditor', // control's name
              $value='',                  // initial value
              $lang='',                   // language
              $mode = '',                 // toolbar mode
              $theme='',                  // theme (skin)
              $width='100%',              // width
              $height='300px',            // height
              $css_stylesheet='',         // css stylesheet file for content
        */
        $this->SPAW = new SPAW_Wysiwyg($id, $textarea->_content);
        $textarea->SetAttr('id', $name);
        $this->SPAW->show();
        $out = HTML::div(array("id" => $id, 'style' => 'display:none'),
            $wikitext);
        return $out;
    }
}
