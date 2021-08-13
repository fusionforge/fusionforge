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
 * requires installation into themes/default/htmlarea2/
 * Output the javascript function to check for MS Internet Explorer >= 5.5 on Windows
 * and call the real js script then, else just a nil func.
 *   version 2: only for MSIE 5.5 and better
 *   version 3: also Mozilla >= 1.3
 *
 * @package WysiwygEdit
 * @author Reini Urban
 */

require_once 'lib/WysiwygEdit.php';

class WysiwygEdit_htmlarea2 extends WysiwygEdit
{

    function Head($name = 'edit[content]')
    {
        return JavaScript("
_editor_url = \"" . DATA_PATH . "/themes/default/htmlarea2/\";
var win_ie_ver = parseFloat(navigator.appVersion.split(\"MSIE\")[1]);
if (navigator.userAgent.indexOf('Mac')        >= 0) { win_ie_ver = 0; }
if (navigator.userAgent.indexOf('Windows CE') >= 0) { win_ie_ver = 0; }
if (navigator.userAgent.indexOf('Opera')      >= 0) { win_ie_ver = 0; }
if (win_ie_ver >= 5.5) {
  document.write('<scr' + 'ipt src=\"' +_editor_url+ 'editor.js\"');
  document.write(' language=\"Javascript1.2\"></scr' + 'ipt>');
} else {
  document.write('<scr'+'ipt>function editor_generate() { return false; }</scr'+'ipt>');
}
 ",
            array('version' => 'JavaScript1.2',
                'type' => 'text/javascript'));
    }

    // to be called after </textarea>
    // version 2
    function Textarea($textarea, $wikitext, $name = 'edit[content]')
    {
        $out = HTML($textarea);
        $out->pushContent(JavaScript("editor_generate('" . $name . "');",
            array('version' => 'JavaScript1.2',
                'defer' => 1)));
        return $out;
    }
}
