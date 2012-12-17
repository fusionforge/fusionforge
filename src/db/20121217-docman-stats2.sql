CREATE RULE docman_dlstats_doccreate_rule AS ON INSERT TO doc_data DO INSERT into docman_dlstats_doctotal_agg (downloads, docid) VALUES (0, lastval());
