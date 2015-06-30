<?php
/*
 * Copyright 1999-2008 $ThePhpWikiProgrammingTeam
 * Copyright 2008-2009 Marc-Etienne Vargenau, Alcatel-Lucent
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
 */

/*
  Standard functions for Wiki functionality
    WikiURL ($pagename, $args, $get_abs_url)
    AbsoluteURL ($url)
    IconForLink ($protocol_or_url)
    PossiblyGlueIconToText($proto_or_url, $text)
    IsSafeURL($url)
    LinkURL ($url, $linktext)
    LinkImage ($url, $alt)
    ImgObject ($img, $url)

    SplitQueryArgs ($query_args)
    LinkPhpwikiURL ($url, $text, $basepage)
    MangleXmlIdentifier($str)

    class Stack { push($item), pop(), cnt(), top() }
    class Alert { show() }
    class WikiPageName {getParent(),isValid(),getWarnings() }

    expand_tabs($str, $tab_width = 8)
    SplitPagename ($page)
    NoSuchRevision ($request, $page, $version)
    TimezoneOffset ($time, $no_colon)
    Iso8601DateTime ($time)
    Rfc2822DateTime ($time)
    ParseRfc1123DateTime ($timestr)
    CTime ($time)
    ByteFormatter ($bytes = 0, $longformat = false)
    __printf ($fmt)
    __sprintf ($fmt)
    __vsprintf ($fmt, $args)

    file_mtime ($filename)
    sort_file_mtime ($a, $b)
    class fileSet {fileSet($directory, $filepattern = false),
                   getFiles($exclude='', $sortby='', $limit='') }
    class imageSet extends fileSet
    class imageOrVideoSet extends fileSet

    glob_to_pcre ($glob)
    glob_match ($glob, $against, $case_sensitive = true)
    explodePageList ($input, $perm = false)
    function_usable ($function_name)
    wikihash ($x)
    count_all ($arg)
    isSubPage ($pagename)
    subPageSlice ($pagename, $pos)
    isActionPage ($filename)

    phpwiki_version ()
    isWikiWord ($word)
    url_get_contents ($uri)
    GenerateId ($name)
    firstNWordsOfContent ($n, $content)
    extractSection ($section, $content, $page, $quiet = false, $sectionhead = false)
    isExternalReferrer()

    string_starts_with($string, $prefix)
    string_ends_with($string, $suffix)
    array_remove($arr,$value)
    longer_timeout($secs=30)
    printSimpleTrace($bt)
    binary_search($needle, $haystack)
    is_localhost()
    javascript_quote_string($s)
    isSerialized($s)
    is_whole_number($var)
    parse_attributes($line)
    is_image ($filename)
    is_video ($filename)

  function: linkExistingWikiWord($wikiword, $linktext, $version)
  moved to: lib/WikiTheme.php
*/
if (defined('_PHPWIKI_STDLIB_LOADED')) return;
else define('_PHPWIKI_STDLIB_LOADED', true);

if (!defined('MAX_PAGENAME_LENGTH')) {
    define('MAX_PAGENAME_LENGTH', 100);
}

/**
 * Convert string to a valid XML identifier.
 *
 * XML 1.0 identifiers are of the form: [A-Za-z][A-Za-z0-9:_.-]*
 *
 * We would like to have, e.g. named anchors within wiki pages
 * names like "Table of Contents" --- clearly not a valid XML
 * fragment identifier.
 *
 * This function implements a one-to-one map from {any string}
 * to {valid XML identifiers}.
 *
 * It does this by
 * converting all bytes not in [A-Za-z0-9:_-],
 * and any leading byte not in [A-Za-z] to '.bb',
 * where 'bb' is the hexadecimal representation of the
 * character.
 *
 * As a special case, the empty string is converted to 'empty.'
 *
 * @param string $str
 * @return string
 */
function MangleXmlIdentifier($str)
{
    if (!$str) {
        return 'empty.';
    }
    return preg_replace_callback(
        '/[^-_:A-Za-z0-9]|(?<=^)[^A-Za-z]/',
        function ($m) {
            return '.' . sprintf('%02x', ord('$m'));
        },
        $str);
}

/**
 * Returns a name for the WIKI_ID cookie that should be unique on the host.
 * But for it to be unique you must have set a unique WIKI_NAME in your
 * configuration file.
 * @return string The name of the WIKI_ID cookie to use for this wiki.
 */
function getCookieName()
{
    return preg_replace("/[^\d\w]/", "_", WIKI_NAME) . "_WIKI_ID";
}

/**
 * Generates a valid URL for a given Wiki pagename.
 * @param mixed $pagename If a string this will be the name of the Wiki page to link to.
 *               If a WikiDB_Page object function will extract the name to link to.
 *               If a WikiDB_PageRevision object function will extract the name to link to.
 * @param array $args
 * @param bool $get_abs_url Default value is false.
 * @return string The absolute URL to the page passed as $pagename.
 */
function WikiURL($pagename, $args = array(), $get_abs_url = false)
{
    global $request, $WikiTheme;
    $anchor = false;

    if (is_object($pagename)) {
        if (is_a($pagename, 'WikiDB_Page')) {
            $pagename = $pagename->getName();
        } elseif (is_a($pagename, 'WikiDB_PageRevision')) {
            $page = $pagename->getPage();
            $args['version'] = $pagename->getVersion();
            $pagename = $page->getName();
        } elseif (is_a($pagename, 'WikiPageName')) {
            $anchor = $pagename->anchor;
            $pagename = $pagename->name;
        } else { // php5
            $anchor = $pagename->anchor;
            $pagename = $pagename->name;
        }
    }
    if (!$get_abs_url and DEBUG and $request->getArg('start_debug')) {
        if (!$args)
            $args = 'start_debug=' . $request->getArg('start_debug');
        elseif (is_array($args))
            $args['start_debug'] = $request->getArg('start_debug'); else
            $args .= '&start_debug=' . $request->getArg('start_debug');
    }
    if (is_array($args)) {
        $enc_args = array();
        foreach ($args as $key => $val) {
            // avoid default args
            if (USE_PATH_INFO and $key == 'pagename')
                ;
            elseif ($key == 'action' and $val == 'browse')
                ; elseif (!is_array($val)) // ugly hack for getURLtoSelf() which also takes POST vars
                $enc_args[] = urlencode($key) . '=' . urlencode($val);
        }
        $args = join('&', $enc_args);
    }

    if (USE_PATH_INFO or !empty($WikiTheme->HTML_DUMP_SUFFIX)) {
        $url = $get_abs_url ? (SERVER_URL . VIRTUAL_PATH . "/") : "";
        $base = preg_replace('/%2f/i', '/', rawurlencode($pagename));
        $url .= $base;
        if (!empty($WikiTheme->HTML_DUMP_SUFFIX)) {
            if (!empty($WikiTheme->VALID_LINKS) and $request->getArg('action') == 'pdf') {
                if (!in_array($pagename, $WikiTheme->VALID_LINKS))
                    $url = '';
                else
                    $url = $base . $WikiTheme->HTML_DUMP_SUFFIX;
            } else {
                $url .= $WikiTheme->HTML_DUMP_SUFFIX;
                if ($args)
                    $url .= "?$args";
            }
        } else {
            if ($args)
                $url .= "?$args";
        }
    } else {
        $url = $get_abs_url ? SERVER_URL . SCRIPT_NAME : basename(SCRIPT_NAME);
        $url .= "?pagename=" . rawurlencode($pagename);
        if ($args)
            $url .= "&$args";
    }
    if ($anchor)
        $url .= "#" . MangleXmlIdentifier($anchor);
    return $url;
}

/** Convert relative URL to absolute URL.
 *
 * This converts a relative URL to one of PhpWiki's support files
 * to an absolute one.
 *
 * @param string $url
 * @return string Absolute URL
 */
function AbsoluteURL($url)
{
    if (preg_match('/^https?:/', $url))
        return $url;
    if ($url[0] != '/') {
        $base = USE_PATH_INFO ? VIRTUAL_PATH : dirname(SCRIPT_NAME);
        while ($base != '/' and substr($url, 0, 3) == "../") {
            $url = substr($url, 3);
            $base = dirname($base);
        }
        if ($base != '/')
            $base .= '/';
        $url = $base . $url;
    }
    return SERVER_URL . $url;
}

