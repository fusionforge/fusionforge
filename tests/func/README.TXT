DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER 
DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER 

Running the test suite will drop your current database, this is
to be used only on test environment.

DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER 
DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER 


First step, you have to start the selenium remote server.
untar the 1.0 release of selenium, then

cd ~/selenium-remote-control-1.0-SNAPSHOT/selenium-server-1.0-SNAPSHOT
java -jar selenium-server.jar -interactive

On a debian system using iceweasel and java in /usr/lib/j2re1.5-sun:
PATH=/usr/lib/j2re1.5-sun/bin:/usr/lib/iceweasel:/bin java -jar selenium-server.jar -interactive

Second step, before launching tests, edit file 'tests/config.php'.
Once configured, run the test suite.

# For PostgreSQL: (NOTE: seems no needed)

configure ~/.pgpass to allow connection from your account.

echo 'localhost:*:gforge:gforge:myadmin' >>  ~/.pgpass
chmod 600 ~/.pgpass

cd tests
php AllTests.php

If you want to run only a specific part, then you can use:

cd tests
phpunit Trackers_AllTests Trackers/AllTests.php


To create an empty database for recording a test:
cd tests
php db_reload.php

===========================================================================
NOTES:

ALL:
* Requires patch: tests/hack_for_emails.patch
Description: 
When an account has its email address with the form 'log:[a-z]+', then 
emails are not sent but written in the data/logs directory instead.
This is useful to avoid sending/flooding with emails when tests are
executed, this has required a small hack in the Gforge code (see utils.php).

MYSQL:
* testMyTestCase(CreateForum) is failing on forum.php:80
=> Seems to be a bug in the mysql port.

PSQL:
* Requires patch: tests/use_non_persistent_connect.patch.
Description:
Adds a new configuration variable '$sys_db_persistent', when set to
false, then connection to postgres will be performed with normal
pg_connect instead of pg_pconnect. This is to allow to drop the database.
