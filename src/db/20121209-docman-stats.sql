CREATE TABLE docman_dlstats_doc (
	ip_address	text,
	docid		integer REFERENCES doc_data(docid) ON UPDATE CASCADE ON DELETE CASCADE,
	month		integer,
	day		integer,
	user_id		integer REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE docman_dlstats_doctotal_agg (
	docid		integer REFERENCES doc_data(docid) ON UPDATE CASCADE ON DELETE CASCADE,
	downloads	integer
);

CREATE RULE docman_dlstats_doc_rule AS ON INSERT TO docman_dlstats_doc DO UPDATE docman_dlstats_doctotal_agg SET downloads = (docman_dlstats_doctotal_agg.downloads + 1) WHERE (docman_dlstats_doctotal_agg.docid = new.docid);

INSERT INTO docman_dlstats_doctotal_agg (docid, downloads) SELECT doc_data.docid, doc_data.download FROM doc_data;
ALTER TABLE doc_data DROP download;