/**
 * Generates icon in front of links.
 *
 * @param string $protocol_or_url URL or protocol to determine which icon to use.
 *
 * @return HtmlElement HtmlElement object that contains data to create img link to
 * icon for use with url or protocol passed to the function. False if no img to be
 * displayed.
 */
function IconForLink($protocol_or_url)
{
    global $WikiTheme;
/*
    if (0 and $filename_suffix == false) {
        // display apache style icon for file type instead of protocol icon
        // - archive: unix:gz,bz2,tgz,tar,z; mac:dmg,dmgz,bin,img,cpt,sit; pc:zip;
        // - document: html, htm, text, txt, rtf, pdf, doc
        // - non-inlined image: jpg,jpeg,png,gif,tiff,tif,swf,pict,psd,eps,ps
        // - audio: mp3,mp2,aiff,aif,au
        // - multimedia: mpeg,mpg,mov,qt
    } else {
*/
        list ($proto) = explode(':', $protocol_or_url, 2);
        $src = $WikiTheme->getLinkIconURL($proto);
        if ($src)
            return HTML::img(array('src' => $src, 'alt' => "", 'class' => 'linkicon'));
        else
            return false;
/*    } */
}

/**
 * Glue icon in front of or after text.
 * Pref: 'noLinkIcons'      - ignore icon if set
 * WikiTheme: 'LinkIcons'   - 'yes'   at front
 *                          - 'no'    display no icon
 *                          - 'front' display at left
 *                          - 'after' display at right
 *
 * @param string $proto_or_url Protocol or URL.  Used to determine the
 * proper icon.
 * @param string $text The text.
 * @return XmlContent.
 */
function PossiblyGlueIconToText($proto_or_url, $text)
{
    global $request, $WikiTheme;
    if ($request->getPref('noLinkIcons'))
        return $text;
    $icon = IconForLink($proto_or_url);
    if (!$icon)
        return $text;
    if ($where = $WikiTheme->getLinkIconAttr()) {
        if ($where == 'no') return $text;
        if ($where != 'after') $where = 'front';
    } else {
        $where = 'front';
    }
    if ($where == 'after') {
        // span the icon only to the last word (tie them together),
        // to let the previous words wrap on line breaks.
        if (!is_object($text)) {
            preg_match('/^(\s*\S*)(\s*)$/', $text, $m);
            list (, $prefix, $last_word) = $m;
        } else {
            $last_word = $text;
            $prefix = false;
        }
        $text = HTML::span(array('style' => 'white-space: nowrap'),
            $last_word, HTML::raw('&nbsp;'), $icon);
        if ($prefix)
            $text = HTML($prefix, $text);
        return $text;
    }
    // span the icon only to the first word (tie them together),
    // to let the next words wrap on line breaks
    if (!is_object($text)) {
        preg_match('/^\s*(\S*)(.*?)\s*$/', $text, $m);
        list (, $first_word, $tail) = $m;
    } else {
        $first_word = $text;
        $tail = false;
    }
    $text = HTML::span(array('style' => 'white-space: nowrap'),
        $icon, $first_word);
    if ($tail)
        $text = HTML($text, $tail);
    return $text;
}

/**
 * Determines if the url passed to function is safe, by detecting if the characters
 * '<', '>', or '"' are present.
 * Check against their urlencoded values also.
 *
 * @param string $url URL to check for unsafe characters.
 * @return boolean True if same, false else.
 */
function IsSafeURL($url)
{
    return !preg_match('/([<>"])|(%3C)|(%3E)|(%22)/', $url);
}

/**
 * Generates an HtmlElement object to store data for a link.
 *
 * @param string $url URL that the link will point to.
 * @param string $linktext Text to be displayed as link.
 * @return HtmlElement HtmlElement object that contains data to construct an html link.
 */
function LinkURL($url, $linktext = '')
{
    // FIXME: Is this needed (or sufficient?)
    if (!IsSafeURL($url)) {
        $link = HTML::span(array('class' => 'error'), _('Bad URL -- remove all of <, >, "'));
        return $link;
    } else {
        if (!$linktext)
            $linktext = preg_replace("/mailto:/A", "", $url);
        $args = array('href' => $url);
        if (defined('EXTERNAL_LINK_TARGET')) // can also be set in the css
            $args['target'] = (is_string(EXTERNAL_LINK_TARGET) and (EXTERNAL_LINK_TARGET != "")) ? EXTERNAL_LINK_TARGET : "_blank";
        $link = HTML::a($args, PossiblyGlueIconToText($url, $linktext));
    }
    $link->setAttr('class', $linktext ? 'namedurl' : 'rawurl');
    return $link;
}

/**
 * Inline Images
 *
 * Syntax: [image.png size=50% border=n align= hspace= vspace= width= height=]
 * Disallows sizes which are too small.
 * Spammers may use such (typically invisible) image attributes to raise their GoogleRank.
 *
 * Handle embeddable objects, like svg, class, vrml, swf, svgz, pdf, avi, wmv especially.
 */
