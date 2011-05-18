<?php //-*-php-*-
// $Id: Template.php 7964 2011-03-05 17:05:30Z vargenau $

require_once("lib/ErrorManager.php");


/** An HTML template.
 */
class Template
{
    /**
     * name optionally of form "theme/template" to include parent templates in children
     */
    function Template ($name, &$request, $args = false) {
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
            $this->_locals = array('CONTENT' => $args);
        else
            $this->_locals = array();
    }

    function _munge_input($template) {

        // Convert < ?plugin expr ? > to < ?php $this->_printPluginPI("expr"); ? >
        $orig[] = '/<\?plugin.*?\?>/se';
        $repl[] = "\$this->_mungePlugin('\\0')";

        // Convert < ?= expr ? > to < ?php $this->_print(expr); ? >
        $orig[] = '/<\?=(.*?)\?>/s';
        $repl[] = '<?php $this->_print(\1);?>';

        // Convert < ?php echo expr ? > to < ?php $this->_print(expr); ? >
        $orig[] = '/<\?php echo (.*?)\?>/s';
        $repl[] = '<?php $this->_print(\1);?>';

        return preg_replace($orig, $repl, $template);
    }

    function _mungePlugin($pi) {
        // HACK ALERT: PHP's preg_replace, with the /e option seems to
        // escape both single and double quotes with backslashes.
        // So we need to unescape the double quotes here...

        $pi = preg_replace('/(?!<\\\\)\\\\"/x', '"', $pi);
        return sprintf('<?php $this->_printPlugin(%s); ?>',
                       "'" . str_replace("'", "\'", $pi) . "'");
    }

    function _printPlugin ($pi) {
    include_once("lib/WikiPlugin.php");
    static $loader;

        if (empty($loader))
            $loader = new WikiPluginLoader;

        $this->_print($loader->expandPI($pi, $this->_request, $this, $this->_basepage));
    }

    function _print ($val) {
        if (isa($val, 'Template')) {
            $this->_expandSubtemplate($val);
        } else {
            PrintXML($val);
        }
    }

    function _expandSubtemplate (&$template) {
        // FIXME: big hack!
        //if (!$template->_request)
        //    $template->_request = &$this->_request;
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
     *
     * @access public
     *
     * @param $token string Name of token to substitute for.
     *
     * @param $replacement string Replacement HTML text.
     */
    function replace($varname, $value) {
        $this->_locals[$varname] = $value;
    }


    function printExpansion ($defaults = false) {
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

        global $WikiTheme, $charset;
        //$this->_dump_template();
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
    function getExpansion ($defaults = false) {
        ob_start();
        $this->printExpansion($defaults);
        $xml = ob_get_contents();
        ob_end_clean();     // PHP problem: Doesn't release its memory?
        return $xml;
    }

    function printXML () {
        $this->printExpansion();
    }

    function asXML () {
        return $this->getExpansion();
    }


    // Debugging:
    function _dump_template () {
        $lines = explode("\n", $this->_munge_input($this->_tmpl));
        $pre = HTML::pre();
        $n = 1;
        foreach ($lines as $line)
            $pre->pushContent(fmt("%4d  %s\n", $n++, $line));
        $pre->printXML();
    }

    function _errorHandler($error) {
        //if (!preg_match('/: eval\(\)\'d code$/', $error->errfile))
    //    return false;

        if (preg_match('/: eval\(\)\'d code$/', $error->errfile)) {
            $error->errfile = "In template '$this->_name'";
            // Hack alert: Ignore 'undefined variable' messages for variables
            //  whose names are ALL_CAPS.
            if (preg_match('/Undefined variable:\s*[_A-Z]+\s*$/', $error->errstr))
                return true;
        }
        // ignore recursively nested htmldump loop: browse -> body -> htmldump -> browse -> body ...
        // FIXME for other possible loops also
        elseif (strstr($error->errfile, "In template 'htmldump'")) {
            ; //return $error;
        }
        elseif (strstr($error->errfile, "In template '")) { // merge
            $error->errfile = preg_replace("/'(\w+)'\)$/", "'\\1' < '$this->_name')",
                                           $error->errfile);
        }
        else {
            $error->errfile .= " (In template '$this->_name')";
        }

        if (!empty($this->_tmpl)) {
            $lines = explode("\n", $this->_tmpl);
            if (isset($lines[$error->errline - 1]))
                $error->errstr .= ":\n\t" . $lines[$error->errline - 1];
        }
    return $error;
    }
};

/**
 * Get a templates
 *
 * This is a convenience function and is equivalent to:
 * <pre>
 *   new Template(...)
 * </pre>
 */
function Template($name, $args = false) {
    global $request;
    return new Template($name, $request, $args);
}

function alreadyTemplateProcessed($name) {
    global $request;
    return !empty($request->_TemplatesProcessed[$name]) ? true : false;
}
/**
 * Make and expand the top-level template.
 *
 *
 * @param $content mixed html content to put into the page
 * @param $title string page title
 * @param $page_revision object A WikiDB_PageRevision object or false
 * @param $args hash Extract args for top-level template
 *
 * @return string HTML expansion of template.
 */
function GeneratePage($content, $title, $page_revision = false, $args = false) {
    global $request;

    if (!is_array($args))
        $args = array();

    $args['CONTENT'] = $content;
    $args['TITLE'] = $title;
    $args['revision'] = $page_revision;

    if (!isset($args['HEADER']))
        $args['HEADER'] = $title;

    printXML(new Template('html', $request, $args));
}


/**
 * For dumping pages as html to a file.
 * Used for action=dumphtml,action=ziphtml,format=pdf,format=xml
 */
function GeneratePageasXML($content, $title, $page_revision = false, $args = false) {
    global $request;

    if (!is_array($args))
        $args = array();

    $content->_basepage = $title;
    $args['CONTENT'] = $content;
    $args['TITLE'] = SplitPagename($title);
    $args['revision'] = $page_revision;

    if (!isset($args['HEADER']))
        $args['HEADER'] = SplitPagename($title);

    global $HIDE_TOOLBARS, $NO_BASEHREF, $WikiTheme;
    $HIDE_TOOLBARS = true;
    if (!$WikiTheme->DUMP_MODE)
    $WikiTheme->DUMP_MODE = 'HTML';

    // FIXME: unfatal errors and login requirements
    $html = asXML(new Template('htmldump', $request, $args));

    $HIDE_TOOLBARS = false;
    //$WikiTheme->DUMP_MODE = false;
    return $html;
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
