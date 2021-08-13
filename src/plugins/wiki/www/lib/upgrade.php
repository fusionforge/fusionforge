<?php
/**
 * Copyright © 2004,2005,2006,2007 $ThePhpWikiProgrammingTeam
 * Copyright © 2008 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * Upgrade existing WikiDB and config settings after installing a new PhpWiki software version.
 * Status: almost no queries for verification.
 *         simple merge conflict resolution, or Overwrite All.
 *
 * Installation on an existing PhpWiki database needs some
 * additional worksteps. Each step will require multiple pages.
 *
 * This is the plan:
 *  1. Check for new or changed database schema and update it
 *     according to some predefined upgrade tables. (medium, complete)
 *  2. Check for new or changed (localized) pgsrc/ pages and ask
 *     for upgrading these. Check timestamps, upgrade silently or
 *     show diffs if existing. Overwrite or merge (easy, complete)
 *  3. Check for new or changed or deprecated index.php/config.ini settings
 *     and help in upgrading these. (for newer changes since 1.3.11, not yet)
 *   3a. Convert old-style index.php into config/config.ini. (easy, not yet)
 *  4. Check for changed plugin invocation arguments. (medium, done)
 *  5. Check for changed theme variables. (hard, not yet)
 *  6. Convert the single-request upgrade to a class-based multi-page
 *     version. (hard)
 *
 * Done: overwrite=1 link on edit conflicts at first occurence "Overwrite all".
 *
 * @author: Reini Urban
 */
require_once 'lib/loadsave.php';

class Upgrade
{
    public $_configUpdates;
    public $check_args;
    public $dbi;
    private $request;

    function __construct(&$request)
    {
        $this->request =& $request;
        $this->dbi =& $request->_dbi;
    }

    private function doPgsrcUpdate($pagename, $path, $filename)
    {
        // don't ever update the HomePage
        if ((defined(HOME_PAGE) and ($pagename == HOME_PAGE))
            or ($pagename == _("HomePage"))
            or ($pagename == "HomePage")
        ) {
            echo "$path/$pagename: " . _("always skip the HomePage") . " ... " . _("Skipped"), "<br />\n";
            return;
        }

        $page = $this->dbi->getPage($pagename);
        if ($page->exists()) {
            // check mtime: update automatically if pgsrc is newer
            $rev = $page->getCurrentRevision();
            $page_mtime = $rev->get('mtime');
            $data = implode("", file($path . "/" . $filename));
            if (($parts = ParseMimeifiedPages($data))) {
                usort($parts, 'SortByPageVersion');
                reset($parts);
                $pageinfo = $parts[0];
                $stat = stat($path . "/" . $filename);
                $new_mtime = 0;
                if (isset($pageinfo['versiondata']['mtime']))
                    $new_mtime = $pageinfo['versiondata']['mtime'];
                if (!$new_mtime and isset($pageinfo['versiondata']['lastmodified']))
                    $new_mtime = $pageinfo['versiondata']['lastmodified'];
                if (!$new_mtime and isset($pageinfo['pagedata']['date']))
                    $new_mtime = $pageinfo['pagedata']['date'];
                if (!$new_mtime)
                    $new_mtime = $stat[9];
                if ($new_mtime > $page_mtime) {
                    echo "$path/$pagename" . _(": ") . _("newer than the existing page")
                         . " ... " . _("Replace") . "<br />\n";
                    LoadAny($this->request, $path . "/" . $filename);
                    echo "<br />\n";
                } else {
                    echo "$path/$pagename" . _(": ") . _("older than the existing page")
                         . " ... " . _("Skipped"), "<br />\n";
                }
            } else {
                echo "$path/$pagename" . _(": ") . _("unknown format") . " ... " . _("Skipped") . "<br />\n";
            }
        } else {
            echo sprintf(_("%s does not exist"), $pagename), "<br />\n";
            LoadAny($this->request, $path . "/" . $filename);
            echo "<br />\n";
        }
    }