function LinkImage($url, $alt = "")
{
    $force_img = "png|jpg|gif|jpeg|bmp|pl|cgi";
    // Disallow tags in img src urls. Typical CSS attacks.
    // FIXME: Is this needed (or sufficient?)
    // FIXED: This was broken for moniker:TP30 test/image.png => url="moniker:TP30" attr="test/image.png"
    $ori_url = $url;
    // support new syntax: [prefix/image.jpg size=50% border=n]
    if (empty($alt)) {
        $alt = "";
    }
    // Extract URL
    $arr = explode(' ', $url);
    if (!empty($arr)) $url = $arr[0];
    if (!IsSafeURL($url)) {
        $link = HTML::span(array('class' => 'error'), _('Bad URL for image -- remove all of <, >, "'));
        return $link;
    }
    // spaces in inline images must be %20 encoded!
    $link = HTML::img(array('src' => $url));

    // Extract attributes
    $arr = parse_attributes(strstr($ori_url, " "));
    foreach ($arr as $attr => $value) {
        // These attributes take strings: lang, id, title, alt
        if (($attr == "lang")
            || ($attr == "id")
            || ($attr == "title")
            || ($attr == "alt")
        ) {
            $link->setAttr($attr, $value);
        } // align = bottom|middle|top|left|right
        // we allow "center" as synonym for "middle"
        elseif (($attr == "align")
            && (($value == "bottom")
                || ($value == "middle")
                || ($value == "center")
                || ($value == "top")
                || ($value == "left")
                || ($value == "right"))
        ) {
            if ($value == "center") {
                $value = "middle";
            }
            $link->setAttr($attr, $value);
        } // These attributes take a number (pixels): border, hspace, vspace
        elseif ((($attr == "border") || ($attr == "hspace") || ($attr == "vspace"))
            && (is_numeric($value))
        ) {
            $link->setAttr($attr, (int)$value);
        } // These attributes take a number (pixels) or a percentage: height, width
        elseif ((($attr == "height") || ($attr == "width"))
            && (preg_match('/\d+[%p]?x?/', $value))
        ) {
            $link->setAttr($attr, $value);
        } // We allow size=50% and size=20x30
        // We replace this with "width" and "height" HTML attributes
        elseif ($attr == "size") {
            if (preg_match('/(\d+%)/', $value, $m)) {
                $link->setAttr('width', $m[1]);
                $link->setAttr('height', $m[1]);
            } elseif (preg_match('/(\d+)x(\d+)/', $value, $m)) {
                $link->setAttr('width', $m[1]);
                $link->setAttr('height', $m[2]);
            }
        } else {
            $url = substr(strrchr($ori_url, "/"), 1);
            $link = HTML::span(array('class' => 'error'),
                sprintf(_("Invalid attribute %s=%s for image %s"),
                    $attr, $value, $url));
            return $link;
        }
    }
    // Correct silently the most common error
    if ($url != $ori_url and empty($arr) and !preg_match("/^http/", $url)) {
        // space belongs to the path
        $file = NormalizeLocalFileName($ori_url);
        if (file_exists($file)) {
            $link = HTML::img(array('src' => $ori_url));
            trigger_error(
                sprintf(_("Invalid image link fixed “%s” => “%s”. Spaces must be quoted with %%20."),
                    $url, $ori_url), E_USER_WARNING);
        } elseif (string_starts_with($ori_url, getUploadDataPath())) {
            $file = substr($file, strlen(getUploadDataPath()));
            $path = getUploadFilePath() . $file;
            if (file_exists($path)) {
                trigger_error(sprintf(_("Invalid image link fixed “%s” => “%s”. Spaces must be quoted with %%20."),
                    $url, $ori_url), E_USER_WARNING);
                $link->setAttr('src', getUploadDataPath() . $file);
                $url = $ori_url;
            }
        }
    }
    if (!$link->getAttr('alt')) {
        $link->setAttr('alt', $alt);
    }
    // Check width and height as spam countermeasure
    if (($width = $link->getAttr('width')) and ($height = $link->getAttr('height'))) {
        //$width  = (int) $width; // px or % or other suffix
        //$height = (int) $height;
        if (($width < 3 and $height < 10) or
            ($height < 3 and $width < 20) or
            ($height < 7 and $width < 7)
        ) {
            $link = HTML::span(array('class' => 'error'),
                _("Invalid image size"));
            return $link;
        }
    } else {
        $size = 0;
        // Prepare for getimagesize($url)
        // $url only valid for external urls, otherwise local path
        if (DISABLE_GETIMAGESIZE)
            ;
        elseif (!preg_match("/\.$force_img$/i", $url))
            ; // only valid image extensions or scripts assumed to generate images
        elseif (preg_match("/^http/", $url)) { // external url
            $size = @getimagesize($url);
        } else { // local file
            if (file_exists($file = NormalizeLocalFileName($url))) { // here
                $size = @getimagesize($file);
            } elseif (file_exists(NormalizeLocalFileName(urldecode($url)))) {
                $size = @getimagesize($file);
                $link->setAttr('src', rawurldecode($url));
            } elseif (string_starts_with($url, getUploadDataPath())) { // there
                $file = substr($file, strlen(getUploadDataPath()));
                $path = getUploadFilePath() . rawurldecode($file);
                $size = @getimagesize($path);
                $link->setAttr('src', getUploadDataPath() . rawurldecode($file));
            } else { // elsewhere
                global $request;
                $size = @getimagesize($request->get('DOCUMENT_ROOT') . urldecode($url));
            }
        }
        if ($size) {
            $width = $size[0];
            $height = $size[1];
            if (($width < 3 and $height < 10)
                or ($height < 3 and $width < 20)
                or ($height < 7 and $width < 7)
            ) {
                $link = HTML::span(array('class' => 'error'),
                    _("Invalid image size"));
                return $link;
            }
        }
    }
    $link->setAttr('class', 'inlineimage');

    /* Check for inlined objects. Everything allowed in INLINE_IMAGES besides
     * png|jpg|gif|jpeg|bmp|pl|cgi.  If no image it is an object to embed.
     * Note: Allow cgi's (pl,cgi) returning images.
     */
    if (!preg_match("/\.(" . $force_img . ")/i", $ori_url)) {
        // HTML::img(array('src' => $url, 'alt' => $alt, 'title' => $alt));
        // => HTML::object(array('src' => $url)) ...;
        return ImgObject($link, $ori_url);
    }
    return $link;
}

/**
 * <object> / <embed> tags instead of <img> for all non-image extensions
 * in INLINE_IMAGES.
 * Called by LinkImage(), not directly.
 * Syntax:  [image.svg size=50% alt=image.gif border=n align= hspace= vspace= width= height=]
 * Samples: [Upload:song.mp3 type=audio/mpeg width=200 height=10]
 *   $alt may be an alternate img
 * TODO: Need to unify with WikiPluginCached::embedObject()
 *
 * Note that Safari 1.0 will crash with <object>, so use only <embed>
 *   http://www.alleged.org.uk/pdc/2002/svg-object.html
 *
 * Allowed object tags:
 *   ID
 *   DATA=URI (object data)
 *   CLASSID=URI (location of implementation)
 *   ARCHIVE=CDATA (archive files)
 *   CODEBASE=URI (base URI for CLASSID, DATA, ARCHIVE)
 *   WIDTH=Length (object width)
 *   HEIGHT=Length (object height)
 *   NAME=CDATA (name for form submission)
 *   USEMAP=URI (client-side image map)
 *   TYPE=ContentType (content-type of object)
 *   CODETYPE=ContentType (content-type of code)
 *   STANDBY=Text (message to show while loading)
 *   TABINDEX=NUMBER (position in tabbing order)
 *   DECLARE (do not instantiate object)
 * The rest is added as <param name="" value="" /> tags
 */
function ImgObject($img, $url)
{
    // get the url args: data="sample.svgz" type="image/svg+xml" width="400" height="300"
    $params = explode(",", "id,width,height,data,classid,archive,codebase,name,usemap,type," .
        "codetype,standby,tabindex,declare");
    if (is_array($url)) {
        $args = $url;
        $found = array();
        foreach ($args as $attr => $value) {
            foreach ($params as $param) {
                if ($param == $attr) {
                    $img->setAttr($param, $value);
                    if (isset($found[$param])) $found[$param]++;
                    else $found[$param] = 1;
                    break;
                }
            }
        }
        // now all remaining args are added as <param> to the object
        $params = array();
        foreach ($args as $attr => $value) {
            if (!isset($found[$attr])) {
                $params[] = HTML::param(array('name' => $attr,
                    'value' => $value));
            }
        }
        $url = $img->getAttr('src');
        $force_img = "png|jpg|gif|jpeg|bmp";
        if (!preg_match("/\.(" . $force_img . ")/i", $url)) {
            $img->setAttr('src', false);
        }
    } else {
        $args = explode(' ', $url);
        if (count($args) >= 1) {
            $url = array_shift($args);
            $found = array();
            foreach ($args as $attr) {
                foreach ($params as $param) {
                    if (preg_match("/^$param=(\S+)$/i", $attr, $m)) {
                        $img->setAttr($param, $m[1]);
                        if (isset($found[$param])) $found[$param]++;
                        else $found[$param] = 1;
                        break;
                    }
                }
            }
            // now all remaining args are added as <param> to the object
            $params = array();
            foreach ($args as $attr) {
                if (!isset($found[$attr]) and preg_match("/^(\S+)=(\S+)$/i", $attr, $m)) {
                    $params[] = HTML::param(array('name' => $m[1],
                        'value' => $m[2]));
                }
            }
        }
    }
    $type = $img->getAttr('type');
    if (!$type) {
        if (file_exists($url)) {
            $type = mime_content_type($url);
        }
    }
    $object = HTML::object(array_merge($img->_attr,
            array('type' => $type)), //'src' => $url
        $img->_content);
    $object->setAttr('class', 'inlineobject');
    if ($params) {
        foreach ($params as $param) $object->pushContent($param);
    }
    if (isBrowserSafari() and !isBrowserSafari(532)) { // recent chrome can do OBJECT
        return HTML::embed($object->_attr, $object->_content);
    }
    $object->pushContent(HTML::embed($object->_attr));
    return $object;
}

class Stack
{
    function __construct()
    {
        $this->items = array();
        $this->size = 0;
    }

    public function push($item)
    {
        $this->items[$this->size] = $item;
        $this->size++;
        return true;
    }

    public function pop()
    {
        if ($this->size == 0) {
            return false; // stack is empty
        }
        $this->size--;
        return $this->items[$this->size];
    }

    public function cnt()
    {
        return $this->size;
    }

    public function top()
    {
        if ($this->size)
            return $this->items[$this->size - 1];
        else
            return '';
    }

}

// end class definition

function SplitQueryArgs($query_args = '')
{
    // FIXME: use the arg-separator which might not be &
    $split_args = explode('&', $query_args);
    $args = array();
    while (list($key, $val) = each($split_args))
        if (preg_match('/^ ([^=]+) =? (.*) /x', $val, $m))
            $args[$m[1]] = $m[2];
    return $args;
}

