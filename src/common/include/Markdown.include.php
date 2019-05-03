<?php
/**
 * Copyright 2019, Franck Villaume - TrivialDev
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

function FF_Markdown($html) {
	if (file_exists(stream_resolve_include_path('markdown.php'))) {
		require_once('markdown.php');
		return Markdown($html);
	} elseif (file_exists(stream_resolve_include_path('Michelf/MarkdownExtra.inc.php'))) {
		require_once stream_resolve_include_path('Michelf/MarkdownExtra.inc.php');
		return \Michelf\MarkdownExtra::defaultTransform($html);
	} else {
		return $html;
	}
}
