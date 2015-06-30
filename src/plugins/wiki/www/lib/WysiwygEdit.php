<?php

/**
 * Baseclass for WysiwygEdit/*
 *
 * ENABLE_WYSIWYG - Support for some WYSIWYG_BACKEND Editors:
 *   tinymce, htmlarea3, CKeditor, spaw, htmlarea2, Wikiwyg
 * Not yet enabled as default, since we cannot convert HTML to Wiki Markup yet.
 * (See HtmlParser.php for the ongoing efforts)
 * We might use a PageType=html, which is contra wiki, but some people
 * might prefer HTML markup.
 *
 * TODO: Change from ENABLE_WYSIWYG constant to user preference variable
 *       (checkbox setting or edit click as in gmail),
 *       when HtmlParser is finished.
 * Based upon htmlarea3.php and tinymce.php
 *
 * WARNING! Probably incompatible with ENABLE_XHTML_XML (TestMe)
 *
 * @package WysiwygEdit
 * @author Reini Urban
 */

require_once 'lib/InlineParser.php';

abstract class WysiwygEdit
{

    function WysiwygEdit()
    {
        $this->_transformer_tags = false;
    }

    abstract function Head($name = 'edit[content]');

    // to be called after </textarea>
    abstract function Textarea($textarea, $wikitext, $name = 'edit[content]');

    /**
     * Handler to convert the Wiki Markup to HTML before editing.
     * This will be converted back by WysiwygEdit_ConvertAfter if required.
     *  *text* => '<b>text<b>'
     *
     * @param $text
     * @return string
     */
    function ConvertBefore($text)
    {
        /**
         * @var WikiRequest $request
         */
        global $request;

        require_once 'lib/BlockParser.php';
        $xml = TransformText($text, $request->getArg('pagename'));
        return $xml->AsXML();
    }

    /**
     * FIXME: Handler to convert the HTML formatting back to wiki formatting.
     * Derived from InlineParser, but returning wiki text instead of HtmlElement objects.
     * '<b>text<b>' => '<span style="font-weight: bold">text</span>' => '*text*'
     *
     * TODO: Switch over to HtmlParser
     *
     * @param $text
     * @return string
     */
    function ConvertAfter($text)
    {
        static $trfm;
        if (empty($trfm)) {
            $trfm = new HtmlTransformer($this->_transformer_tags);
        }
        $markup = $trfm->parse($text); // version 2.0
        return $markup;
    }
}

// re-use these classes for the regexp's.
// just output strings instead of XmlObjects
class Markup_html_br extends Markup_linebreak
{
    function markup($match)
    {
        return $match;
    }
}

class Markup_html_simple_tag extends Markup_html_emphasis
{
    function markup($match, $body)
    {
        $tag = substr($match, 1, -1);
        switch ($tag) {
            case 'b':
            case 'strong':
                return "*" . $body . "*";
            case 'big':
                return "<big>" . $body . "</big>";
            case 'i':
            case 'em':
                return "_" . $body . "_";
        }
        return '';
    }
}

class Markup_html_p extends BalancedMarkup
{
    function getStartRegexp()
    {
        return "<(?:p|P)( class=\".*\")?>";
    }

    function getEndRegexp($match)
    {
        return "<\\/" . substr($match, 1);
    }

    function markup($match, $body)
    {
        return $body . "\n";
    }
}

//'<span style="font-weight: bold">text</span>' => '*text*'
class Markup_html_spanbold extends BalancedMarkup
{
    function getStartRegexp()
    {
        return "<(?:span|SPAN) style=\"font-weight: bold\">";
    }

    function getEndRegexp($match)
    {
        return "<\\/" . substr($match, 1);
    }

    function markup($match, $body)
    {
        //Todo: convert style formatting to simplier nested <b><i> tags
        return "*" . $body . "*";
    }
}

class HtmlTransformer extends InlineTransformer
{
    function __construct($tags = false)
    {
        if (!$tags)
            $tags = array('escape', 'html_br', 'html_spanbold', 'html_simple_tag', 'html_p',);
        /*
         'html_a','html_span','html_div',
         'html_table','html_hr','html_pre',
         'html_blockquote',
         'html_indent','html_ol','html_li','html_ul','html_img'
        */
        parent::__construct($tags);
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
