ForumML

ForumML is a contraction of "Forum - Mailing List".
The goal of the plugin is to add forum-like behaviors to mailing lists.

ForumML is a plugin that adds a web interface to mailing list archives.
It makes it easier to browse and search mailing lists.
It also provides a way to post to a list from the web interface.
In this case, Codendi uses your login email adress to post to the list.
The actual acceptance/distribution/archival of the message still 
depends on mailman configuration.


==== INSTALLATION ===

bin/installFF.sh should do whatever is necessary for the plugin works
* creation of directory with good rights
* installation of pear packages
* config mailman

==== Importing existing list archives in Codendi ====

## To import ML archives of specific projects, into ForumML DB, 
run 'mail_2_DB.php' script.
1st argument: list name
2nd argument: 2
$> /usr/share/codendi/src/utils/php-launcher /usr/share/codendi/plugins/forumml/bin/mail_2_DB.php codex-support 2

## To import ML archives of all Codendi projects, for which the plugin is enabled
run 'ml_arch_2_DB.pl' script:
$> /usr/share/codendi/plugins/forumml/bin/ml_arch_2_DB.pl


==== Importing existing list archives in iFusionForge ====

## To import ML archives of specific projects, into ForumML DB, 
run 'mail_2_DBFF.php' script.
1st argument: list name
2nd argument: 2
$> /usr/bin/php -q -d include_path=.:/etc/gforge:/usr/share/gforge:/usr/share/gforge/www/include:/usr/share/gforge/plugins forumml/bin/mail_2_DBFF.php mylistname 2

## To import ML archives of all projects, for which the plugin is enabled
run 'ml_arch_2_DBFF.pl' script:
$> forumml/bin/ml_arch_2_DBFF.pl


