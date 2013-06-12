<?php

/**
 * Copyright 1999, 2000, 2001, 2002, 2004 $ThePhpWikiProgrammingTeam
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
 * This is a replacement for MagicPhpWikiURL forms.
 * Just a few old actions are supported, which where previously
 * encoded with the phpwiki: syntax.
 */
class WikiPlugin_WikiForm
    extends WikiPlugin
{
    function getDescription()
    {
        return _("Provide generic WikiForm input buttons.");
    }

    function getDefaultArguments()
    {
        return array('action' => 'upload', // 'upload', 'loadfile',
            // 'dumphtml' or 'dumpserial'
            'default' => false,
            'buttontext' => false,
            'overwrite' => false,
            'size' => 50);
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        extract($this->getArgs($argstr, $request));
        $form = HTML::form(array('action' => $request->getPostURL(),
                'method' => 'post',
                'class' => 'wikiadmin',
                'accept-charset' => 'UTF-8'),
            HiddenInputs(array('action' => $action,
                'overwrite' => $overwrite,
                'pagename' => $basepage)));
        $input = array('type' => 'text',
            'value' => $default,
            'size' => $size);

        switch ($action) {
            case 'loadfile':
                $input['name'] = 'source';
                if (!$default)
                    $input['value'] = DEFAULT_DUMP_DIR;
                if (!$buttontext)
                    $buttontext = _("Load File");
                $class = false;
                break;
            case 'dumpserial':
                $input['name'] = 'directory';
                if (!$default)
                    $input['value'] = DEFAULT_DUMP_DIR;
                if (!$buttontext)
                    $buttontext = _("Dump Pages");
                $class = 'wikiadmin';
                break;
            case 'dumphtml':
                $input['name'] = 'directory';
                if (!$default)
                    $input['value'] = HTML_DUMP_DIR;
                if (!$buttontext)
                    $buttontext = _("Dump Pages as XHTML");
                $class = 'wikiadmin';
                break;
            case 'upload':
                $form->setAttr('enctype', 'multipart/form-data');
                $form->pushContent(HTML::input(array('name' => 'MAX_FILE_SIZE',
                    'value' => MAX_UPLOAD_SIZE,
                    'type' => 'hidden')));
                $input['name'] = 'file';
                $input['type'] = 'file';
                if (!$buttontext)
                    $buttontext = _("Upload");
                $class = false; // local OS function, so use native OS button
                break;
            default:
                return HTML::div(array('class' => "error"), fmt("WikiForm: %s: unknown action", $action));
        }

        $input = HTML::input($input);
        $input->addTooltip($buttontext);
        $button = Button('submit:', $buttontext, $class);
        if ($request->getArg('start_debug'))
            $form->pushContent(HTML::input(array('name' => 'start_debug',
                'value' => $request->getArg('start_debug'),
                'type' => 'hidden')));
        $form->pushContent(HTML::span(array('class' => $class),
            $input, $button));

        return $form;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