    public function CheckActionPageUpdate()
    {
        echo "<h2>", sprintf(_("Check for necessary %s updates"), _("Action Pages")), "</h2>\n";
        // 1.3.13 before we pull in all missing pages, we rename existing ones
        $this->_rename_page_helper("_AuthInfo", "DebugAuthInfo");
        $this->_rename_page_helper("Help/_AuthInfoPlugin", "Help/DebugAuthInfoPlugin");
        $this->_rename_page_helper("_GroupInfo", "DebugGroupInfo");
        $this->_rename_page_helper("Help/_GroupInfoPlugin", "Help/DebugGroupInfoPlugin");
        $this->_rename_page_helper("_BackendInfo", "DebugBackendInfo");
        $this->_rename_page_helper("Help/_BackendInfoPlugin", "Help/DebugBackendInfoPlugin");
        $this->_rename_page_helper("Help/_WikiTranslationPlugin", "Help/WikiTranslationPlugin");
        $this->_rename_page_helper("Help/Advice Mediawiki users", "Help/Advice for Mediawiki users");
        $this->_rename_page_helper("DebugInfo", "DebugBackendInfo");
        $this->_rename_page_helper("_GroupInfo", "GroupAuthInfo"); // never officially existed
        $this->_rename_page_helper("InterWikiKarte", "InterWikiListe"); // German only
        $this->_rename_page_helper("TemplateTalk", "Template/Talk");

        $path = findFile('pgsrc');
        $pgsrc = new FileSet($path);
        // most actionpages have the same name as the plugin
        $loc_path = findLocalizedFile('pgsrc');
        foreach ($pgsrc->getFiles() as $filename) {
            if (substr($filename, -1, 1) == '~') continue;
            if (substr($filename, -5, 5) == '.orig') continue;
            $pagename = urldecode($filename);
            if (isActionPage($pagename)) {
                $translation = __($pagename);
                if ($translation == $pagename)
                    $this->doPgsrcUpdate($pagename, $path, $filename);
                elseif (findLocalizedFile('pgsrc/' . urlencode($translation), 1))
                    $this->doPgsrcUpdate($translation, $loc_path, urlencode($translation));
                else
                    $this->doPgsrcUpdate($pagename, $path, $filename);
            }
        }
    }

    // see loadsave.php for saving new pages.
    public function CheckPgsrcUpdate()
    {
        // Check some theme specific pgsrc files (blog, wikilens, fusionforge, custom).
        // We check theme specific pgsrc first in case the page is present in both
        // theme specific and global pgsrc
        global $WikiTheme;
        $path = $WikiTheme->file("pgsrc");
        // TBD: the call to FileSet prints a warning:
        // Notice: Unable to open directory 'themes/MonoBook/pgsrc' for reading
        $themepgsrc = array();
        $pgsrc = new FileSet($path);
        if ($pgsrc->getFiles()) {
            echo "<h2>", sprintf(_("Check for necessary theme %s updates"),
                "pgsrc"), "</h2>\n";
            foreach ($pgsrc->getFiles() as $filename) {
                if (substr($filename, -1, 1) == '~') continue;
                if (substr($filename, -5, 5) == '.orig') continue;
                $pagename = urldecode($filename);
                $themepgsrc[] = $pagename;
                $this->doPgsrcUpdate($pagename, $path, $filename);
            }
        }

        echo "<h2>", sprintf(_("Check for necessary %s updates"), "pgsrc"), "</h2>\n";
        $translation = __("HomePage");
        if ($translation == "HomePage") {
            $path = findFile(WIKI_PGSRC);
        } else {
            $path = findLocalizedFile(WIKI_PGSRC);
        }
        $pgsrc = new FileSet($path);
        // fixme: verification, ...
        foreach ($pgsrc->getFiles() as $filename) {
            if (substr($filename, -1, 1) == '~') continue;
            if (substr($filename, -5, 5) == '.orig') continue;
            $pagename = urldecode($filename);
            if (!isActionPage($filename)) {
                // There're a lot of now unneeded pages around.
                // At first rename the BlaPlugin pages to Help/<pagename> and then to the update.
                $this->_rename_to_help_page($pagename);
                if (in_array($pagename, $themepgsrc)) {
                    echo sprintf(_('%s already checked in theme pgsrc'), $pagename).' ... '._('Skipped').'<br />';
                } else {
                    $this->doPgsrcUpdate($pagename, $path, $filename);
                }
            }
        }
    }

    private function _rename_page_helper($oldname, $pagename)
    {
        if ($this->dbi->isWikiPage($oldname) and !$this->dbi->isWikiPage($pagename)) {
            echo sprintf(_("rename %s to %s"), $oldname, $pagename), " ... ";
            if ($this->dbi->_backend->rename_page($oldname, $pagename)) {
                echo _("OK"), " <br />\n";
            } else {
                echo ' <span style="color: red; font-weight: bold;">' . _("FAILED") . "</span><br />\n";
            }
        }
    }

