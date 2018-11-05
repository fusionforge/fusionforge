<?php
/**
 * Userhome Peer Rating Widget Class
 *
 * Copyright 2018, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
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

require_once 'Widget.class.php';
require_once $gfwww.'include/vote_function.php';


class Widget_UserhomePeerRatings extends Widget {

	function __construct($owner_id) {
		$me = session_get_user();
		$user = user_get_object($owner_id);
		if (forge_get_config('use_ratings')) {
			if ($user->usesRatings() && (!$me || $me->usesRatings())) {
				$this->owner_id = $owner_id;
				parent::__construct('uhpeerratings', $owner_id, WidgetLayoutManager::OWNER_TYPE_USERHOME);
				$this->title = _('Peer Rating');
			}
		}
	}

	function getTitle() {
		return $this->title;
	}

	function isAvailable() {
		return isset($this->title);
	}

	function getContent() {
		$me = session_get_user();
		$user = user_get_object($this->owner_id);
		print "<p>";
		print _('If you are familiar with this user, please take a moment to rate him/her on the following criteria. Keep in mind, that your rating will be visible to the user and others.');
		print "</p>";

		print "<p>";
		printf(_('The %s Peer Rating system is based on concepts from <a href="http://www.advogato.com/">Advogato.</a> The system has been re-implemented and expanded in a few ways.'), forge_get_config('forge_name'));
		print "</p>";
		?>

		<div class="align-center">
		<?php vote_show_user_rate_box ($user->getID(), $me? $me->getID() : 0); ?>
		</div>

		<?php
		print "<p>";
		print _('The Peer rating box shows all rating averages (and response levels) for each individual criteria. Due to the math and processing required to do otherwise, these numbers incorporate responses from both “trusted” and “non-trusted” users.');
		print "</p>";

		print "<ul>";
		print "<li>";
		printf(_('The “Sitewide Rank” field shows the user\'s rank compared to all ranked %s users.'), forge_get_config('forge_name'));
		print "</li>";

		print "<li>";
		print _('The “Aggregate Score” shows an average, weighted overall score, based on trusted-responses only.');
		print "</li>";

		print "<li>";
		print _('The “Personal Importance” field shows the weight that users ratings of other developers will be given (between 1 and 1.5) -- higher rated user\'s responses are given more weight.');
		print "</li>";
		print "</ul>";

		print "<p>";
		print "<em>";
		printf(_('If you would like to opt-out from peer rating system (this will affect your ability to both rate and be rated), refer to <a href="%s">your account maintenance page</a>. If you choose not to participate, your ratings of other users will be permanently deleted and the “Peer Rating” box will disappear from your user page.'),
				util_make_url("/account"));
		print "</em>";
		print "</p>";
	}
}
