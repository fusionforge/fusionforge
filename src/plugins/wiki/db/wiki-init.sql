CREATE TABLE plugin_wiki_accesslog (
    time_stamp integer,
    remote_host character varying(100),
    remote_user character varying(50),
    request_method character varying(10),
    request_line character varying(255),
    request_args character varying(255),
    request_file character varying(255),
    request_uri character varying(255),
    request_time character(28),
    status smallint,
    bytes_sent integer,
    referer character varying(255),
    agent character varying(255),
    request_duration double precision
);
-- ALTER TABLE public.plugin_wiki_accesslog OWNER TO gforge;

CREATE TABLE plugin_wiki_page (
    id serial NOT NULL,
    pagename character varying(100) NOT NULL,
    hits integer DEFAULT 0 NOT NULL,
    pagedata text DEFAULT ''::text NOT NULL,
    cached_html bytea DEFAULT ''::bytea,
    CONSTRAINT plugin_wiki_page_pagename_check CHECK (((pagename)::text <> ''::text))
);
-- ALTER TABLE public.plugin_wiki_page OWNER TO gforge;

CREATE TABLE plugin_wiki_recent (
    id integer,
    latestversion integer,
    latestmajor integer,
    latestminor integer
);
-- ALTER TABLE public.plugin_wiki_recent OWNER TO gforge;

CREATE TABLE plugin_wiki_version (
    id integer,
    version integer NOT NULL,
    mtime integer NOT NULL,
    minor_edit smallint DEFAULT 0,
    content text DEFAULT ''::text NOT NULL,
    versiondata text DEFAULT ''::text NOT NULL
);
-- ALTER TABLE public.plugin_wiki_version OWNER TO gforge;

CREATE TABLE plugin_wiki_nonempty (
    id integer NOT NULL
);
-- ALTER TABLE public.plugin_wiki_nonempty OWNER TO gforge;

CREATE TABLE plugin_wiki_link (
    linkfrom integer NOT NULL,
    linkto integer NOT NULL,
    relation integer
);
-- ALTER TABLE public.plugin_wiki_link OWNER TO gforge;

CREATE TABLE plugin_wiki_member (
    userid character(48) NOT NULL,
    groupname character(48) DEFAULT 'users'::bpchar NOT NULL
);
-- ALTER TABLE public.plugin_wiki_member OWNER TO gforge;


CREATE TABLE plugin_wiki_pagedata (
    id integer NOT NULL,
    date integer,
    locked boolean,
    rest text DEFAULT ''::text NOT NULL
);
-- ALTER TABLE public.plugin_wiki_pagedata OWNER TO gforge;

CREATE TABLE plugin_wiki_pageperm (
    id integer NOT NULL,
    "access" character(12) NOT NULL,
    groupname character varying(48),
    allowed boolean
);
-- ALTER TABLE public.plugin_wiki_pageperm OWNER TO gforge;

CREATE TABLE plugin_wiki_pref (
    userid character(48) NOT NULL,
    prefs text DEFAULT ''::text,
    passwd character(48) DEFAULT ''::bpchar,
    groupname character(48) DEFAULT 'users'::bpchar
);
-- ALTER TABLE public.plugin_wiki_pref OWNER TO gforge;

CREATE TABLE plugin_wiki_rating (
    dimension integer NOT NULL,
    raterpage bigint NOT NULL,
    rateepage bigint NOT NULL,
    ratingvalue double precision NOT NULL,
    rateeversion bigint NOT NULL,
    tstamp timestamp without time zone NOT NULL
);
-- ALTER TABLE public.plugin_wiki_rating OWNER TO gforge;

CREATE TABLE plugin_wiki_session (
    sess_id character(32) NOT NULL,
    sess_data bytea NOT NULL,
    sess_date integer,
    sess_ip character(40) NOT NULL
);
-- ALTER TABLE public.plugin_wiki_session OWNER TO gforge;

