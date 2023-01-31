CREATE TABLE plugin_stopforumspam_known_entries (
	datatype text NOT NULL,
	entry text NOT NULL,
	last_seen INTEGER NOT NULL,
        CONSTRAINT stopforumspam_entries_unique UNIQUE (datatype, entry)
) ;
CREATE TABLE plugin_stopforumspam_last_fetch (
	datatype text NOT NULL,
	period text NOT NULL,
	last_fetch INTEGER NOT NULL,
        CONSTRAINT stopforumspam_lastfetch_unique UNIQUE (datatype, period)
) ;
