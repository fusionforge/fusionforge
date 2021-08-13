<?php
/**
 * Copyright © 2001-2003 Jeff Dairiki
 * Copyright © 2001-2003 Carsten Klapp
 * Copyright © 2002,2004-2007 Reini Urban
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

require_once 'lib/ErrorManager.php';

/** An HTML template.
 */
class Template
{
    /**
     * name optionally of form "theme/template" to include parent templates in children
     *
     * @param string $name
     * @param WikiRequest $request
     * @param array $args
     */
    function __construct($name, &$request, $args = array())
    {
        global $WikiTheme;

        $this->_request =& $request;
        $this->_basepage = $request->getArg('pagename');

        if (strstr($name, "/")) {
            $oldname = $WikiTheme->_name;
            $oldtheme = $WikiTheme->_theme;
            list($themename, $name) = explode("/", $name);
            $WikiTheme->_theme = "themes/$themename";
            $WikiTheme->_name = $name;
        }
        $this->_name = $name;
        $file = $WikiTheme->findTemplate($name);
        if (!$file) {
            trigger_error("no template for $name found.", E_USER_WARNING);
            return;
        }
        if (isset($oldname)) {
            $WikiTheme->_name = $oldname;
            $WikiTheme->_theme = $oldtheme;
        }
        $fp = fopen($file, "rb");
        if (!$fp) {
            trigger_error("$file not found", E_USER_WARNING);
            return;
        }
        $request->_TemplatesProcessed[$name] = 1;
        $this->_tmpl = fread($fp, filesize($file));
        if ($fp) fclose($fp);
        //$userid = $request->_user->_userid;
        if (is_array($args))
            $this->_locals = $args;
        elseif ($args)
            $this->_locals = array('CONTENT' => $args); else
            $this->_locals = array();
    }

    private function _munge_input($template)
    {
        // Convert < ?plugin expr ? > to < ?php $this->_printPlugin("expr"); ? >
        $orig[] = '/<\?plugin.*?\?>/s';
        $repl[] = "<?php \$this->_printPlugin('\\0'); ?>";

        // Convert <<expr>> to < ?php $this->_printPlugin("expr"); ? >
        $orig[] = '/<<(.*?)>>/s';
        $repl[] = "<?php \$this->_printPlugin('<?plugin \\1 ?>'); ?>";

        // Convert < ?php echo expr ? > to < ?php $this->_print(expr); ? >
        $orig[] = '/<\?php echo (.*?)\?>/s';
        $repl[] = '<?php $this->_print(\1);?>';

        return preg_replace($orig, $repl, $template);
    }

    private function _printPlugin($pi)
    {
        include_once 'lib/WikiPlugin.php';
        static $loader;

        if (empty($loader))
            $loader = new WikiPluginLoader();

        $this->_print($loader->expandPI($pi, $this->_request, $this, $this->_basepage));
    }

    private function _print($val)
    {
        if (is_a($val, 'Template')) {
            $this->_expandSubtemplate($val);
        } else {
            PrintXML($val);
        }
    }

    private function _expandSubtemplate(&$template)
    {
        if (DEBUG) {
            echo "<!-- Begin $template->_name -->\n";
        }
        // Expand sub-template with defaults from this template.
        $template->printExpansion($this->_vars);
        if (DEBUG) {
            echo "<!-- End $template->_name -->\n";
        }
    }

    /**
     * Substitute HTML replacement text for tokens in template.
     *
     * Constructs a new WikiTemplate based upon the named template.
     * @param string $varname Name of token to substitute for.
     * @param string $value Replacement HTML text.
     */
    public function replace($varname, $value)
    {
        $this->_locals[$varname] = $value;
    }

    public function printExpansion($defaults = false)
    {
        if (!is_array($defaults)) // HTML object or template object
            $defaults = array('CONTENT' => $defaults);
        $this->_vars = array_merge($defaults, $this->_locals);
        extract($this->_vars);

        global $request;
        if (!isset($user))
            $user = $request->getUser();
        if (!isset($page))
            $page = $request->getPage();
        // Speedup. I checked all templates
        if (!isset($revision))
            $revision = false;

        global $WikiTheme;
        $SEP = $WikiTheme->getButtonSeparator();

        global $ErrorManager;
        $ErrorManager->pushErrorHandler(new WikiMethodCb($this, '_errorHandler'));

        eval('?>' . $this->_munge_input($this->_tmpl));

        $ErrorManager->popErrorHandler();
    }

