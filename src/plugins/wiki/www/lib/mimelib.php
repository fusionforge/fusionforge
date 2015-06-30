<?php

/**
 * Routines for Mime mailification of pages.
 */

/**
 * Routines for quoted-printable en/decoding.
 */
function QuotedPrintableEncode($string)
{
    // Quote special characters in line.
    $quoted = "";
    while ($string) {
        // The complicated regexp is to force quoting of trailing spaces.
        preg_match('/^([ !-<>-~]*)(?:([!-<>-~]$)|(.))/s', $string, $match);
        $quoted .= $match[1] . $match[2];
        if (!empty($match[3]))
            $quoted .= sprintf("=%02X", ord($match[3]));
        $string = substr($string, strlen($match[0]));
    }
    // Split line.
    // This splits the line (preferably after white-space) into lines
    // which are no longer than 76 chars (after adding trailing '=' for
    // soft line break, but before adding \r\n.)
    return preg_replace('/(?=.{77})(.{10,74}[ \t]|.{71,73}[^=][^=])/s',
        "\\1=\r\n", $quoted);
}

function QuotedPrintableDecode($string)
{
    // Eliminate soft line-breaks.
    $string = preg_replace('/=[ \t\r]*\n/', '', $string);
    return quoted_printable_decode($string);
}

define('MIME_TOKEN_REGEXP', "[-!#-'*+.0-9A-Z^-~]+");

function MimeContentTypeHeader($type, $subtype, $params)
{
    $header = "Content-Type: $type/$subtype";
    reset($params);
    while (list($key, $val) = each($params)) {
        //FIXME:  what about non-ascii printables in $val?
        if (!preg_match('/^' . MIME_TOKEN_REGEXP . '$/', $val))
            $val = '"' . addslashes($val) . '"';
        $header .= ";\r\n  $key=$val";
    }
    return "$header\r\n";
}

function MimeMultipart($parts)
{
    global $mime_multipart_count;

    // The string "=_" can not occur in quoted-printable encoded data.
    $boundary = "=_multipart_boundary_" . ++$mime_multipart_count;

    $head = MimeContentTypeHeader('multipart', 'mixed',
        array('boundary' => $boundary));

    $sep = "\r\n--$boundary\r\n";

    return $head . $sep . implode($sep, $parts) . "\r\n--${boundary}--\r\n";
}

/**
 * For reference see:
 * http://www.nacs.uci.edu/indiv/ehood/MIME/2045/rfc2045.html
 * http://www.faqs.org/rfcs/rfc2045.html
 * (RFC 1521 has been superceeded by RFC 2045 & others).
 *
 * Also see http://www.faqs.org/rfcs/rfc2822.html
 *
 *
 * Notes on content-transfer-encoding.
 *
 * "7bit" means short lines of US-ASCII.
 * "8bit" means short lines of octets with (possibly) the high-order bit set.
 * "binary" means lines are not necessarily short enough for SMTP
 * transport, and non-ASCII characters may be present.
 *
 * Only "7bit", "quoted-printable", and "base64" are universally safe
 * for transport via e-mail.  (Though many MTAs can/will be configured to
 * automatically convert encodings to a safe type if they receive
 * mail encoded in '8bit' and/or 'binary' encodings.
 */

/**
 * @param WikiDB_Page $page
 * @param WikiDB_PageRevision $revision
 * @return string
 */