    private function _rename_to_help_page($pagename)
    {
        $newprefix = _("Help") . "/";
        if (substr($pagename, 0, strlen($newprefix)) != $newprefix)
            return;
        $oldname = substr($pagename, strlen($newprefix));
        $this->_rename_page_helper($oldname, $pagename);
    }

    /**
     * preg_replace over local file.
     * Only line-orientated matches possible.
     */
    public function fixLocalFile($match, $replace, $filename)
    {
        $o_filename = $filename;
        if (!file_exists($filename))
            $filename = findFile($filename);
        if (!file_exists($filename))
            return array(false, sprintf(_("File “%s” not found."), $o_filename));
        $found = false;
        if (is_writable($filename)) {
            $in = fopen($filename, "rb");
            $out = fopen($tmp = tempnam(getUploadFilePath(), "cfg"), "wb");
            if (isWindows())
                $tmp = str_replace("/", "\\", $tmp);
            // Detect the existing linesep at first line. fgets strips it even if 'rb'.
            // Before we simply assumed \r\n on Windows local files.
            $s = fread($in, 1024);
            rewind($in);
            $linesep = (substr_count($s, "\r\n") > substr_count($s, "\n")) ? "\r\n" : "\n";
            //$linesep = isWindows() ? "\r\n" : "\n";
            while ($s = fgets($in)) {
                // =>php-5.0.1 can fill count
                //$new = preg_replace($match, $replace, $s, -1, $count);
                $new = preg_replace($match, $replace, $s);
                if ($new != $s) {
                    $s = $new . $linesep;
                    $found = true;
                }
                fputs($out, $s);
            }
            fclose($in);
            fclose($out);
            if (!$found) {
                // todo: skip
                $reason = sprintf(_("%s not found in %s"), $match, $filename);
                unlink($out);
                return array(false, $reason);
            } else {
                @unlink($filename.".bak");
                @rename($filename, $filename.".bak");
                if (!rename($tmp, $filename))
                    return array(false, sprintf(_("couldn't move %s to %s"), $tmp, $filename));
                return true;
            }
        } else {
            return array(false, sprintf(_("file %s is not writable"), $filename));
        }
    }

    public function CheckConfigUpdate()
    {
        echo "<h2>", sprintf(_("Check for necessary %s updates"),
            "config.ini"), "</h2>\n";
        $entry = new UpgradeConfigEntry($this,
             array('key' => 'cache_control_none',
            'header' => sprintf(_("Check for %s"), "CACHE_CONTROL = NONE"),
            'applicable_args' => array('CACHE_CONTROL'),
            'notice' => _("CACHE_CONTROL is set to 'NONE', and must be changed to 'NO_CACHE'"),
            'check_args' => array("/^\s*CACHE_CONTROL\s*=\s*NONE/", "CACHE_CONTROL = NO_CACHE")));
        $entry->setApplicableCb(new WikiMethodCb($entry, '_applicable_defined_and_empty'));
        $this->_configUpdates[] = $entry;

        $entry = new UpgradeConfigEntry($this,
             array('key' => 'group_method_none',
            'header' => sprintf(_("Check for %s"), "GROUP_METHOD = NONE"),
            'applicable_args' => array('GROUP_METHOD'),
            'notice' => _("GROUP_METHOD is set to NONE, and must be changed to \"NONE\""),
            'check_args' => array("/^\s*GROUP_METHOD\s*=\s*NONE/", "GROUP_METHOD = \"NONE\"")));
        $entry->setApplicableCb(new WikiMethodCb($entry, '_applicable_defined_and_empty'));
        $this->_configUpdates[] = $entry;

        $entry = new UpgradeConfigEntry($this,
             array('key' => 'blog_empty_default_prefix',
            'header' => sprintf(_("Check for %s"), "BLOG_EMPTY_DEFAULT_PREFIX"),
            'applicable_args' => array('BLOG_EMPTY_DEFAULT_PREFIX'),
            'notice' => _("fix BLOG_EMPTY_DEFAULT_PREFIX into BLOG_DEFAULT_EMPTY_PREFIX"),
            'check_args' => array("/BLOG_EMPTY_DEFAULT_PREFIX\s*=/", "BLOG_DEFAULT_EMPTY_PREFIX =")));
        $entry->setApplicableCb(new WikiMethodCb($entry, '_applicable_defined'));
        $this->_configUpdates[] = $entry;

        // TODO: find extra file updates
        if (empty($this->_configUpdates))
            return;
        foreach ($this->_configUpdates as $update) {
            $update->check();
        }
    }

} // class Upgrade

