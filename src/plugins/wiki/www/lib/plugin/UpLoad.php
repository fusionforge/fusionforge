<?php
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
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * UpLoad:  Allow Administrator to upload files to a special directory,
 *          which should preferably be added to the InterWikiMap
 * Usage:   <<UpLoad>>
 * Author:  NathanGass <gass@iogram.ch>
 * Changes: ReiniUrban <rurban@x-ray.at>,
 *          qubit <rtryon@dartmouth.edu>
 *          Marc-Etienne Vargenau, Alcatel-Lucent
 */

class WikiPlugin_UpLoad
    extends WikiPlugin
{
    public $allowed_extensions;
    public $disallowed_extensions;
    // TODO: use PagePerms instead
    public $only_authenticated = true; // allow only authenticated users may upload.

    function getDescription()
    {
        return _("Upload files to the local InterWiki [[Upload:filename]]");
    }

    function getDefaultArguments()
    {
        return array('logfile' => 'phpwiki-upload.log',
            // add a link of the fresh file automatically to the
            // end of the page (or current page)
            'autolink' => true,
            'page' => '[pagename]',
            'mode' => 'actionpage', // or edit
        );
    }

    /**
     * @param WikiDB $dbi
     * @param string $argstr
     * @param WikiRequest $request
     * @param string $basepage
     * @return mixed
     */
    function run($dbi, $argstr, &$request, $basepage)
    {
        $this->allowed_extensions = explode(",",
            "7z,avi,bmp,bz2,c,cfg,diff,doc,docx,flv,gif,h,ics,ini,".
            "jpeg,jpg,kmz,mp3,odg,odp,ods,odt,ogg,patch,pdf,png,ppt,".
            "pptx,rar,svg,tar,tar.gz,txt,xls,xlsx,xml,xsd,zip");
        $this->disallowed_extensions = explode(",",
            "ad[ep],asd,ba[st],chm,cmd,com,cgi,cpl,crt,dll,eml,exe,".
            "hlp,hta,in[fs],isp,jse?,lnk,md[betw],ms[cipt],nws,ocx,".
            "ops,pcd,p[ir]f,php\d?,phtml,pl,py,reg,sc[frt],sh[bsm]?,".
            "url,vb[esx]?,vxd,ws[cfh]");
        //removed "\{[[:xdigit:]]{8}(?:-[[:xdigit:]]{4}){3}-[[:xdigit:]]{12}\}"

        $args = $this->getArgs($argstr, $request);
        extract($args);

        $file_dir = getUploadFilePath();

        $form = HTML::form(array('action' => $request->getPostURL(),
            'enctype' => 'multipart/form-data',
            'method' => 'post'));
        $contents = HTML::div(array('class' => 'wikiaction'));
        $contents->pushContent(HTML::input(array('type' => 'hidden',
            'name' => 'MAX_FILE_SIZE',
            'value' => MAX_UPLOAD_SIZE)));
        $contents->pushContent(HTML::input(array('name' => 'userfile',
            'required' => 'required',
            'type' => 'file')));
        if ($mode == 'edit') {
            $contents->pushContent(HTML::input(array('name' => 'action',
                'type' => 'hidden',
                'value' => 'edit')));
            $contents->pushContent(HTML::raw(" "));
            $contents->pushContent(HTML::input(array('value' => _("Upload"),
                'name' => 'edit[upload]',
                'type' => 'submit')));
        } else {
            $contents->pushContent(HTML::raw(" "));
            $contents->pushContent(HTML::input(array('value' => _("Upload"),
                'type' => 'submit')));
        }
        $form->pushContent($contents);

        $message = HTML();
        if ($request->isPost() and $this->only_authenticated) {
            // Make sure that the user is logged in.
            $user = $request->getUser();
            if (!$user->isAuthenticated()) {
                if (defined('FUSIONFORGE') && FUSIONFORGE) {
                    $message->pushContent(HTML::div(array('class' => 'error'),
                        HTML::p(_("You cannot upload files.")),
                        HTML::ul(
                            HTML::li(_("Check you are logged in.")),
                            HTML::li(_("Check you are in the right project.")),
                            HTML::li(_("Check you are a member of the current project."))
                        )
                    ));
                } else {
                    $message->pushContent(HTML::p(array('class' => 'error'),
                        _("ACCESS DENIED: You must log in to upload files.")));
                }
                return HTML($message, $form);
            }
        }

        $userfile = $request->getUploadedFile('userfile');
        if ($userfile) {
            $userfile_name = $userfile->getName();
            $userfile_name = trim(basename($userfile_name));
            if (UPLOAD_USERDIR) {
                $username = $request->_user->_userid;
                $file_dir .= $username;
                $file_dir .= "/";
                // $userfile_name = $request->_user->_userid . "/" . $userfile_name;
            }
            $trimmed_file_dir = rtrim($file_dir, '/');

            if (file_exists($trimmed_file_dir) && !is_dir($trimmed_file_dir)) {
                $message->pushContent(HTML::p(array('class' => 'error'), fmt("Cannot upload, “%s” is not a directory.", $trimmed_file_dir)));
                return HTML($message, $form);
            }
            if (!file_exists($trimmed_file_dir) && !@mkdir($file_dir, 0775)) {
                $message->pushContent(HTML::p(array('class' => 'error'), fmt("Cannot create upload directory “%s”.", $file_dir)));
                return HTML($message, $form);
            }
            if (!is_writable($trimmed_file_dir)) {
                $message->pushContent(HTML::p(array('class' => 'error'), fmt("Cannot upload, “%s” is not writable.", $file_dir)));
                return HTML($message, $form);
            }

            $userfile_tmpname = $userfile->getTmpName();
            $err_header = HTML::div(array('class' => 'error'), HTML::p(fmt("Error uploading “%s”", $userfile_name)));
            if (preg_match("/(\." . join("|\.", $this->disallowed_extensions) . ")(\.|\$)/i", $userfile_name)) {
                $err_header->pushContent(HTML::p(fmt("Files with extension %s are not allowed.",
                    join(", ", $this->disallowed_extensions))));
                $message->pushContent($err_header);
                return HTML($message, $form);
            }
            if (!DISABLE_UPLOAD_ONLY_ALLOWED_EXTENSIONS and
                !preg_match("/(\." . join("|\.", $this->allowed_extensions) . ")\$/i", $userfile_name)) {
                $err_header->pushContent(HTML::p(fmt("Only files with the extension %s are allowed.",
                    join(", ", $this->allowed_extensions))));
                $message->pushContent($err_header);
                return HTML($message, $form);
            }
            if ($userfile->getSize() > (MAX_UPLOAD_SIZE)) {
                $err_header->pushContent(HTML::p(_("Sorry but this file is too big.")));
                $message->pushContent($err_header);
                return HTML($message, $form);
            }

            $sanified_userfile_name = sanify_filename($userfile_name);

            if (preg_match("/[^._a-zA-Z0-9- ]/", strip_accents($sanified_userfile_name))) {
                $err_header->pushContent(HTML::p(_("Invalid filename.")));
                $message->pushContent($err_header);
                return HTML($message, $form);
            }

            if (file_exists($file_dir . $sanified_userfile_name)) {
                $err_header->pushContent(HTML::p(fmt("There is already a file with name “%s” uploaded.", $sanified_userfile_name)));
                $message->pushContent($err_header);
                return HTML($message, $form);
            }
            if (move_uploaded_file($userfile_tmpname, $file_dir . $sanified_userfile_name) or
                (IsWindows() and rename($userfile_tmpname, $file_dir . $sanified_userfile_name))) {
                $interwiki = new PageType_interwikimap();
                if (UPLOAD_USERDIR) {
                    $link = $interwiki->link("[[Upload:$username/$sanified_userfile_name]]");
                } else {
                    $link = $interwiki->link("[[Upload:$sanified_userfile_name]]");
                }
                if ($sanified_userfile_name != $userfile_name) {
                    $message->pushContent(HTML::div(array('class' => 'feedback'),
                        HTML::p(_("File successfully uploaded.")),
                        HTML::p($link),
                        HTML::p(_("Note: some forbidden characters in filename have been replaced by dash."))));
                } else {
                    $message->pushContent(HTML::div(array('class' => 'feedback'),
                        HTML::p(_("File successfully uploaded.")),
                        HTML::p($link)));
                }
                // the upload was a success and we need to mark this event in the "upload log"
                if ($logfile) {
                    $upload_log = $file_dir . basename($logfile);
                    $this->log($userfile, $upload_log, $message);
                }
                if ($autolink) {
                    require_once 'lib/loadsave.php';
                    $pagehandle = $dbi->getPage($page);
                    if ($pagehandle->exists()) { // don't replace default contents
                        $current = $pagehandle->getCurrentRevision();
                        $version = $current->getVersion();
                        $text = $current->getPackedContent();
                        // don't inline images
                        if (UPLOAD_USERDIR) {
                            $newtext = $text . "\n* [[Upload:$username/$sanified_userfile_name]]";
                        } else {
                            $newtext = $text . "\n* [[Upload:$sanified_userfile_name]]";
                        }
                        $meta = $current->_data;
                        if (UPLOAD_USERDIR) {
                            $meta['summary'] = sprintf(_("uploaded %s"), $username.'/'.$sanified_userfile_name);
                        } else {
                            $meta['summary'] = sprintf(_("uploaded %s"), $sanified_userfile_name);
                        }
                        $pagehandle->save($newtext, $version + 1, $meta);
                    }
                }
            } else {
                $err_header->pushContent(HTML::p(_("Uploading failed.")));
                $message->pushContent($err_header);
            }
        }

        return HTML($message, $form);
    }

    private function log($userfile, $upload_log, &$message)
    {
        global $WikiTheme;
        /**
         * @var WikiRequest $request
         */
        global $request;

        $user = $request->_user;
        if (file_exists($upload_log) and (!is_writable($upload_log))) {
            trigger_error(_("The upload logfile exists but is not writable."), E_USER_WARNING);
        } elseif (!$log_handle = fopen($upload_log, "a")) {
            trigger_error(_("Can't open the upload logfile."), E_USER_WARNING);
        } else { // file size in KB; precision of 0.1
            $file_size = round(($userfile->getSize()) / 1024, 1);
            if ($file_size <= 0) {
                $file_size = "&lt; 0.1";
            }
            $userfile_name = $userfile->getName();
            fwrite($log_handle,
                "\n"
                    . "<tr><td><a href=\"$userfile_name\">$userfile_name</a></td>"
                    . "<td class=\"align-right\">$file_size kB</td>"
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
