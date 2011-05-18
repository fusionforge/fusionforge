<?php // -*-php-*-
// $Id: Chart.php 8071 2011-05-18 14:56:14Z vargenau $
/*
 * Copyright 2007 $ThePhpWikiProgrammingTeam
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
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The ChartPlugin ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

class WikiPlugin_Chart
extends WikiPlugin
{
    function getName() {
        return _("Chart");
    }

    function getDescription() {
        return _("Render SVG charts");
    }

    function getDefaultArguments() {
        return array('width'  => 200,
                     'height' => 200,
                     'type' => 'line', // or 'area', 'bar', 'pie'
                     // 'xlabel' => 'x', // TODO
                     // 'ylabel' => 'y', // TODO
                     'color' => 'green',
                     // 'legend' => false, // TODO
                     'data' => false // mandatory
                     );
    }
    function handle_plugin_args_cruft(&$argstr, &$args) {
        $this->source = $argstr;
    }

    function run($dbi, $argstr, &$request, $basepage) {

        global $WikiTheme;
        $args = $this->getArgs($argstr, $request);
        extract($args);
        $html = HTML();
        $js = JavaScript('', array ('src' => $WikiTheme->_findData('ASCIIsvg.js')));
        $html->pushContent($js);

        $values = explode(",", $data);

        // x_min = 0
        // x_max = number of elements in data
        // y_min = 0 or smallest element in data if negative
        // y_max = biggest element in data

        $x_max = sizeof($values) + 1;
        $y_min = min($values);
        if ($y_min > 0) {
            $y_min = 0;
        }
        $y_max = max($values);
        // sum is used for the pie only, so we ignore negative values
        $sum = 0;
        foreach ($values as $value) {
            if ($value > 0) {
                $sum += $value;
            }
        }

        $source = 'initPicture(0,'.$x_max.','.$y_min.','.$y_max.'); axes(); stroke = "'.$color.'"; strokewidth = 5;';

        if ($type == "bar") {
            $abscisse = 1;
            $source .= 'strokewidth = 10; ';
            foreach ($values as $value) {
                $source .= 'point1 = ['.$abscisse.', 0];'
                        .  'point2 = ['.$abscisse.','.$value.'];'
                        .  'line(point1, point2);';
                $abscisse += 1;
            }
        } else if ($type == "line") {
            $abscisse = 0;
            $source .= 'strokewidth = 3; p = []; ';
            foreach ($values as $value) {
                $source .= 'for (t = 1; t < 1.01; t += 1) p[p.length] = ['
                        .  $abscisse
                        .  ', t*'
                        .  trim($value)
                        .  '];';
                $abscisse += 1;
            }
            $source .= 'path(p);';
        } else if ($type == "pie") {
            $source = 'initPicture(-1.1,1.1,-1.1,1.1); stroke = "'.$color.'"; strokewidth = 1;'
                    . 'center = [0, 0]; circle(center, 1);'
                    . 'point = [1, 0]; line(center, point);';
            $angle = 0;
            foreach ($values as $value) {
                if ($value > 0) {
                    $angle += $value/$sum;
                    $source .= 'point = [cos(2*pi*'.$angle.'), sin(2*pi*'.$angle.')]; line(center, point);';
                }
            }
        }

        $embedargs = array('width'  => $args['width'],
                           'height' => $args['height'],
                           'script' => $source);
        $embed = new SVG_HTML("embed", $embedargs);
        $html->pushContent($embed);
        return $html;
    }
};

class SVG_HTML extends HtmlElement {
    function startTag() {
        $start = "<" . $this->_tag;
        $this->_setClasses();
        foreach ($this->_attr as $attr => $val) {
            if (is_bool($val)) {
                if (!$val)
                    continue;
                $val = $attr;
            }
            $qval = str_replace("\"", '&quot;', $this->_quote((string)$val));
            if ($attr == 'script')
                // note the ' not "
                $start .= " $attr='$qval'";
            else
                $start .= " $attr=\"$qval\"";
        }
        $start .= ">";
        return $start;
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
