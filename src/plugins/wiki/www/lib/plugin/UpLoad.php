<?php // -*-php-*-
// rcs_id('$Id: UpLoad.php 7659 2010-08-31 14:55:29Z vargenau $');
/*
 * Copyright 2003,2004,2007 $ThePhpWikiProgrammingTeam
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 */

/**
 * UpLoad:  Allow Administrator to upload files to a special directory,
 *          which should preferably be added to the InterWikiMap
 * Usage:   <<UpLoad >>
 * Author:  NathanGass <gass@iogram.ch>
 * Changes: ReiniUrban <rurban@x-ray.at>,
 *          qubit <rtryon@dartmouth.edu>
 *          Marc-Etienne Vargenau, Alcatel-Lucent
 * Note:    See also Jochen Kalmbach's plugin/UserFileManagement.php
 */

class WikiPlugin_UpLoad
extends WikiPlugin
{
    var $disallowed_extensions;
    // TODO: use PagePerms instead
    var $only_authenticated = true; // allow only authenticated users may upload.

    function getName () {
        return "UpLoad";
    }

    function getDescription () {
        return _("Upload files to the local InterWiki Upload:<filename>");
    }

    function getDefaultArguments() {
        return array('logfile'  => 'phpwiki-upload.log',
                     // add a link of the fresh file automatically to the
                     // end of the page (or current page)
                     'autolink' => true,
                     'page'     => '[pagename]',
                     'size'     => 50,
                     'mode'     => 'actionpage', // or edit
                     );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $this->allowed_extensions = explode("\n",
"7z
avi
bmp
bz2
c
cfg
diff
doc
docx
flv
gif
h
ics
ini
jpeg
jpg
kmz
mp3
odg
odp
ods
odt
ogg
patch
pdf
png
ppt
pptx
rar
svg
tar
tar.gz
txt
xls
xlsx
xml
xsd
zip");
        $this->disallowed_extensions = explode("\n",
"ad[ep]
asd
ba[st]
chm
cmd
com
cgi
cpl
crt
dll
eml
exe
hlp
hta
in[fs]
isp
jse?
lnk
md[betw]
ms[cipt]
nws
ocx
ops
pcd
p[ir]f
php\d?
phtml
pl
py
reg
sc[frt]
sh[bsm]?
swf
url
vb[esx]?
vxd
ws[cfh]");
        //removed "\{[[:xdigit:]]{8}(?:-[[:xdigit:]]{4}){3}-[[:xdigit:]]{12}\}"

        $args = $this->getArgs($argstr, $request);
        extract($args);

        $file_dir = getUploadFilePath();
        $file_dir .= "/";
        $form = HTML::form(array('action'  => $request->getPostURL(),
                                 'enctype' => 'multipart/form-data',
                                 'method'  => 'post'));
        $contents = HTML::div(array('class' => 'wikiaction'));
        $contents->pushContent(HTML::input(array('type' => 'hidden',
                                                 'name' => 'MAX_FILE_SIZE',
                                                 'value'=> MAX_UPLOAD_SIZE)));
        $contents->pushContent(HTML::input(array('name' => 'userfile',
                                                 'type' => 'file',
                                                 'size' => $size)));
        if ($mode == 'edit') {
            $contents->pushContent(HTML::input(array('name' => 'action',
                                                     'type' => 'hidden',
                                                     'value'=> 'edit')));
            $contents->pushContent(HTML::raw(" "));
            $contents->pushContent(HTML::input(array('value' => _("Upload"),
                                                     'name'  => 'edit[upload]',
                                                     'type'  => 'submit')));
        } else {
            $contents->pushContent(HTML::raw(" "));
            $contents->pushContent(HTML::input(array('value' => _("Upload"),
                                                     'type'  => 'submit')));
        }
        $form->pushContent($contents);

        $message = HTML();
        if ($request->isPost() and $this->only_authenticated) {
            // Make sure that the user is logged in.
            $user = $request->getUser();
            if (!$user->isAuthenticated()) {
                if (defined('FUSIONFORGE') and FUSIONFORGE) {
                    $message->pushContent(HTML::div(array('class' => 'error'),
                                            HTML::p(_("You cannot upload files.")),
                                            HTML::ul(
                                              HTML::li(_("Check you are logged in.")),
                                              HTML::li(_("Check you are in the right project.")),
                                              HTML::li(_("Check you are a member of the current project."))
                                            )
                                         ));
                } else {
                    $message->pushContent(HTML::div(array('class' => 'error'),
                                            HTML::p(_("ACCESS DENIED: You must log in to upload files."))));
                }
                $result = HTML();
                $result->pushContent($form);
                $result->pushContent($message);
                return $result;
            }
        }

        $userfile = $request->getUploadedFile('userfile');
        if ($userfile) {
            $userfile_name = $userfile->getName();
            $userfile_name = trim(basename($userfile_name));
            if (UPLOAD_USERDIR) {
                $file_dir .= $request->_user->_userid;
                if (!file_exists($file_dir))
                    mkdir($file_dir, 0775);
                $file_dir .= "/";
                $u_userfile = $request->_user->_userid . "/" . $userfile_name;
            } else {
                $u_userfile = $userfile_name;
            }
            $u_userfile = preg_replace("/ /", "%20", $u_userfile);
            $userfile_tmpname = $userfile->getTmpName();
            $err_header = HTML::div(array('class' => 'error'),
                                HTML::p(fmt("ERROR uploading '%s'", $userfile_name)));
            if (preg_match("/(\." . join("|\.", $this->disallowed_extensions) . ")(\.|\$)/i",
                           $userfile_name))
            {
                    $message->pushContent($err_header);
                $message->pushContent(HTML::p(fmt("Files with extension %s are not allowed.",
                                              join(", ", $this->disallowed_extensions))));
            }
            elseif (! DISABLE_UPLOAD_ONLY_ALLOWED_EXTENSIONS and
                    ! preg_match("/(\." . join("|\.", $this->allowed_extensions) . ")\$/i",
                               $userfile_name))
            {
                    $message->pushContent($err_header);
                $message->pushContent(HTML::p(fmt("Only files with the extension %s are allowed.",
                                              join(", ", $this->allowed_extensions))));
            }
            elseif (preg_match("/[^._a-zA-Z0-9- ]/", strip_accents($userfile_name)))
            {
                    $message->pushContent($err_header);
                $message->pushContent(HTML::p(_("Invalid filename. File names may only contain alphanumeric characters and dot, underscore, space or dash.")));
            }
            elseif (file_exists($file_dir . $userfile_name)) {
                    $message->pushContent($err_header);
                $message->pushContent(HTML::p(fmt("There is already a file with name %s uploaded.",
                                                  $u_userfile)));
            }
            elseif ($userfile->getSize() > (MAX_UPLOAD_SIZE)) {
                    $message->pushContent($err_header);
                $message->pushContent(HTML::p(_("Sorry but this file is too big.")));
            }
            elseif (move_uploaded_file($userfile_tmpname, $file_dir . $userfile_name) or
                    (IsWindows() and rename($userfile_tmpname, $file_dir . $userfile_name))
                    )
            {
                    $interwiki = new PageType_interwikimap();
                $link = $interwiki->link("Upload:$u_userfile");
                $message->pushContent(HTML::div(array('class' => 'feedback'),
                                                HTML::p(_("File successfully uploaded.")),
                                                HTML::p($link)));

                // the upload was a success and we need to mark this event in the "upload log"
                if ($logfile) {
                    $upload_log = $file_dir . basename($logfile);
                    $this->log($userfile, $upload_log, $message);
                }
                if ($autolink) {
                    require_once("lib/loadsave.php");
                    $pagehandle = $dbi->getPage($page);
                    if ($pagehandle->exists()) {// don't replace default contents
                        $current = $pagehandle->getCurrentRevision();
                        $version = $current->getVersion();
                        $text = $current->getPackedContent();
                        $newtext = $text . "\n* Upload:$u_userfile"; // don't inline images
                        $meta = $current->_data;
                        $meta['summary'] = sprintf(_("uploaded %s"),$u_userfile);
                        $pagehandle->save($newtext, $version + 1, $meta);
                    }
                }
            } else {
                    $message->pushContent($err_header);
                $message->pushContent(HTML::br(),_("Uploading failed."),HTML::br());
            }
        }
        else {
            $message->pushContent(HTML::br(),_("No file selected. Please select one."),HTML::br());
        }

        //$result = HTML::div( array( 'class' => 'wikiaction' ) );
        $result = HTML();
        $result->pushContent($form);
        $result->pushContent($message);
        return $result;
    }

    function log ($userfile, $upload_log, &$message) {
            global $WikiTheme;
            $user = $GLOBALS['request']->_user;
        if (file_exists($upload_log) and (!is_writable($upload_log))) {
            trigger_error(_("The upload logfile exists but is not writable."), E_USER_WARNING);
        }
        elseif (!$log_handle = fopen ($upload_log, "a")) {
            trigger_error(_("Can't open the upload logfile."), E_USER_WARNING);
        }
        else {        // file size in KB; precision of 0.1
            $file_size = round(($userfile->getSize())/1024, 1);
            if ($file_size <= 0) {
                $file_size = "&lt; 0.1";
            }
            $userfile_name = $userfile->getName();
            fwrite($log_handle,
                   "\n"
                   . "<tr><td><a href=\"$userfile_name\">$userfile_name</a></td>"
                   . "<td align=\"right\">$file_size kB</td>"
                   . "<td>&nbsp;&nbsp;" . $WikiTheme->formatDate(time()) . "</td>"
                   . "<td>&nbsp;&nbsp;<em>" . $user->getId() . "</em></td></tr>");
            fclose($log_handle);
        }
        return;
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