function MimeifyPageRevision(&$page, &$revision)
{
    // $wikidb =& $revision->_wikidb;
    // $page = $wikidb->getPage($revision->getName());
    // FIXME: add 'hits' to $params
    $params = array('pagename' => $page->getName(),
        'flags' => "",
        'author' => $revision->get('author'),
        'owner' => $page->getOwner(),
        'version' => $revision->getVersion(),
        'lastmodified' => $revision->get('mtime'));

    if ($page->get('mtime'))
        $params['created'] = $page->get('mtime');
    if ($page->get('locked'))
        $params['flags'] = 'PAGE_LOCKED';
    if (ENABLE_EXTERNAL_PAGES && $page->get('external'))
        $params['flags'] = ($params['flags'] ? $params['flags'] . ',EXTERNAL_PAGE' : 'EXTERNAL_PAGE');
    if ($revision->get('author_id'))
        $params['author_id'] = $revision->get('author_id');
    if ($revision->get('summary'))
        $params['summary'] = $revision->get('summary');
    if ($page->get('hits'))
        $params['hits'] = $page->get('hits');
    if ($page->get('owner'))
        $params['owner'] = $page->get('owner');
    if ($page->get('perm') and class_exists('PagePermission')) {
        $acl = getPagePermissions($page);
        $params['acl'] = $acl->asAclLines();
        //TODO: convert to multiple lines? acl-view => groups,...; acl-edit => groups,...
    }

    // Non-US-ASCII is not allowed in Mime headers (at least not without
    // special handling) --- so we urlencode all parameter values.
    foreach ($params as $key => $val)
        $params[$key] = rawurlencode($val);
    if (isset($params['acl']))
        // default: "view:_EVERY; edit:_AUTHENTICATED; create:_AUTHENTICATED,_BOGOUSER; ".
        //          "list:_EVERY; remove:_ADMIN,_OWNER; change:_ADMIN,_OWNER; dump:_EVERY; "
        $params['acl'] = str_replace(array("%3A", "%3B%20", "%2C"), array(":", "; ", ","), $params['acl']);

    $out = MimeContentTypeHeader('application', 'x-phpwiki', $params);
    $out .= sprintf("Content-Transfer-Encoding: %s\r\n",
        STRICT_MAILABLE_PAGEDUMPS ? 'quoted-printable' : 'binary');

    $out .= "\r\n";

    foreach ($revision->getContent() as $line) {
        // This is a dirty hack to allow saving binary text files. See above.
        $line = rtrim($line);
        if (STRICT_MAILABLE_PAGEDUMPS)
            $line = QuotedPrintableEncode(rtrim($line));
        $out .= "$line\r\n";
    }
    return $out;
}

/**
 * Routines for parsing Mime-ified phpwiki pages.
 */
function ParseRFC822Headers(&$string)
{
    if (preg_match("/^From (.*)\r?\n/", $string, $match)) {
        $headers['from '] = preg_replace('/^\s+|\s+$/', '', $match[1]);
        $string = substr($string, strlen($match[0]));
    }

    while (preg_match('/^([!-9;-~]+) [ \t]* : [ \t]* '
            . '( .* \r?\n (?: [ \t] .* \r?\n)* )/x',
        $string, $match)) {
        $headers[strtolower($match[1])]
            = preg_replace('/^\s+|\s+$/', '', $match[2]);
        $string = substr($string, strlen($match[0]));
    }

    if (empty($headers))
        return false;

    if (strlen($string) > 0) {
        if (!preg_match("/^\r?\n/", $string, $match)) {
            // No blank line after headers.
            return false;
        }
        $string = substr($string, strlen($match[0]));
    }

    return $headers;
}

function ParseMimeContentType($string)
{
    // FIXME: Remove (RFC822 style comments).

    // Get type/subtype
    if (!preg_match(':^\s*(' . MIME_TOKEN_REGEXP . ')\s*'
            . '/'
            . '\s*(' . MIME_TOKEN_REGEXP . ')\s*:x',
        $string, $match)
    )
        ExitWiki(sprintf("Bad %s", 'MIME content-type'));

    $type = strtolower($match[1]);
    $subtype = strtolower($match[2]);
    $string = substr($string, strlen($match[0]));

    $param = array();
    while (preg_match('/^;\s*(' . MIME_TOKEN_REGEXP . ')\s*=\s*'
            . '(?:(' . MIME_TOKEN_REGEXP . ')|"((?:[^"\\\\]|\\.)*)") \s*/sx',
        $string, $match)) {
        //" <--kludge for brain-dead syntax coloring
        if (strlen($match[2]))
            $val = $match[2];
        else
            $val = preg_replace('/[\\\\](.)/s', '\\1', $match[3]);

        $param[strtolower($match[1])] = $val;

        $string = substr($string, strlen($match[0]));
    }

    return array($type, $subtype, $param);
}

function ParseMimeMultipart($data, $boundary)
{
    if (!$boundary) {
        ExitWiki("No boundary?");
    }

    $boundary = preg_quote($boundary);

    while (preg_match("/^(|.*?\n)--$boundary((?:--)?)[^\n]*\n/s",
        $data, $match)) {
        $data = substr($data, strlen($match[0]));
        if (!isset($parts)) {
            $parts = array(); // First time through: discard leading chaff
        } else {
            if ($content = ParseMimeifiedPages($match[1]))
                for (reset($content); $p = current($content); next($content))
                    $parts[] = $p;
        }

        if ($match[2])
            return $parts; // End boundary found.
    }
    ExitWiki("No end boundary?");
}

function GenerateFootnotesFromRefs($params)
{
    $footnotes = array();
    reset($params);
    while (list($p, $reference) = each($params)) {
        if (preg_match('/^ref([1-9][0-9]*)$/', $p, $m))
            $footnotes[$m[1]] = sprintf(_("[%d] See [%s]"),
                $m[1], rawurldecode($reference));
    }

    if (sizeof($footnotes) > 0) {
        ksort($footnotes);
        return "-----\n"
            . "!" . _("References") . "\n"
            . join("\n%%%\n", $footnotes) . "\n";
    } else
        return "";
}