CREATE TABLE plugin_wiki_versiondata (
    id integer NOT NULL,
    version integer NOT NULL,
    markup smallint DEFAULT 2,
    author character varying(48),
    author_id character varying(48),
    pagetype character varying(20) DEFAULT 'wikitext'::character varying,
    rest text DEFAULT ''::text NOT NULL
);
-- ALTER TABLE public.plugin_wiki_versiondata OWNER TO gforge;


ALTER TABLE ONLY plugin_wiki_pref
    ADD CONSTRAINT plugin_wiki_pref_pkey PRIMARY KEY (userid);
ALTER TABLE ONLY plugin_wiki_session
    ADD CONSTRAINT plugin_wiki_session_pkey PRIMARY KEY (sess_id);

CREATE UNIQUE INDEX plugin_wiki_page_id_idx ON plugin_wiki_page (id);

CREATE INDEX plugin_wiki_link_from_idx ON plugin_wiki_link USING btree (linkfrom);
CREATE INDEX plugin_wiki_link_to_idx ON plugin_wiki_link USING btree (linkto);
CREATE INDEX plugin_wiki_log_host_idx ON plugin_wiki_accesslog USING btree (remote_host);
CREATE INDEX plugin_wiki_log_time_idx ON plugin_wiki_accesslog USING btree (time_stamp);
CREATE INDEX plugin_wiki_member_group_idx ON plugin_wiki_member USING btree (groupname);
CREATE INDEX plugin_wiki_member_id_idx ON plugin_wiki_member USING btree (userid);
CREATE UNIQUE INDEX plugin_wiki_nonmt_id_idx ON plugin_wiki_nonempty USING btree (id);
CREATE INDEX plugin_wiki_pagedata_id_idx ON plugin_wiki_pagedata USING btree (id);
CREATE INDEX plugin_wiki_pageperm_access_idx ON plugin_wiki_pageperm USING btree ("access");
CREATE INDEX plugin_wiki_pageperm_id_idx ON plugin_wiki_pageperm USING btree (id);
CREATE UNIQUE INDEX plugin_wiki_rating_id_idx ON plugin_wiki_rating USING btree (dimension, raterpage, rateepage);
CREATE UNIQUE INDEX plugin_wiki_recent_id_idx ON plugin_wiki_recent USING btree (id);
CREATE INDEX plugin_wiki_recent_lv_idx ON plugin_wiki_recent USING btree (latestversion);
CREATE INDEX plugin_wiki_relation_idx ON plugin_wiki_link USING btree (relation);
CREATE INDEX plugin_wiki_sess_date_idx ON plugin_wiki_session USING btree (sess_date);
CREATE INDEX plugin_wiki_sess_ip_idx ON plugin_wiki_session USING btree (sess_ip);
CREATE UNIQUE INDEX plugin_wiki_vers_id_idx ON plugin_wiki_version USING btree (id, version);
CREATE INDEX plugin_wiki_vers_mtime_idx ON plugin_wiki_version USING btree (mtime);


CREATE INDEX pref_group_idx ON plugin_wiki_pref USING btree (groupname);


ALTER TABLE ONLY plugin_wiki_link
    ADD CONSTRAINT plugin_wiki_link_linkfrom_fkey FOREIGN KEY (linkfrom) REFERENCES plugin_wiki_page(id);
ALTER TABLE ONLY plugin_wiki_link
    ADD CONSTRAINT plugin_wiki_link_linkto_fkey FOREIGN KEY (linkto) REFERENCES plugin_wiki_page(id);
ALTER TABLE ONLY plugin_wiki_member
    ADD CONSTRAINT plugin_wiki_member_userid_fkey FOREIGN KEY (userid) REFERENCES plugin_wiki_pref(userid);
ALTER TABLE ONLY plugin_wiki_nonempty
    ADD CONSTRAINT plugin_wiki_nonempty_id_fkey FOREIGN KEY (id) REFERENCES plugin_wiki_page(id);
ALTER TABLE ONLY plugin_wiki_pagedata
    ADD CONSTRAINT plugin_wiki_pagedata_id_fkey FOREIGN KEY (id) REFERENCES plugin_wiki_page(id);