function LinkPhpwikiURL($url, $text = '', $basepage = false)
{
    /**
     * @var WikiRequest $request
     */
    global $request;

    $args = array();

    if (!preg_match('/^ phpwiki: ([^?]*) [?]? (.*) $/x', $url, $m)) {
        return HTML::span(array('class' => 'error'), _("BAD phpwiki: URL"));
    }

    if ($m[1])
        $pagename = urldecode($m[1]);
    $qargs = $m[2];

    if (empty($pagename) &&
        preg_match('/^(diff|edit|links|info)=([^&]+)$/', $qargs, $m)
    ) {
        // Convert old style links (to not break diff links in
        // RecentChanges).
        $pagename = urldecode($m[2]);
        $args = array("action" => $m[1]);
    } else {
        $args = SplitQueryArgs($qargs);
    }

    if (empty($pagename))
        $pagename = $request->getArg('pagename');

    if (isset($args['action']) && $args['action'] == 'browse')
        unset($args['action']);

    /*FIXME:
      if (empty($args['action']))
      $class = 'wikilink';
      else if (is_safe_action($args['action']))
      $class = 'wikiaction';
    */
    if (empty($args['action']) || is_safe_action($args['action']))
        $class = 'wikiaction';
    else {
        // Don't allow administrative links on unlocked pages.
        $dbi = $request->getDbh();
        $page = $dbi->getPage($basepage ? $basepage : $pagename);
        if (!$page->get('locked'))
            return HTML::span(array('class' => 'wikiunsafe'),
                HTML::u(_("Lock page to enable link")));
        $class = 'wikiadmin';
    }

    if (!$text)
        $text = HTML::span(array('class' => 'rawurl'), $url);

    $wikipage = new WikiPageName($pagename);
    if (!$wikipage->isValid()) {
        global $WikiTheme;
        return $WikiTheme->linkBadWikiWord($wikipage, $url);
    }

    return HTML::a(array('href' => WikiURL($pagename, $args), 'class' => $class), $text);
}

/**
 * A class to assist in parsing wiki pagenames.
 *
 * Now with subpages and anchors, parsing and passing around
 * pagenames is more complicated.  This should help.
 */
class WikiPageName
{
    /** Short name for page.
     *
     * This is the value of $name passed to the constructor.
     * (For use, e.g. as a default label for links to the page.)
     */
    public $shortName;

    /** The full page name.
     *
     * This is the full name of the page (without anchor).
     */
    public $name;

    /** The anchor.
     *
     * This is the referenced anchor within the page, or the empty string.
     */
    public $anchor;

    public $_errors;
    public $_warnings;

    /**
     * @param mixed $name Page name.
     * WikiDB_Page, WikiDB_PageRevision, or string.
     * This can be a relative subpage name (like '/SubPage'),
     * or can be the empty string to refer to the $basename.
     *
     * @param mixed $basename Page name from which to interpret
     * relative or other non-fully-specified page names.
     *
     * @param mixed $anchor For links to anchors in page.
     */
    function __construct($name, $basename = false, $anchor = false)
    {
        if (is_string($name)) {
            $this->shortName = $name;
            if (strstr($name, ':')) {
                list($moniker, $shortName) = explode(":", $name, 2);
                $map = getInterwikiMap(); // allow overrides to custom maps
                if (isset($map->_map[$moniker])) {
                    $url = $map->_map[$moniker];
                    if (strstr($url, '%s'))
                        $url = sprintf($url, $shortName);
                    else
                        $url .= $shortName;
                    $this->url = $url;
                    // expand Talk or User, but not to absolute urls!
                    if (strstr($url, '//')) {
                        if ($moniker == 'Talk')
                            $name = $name . '/' . _("Discussion");
                    } else {
                        $name = $url;
                    }
                    $this->shortName = $shortName;
                }
            }
            // FIXME: We should really fix the cause for "/PageName" in the WikiDB
            if ($name == '' or $name[0] == '/') {
                if ($basename)
                    $name = $this->_pagename($basename) . $name;
                else {
                    $name = $this->_normalize_bad_pagename($name);
                    $this->shortName = $name;
                }
            }
        } else {
            $name = $this->_pagename($name);
            $this->shortName = $name;
        }

        $this->name = $this->_check($name);
        $this->anchor = (string)$anchor;
    }

    function getName()
    {
        return $this->name;
    }

    function getParent()
    {
        $name = $this->name;
        if (!($tail = strrchr($name, '/')))
            return false;
        return substr($name, 0, -strlen($tail));
    }

    function isValid($strict = false)
    {
        if ($strict)
            return !isset($this->_errors);
        return (is_string($this->name) and $this->name != '');
    }

    function getWarnings()
    {
        $warnings = array();
        if (isset($this->_warnings))
            $warnings = array_merge($warnings, $this->_warnings);
        if (isset($this->_errors))
            $warnings = array_merge($warnings, $this->_errors);
        if (!$warnings)
            return false;

        return sprintf(_("“%s”: Bad page name: %s"),
            $this->shortName, join(', ', $warnings));
    }

    private function _pagename($page)
    {
        if (is_a($page, 'WikiDB_Page'))
            return $page->getName();
        elseif (is_a($page, 'WikiDB_PageRevision'))
            return $page->getPageName();
        elseif (is_a($page, 'WikiPageName'))
            return $page->name;
        return $page;
    }

    private function _normalize_bad_pagename($name)
    {
        trigger_error("Bad pagename: " . $name, E_USER_WARNING);

        // Punt...  You really shouldn't get here.
        if (empty($name)) {
            global $request;
            return $request->getArg('pagename');
        }
        assert($name[0] == '/');
        $this->_errors[] = _("Leading “/” not allowed");
        return substr($name, 1);
    }

    /**
     * Compress internal white-space to single space character.
     *
     * This leads to problems with loading a foreign charset pagename,
     * which cannot be deleted anymore, because unknown chars are compressed.
     * So BEFORE importing a file _check must be done !!!
     */
    private function _check($pagename)
    {
        // Compress internal white-space to single space character.
        $pagename = preg_replace('/[\s]+/', ' ', $orig = $pagename);
        if ($pagename != $orig)
            $this->_warnings[] = _("White space converted to single space");

        // Delete any control characters.
        if (DATABASE_TYPE == 'file' or DATABASE_TYPE == 'flatfile') {
            $pagename = preg_replace('/[\x00-\x1f\x7f\x80-\x9f]/', '', $orig = $pagename);
            if ($pagename != $orig)
                $this->_errors[] = _("Control characters not allowed");
        }

        // Strip leading and trailing white-space.
        $pagename = trim($pagename);

        $orig = $pagename;
        while ($pagename and $pagename[0] == '/')
            $pagename = substr($pagename, 1);
        if ($pagename != $orig)
            $this->_errors[] = _("Leading “/” not allowed");

        // not only for SQL, also to restrict url length
        if (strlen($pagename) > MAX_PAGENAME_LENGTH) {
            $pagename = substr($pagename, 0, MAX_PAGENAME_LENGTH);
            $this->_errors[] = _("Page name too long");
        }

        // disallow some chars only on file
        if ((DATABASE_TYPE == 'file'
            or DATABASE_TYPE == 'flatfile')
            and preg_match('/(:|\.\.)/', $pagename, $m)
        ) {
            $this->_warnings[] = sprintf(_("Illegal chars %s removed"), $m[1]);
            $pagename = str_replace('..', '', $pagename);
            $pagename = str_replace(':', '', $pagename);
        }

        return $pagename;
    }
}

/**
 * Expand tabs in string.
 *
 * Converts all tabs to (the appropriate number of) spaces.
 *
 * @param string $str
 * @param int $tab_width
 * @return string
 */
function expand_tabs($str, $tab_width = 8)
{
    $split = explode("\t", $str);
    $tail = array_pop($split);
    $expanded = "\n";
    foreach ($split as $hunk) {
        $expanded .= $hunk;
        $pos = strlen(strrchr($expanded, "\n")) - 1;
        $expanded .= str_repeat(" ", ($tab_width - $pos % $tab_width));
    }
    return substr($expanded, 1) . $tail;
}

/**
 * Split WikiWords in page names.
 *
 * It has been deemed useful to split WikiWords (into "Wiki Words") in
 * places like page titles. This is rumored to help search engines
 * quite a bit.
 *
 * @param $page string The page name.
 *
 * @return string The split name.
 */
