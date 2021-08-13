<?php
/**
 * Copyright © 2004 Reini Urban
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
 * Custom UserPreferences:
 * A list of name => _UserPreference class pairs.
 * Rationale: Certain themes should be able to extend the predefined list
 * of preferences. Display/editing is done in the theme specific userprefs.tmpl
 * but storage/sanification/update/... must be extended to the get/setPreferences methods.
 *
 * This is just at alpha stage, a recommendation to the wikilens group.
 */

class _UserPreference_recengine // recommendation engine method
    extends _UserPreference
{
    public $valid_values = array('php', 'mysuggest', 'mymovielens', 'mycluto');
    public $default_value = 'php';

    function sanify($value)
    {
        if (!in_array($value, $this->valid_values)) return $this->default_value;
        else return $value;
    }
}

class _UserPreference_recalgo // recommendation engine algorithm
    extends _UserPreference
{
    public $valid_values = array
    (
        'itemCos', // Item-based Top-N recommendation algorithm with cosine-based similarity function
        'itemProb', // Item-based Top-N recommendation algorithm with probability-based similarity function.
        // This algorithms tends to outperform the rest.
        'userCos', // User-based Top-N recommendation algorithm with cosine-based similarity function.
        'bayes'); // Naïve Bayesian Classifier
    public $default_value = 'itemProb';

    function sanify($value)
    {
        if (!in_array($value, $this->valid_values)) return $this->default_value;
        else return $value;
    }
}

class _UserPreference_recnnbr // recommendation engine key clustering, neighborhood size
    extends _UserPreference_numeric
{
}

$WikiTheme->customUserPreferences
(array
(
    'recengine' => new _UserPreference_recengine('php'),
    'recalgo' => new _UserPreference_recalgo('itemProb'),
    //recnnbr: typically 15-30 for item-based, 40-80 for user-based algos
    'recnnbr' => new _UserPreference_recnnbr(10, 14, 80),
));