    // FIXME (1.3.12)
    // Find a way to do template expansion less memory intensive and faster.
    // 1.3.4 needed no memory at all for dumphtml, now it needs +15MB.
    // Smarty? As before?
    public function getExpansion($defaults = false)
    {
        ob_start();
        $this->printExpansion($defaults);
        $xml = ob_get_contents();
        ob_end_clean(); // PHP problem: Doesn't release its memory?
        return $xml;
    }

    public function printXML()
    {
        $this->printExpansion();
    }

    public function asXML()
    {
        return $this->getExpansion();
    }

    public function _errorHandler($error)
    {
        if (preg_match('/: eval\(\)\'d code$/', $error->errfile)) {
            $error->errfile = "In template '$this->_name'";
            // Hack alert: Ignore 'undefined variable' messages for variables
            //  whose names are ALL_CAPS.
            if (preg_match('/Undefined variable:\s*[_A-Z]+\s*$/', $error->errstr))
                return true;
        } // ignore recursively nested htmldump loop: browse -> body -> htmldump -> browse -> body ...
        // FIXME for other possible loops also
        elseif (strstr($error->errfile, "In template 'htmldump'")) {
            ; //return $error;
        } elseif (strstr($error->errfile, "In template '")) { // merge
            $error->errfile = preg_replace("/'(\w+)'\)$/", "'\\1' < '$this->_name')",
                $error->errfile);
        } else {
            $error->errfile .= " (In template '$this->_name')";
        }

        if (!empty($this->_tmpl)) {
            $lines = explode("\n", $this->_tmpl);
            if (isset($lines[$error->errline - 1]))
                $error->errstr .= ":\n\t" . $lines[$error->errline - 1];
        }
        return $error;
    }
}

/**
 * Get a templates
 *
 * This is a convenience function and is equivalent to:
 * <pre>
 *   new Template(...)
 * </pre>
 */
function Template($name, $args = array())
{
    global $request;
    return new Template($name, $request, $args);
}

function alreadyTemplateProcessed($name)
{
    global $request;
    return !empty($request->_TemplatesProcessed[$name]) ? true : false;
}

/**
 * Make and expand the top-level template.
 *
 *
 * @param mixed $content html content to put into the page
 * @param string $title page title
 * @param object|bool $page_revision A WikiDB_PageRevision object or false
 * @param array $args hash Extract args for top-level template
 */
function GeneratePage($content, $title, $page_revision = false, $args = array())
{
    global $request;

    if (!is_array($args))
        $args = array();

    $args['CONTENT'] = $content;
    $args['TITLE'] = $title;
    $args['revision'] = $page_revision;

    if (!isset($args['HEADER']))
        $args['HEADER'] = $title;

    PrintXML(new Template('html', $request, $args));
}

/**
 * For dumping pages as html to a file.
 * Used for action=dumphtml,action=ziphtml,format=pdf,format=xml
 */
function GeneratePageAsXML($content, $title, $page_revision = null, $args = array())
{
    global $request;

    if (!is_array($args))
        $args = array();

    $content->_basepage = $title;
    $args['CONTENT'] = $content;
    $args['TITLE'] = SplitPagename($title);
    $args['revision'] = $page_revision;

    if (!isset($args['HEADER']))
        $args['HEADER'] = SplitPagename($title);

    global $HIDE_TOOLBARS, $WikiTheme;
    $HIDE_TOOLBARS = true;
    if (!$WikiTheme->DUMP_MODE)
        $WikiTheme->DUMP_MODE = 'HTML';

    // FIXME: unfatal errors and login requirements
    $html = AsXML(new Template('htmldump', $request, $args));

    $HIDE_TOOLBARS = false;
    //$WikiTheme->DUMP_MODE = false;
    return $html;
}
