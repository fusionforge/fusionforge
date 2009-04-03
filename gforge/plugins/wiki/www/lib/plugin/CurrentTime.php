<?php // -*-php-*-
rcs_id('$Id: CurrentTime.php 6430 2009-01-23 12:33:28Z vargenau $');
/*
 * Copyright 2008 Marc-Etienne Vargenau, Alcatel-Lucent
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

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The CurrentTimePlugin ("Contribution") has not been tested and/or
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

/**
 * A simple plugin that displays current time and date.
 *
 * Usage:
 * <?plugin CurrentTime?>
 * <?plugin CurrentTime format=XXX ?>
 */

class WikiPlugin_CurrentTime
extends WikiPlugin
{
    // Five required functions in a WikiPlugin.

    function getName () {
        return _("CurrentTime");
    }

    function getDescription () {
        return _("A simple plugin that displays current time and date");

    }

    function getVersion() {
        return preg_replace("/[Revision: $]/", '',
                            "\$Revision: 6185 $");
    }

    // Establish default values for each of this plugin's arguments.
    function getDefaultArguments() {
        return array('format'  => '%Y-%m-%d %T');
    }

    function run($dbi, $argstr, &$request, $basepage) {
        extract($this->getArgs($argstr, $request));

        if ($format == 'date') {
            $format = '%Y-%m-%d';
        }
        if ($format == 'time') {
            $format = '%T';
        }

        return HTML::raw(strftime($format, time()));
    }
};

// For emacs users
// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
