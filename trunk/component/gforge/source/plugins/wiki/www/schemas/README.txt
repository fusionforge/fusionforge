These files are used to initialize the database tables for a Wiki.
First create the database using the database commands suitable for
your DBMS. See doc/INSTALL.<DBMS>

At first check the appropriate <DBMS>-{initialize|destroy}.sql for
your configured DATABASE_PREFIX. The default is an empty
prefix. A prefix is only needed for multiple wiki's on the same database.

Then run the appropriate <DBMS>-initialize.sql to initialize your database.

If you have a database you have been using you should destroy it first
with the appropriate <DBMS>-destroy.sql file.

The separation of files into "initialize" and "destroy" is intended
to give some small measure of additional protection against
accidentally destroying a live database, but BE CAREFUL.