function SplitPagename($page)
{

    if (preg_match("/\s/", $page))
        return $page; // Already split --- don't split any more.

    // This algorithm is specialized for several languages.
    // (Thanks to Pierrick MEIGNEN)
    // Improvements for other languages welcome.
    static $RE;
    if (!isset($RE)) {
        // This mess splits between a lower-case letter followed by
        // either an upper-case or a numeral; except that it wont
        // split the prefixes 'Mc', 'De', or 'Di' off of their tails.
        switch ($GLOBALS['LANG']) {
            case 'en':
            case 'it':
            case 'es':
            case 'de':
                $RE[] = '/([[:lower:]])((?<!Mc|De|Di)[[:upper:]]|\d)/';
                break;
            case 'fr':
                $RE[] = '/([[:lower:]])((?<!Mc|Di)[[:upper:]]|\d)/';
                break;
        }
        $sep = preg_quote('/', '/');
        // This the single-letter words 'I' and 'A' from any following
        // capitalized words.
        switch ($GLOBALS['LANG']) {
            case 'en':
                $RE[] = "/(?<= |${sep}|^)([AI])([[:upper:]][[:lower:]])/";
                break;
            case 'fr':
                $RE[] = "/(?<= |${sep}|^)([À])([[:upper:]][[:lower:]])/";
                break;
        }
        // Split at underscore
        $RE[] = '/(_)([[:alpha:]])/';
        $RE[] = '/([[:alpha:]])(_)/';
        // Split numerals from following letters.
        $RE[] = '/(\d)([[:alpha:]])/';
        // Split at subpage separators. TBD in WikiTheme.php
        $RE[] = "/([^${sep}]+)(${sep})/";
        $RE[] = "/(${sep})([^${sep}]+)/";

        foreach ($RE as $key)
            $RE[$key] = $key;
    }

    foreach ($RE as $regexp) {
        $page = preg_replace($regexp, '\\1 \\2', $page);
    }
    return $page;
}

function NoSuchRevision(&$request, $page, $version)
{
    $html = HTML(HTML::h2(_("Revision Not Found")),
        HTML::p(fmt("I'm sorry.  Version %d of %s is not in the database.",
            $version, WikiLink($page, 'auto'))));
    include_once 'lib/Template.php';
    GeneratePage($html, _("Bad Version"), $page->getCurrentRevision());
    $request->finish();
}

/**
 * Get time offset for local time zone.
 *
 * @param int $time Get offset for this time. Default: now.
 * @param bool $no_colon Don't put colon between hours and minutes.
 * @return string Offset as a string in the format +HH:MM.
 */
function TimezoneOffset($time = 0, $no_colon = false)
{
    if ($time == 0)
        $time = time();
    $secs = date('Z', $time);

    if ($secs < 0) {
        $sign = '-';
        $secs = -$secs;
    } else {
        $sign = '+';
    }
    $colon = $no_colon ? '' : ':';
    $mins = intval(($secs + 30) / 60);
    return sprintf("%s%02d%s%02d",
        $sign, $mins / 60, $colon, $mins % 60);
}

/**
 * Format time in ISO-8601 format.
 *
 * @param int $time Time.  Default: now.
 * @return string Date and time in ISO-8601 format.
 */
function Iso8601DateTime($time = 0)
{
    if ($time == 0)
        $time = time();
    $tzoff = TimezoneOffset($time);
    $date = date('Y-m-d', $time);
    $time = date('H:i:s', $time);
    return $date . 'T' . $time . $tzoff;
}

/**
 * Format time in RFC-2822 format.
 *
 * @param int $time Time.  Default: now.
 * @return string Date and time in RFC-2822 format.
 */
function Rfc2822DateTime($time = 0)
{
    if ($time == 0)
        $time = time();
    return date('D, j M Y H:i:s ', $time) . TimezoneOffset($time, 'no colon');
}

/**
 * Format time in RFC-1123 format.
 *
 * @param int $time Time.  Default: now.
 * @return string Date and time in RFC-1123 format.
 */
function Rfc1123DateTime($time = 0)
{
    if ($time == 0)
        $time = time();
    return gmdate('D, d M Y H:i:s \G\M\T', $time);
}

/** Parse date in RFC-1123 format.
 *
 * According to RFC 1123 we must accept dates in the following
 * formats:
 *
 *   Sun, 06 Nov 1994 08:49:37 GMT  ; RFC 822, updated by RFC 1123
 *   Sunday, 06-Nov-94 08:49:37 GMT ; RFC 850, obsoleted by RFC 1036
 *   Sun Nov  6 08:49:37 1994       ; ANSI C's asctime() format
 *
 * (Though we're only allowed to generate dates in the first format.)
 */
function ParseRfc1123DateTime($timestr)
{
    $timestr = trim($timestr);
    if (preg_match('/^ \w{3},\s* (\d{1,2}) \s* (\w{3}) \s* (\d{4}) \s*'
            . '(\d\d):(\d\d):(\d\d) \s* GMT $/ix',
        $timestr, $m)
    ) {
        list(, $mday, $mon, $year, $hh, $mm, $ss) = $m;
    } elseif (preg_match('/^ \w+,\s* (\d{1,2})-(\w{3})-(\d{2}|\d{4}) \s*'
            . '(\d\d):(\d\d):(\d\d) \s* GMT $/ix',
        $timestr, $m)
    ) {
        list(, $mday, $mon, $year, $hh, $mm, $ss) = $m;
        if ($year < 70) $year += 2000;
        elseif ($year < 100) $year += 1900;
    } elseif (preg_match('/^\w+\s* (\w{3}) \s* (\d{1,2}) \s*'
            . '(\d\d):(\d\d):(\d\d) \s* (\d{4})$/ix',
        $timestr, $m)
    ) {
        list(, $mon, $mday, $hh, $mm, $ss, $year) = $m;
    } else {
        // Parse failed.
        return false;
    }

    $time = strtotime("$mday $mon $year ${hh}:${mm}:${ss} GMT");
    if ($time == -1)
        return false; // failed
    return $time;
}

/**
 * Format time to standard 'ctime' format.
 *
 * @param int $time Time.  Default: now.
 * @return string Date and time.
 */
function CTime($time = 0)
{
    if ($time == 0)
        $time = time();
    return date("D M j H:i:s Y", $time);
}

/**
 * Format number as kibibytes or bytes.
 * Short format is used for PageList
 * Long format is used in PageInfo
 *
 * @param $bytes       int.  Default: 0.
 * @param $longformat  bool. Default: false.
 * @return FormattedText (XmlElement.php).
 */
function ByteFormatter($bytes = 0, $longformat = false)
{
    if ($bytes < 0)
        return fmt("-???");
    if ($bytes < 1024) {
        if (!$longformat)
            $size = fmt("%s B", $bytes);
        else
            $size = fmt("%s bytes", $bytes);
    } else {
        $kb = round($bytes / 1024, 1);
        if (!$longformat)
            $size = fmt("%s KiB", $kb);
        else
            $size = fmt("%s KiB (%s bytes)", $kb, $bytes);
    }
    return $size;
}

/**
 * Internationalized printf.
 *
 * This is essentially the same as PHP's built-in printf
 * with the following exceptions:
 * <ol>
 * <li> It passes the format string through gettext().
 * <li> It supports the argument reordering extensions.
 * </ol>
 *
 * Example:
 *
 * In php code, use:
 * <pre>
 *    __printf("Differences between versions %s and %s of %s",
 *             $new_link, $old_link, $page_link);
 * </pre>
 *
 * Then in locale/po/de.po, one can reorder the printf arguments:
 *
 * <pre>
 *    msgid "Differences between %s and %s of %s."
 *    msgstr "Der Unterschiedsergebnis von %3$s, zwischen %1$s und %2$s."
 * </pre>
 *
 * (Note that while PHP tries to expand $vars within double-quotes,
 * the values in msgstr undergo no such expansion, so the '$'s
 * okay...)
 *
 * One shouldn't use reordered arguments in the default format string.
 * Backslashes in the default string would be necessary to escape the
 * '$'s, and they'll cause all kinds of trouble....
 */
function __printf($fmt)
{
    $args = func_get_args();
    array_shift($args);
    echo __vsprintf($fmt, $args);
}

