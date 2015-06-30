<?php

/**
 * CKeditor is compatible with most internet browsers
 *
 * Download: http://ckeditor.com/
 * Suggested installation into themes/default/CKeditor/
 * or the default /CKeditor/. See $this->BasePath below.
 *
 * @package WysiwygEdit
 * @author Reini Urban
 */

require_once 'lib/WysiwygEdit.php';

class WysiwygEdit_CKeditor extends WysiwygEdit
{

    function __construct()
    {
        global $LANG;
        $this->_transformer_tags = false;
        $this->BasePath = DATA_PATH . '/themes/default/CKeditor/';
        $this->_htmltextid = "edit-content"; // CKEditor1;
        $this->_wikitextid = "editareawiki";
        $this->_jsdefault = "
oCKeditor.BasePath = '$this->BasePath';
oCKeditor.Height = 300;
// oCKeditor.ToolbarSet = 'Basic' ;
oCKeditor.Config.DefaultLanguage = '$LANG';
oCKeditor.Config.LinkBrowserURL  = oCKeditor.BasePath + 'editor/filemanager/browser/default/browser.html?Connector=connectors/php/connector.php';
oCKeditor.Config.ImageBrowserURL = oCKeditor.BasePath + 'editor/filemanager/browser/default/browser.html?Type=Image&Connector=connectors/php/connector.php';
";
        if (!empty($_REQUEST['start_debug']))
            $this->_jsdefault = "\noCKeditor.Config.Debug = true;";
    }

    function Head($name = 'edit[content]')
    {
        global $WikiTheme;
        $WikiTheme->addMoreHeaders
        (Javascript('', array('src' => $this->BasePath . 'ckeditor.js',
            'language' => 'JavaScript')));
        return JavaScript("
window.onload = function()
{
var oCKeditor = new CKeditor( '$this->_htmltextid' ) ;"
            . $this->_jsdefault . "
// force textarea in favor of iFrame?
// oCKeditor._IsCompatibleBrowser = function() { return false; }
oCKeditor.ReplaceTextarea();
}");
    }

    function Textarea($textarea, $wikitext, $name = 'edit[content]')
    {
        return $this->Textarea_Replace($textarea, $wikitext, $name);
    }

    /* either iframe or textarea */
    function Textarea_Create($textarea, $wikitext, $name = 'edit[content]')
    {
        $htmltextid = $name;
        $out = HTML(
            JavaScript("
var oCKeditor = new CKeditor( '$htmltextid' ) ;
oCKeditor.Value = '" . $textarea->_content[0]->asXML() . "';"
                . $this->_jsdefault . "
oCKeditor.Create();"),
            HTML::div(array("id" => $this->_wikitextid,
                    'style' => 'display:none'),
                $wikitext),
            "\n");
        return $out;
    }

    /* textarea only */
    function Textarea_Replace($textarea, $wikitext, $name = 'edit[content]')
    {
        $htmltextid = $this->_htmltextid;
        $textarea->SetAttr('id', $htmltextid);
        $out = HTML($textarea,
            HTML::div(array("id" => $this->_wikitextid,
                    'style' => 'display:none'),
                $wikitext),
            "\n");
        return $out;
    }

    /* via the PHP object */
    function Textarea_PHP($textarea, $wikitext, $name = 'edit[content]')
    {
        global $LANG;
        $this->FilePath = realpath(PHPWIKI_DIR . '/themes/default/CKeditor') . "/";

        $htmltextid = "edit-content";

        include_once($this->FilePath . 'ckeditor.php');
        $this->oCKeditor = new CKeditor($htmltextid);
        $this->oCKeditor->BasePath = $this->BasePath;
        $this->oCKeditor->Value = $textarea->_content[0]->asXML();

        $this->oCKeditor->Config['AutoDetectLanguage'] = true;
        $this->oCKeditor->Config['DefaultLanguage'] = $LANG;
        $this->oCKeditor->Create();

        return HTML::div(array("id" => $this->_wikitextid,
                'style' => 'display:none'),
            $wikitext);
    }

}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