class UpgradeEntry
{
    public $applicable_cb;
    public $header;
    public $method_cb;
    public $check_cb;
    public $reason;
    public /* array */ $applicable_args;
    public /* object */ $parent;
    private /* array */ $check_args;
    private /* string */ $notice;
    private /* string */ $_db_key;
    private $upgrade;

    /**
     * Add an upgrade item to be checked.
     *
     * @param object $parent The parent Upgrade class to inherit the version properties
     * @param array $params
     */
    function __construct(&$parent, $params)
    {
        $this->parent =& $parent;
        foreach (array('key' => 'required',
                     // the wikidb stores the version when we actually fixed that.
                     'header' => '', // always printed
                     'applicable_cb' => null, // method to check if applicable
                     'applicable_args' => array(), // might be the config name
                     'notice' => '',
                     'check_cb' => null, // method to apply
                     'check_args' => array())
                 as $k => $v) {
            if (!isset($params[$k])) { // default
                if ($v == 'required') trigger_error("Required arg $k missing", E_USER_ERROR);
                else $this->{$k} = $v;
            } else {
                $this->{$k} = $params[$k];
            }
        }
        if ($this->notice === '' and count($this->applicable_args) > 0)
            $this->notice = 'Check for ' . join(', ', $this->applicable_args);
        $this->_db_key = "_upgrade";
        $this->upgrade = $this->parent->dbi->get($this->_db_key);
    }

    /* needed ? */
    public function setApplicableCb($object)
    {
        $this->applicable_cb =& $object;
    }

    public function pass()
    {
        // store in db no to fix again
        $this->parent->dbi->set($this->_db_key, $this->upgrade);
        echo "<b>", _("FIXED"), "</b>";
        if (isset($this->reason))
            echo _(": "), $this->reason;
        echo "<br />\n";
        flush();
        return true;
    }

    public function fail()
    {
        echo '<span style="color: red; font-weight: bold; ">' . _("FAILED") . "</span>";
        if (isset($this->reason))
            echo _(": "), $this->reason;
        echo "<br />\n";
        flush();
        return false;
    }

    private function skip()
    { // not applicable
        if (isset($this->silent_skip))
            return true;
        echo " ... " . _("Skipped") . "<br />\n";
        flush();
        return true;
    }

    public function check($args = null)
    {
        if ($this->header) echo $this->header, ' ... ';
        if (is_object($this->applicable_cb)) {
            if (!$this->applicable_cb->call_array($this->applicable_args))
                return $this->skip();
        }
        if ($this->notice) {
            if ($this->header)
                echo "<br />\n";
            echo $this->notice, " ";
            flush();
        }
        if (!is_null($args)) $this->check_args =& $args;
        if (is_object($this->check_cb))
            $do = $this->method_cb->call_array($this->check_args);
        else
            $do = $this->default_method($this->check_args);
        if (is_array($do)) {
            $this->reason = $do[1];
            $do = $do[0];
        }
        return $do ? $this->pass() : $this->fail();
    }
} // class UpgradeEntry

class UpgradeConfigEntry extends UpgradeEntry
{
    public function _applicable_defined()
    {
        return defined($this->applicable_args[0]);
    }

    public function _applicable_defined_and_empty()
    {
        $const = $this->applicable_args[0];
        return defined($const) and !constant($const);
    }

    public function default_method($args)
    {
        $match = $args[0];
        $replace = $args[1];
        return $this->parent->fixLocalFile($match, $replace, "config/config.ini");
    }
} // class UpdateConfigEntry

/** entry function from lib/main.php
 */
function DoUpgrade(&$request)
{

    if (!$request->_user->isAdmin()) {
        $request->_notAuthorized(WIKIAUTH_ADMIN);
        $request->finish(
            HTML::div(array('class' => 'disabled-plugin'),
                fmt("Upgrade disabled: user != isAdmin")));
        return;
    }
    // TODO: StartLoadDump should turn on implicit_flush.
    @ini_set("implicit_flush", true);
    StartLoadDump($request, _("Upgrading this PhpWiki"));
    $upgrade = new Upgrade($request);
    if (!$request->getArg('nopgsrc')) {
        $upgrade->CheckPgsrcUpdate();
        $upgrade->CheckActionPageUpdate();
    }
    if (!$request->getArg('noconfig')) {
        $upgrade->CheckConfigUpdate();
    }
    EndLoadDump($request);
}