ALTER TABLE ONLY plugin_wiki_pageperm
    ADD CONSTRAINT plugin_wiki_pageperm_id_fkey FOREIGN KEY (id) REFERENCES plugin_wiki_page(id);
ALTER TABLE ONLY plugin_wiki_rating
    ADD CONSTRAINT plugin_wiki_rating_rateepage_fkey FOREIGN KEY (rateepage) REFERENCES plugin_wiki_page(id);
ALTER TABLE ONLY plugin_wiki_rating
    ADD CONSTRAINT plugin_wiki_rating_raterpage_fkey FOREIGN KEY (raterpage) REFERENCES plugin_wiki_page(id);
ALTER TABLE ONLY plugin_wiki_recent
    ADD CONSTRAINT plugin_wiki_recent_id_fkey FOREIGN KEY (id) REFERENCES plugin_wiki_page(id);
ALTER TABLE ONLY plugin_wiki_version
    ADD CONSTRAINT plugin_wiki_version_id_fkey FOREIGN KEY (id) REFERENCES plugin_wiki_page(id);
ALTER TABLE ONLY plugin_wiki_versiondata
    ADD CONSTRAINT plugin_wiki_versiondata_id_fkey FOREIGN KEY (id, version) REFERENCES plugin_wiki_version(id, version);


CREATE VIEW plugin_wiki_curr_page AS
    SELECT p.id, p.pagename, p.hits, p.pagedata, p.cached_html, v.version, v.mtime, v.minor_edit, v.content, v.versiondata FROM ((plugin_wiki_page p JOIN plugin_wiki_version v USING (id)) JOIN plugin_wiki_recent r ON (((v.id = r.id) AND (v.version = r.latestversion))));
-- ALTER TABLE public.plugin_wiki_curr_page OWNER TO gforge;

CREATE VIEW plugin_wiki_existing_page AS
    SELECT p.id, p.pagename, p.hits, p.pagedata, p.cached_html FROM (plugin_wiki_page p JOIN plugin_wiki_nonempty n USING (id));
-- ALTER TABLE public.plugin_wiki_existing_page OWNER TO gforge;


CREATE FUNCTION plugin_wiki_prepare_rename_page(integer, integer) RETURNS void
    AS $_$
DELETE FROM plugin_wiki_page WHERE id = $2;
DELETE FROM plugin_wiki_version  WHERE id = $2;
DELETE FROM plugin_wiki_recent   WHERE id = $2;
DELETE FROM plugin_wiki_nonempty WHERE id = $2;
UPDATE plugin_wiki_link SET linkfrom = $1 WHERE linkfrom = $2;
UPDATE plugin_wiki_link SET linkto = $1   WHERE linkto = $2;
$_$
    LANGUAGE sql;

-- ALTER FUNCTION public.plugin_wiki_prepare_rename_page(integer, integer) OWNER TO gforge;
CREATE FUNCTION plugin_wiki_update_recent(integer, integer) RETURNS integer
    AS $_$
DELETE FROM plugin_wiki_recent WHERE id = $1;
INSERT INTO plugin_wiki_recent (id, latestversion, latestmajor, latestminor)
  SELECT id, MAX(version) AS latestversion,
	     MAX(CASE WHEN minor_edit =  0 THEN version END) AS latestmajor,
             MAX(CASE WHEN minor_edit <> 0 THEN version END) AS latestminor
    FROM plugin_wiki_version WHERE id = $2 GROUP BY id;
DELETE FROM plugin_wiki_nonempty WHERE id = $1;
INSERT INTO plugin_wiki_nonempty (id)
  SELECT plugin_wiki_recent.id
    FROM plugin_wiki_recent, plugin_wiki_version
    WHERE plugin_wiki_recent.id = plugin_wiki_version.id
          AND version = latestversion
          AND content <> ''
          AND plugin_wiki_recent.id = $1;
SELECT id FROM plugin_wiki_nonempty WHERE id = $1;
$_$
    LANGUAGE sql;