// counterpart to $acl->asAclLines() and rawurl undecode
// default: "view:_EVERY; edit:_AUTHENTICATED; create:_AUTHENTICATED,_BOGOUSER; ".
//          "list:_EVERY; remove:_ADMIN,_OWNER; change:_ADMIN,_OWNER; dump:_EVERY; "
function ParseMimeifiedPerm($string)
{
    if (!class_exists('PagePermission')) {
        return '';
    }
    $hash = array();
    foreach (explode(";", trim($string)) as $accessgroup) {
        list($access, $groupstring) = explode(":", trim($accessgroup));
        $access = trim($access);
        $groups = explode(",", trim($groupstring));
        foreach ($groups as $group) {
            $group = trim($group);
            $bool = (boolean)(substr($group, 0, 1) != '-');
            if (substr($group, 0, 1) == '-' or substr($group, 0, 1) == '+')
                $group = substr($group, 1);
            $hash[$access][$group] = $bool;
        }
    }
    $perm = new PagePermission($hash);
    $perm->sanify();
    return serialize($perm->perm);
}

// Convert references in meta-data to footnotes.
// Only zip archives generated by phpwiki 1.2.x or earlier should have
// references.
function ParseMimeifiedPages($data)
{
    // We may need a lot of memory and time for the dump
    ini_set("memory_limit", -1);
    ini_set('max_execution_time', 0);

    if (!($headers = ParseRFC822Headers($data))
        || empty($headers['content-type'])
    ) {
        //trigger_error( sprintf(_("Can't find %s"),'content-type header'),
        //               E_USER_WARNING );
        return false;
    }
    $typeheader = $headers['content-type'];

    if (!(list ($type, $subtype, $params) = ParseMimeContentType($typeheader))) {
        trigger_error(sprintf("Can't parse %s: (%s)",
                'content-type', $typeheader),
            E_USER_WARNING);
        return false;
    }
    if ("$type/$subtype" == 'multipart/mixed') {
        return ParseMimeMultipart($data, $params['boundary']);
    } elseif ("$type/$subtype" != 'application/x-phpwiki') {
        trigger_error(sprintf("Bad %s", "content-type: $type/$subtype"),
            E_USER_WARNING);
        return false;
    }

    // FIXME: more sanity checking?
    $page = array();
    $pagedata = array();
    $versiondata = array();
    if (isset($headers['date']))
        $pagedata['date'] = strtotime($headers['date']);

    //DONE: support owner and acl
    foreach ($params as $key => $value) {
        if (empty($value))
            continue;
        $value = rawurldecode($value);
        switch ($key) {
            case 'pagename':
            case 'version':
                $page[$key] = $value;
                break;
            case 'flags':
                if (preg_match('/PAGE_LOCKED/', $value))
                    $pagedata['locked'] = 'yes';
                if (ENABLE_EXTERNAL_PAGES && preg_match('/EXTERNAL_PAGE/', $value))
                    $pagedata['external'] = 'yes';
                break;
            case 'owner':
            case 'created':
            case 'hits':
                $pagedata[$key] = $value;
                break;
            case 'acl':
            case 'perm':
                if (class_exists('PagePermission')) {
                    $pagedata['perm'] = ParseMimeifiedPerm($value);
                }
                break;
            case 'lastmodified':
                $versiondata['mtime'] = $value;
                break;
            case 'author':
            case 'author_id':
            case 'summary':
            case 'pagetype':
                $versiondata[$key] = $value;
                break;
        }
    }

    // FIXME: do we need to try harder to find a pagename if we
    //        haven't got one yet?
    if (!isset($versiondata['author'])) {
        global $request;
        if (is_object($request)) {
            $user = $request->getUser();
            $versiondata['author'] = $user->getId(); //FIXME:?
        }
    }

    $encoding = strtolower($headers['content-transfer-encoding']);
    if ($encoding == 'quoted-printable')
        $data = QuotedPrintableDecode($data);
    else if ($encoding && $encoding != 'binary')
        ExitWiki(sprintf("Unknown %s", 'encoding type: $encoding'));

    $data .= GenerateFootnotesFromRefs($params);

    $page['content'] = preg_replace('/[ \t\r]*\n/', "\n", chop($data));
    $page['pagedata'] = $pagedata;
    $page['versiondata'] = $versiondata;

    return array($page);
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
