<?php
// $Id: htmlarea2.php 7956 2011-03-03 17:08:31Z vargenau $
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

require_once("lib/WysiwygEdit.php");

class WysiwygEdit_htmlarea2 extends WysiwygEdit {

    function Head($name='edit[content]') {
        return JavaScript("
_editor_url = \"".DATA_PATH."/themes/default/htmlarea2/\";
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
    function Textarea($textarea,$wikitext,$name='edit[content]') {
        $out = HTML($textarea);
        $out->pushContent(JavaScript("editor_generate('".$name."');",
                                     array('version' => 'JavaScript1.2',
                                           'defer' => 1)));
        return $out;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