/**
 * Internationalized sprintf.
 *
 * This is essentially the same as PHP's built-in printf with the
 * following exceptions:
 *
 * <ol>
 * <li> It passes the format string through gettext().
 * <li> It supports the argument reordering extensions.
 * </ol>
 *
 * @see __printf
 */
function __sprintf($fmt)
{
    $args = func_get_args();
    array_shift($args);
    return __vsprintf($fmt, $args);
}

/**
 * Internationalized vsprintf.
 *
 * This is essentially the same as PHP's built-in printf with the
 * following exceptions:
 *
 * <ol>
 * <li> It passes the format string through gettext().
 * <li> It supports the argument reordering extensions.
 * </ol>
 *
 * @see __printf
 */
function __vsprintf($fmt, $args)
{
    $fmt = gettext($fmt);
    // PHP's sprintf doesn't support variable with specifiers,
    // like sprintf("%*s", 10, "x"); --- so we won't either.

    if (preg_match_all('/(?<!%)%(\d+)\$/x', $fmt, $m)) {
        // Format string has '%2$s' style argument reordering.
        // PHP doesn't support this.
        if (preg_match('/(?<!%)%[- ]?\d*[^- \d$]/x', $fmt))
            // literal variable name substitution only to keep locale
            // strings uncluttered
            trigger_error(sprintf(_("Can't mix “%s” with “%s” type format strings"),
                '%1\$s', '%s'), E_USER_WARNING); //php+locale error

        $fmt = preg_replace('/(?<!%)%\d+\$/x', '%', $fmt);
        $newargs = array();

        // Reorder arguments appropriately.
        foreach ($m[1] as $argnum) {
            if ($argnum < 1 || $argnum > count($args))
                trigger_error(sprintf(_("%s: argument index out of range"),
                    $argnum), E_USER_WARNING);
            $newargs[] = $args[$argnum - 1];
        }
        $args = $newargs;
    }

    // Not all PHP's have vsprintf, so...
    array_unshift($args, $fmt);
    return call_user_func_array('sprintf', $args);
}

function file_mtime($filename)
{
    if ($stat = @stat($filename))
        return $stat[9];
    else
        return false;
}

function sort_file_mtime($a, $b)
{
    $ma = file_mtime($a);
    $mb = file_mtime($b);
    if (!$ma or !$mb or $ma == $mb) return 0;
    return ($ma > $mb) ? -1 : 1;
}

class fileSet
{
    function __construct($directory, $filepattern = false)
    {
        $this->_fileList = array();
        $this->_pattern = $filepattern;
        if ($filepattern) {
            $this->_pcre_pattern = glob_to_pcre($this->_pattern);
        }
        $this->_case = !isWindows();
        $this->_pathsep = '/';

        if (empty($directory) or !file_exists($directory) or !is_dir($directory)) {
            return; // early return
        }

        $dir_handle = opendir($dir = $directory);
        if (empty($dir_handle)) {
            return; // early return
        }

        while ($filename = readdir($dir_handle)) {
            if ($filename[0] == '.' || filetype($dir . $this->_pathsep . $filename) != 'file')
                continue;
            if ($this->_filenameSelector($filename)) {
                array_push($this->_fileList, "$filename");
            }
        }
        closedir($dir_handle);
    }

    /**
     * Build an array in $this->_fileList of files from $dirname.
     * Subdirectories are not traversed.
     *
     * (This was a function LoadDir in lib/loadsave.php)
     * See also http://www.php.net/manual/en/function.readdir.php
     */
    public function getFiles($exclude = '', $sortby = '', $limit = '')
    {
        $list = $this->_fileList;

        if ($sortby) {
            require_once 'lib/PageList.php';
            switch (Pagelist::sortby($sortby, 'db')) {
                case 'pagename ASC':
                    break;
                case 'pagename DESC':
                    $list = array_reverse($list);
                    break;
                case 'mtime ASC':
                    usort($list, 'sort_file_mtime');
                    break;
                case 'mtime DESC':
                    usort($list, 'sort_file_mtime');
                    $list = array_reverse($list);
                    break;
            }
        }
        if ($limit)
            return array_splice($list, 0, $limit);
        return $list;
    }

    protected function _filenameSelector($filename)
    {
        if (!$this->_pattern)
            return true;
        else {
            if (!$this->_pcre_pattern)
                $this->_pcre_pattern = glob_to_pcre($this->_pattern);
            return preg_match('/' . $this->_pcre_pattern . ($this->_case ? '/' : '/i'),
                $filename);
        }
    }
}

class imageSet extends fileSet
{
    /**
     * A file is considered an image when the suffix matches one from
     * INLINE_IMAGES.
     */
    protected function _filenameSelector($filename)
    {
        return is_image($filename);
    }
}

class imageOrVideoSet extends fileSet
{
    protected function _filenameSelector($filename)
    {
        return is_image($filename) or is_video($filename);
    }
}

// Convert fileglob to regex style:
// Convert some wildcards to pcre style, escape the rest
// Escape . \\ + * ? [ ^ ] $ ( ) { } = ! < > | : /
// Fixed bug #994994: "/" in $glob.
function glob_to_pcre($glob)
{
    // check simple case: no need to escape
    $escape = '\[](){}=!<>|:/';
    if (strcspn($glob, $escape . ".+*?^$") == strlen($glob))
        return $glob;
    // preg_replace cannot handle "\\\\\\2" so convert \\ to \xff
    $glob = strtr($glob, "\\", "\xff");
    $glob = str_replace("/", "\\/", $glob);
    // first convert some unescaped expressions to pcre style: . => \.
    $special = '.^$';
    $re = preg_replace('/([^\xff])?([' . preg_quote($special) . '])/',
        "\\1\xff\\2", $glob);

    // * => .*, ? => .
    $re = preg_replace('/([^\xff])?\*/', '$1.*', $re);
    $re = preg_replace('/([^\xff])?\?/', '$1.', $re);
    if (!preg_match('/^[\?\*]/', $glob))
        $re = '^' . $re;
    if (!preg_match('/[\?\*]$/', $glob))
        $re = $re . '$';

    // Fixes Bug 1182997
    // .*? handled above, now escape the rest
    //while (strcspn($re, $escape) != strlen($re)) // loop strangely needed
    $re = preg_replace('/([^\xff])([' . preg_quote($escape, "/") . '])/',
        "\\1\xff\\2", $re);
    // Problem with 'Date/Time' => 'Date\/Time' => 'Date\xff\/Time' => 'Date\/Time'
    // 'plugin/*.php'
    $re = preg_replace('/\xff/', '', $re);
    return $re;
}

function glob_match($glob, $against, $case_sensitive = true)
{
    return preg_match('/' . glob_to_pcre($glob) . ($case_sensitive ? '/' : '/i'),
        $against);
}

function explodePageList($input, $include_empty = false, $sortby = 'pagename',
                         $limit = '', $exclude = '')
{
    include_once 'lib/PageList.php';
    return PageList::explodePageList($input, $include_empty, $sortby, $limit, $exclude);
}

// Class introspections

/** Determine whether a function is okay to use.
 *
 * Some providers (e.g. Lycos) disable some of PHP functions for
 * "security reasons."  This makes those functions, of course,
 * unusable, despite the fact the function_exists() says they
 * exist.
 *
 * This function test to see if a function exists and is not
 * disallowed by PHP's disable_functions config setting.
 *
 * @param string $function_name  Function name
 * @return bool  True iff function can be used.
 */
function function_usable($function_name)
{
    static $disabled;
    if (!is_array($disabled)) {
        $disabled = array();
        // Use get_cfg_var since ini_get() is one of the disabled functions
        // (on Lycos, at least.)
        $split = preg_split('/\s*,\s*/', trim(get_cfg_var('disable_functions')));
        foreach ($split as $f)
            $disabled[strtolower($f)] = true;
    }

    return (function_exists($function_name)
        and !isset($disabled[strtolower($function_name)])
    );
}

/** Hash a value.
 *
 * This is used for generating ETags.
 */
function wikihash($x)
{
    if (is_scalar($x)) {
        return $x;
    } elseif (is_array($x)) {
        ksort($x);
        return md5(serialize($x));
    } elseif (is_object($x)) {
        return $x->hash();
    }
    trigger_error("Can't hash $x", E_USER_ERROR);
    return false;
}

