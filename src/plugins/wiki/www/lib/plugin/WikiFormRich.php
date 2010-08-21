<?php // -*-php-*-
// rcs_id('$Id: WikiFormRich.php 7638 2010-08-11 11:58:40Z vargenau $');
/*
 * Copyright 2004,2006,2007 $ThePhpWikiProgrammingTeam
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
 * This is another replacement for MagicPhpWikiURL forms.
 * Previously encoded with the "phpwiki:" syntax.
 *
 * Enhanced WikiForm to be more generic:
 * - editbox[]                 name=.. value=.. text=.. autocomplete=1
 * - checkbox[]         name=.. value=0|1 checked text=..
 * - radio[]                 name=.. value=.. text=..
 * - pulldown[]                name=.. value=.. selected=.. text=.. autocomplete=1
 * - combobox[]                name=.. value=.. text=.. method=.. args=..
 * - hidden[]                name=.. value=..
 * - submit[]
 * - action, submit buttontext, optional cancel button (bool)
 * - method=get or post, Default: post.

 * Valid arguments for pulldown and editbox: autocomplete=1, Default: 0
 * If autocomplete=1, additional arguments method and args may be used.
 * If no method is given, value will be used to fill in the valid values.
 * method="xmlrpc:server:name" or "url:http://server/wiki/method" or "array:jsvariable"
 * or "plugin:pluginname"
 * args are optional arguments, space seperated, for the method.
 * A combobox is a pulldown with autocomplete=1.
 *
 * @Author: Reini Urban

 * Values which are constants are evaluated.
 * The cancel button must be supported by the action.
 *   (just some wikiadmin actions so far)
 * improve layout by: nobr=1
 * some allow values as list from from <!plugin-list !>

 Samples:
   <<WikiFormRich action=dumpserial method=get
            checkbox[] name=include value="all"
            editbox[] name=directory value=DEFAULT_DUMP_DIR
            editbox[] name=pages value=*
            editbox[] name=exclude value="" >>
   <<WikiFormRich action=dumphtml method=get
            editbox[] name=directory value=HTML_DUMP_DIR
            editbox[] name=pages value="*"
            editbox[] name=exclude value="" >>
   <<WikiFormRich action=loadfile method=get
            editbox[]  name=source value=DEFAULT_WIKI_PGSRC
            checkbox[] name=overwrite value=1
            editbox[]  name=exclude value="" >>
  <<WikiFormRich action=TitleSearch method=get class=wikiadmin nobr=1
             editbox[] name=s text=""
           submit[]
             checkbox[] name=case_exact
             checkbox[] name=regex >>
  <<WikiFormRich action=FullTextSearch method=get class=wikiadmin nobr=1
             editbox[] name=s text=""
           submit[]
             checkbox[] name=case_exact
             checkbox[] name=regex >>
  <<WikiFormRich action=FuzzyPages method=get class=wikiadmin nobr=1
             editbox[] name=s text=""
           submit[]
             checkbox[] name=case_exact ?>
  <<WikiFormRich action=AppendText buttontext="AddPlugin"
             radio[] name=s value=<!plugin-list BackLinks page=WikiPlugin limit=10 !>
             ?>
  <<WikiFormRich action=AppendText buttontext="AddPlugin"
             pulldown[] name=s text="Plugins: " value=<!plugin-list BackLinks page=WikiPlugin !>
             ?>
  <<WikiFormRich action=AppendText buttontext="AddCategory"
             pulldown[] name=s text="Categories: " value=<!plugin-list TitleSearch s=Category !>
             ?>
  <<WikiFormRich action=SemanticSearch buttontext="AddRelation"
             combobox[] name=relation text="Relation: " method=listRelations
             ?>
  <<WikiFormRich action=AppendText buttontext="InsertTemplate"
             combobox[] name=s text="Template: " method=titleSearch args="Template/"
             ?>
*/