ALTER TABLE plugin_wiki_version ADD COLUMN idxFTI tsvector;
UPDATE plugin_wiki_version SET idxFTI=to_tsvector('default', content);
CREATE INDEX idxFTI_idx ON plugin_wiki_version USING gist(idxFTI);
CREATE TRIGGER tsvectorupdate BEFORE UPDATE OR INSERT ON plugin_wiki_version
     FOR EACH ROW EXECUTE PROCEDURE tsearch2(idxFTI, content);

-- ALTER FUNCTION public.plugin_wiki_update_recent(integer, integer) OWNER TO gforge;



-- REVOKE ALL ON TABLE plugin_wiki_accesslog FROM PUBLIC;
-- REVOKE ALL ON TABLE plugin_wiki_accesslog FROM gforge;
-- GRANT ALL ON TABLE plugin_wiki_accesslog TO gforge;


-- REVOKE ALL ON TABLE plugin_wiki_recent FROM PUBLIC;
-- REVOKE ALL ON TABLE plugin_wiki_recent FROM gforge;
-- GRANT ALL ON TABLE plugin_wiki_recent TO gforge;
-- REVOKE ALL ON TABLE plugin_wiki_version FROM PUBLIC;
-- REVOKE ALL ON TABLE plugin_wiki_version FROM gforge;
-- GRANT ALL ON TABLE plugin_wiki_version TO gforge;
-- REVOKE ALL ON TABLE plugin_wiki_nonempty FROM PUBLIC;
-- REVOKE ALL ON TABLE plugin_wiki_nonempty FROM gforge;
-- GRANT ALL ON TABLE plugin_wiki_nonempty TO gforge;
-- REVOKE ALL ON TABLE plugin_wiki_link FROM PUBLIC;
-- REVOKE ALL ON TABLE plugin_wiki_link FROM gforge;
-- GRANT ALL ON TABLE plugin_wiki_link TO gforge;
-- REVOKE ALL ON TABLE plugin_wiki_member FROM PUBLIC;
-- REVOKE ALL ON TABLE plugin_wiki_member FROM gforge;
-- GRANT ALL ON TABLE plugin_wiki_member TO gforge;
-- REVOKE ALL ON TABLE plugin_wiki_pref FROM PUBLIC;
-- REVOKE ALL ON TABLE plugin_wiki_pref FROM gforge;
-- GRANT ALL ON TABLE plugin_wiki_pref TO gforge;
-- REVOKE ALL ON TABLE plugin_wiki_rating FROM PUBLIC;
-- REVOKE ALL ON TABLE plugin_wiki_rating FROM gforge;
-- GRANT ALL ON TABLE plugin_wiki_rating TO gforge;
-- REVOKE ALL ON TABLE plugin_wiki_session FROM PUBLIC;
-- REVOKE ALL ON TABLE plugin_wiki_session FROM gforge;
-- GRANT ALL ON TABLE plugin_wiki_session TO gforge;

CREATE TABLE plugin_wiki_config
(
  group_id integer NOT NULL,
  config_name character varying(40) NOT NULL,
  config_value integer NOT NULL DEFAULT 0,
  CONSTRAINT plugin_wiki_config_pkey PRIMARY KEY (group_id, config_name)
)
WITH OIDS;
ALTER TABLE plugin_wiki_config OWNER TO gforge;

-- For existing wikis, we enable wikiwords as before.
-- Not doing it could break links.
INSERT INTO plugin_wiki_config
  SELECT group_id AS group_id, 'DISABLE_MARKUP_WIKIWORD' AS config_name, '0' AS config_value
  FROM group_plugin, plugins
  WHERE group_plugin.plugin_id = plugins.plugin_id AND plugin_name = 'wiki';

-- For existing wikis, we disable spam prevention.
-- This is a change, but cannot be a problem.
INSERT INTO plugin_wiki_config
  SELECT group_id AS group_id, 'NUM_SPAM_LINKS' AS config_name, '0' AS config_value
  FROM group_plugin, plugins
  WHERE group_plugin.plugin_id = plugins.plugin_id AND plugin_name = 'wiki';

