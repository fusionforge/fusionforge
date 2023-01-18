CREATE TABLE plugin_stopforumspam_known_entries (
	datatype text NOT NULL,
	entry text NOT NULL,
	last_seen INTEGER NOT NULL
) ;
CREATE TABLE plugin_stopforumspam_last_fetch (
	datatype text NOT NULL,
	period text NOT NULL,
	last_fetch INTEGER NOT NULL
) ;