function rand_ascii($length = 1)
{
    $s = "";
    for ($i = 1; $i <= $length; $i++) {
        // return only typeable 7 bit ascii, avoid quotes
        $s .= chr(mt_rand(40, 126));
    }
    return $s;
}

/* by Dan Frankowski.
 */
function rand_ascii_readable($length = 6)
{
    // Pick a few random letters or numbers
    $word = "";
    // Don't use 1lI0O, because they're hard to read
    $letters = "abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
    $letter_len = strlen($letters);
    for ($i = 0; $i < $length; $i++) {
        $word .= $letters[mt_rand(0, $letter_len - 1)];
    }
    return $word;
}

/**
 * Recursively count all non-empty elements
 * in array of any dimension or mixed - i.e.
 * array('1' => 2, '2' => array('1' => 3, '2' => 4))
 * See http://www.php.net/manual/en/function.count.php
 */
function count_all($arg)
{
    // skip if argument is empty
    if ($arg) {
        //print_r($arg); //debugging
        $count = 0;
        // not an array, return 1 (base case)
        if (!is_array($arg))
            return 1;
        // else call recursively for all elements $arg
        foreach ($arg as $key => $val)
            $count += count_all($val);
        return $count;
    }
    return 0;
}

function isSubPage($pagename)
{
    return (strstr($pagename, '/'));
}

function subPageSlice($pagename, $pos)
{
    $pages = explode('/', $pagename);
    $pages = array_slice($pages, $pos, 1);
    return $pages[0];
}

function isActionPage($filename)
{

    global $AllActionPages;

    $localizedAllActionPages = array_map("__", $AllActionPages);

    return (in_array($filename, $localizedAllActionPages));
}

/**
 * Alert
 *
 * Class for "popping up" and alert box.  (Except that right now, it doesn't
 * pop up...)
 *
 * FIXME:
 * This is a hackish and needs to be refactored.  However it would be nice to
 * unify all the different methods we use for showing Alerts and Dialogs.
 * (E.g. "Page deleted", login form, ...)
 */
class Alert
{
    /**
     * @param mixed  $head    Header ("title") for alert box.
     * @param mixed  $body    The text in the alert box.
     * @param array  $buttons An array mapping button labels to URLs.
     *    The default is a single "Okay" button pointing to $request->getURLtoSelf().
     */
    function __construct($head, $body, $buttons = array())
    {
        if (is_array($body)) {
            $html = HTML::ol();
            foreach ($body as $li) {
                $html->pushContent(HTML::li($li));
            }
            $body = $html;
        }
        $this->_tokens = array('HEADER' => $head, 'CONTENT' => $body);
        $this->_buttons = $buttons;
    }

    /**
     * Show the alert box.
     */
    public function show()
    {
        global $request;

        $tokens = $this->_tokens;
        $tokens['BUTTONS'] = $this->_getButtons();

        $request->discardOutput();
        $tmpl = new Template('dialog', $request, $tokens);
        $tmpl->printXML();
        $request->finish();
    }

    private function _getButtons()
    {
        global $request;

        $buttons = $this->_buttons;
        if (!$buttons)
            $buttons = array(_("OK") => $request->getURLtoSelf());

        global $WikiTheme;
        foreach ($buttons as $label => $url)
            print "$label $url\n";
        $out[] = $WikiTheme->makeButton($label, $url, 'wikiaction');
        return new XmlContent($out);
    }
}

// 1.3.8     => 1030.08
// 1.3.9-p1  => 1030.091
// 1.3.10pre => 1030.099
// 1.3.11pre-20041120 => 1030.1120041120
// 1.3.12-rc1 => 1030.119
function phpwiki_version()
{
    static $PHPWIKI_VERSION;
    if (!isset($PHPWIKI_VERSION)) {
        $arr = explode('.', preg_replace('/\D+$/', '', PHPWIKI_VERSION)); // remove the pre
        $arr[2] = preg_replace('/\.+/', '.', preg_replace('/\D/', '.', $arr[2]));
        $PHPWIKI_VERSION = $arr[0] * 1000 + $arr[1] * 10 + 0.01 * $arr[2];
        if (strstr(PHPWIKI_VERSION, 'pre') or strstr(PHPWIKI_VERSION, 'rc'))
            $PHPWIKI_VERSION -= 0.01;
    }
    return $PHPWIKI_VERSION;
}

function phpwiki_gzhandler($ob)
{
    /**
     * @var WikiRequest $request
     */
    global $request;

    $ob = gzencode($ob);
    $request->_ob_get_length = strlen($ob);
    if (!headers_sent()) {
        header(sprintf("Content-Length: %d", $request->_ob_get_length));
    }
    return $ob;
}

function isWikiWord($word)
{
    global $WikiNameRegexp;
    //or preg_match('/\A' . $WikiNameRegexp . '\z/', $word) ??
    return preg_match("/^$WikiNameRegexp\$/", $word);
}

/**
 * isAsciiString($string)
 */
function isAsciiString($s)
{
    $ptrASCII = '[\x00-\x7F]';
    return preg_match("/^($ptrASCII)*$/s", $s);
}

/**
 * Workaround for allow_url_fopen, to get the content of an external URI.
 * It returns the contents in one slurp. Parsers might want to check for allow_url_fopen
 * and use fopen, fread chunkwise. (see lib/XmlParser.php)
 */
function url_get_contents($uri)
{
    if (get_cfg_var('allow_url_fopen')) { // was ini_get('allow_url_fopen'))
        return @file_get_contents($uri);
    } else {
        require_once 'lib/HttpClient.php';
        $bits = parse_url($uri);
        $host = $bits['host'];
        $port = isset($bits['port']) ? $bits['port'] : 80;
        $path = isset($bits['path']) ? $bits['path'] : '/';
        if (isset($bits['query'])) {
            $path .= '?' . $bits['query'];
        }
        $client = new HttpClient($host, $port);
        $client->use_gzip = false;
        if (!$client->get($path)) {
            return false;
        } else {
            return $client->getContent();
        }
    }
}

/**
 * Generate consecutively named strings:
 *   Name, Name2, Name3, ...
 */
function GenerateId($name)
{
    static $ids = array();
    if (empty($ids[$name])) {
        $ids[$name] = 1;
        return $name;
    } else {
        $ids[$name]++;
        return $name . $ids[$name];
    }
}

// from IncludePage. To be of general use.
// content: string or array of strings
function firstNWordsOfContent($n, $content)
{
    if ($content and $n > 0) {
        if (is_array($content)) {
            // fixme: return a list of lines then?
            //$content = join("\n", $content);
            //$return_array = true;
            $wordcount = 0;
            $new = array();
            foreach ($content as $line) {
                $words = explode(' ', $line);
                if ($wordcount + count($words) > $n) {
                    $new[] = implode(' ', array_slice($words, 0, $n - $wordcount))
                        . sprintf(_("... (first %s words)"), $n);
                    return $new;
                } else {
                    $wordcount += count($words);
                    $new[] = $line;
                }
            }
            return $new;
        } else {
            // fixme: use better whitespace/word separators
            $words = explode(' ', $content);
            if (count($words) > $n) {
                return join(' ', array_slice($words, 0, $n))
                    . sprintf(_("... (first %s words)"), $n);
            } else {
                return $content;
            }
        }
    } else {
        return '';
    }
}

// moved from lib/plugin/IncludePage.php
function extractSection($section, $content, $page, $quiet = false, $sectionhead = false)
{
    $qsection = preg_replace('/\s+/', '\s+', preg_quote($section, '/'));

    if (preg_match("/ ^(!{1,}|={2,})\\s*$qsection\s*=*" // section header
            . "  \\s*$\\n?" // possible blank lines
            . "  ( (?: ^.*\\n? )*? )" // some lines
            . "  (?= ^\\1 | \\Z)/xm", // sec header (same or higher level) (or EOF)
        implode("\n", $content),
        $match)
    ) {
        // Strip trailing blanks lines and ---- <hr>s
        $text = preg_replace("/\\s*^-{4,}\\s*$/m", "", $match[2]);
        if ($sectionhead)
            $text = $match[1] . $section . "\n" . $text;
        return explode("\n", $text);
    }
    if ($quiet)
        $mesg = $page . " " . $section;
    else
        $mesg = $section;
    return array(sprintf(_("<%s: no such section>"), $mesg));
}