class WikiPlugin_WikiFormRich
extends WikiPlugin
{
    function getName () {
        return "WikiFormRich";
    }
    function getDescription () {
        return _("Provide generic WikiForm input buttons");
    }
    function getDefaultArguments() {
        return array('action' => false,     // required argument
                     'method' => 'post',    // or get
                     'class'  => 'wikiaction',
                     'buttontext' => false, // for the submit button. default: action
                     'cancel' => false,     // boolean if the action supports cancel also
                     'nobr' => false,       // "no break": linebreaks or not
                     );
    }

    /* TODO: support better block alignment: <br>, tables, indent
     */
    function handle_plugin_args_cruft($argstr, $args) {
            $allowed = array("editbox", "hidden", "checkbox", "radiobutton"/*deprecated*/,
                             "radio", "pulldown", "submit", "reset", "combobox");
            // no editbox[] = array(...) allowed (space)
            $arg_array = preg_split("/\n/", $argstr);
            // for security we should check this better
        $arg = '';
            for ($i = 0; $i < count($arg_array); $i++) {
                //TODO: we require an name=value pair here, but submit may go without also.
                if (preg_match("/^\s*(".join("|",$allowed).")\[\](.*)$/", $arg_array[$i], $m)) {
                        $name = $m[1]; // one of the allowed input types
                $this->inputbox[][$name] = array(); $j = count($this->inputbox) - 1;
                $curargs = trim($m[2]);
                // must match name=NAME and also value=<!plugin-list name !>
                while (preg_match("/^(\w+?)=((?:\".*?\")|(?:\w+)|(?:\"?<!plugin-list.+?!>\"?))\s*/",
                                  $curargs, $m)) {
                    $attr = $m[1]; $value = $m[2];
                    $curargs = substr($curargs, strlen($m[0]));
                    if (preg_match("/^\"(.*)\"$/", $value, $m))
                        $value = $m[1];
                    if (in_array($name, array("pulldown","checkbox","radio","radiobutton","combobox"))
                            and preg_match('/^<!plugin-list.+!>$/', $value, $m))
                        // like pulldown[] name=test value=<!plugin-list BackLinks page=HomePage!>
                    {
                            $loader = new WikiPluginLoader();
                            $markup = null;
                            $basepage = null;
                            $plugin_str = preg_replace(array("/^<!/","/!>$/"),array("<?","?>"), $value);
                            // will return a pagelist object! pulldown,checkbox,radiobutton
                            $value = $loader->expandPI($plugin_str, $GLOBALS['request'], $markup, $basepage);
                            if (isa($value, 'PageList'))
                                $value = $value->pageNames(); // apply limit
                            elseif (!is_array($value))
                                    trigger_error(sprintf("Invalid argument %s ignored", htmlentities($arg_array[$i])),
                                                  E_USER_WARNING);
                    }
                    elseif (defined($value))
                        $value = constant($value);
                    $this->inputbox[$j][$name][$attr] = $value;
                }
                        //trigger_error("not yet finished");
                //eval('$this->inputbox[]["'.$m[1].'"]='.$m[2].';');
            } else {
                        trigger_error(sprintf("Invalid argument %s ignored", htmlentities($arg_array[$i])),
                                      E_USER_WARNING);
            }
            }
        return;
    }

    function run($dbi, $argstr, &$request, $basepage) {
        extract($this->getArgs($argstr, $request));
        if (empty($action)) {
            return $this->error(fmt("A required argument '%s' is missing.", "action"));
        }
        $form = HTML::form(array('action' => $request->getPostURL(),
                                 'method' => strtolower($method),
                                 'class'  => 'wikiformrich',
                                 'accept-charset' => $GLOBALS['charset']),
                           HiddenInputs(array('action' => $action)));
        $nbsp = HTML::Raw('&nbsp;');
        $already_submit = 0;
        foreach ($this->inputbox as $inputbox) {
            foreach ($inputbox as $inputtype => $input) {
              if ($inputtype == 'radiobutton') $inputtype = 'radio'; // convert from older versions
              $input['type'] = $inputtype;
              $text = '';
              if ($inputtype != 'submit') {
                  if (empty($input['name']))
                      return $this->error(fmt("A required argument '%s' is missing.",
                                            $inputtype."[][name]"));
                  if (!isset($input['text'])) $input['text'] = gettext($input['name']);
                  $text = $input['text'];
                  unset($input['text']);
              }
              switch($inputtype) {
              case 'checkbox': // text right
              case 'radio':
                if (empty($input['value'])) $input['value'] = 1;
                if (is_array($input['value'])) {
                    $div = HTML::div(array('class' => $class));
                    $values = $input['value'];
                    $name = $input['name'];
                    $input['name'] = $inputtype == 'checkbox' ? $name."[]" : $name;
                    foreach ($values as $val) {
                        $input['value'] = $val;
                        if ($request->getArg($name)) {
                            if ($request->getArg($name) == $val)
                                $input['checked'] = 'checked';
                            else
                                unset($input['checked']);
                        }
                        $div->pushContent(HTML::input($input), $nbsp, $val, $nbsp, "\n");
                        if (!$nobr)
                            $div->pushContent(HTML::br());
                    }
                    $form->pushContent($div);
                } else {
                    if (empty($input['checked'])) {
                        if ($request->getArg($input['name']))
                            $input['checked'] = 'checked';
                    } else {
                        $input['checked'] = 'checked';
                    }
                    if ($nobr)
                        $form->pushContent(HTML::input($input), $nbsp, $text, $nbsp);
                    else
                        $form->pushContent(HTML::div(array('class' => $class), HTML::input($input), $nbsp, $text));
                }
                break;
              case 'editbox': // text left
                  $input['type'] = 'text';
                  if (empty($input['value']) and ($s = $request->getArg($input['name'])))
                      $input['value'] = $s;
                  if (!empty($input['autocomplete']))
                      $this->_doautocomplete($form, $inputtype, $input, $input['value']);
                  if ($nobr)
                      $form->pushContent($text, $nbsp, HTML::input($input));
                  else
                      $form->pushContent(HTML::div(array('class' => $class), $text, $nbsp, HTML::input($input)));
                  break;
              case 'combobox': // text left
                  $input['autocomplete'] = 1;
              case 'pulldown':
                  $values = @$input['value'];
                  unset($input['value']);
                  unset($input['type']);
                  if (is_string($values)) $values = explode(",", $values);
                  if (!empty($input['autocomplete']))
                      $this->_doautocomplete($form, $inputtype, $input, $values);
                  $select = HTML::select($input);
                  if (empty($values) and ($s = $request->getArg($input['name']))) {
                      $select->pushContent(HTML::option(array('value'=> $s), $s));
                  } elseif (is_array($values)) {
                      $name = $input['name'];
                      unset($input['name']);
                      foreach ($values as $val) {
                          $input = array('value' => $val);
                          if ($request->getArg($name)) {
                              if ($request->getArg($name) == $val)
                                  $input['selected'] = 'selected';
                              else
                                  unset($input['selected']);
                          }
                          //TODO: filter uneeded attributes
                          $select->pushContent(HTML::option($input, $val));
                      }
                  } else { // force empty option
                      $select->pushContent(HTML::option(array(), ''));
                  }
                  $form->pushContent($text, $nbsp, $select);
                  break;
              case 'reset':
              case 'hidden':
                  $form->pushContent(HTML::input($input));
                  break;
              // change the order of inputs, by explicitly placing a submit button here.
              case 'submit': // text right (?)
                  //$input['type'] = 'submit';
                  if (empty($input['value'])) $input['value'] = $buttontext ? $buttontext : $action;
                  unset($input['text']);
                  if (empty($input['class'])) $input['class'] = $class;
                  if ($nobr)
                      $form->pushContent(HTML::input($input), $nbsp, $text, $nbsp);
                  else
                      $form->pushContent(HTML::div(array('class' => $class), HTML::input($input), $text));
                  // unset the default submit button
                  $already_submit = 1;
                  break;
              }
            }
        }
        if ($request->getArg('start_debug'))
            $form->pushContent(HTML::input
                               (array('name' => 'start_debug',
                                      'value' =>  $request->getArg('start_debug'),
                                      'type'  => 'hidden')));
        if (!USE_PATH_INFO)
            $form->pushContent(HiddenInputs(array('pagename' => $basepage)));
        if (!$already_submit) {
            if (empty($buttontext)) $buttontext = $action;
            $submit = Button('submit:', $buttontext, $class);
            if ($cancel) {
                $form->pushContent(HTML::span
                                   (array('class' => $class),
                                    $submit,
                                    Button('submit:cancel', _("Cancel"), $class)));
            } else {
                $form->pushContent(HTML::span(array('class' => $class),
                                              $submit));
            }
        }
        return $form;
    }

    function _doautocomplete(&$form, $inputtype, &$input, &$values) {
        global $request;
        $input['class'] = "dropdown";
        $input['acdropdown'] = "true";
        //$input['autocomplete'] = "OFF";
        $input['autocomplete_complete'] = "true";
        // only match begin: autocomplete_matchbegin, or
        $input['autocomplete_matchsubstring'] = "true";
        if (empty($values)) {
            if ($input['method']) {
                if (empty($input['args'])) {
                    if (preg_match("/^(.*?) (.*)$/",$input['method'],$m)) {
                        $input['method'] = $m[1];
                        $input['args'] = $m[2];
                    } else
                        $input['args'] = null;
                }
                static $tmpArray = 'tmpArray00';
                // deferred remote xmlrpc call
                if (string_starts_with($input['method'], "dynxmlrpc:")) {
                    // how is server + method + args encoding parsed by acdropdown?
                    $input['autocomplete_list'] = substr($input['method'],3);
                    if ($input['args'])
                        $input['autocomplete_list'] .= (" ".$input['args']);
                // static xmlrpc call, local only
                } elseif (string_starts_with($input['method'], "xmlrpc:")) {
                    include_once("lib/XmlRpcClient.php");
                    $values = wiki_xmlrpc_post(substr($input['method'],7), $input['args']);
                } elseif (string_starts_with($input['method'], "url:")) {
                    include_once("lib/HttpClient.php");
                    $html = HttpClient::quickGet(substr($input['method'],4));
                    //TODO: how to parse the HTML result into a list?
                } elseif (string_starts_with($input['method'], "dynurl:")) {
                    $input['autocomplete_list'] = substr($input['method'],3);
                } elseif (string_starts_with($input['method'], "plugin:")) {
                    $dbi = $request->getDbh();
                    $pluginName = substr($input['method'],7);
                    $basepage = '';
                    require_once("lib/WikiPlugin.php");
                    $w = new WikiPluginLoader;
                    $p = $w->getPlugin($pluginName, false); // second arg?
                    if (!is_object($p))
                        trigger_error("invalid input['method'] ".$input['method'], E_USER_WARNING);
                    $pagelist = $p->run($dbi, @$input['args'], $request, $basepage);
                    $values = array();
                    if (is_object($pagelist) and isa($pagelist, 'PageList')) {
                        foreach ($pagelist->_pages as $page) {
                            if (is_object($page))
                                $values[] = $page->getName();
                            else
                                $values[] = (string)$page;
                        }
                    }
                } elseif (string_starts_with($input['method'], "array:")) {
                    // some predefined values (e.g. in a template or themeinfo.php)
                    $input['autocomplete_list'] = $input['method'];
                } else {
                    trigger_error("invalid input['method'] ".$input['method'], E_USER_WARNING);
                }
                if (empty($input['autocomplete_list']))
                {
                    $tmpArray++;
                    $input['autocomplete_list']="array:".$tmpArray;
                    $svalues = empty($values) ? "" : join("','", $values);
                    $form->pushContent(JavaScript("var $tmpArray = new Array('".$svalues."')"));
                }
                if (count($values) == 1)
                    $input['value'] = $values[0];
                else
                    $input['value'] = "";
                unset($input['method']);
                unset($input['args']);
                //unset($input['autocomplete']);
            }
            elseif ($s = $request->getArg($input['name']))
                $input['value'] = $s;
        }
        return true;
    }
};

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
