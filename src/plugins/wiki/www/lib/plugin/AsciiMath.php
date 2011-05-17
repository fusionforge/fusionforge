<?php // -*-php-*-
// rcs_id('$Id: AsciiMath.php 7417 2010-05-19 12:57:42Z vargenau $');
/*
 * Copyright 2005 $ThePhpWikiProgrammingTeam
 * Copyright 2009 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

if (phpversion() >= '5') {
    require_once("lib/ASCIIMathPHP/ASCIIMathPHP-2.0.class.php");
} else {
    require_once("lib/ASCIIMathPHP/ASCIIMathPHP.class.php");
}

/**
 * Render ASCII math as MathML
 * Requires ENABLE_XHTML_XML = true
 * See http://www.jcphysics.com/ASCIIMath/
 * Syntax: http://www1.chapman.edu/~jipsen/mathml/asciimathsyntax.xml
 * Example: "int_-1^1 sqrt(1-x^2)dx = pi/2"
 * => <math xmlns="http://www.w3.org/1998/Math/MathML">
        <mrow><msubsup><mo>&#8747;</mo><mn>-1</mn><mn>1</mn></msubsup></mrow>
        <msqrt><mrow><mn>1</mn><mo>-</mo><msup><mi>x</mi><mn>2</mn></msup></mrow></msqrt>
        <mi>d</mi>
        <mi>x</mi>
        <mo>=</mo>
        <mfrac><mi>&#960;</mi><mo>2</mo></mfrac>
      </math>
 */
class WikiPlugin_AsciiMath
extends WikiPlugin
{
    function getName() {
        return _("AsciiMath");
    }

    function getDescription() {
        return _("Render ASCII Math as MathML");
    }

    function getDefaultArguments() {
        return array();
    }
    function handle_plugin_args_cruft(&$argstr, &$args) {
        $this->source = $argstr;
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        if (empty($this->source)) {
            return HTML::div(array('class' => "error"), "Please provide a formula to AsciiMath plugin");
        }

        if (phpversion() >= '5') {
            include("lib/ASCIIMathPHP/ASCIIMathPHP-2.0.cfg.php");
        } else {
            include("lib/ASCIIMathPHP/ASCIIMathPHP.cfg.php");
        }
        $ascii_math = new ASCIIMathPHP($symbol_arr, $this->source);
        $ascii_math->genMathML();
        return HTML::raw($ascii_math->getMathML());
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