// Extract the first $sections sections of the page
function extractSections($sections, $content, $page, $quiet = false, $sectionhead = false)
{

    $mycontent = $content;
    $result = "";

    while ($sections > 0) {

        if (preg_match("/ ^(!{1,}|={2,})\\s*(.*)\\n" // section header
                . "  \\s*$\\n?" // possible blank lines
                . "  ( (?: ^.*\\n? )*? )" // some lines
                . "  ( ^\\1 (.|\\n)* | \\Z)/xm", // sec header (same or higher level) (or EOF)
            implode("\n", $mycontent),
            $match)
        ) {
            $section = $match[2];
            // Strip trailing blanks lines and ---- <hr>s
            $text = preg_replace("/\\s*^-{4,}\\s*$/m", "", $match[3]);
            if ($sectionhead)
                $text = $match[1] . $section . "\n" . $text;
            $result .= $text;

            $mycontent = explode("\n", $match[4]);
            $sections--;
            if ($sections === 0) {
                return explode("\n", $result);
            }
        }
    }
    return '';
}

// use this faster version: only load ExternalReferrer if we came from an external referrer
function isExternalReferrer(&$request)
{
    if ($referrer = $request->get('HTTP_REFERER')) {
        $home = SERVER_URL; // SERVER_URL or SCRIPT_NAME, if we want to check sister wiki's also
        if (string_starts_with(strtolower($referrer), strtolower($home))) return false;
        require_once 'lib/ExternalReferrer.php';
        $se = new SearchEngines();
        return $se->parseSearchQuery($referrer);
    }
    //if (DEBUG) return array('query' => 'wiki');
    return false;
}

/**
 * Useful for PECL overrides: ldap, soap, xmlrpc, pdo, pdo_<driver>
 */
function loadPhpExtension($extension)
{
    if (!extension_loaded($extension)) {
        $isWindows = (substr(PHP_OS, 0, 3) == 'WIN');
        $soname = ($isWindows ? 'php_' : '')
            . $extension
            . ($isWindows ? '.dll' : '.so');
        if (!@dl($soname))
            return false;
    }
    return extension_loaded($extension);
}

function string_starts_with($string, $prefix)
{
    return (substr($string, 0, strlen($prefix)) == $prefix);
}

function string_ends_with($string, $suffix)
{
    return (substr($string, -strlen($suffix)) == $suffix);
}

function array_remove($arr, $value)
{
    return array_values(array_diff($arr, array($value)));
}

/**
 * Ensure that the script will have another $secs time left.
 * Works only if safe_mode is off.
 * For example not to timeout on waiting socket connections.
 *   Use the socket timeout as arg.
 */
function longer_timeout($secs = 30)
{
    $timeout = @ini_get("max_execution_time") ? ini_get("max_execution_time") : 30;
    $timeleft = $timeout - $GLOBALS['RUNTIMER']->getTime();
    if ($timeleft < $secs)
        @set_time_limit(max($timeout, (integer)($secs + $timeleft)));
}

function printSimpleTrace($bt)
{
    echo "\nTraceback:\n";
    debug_print_backtrace();
}

/**
 * @param mixed $needle
 * @param array $haystack one-dimensional numeric array only, no hash
 * @return integer
 * @desc Feed a sorted array to $haystack and a value to search for to $needle.
It will return false if not found or the index where it was found.
From dennis.decoene@moveit.be http://www.php.net/array_search
 */
function binary_search($needle, $haystack)
{
    $high = count($haystack);
    $low = 0;

    while (($high - $low) > 1) {
        $probe = floor(($high + $low) / 2);
        if ($haystack[$probe] < $needle) {
            $low = $probe;
        } elseif ($haystack[$probe] == $needle) {
            $high = $low = $probe;
        } else {
            $high = $probe;
        }
    }

    if ($high == count($haystack) || $haystack[$high] != $needle) {
        return false;
    } else {
        return $high;
    }
}

function is_localhost()
{
    return $_SERVER['SERVER_ADDR'] == '127.0.0.1';
}

/**
 * Take a string and quote it sufficiently to be passed as a Javascript
 * string between ''s
 */
function javascript_quote_string($s)
{
    return str_replace("'", "\'", $s);
}

function isSerialized($s)
{
    return (!empty($s) and (strlen($s) > 3) and (substr($s, 1, 1) == ':'));
}

/**
 * Determine if a variable represents a limit
 * It can be an integer or two integers separated by ","
 */

function is_limit($var)
{
    if (is_whole_number($var)) {
        return true;
    }
    $limits = explode(',', $var);
    return (count($limits) == 2)
       && is_whole_number($limits[0])
       && is_whole_number($limits[1]);
}

/**
 * Determine if a variable represents a whole number
 */

function is_whole_number($var)
{
    return (is_numeric($var) && (intval($var) == floatval($var)));
}

/**
 * Take a string and return an array of pairs (attribute name, attribute value)
 *
 * We allow attributes with or without double quotes (")
 * Attribute-value pairs may be separated by space or comma
 * Space is normal HTML attributes, comma is for RichTable compatibility
 * border=1, cellpadding="5"
 * border=1 cellpadding="5"
 * style="font-family: sans-serif; border-top:1px solid #dddddd;"
 * style="font-family: Verdana, Arial, Helvetica, sans-serif"
 */
function parse_attributes($line)
{

    $options = array();

    if (empty($line)) return $options;
    $line = trim($line);
    if (empty($line)) return $options;
    $line = trim($line, ",");
    if (empty($line)) return $options;

    // First we have an attribute name.

    $i = 0;
    while (($i < strlen($line)) && ($line[$i] != '=')) {
        $i++;
    }
    $attribute = substr($line, 0, $i);
    $attribute = strtolower($attribute);

    $line = substr($line, $i + 1);
    $line = trim($line);
    $line = trim($line, "=");
    $line = trim($line);

    if (empty($line)) return $options;

    // Then we have the attribute value.

    $i = 0;
    // Attribute value might be between double quotes
    // In that case we have to find the closing double quote
    if ($line[0] == '"') {
        $i++; // skip first '"'
        while (($i < strlen($line)) && ($line[$i] != '"')) {
            $i++;
        }
        $value = substr($line, 0, $i);
        $value = trim($value, '"');
        $value = trim($value);

        // If there are no double quotes, we have to find the next space or comma
    } else {
        while (($i < strlen($line)) && (($line[$i] != ' ') && ($line[$i] != ','))) {
            $i++;
        }
        $value = substr($line, 0, $i);
        $value = trim($value);
        $value = trim($value, ",");
        $value = trim($value);
    }

    $options[$attribute] = $value;

    $line = substr($line, $i + 1);
    $line = trim($line);
    $line = trim($line, ",");
    $line = trim($line);

    return $options + parse_attributes($line);
}

/**
 * Returns true if the filename ends with an image suffix.
 * Uses INLINE_IMAGES if defined, else "png|jpg|jpeg|gif|swf"
 */
function is_image($filename)
{

    if (defined('INLINE_IMAGES')) {
        $inline_images = INLINE_IMAGES;
    } else {
        $inline_images = "png|jpg|jpeg|gif|swf";
    }

    foreach (explode("|", $inline_images) as $suffix) {
        if (string_ends_with(strtolower($filename), "." . $suffix)) {
            return true;
        }
    }
    return false;
}

/**
 * Returns true if the filename ends with an video suffix.
 * Currently FLV, OGG, MP4 and WebM.
 */
function is_video($filename)
{

    return string_ends_with(strtolower($filename), ".flv")
        or string_ends_with(strtolower($filename), ".ogg")
        or string_ends_with(strtolower($filename), ".mp4")
        or string_ends_with(strtolower($filename), ".webm");
}

/**
 * Remove accents from given text.
 */
function strip_accents($text)
{
    $res = utf8_decode($text);
    $res = strtr($res,
        utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),
        'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    return utf8_encode($res);
}

/**
 * Sanify filename: replace all disallowed characters with dashes
 */
function sanify_filename($filename)
{
    return mb_ereg_replace('[^\w\. \-]', '-', $filename);
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
