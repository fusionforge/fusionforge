
CREATE TABLE plugin_wiki_page (
    id integer NOT NULL,
    pagename character varying(100) NOT NULL,
    hits integer DEFAULT 0 NOT NULL,
    pagedata text DEFAULT ''::text NOT NULL,
    cached_html text DEFAULT ''::text
);

CREATE TABLE plugin_wiki_version (
    id integer NOT NULL,
    "version" integer NOT NULL,
    mtime integer NOT NULL,
    minor_edit smallint DEFAULT 0,
    content text DEFAULT ''::text NOT NULL,
    versiondata text DEFAULT ''::text NOT NULL
);

CREATE TABLE plugin_wiki_recent (
    id integer NOT NULL,
    latestversion integer,
    latestmajor integer,
    latestminor integer
);

CREATE TABLE plugin_wiki_nonempty (
    id integer NOT NULL
);

CREATE TABLE plugin_wiki_link (
    linkfrom integer NOT NULL,
    linkto integer NOT NULL
);

CREATE TABLE plugin_wiki_rating (
    dimension integer NOT NULL,
    raterpage bigint NOT NULL,
    rateepage bigint NOT NULL,
    ratingvalue double precision NOT NULL,
    rateeversion bigint NOT NULL,
    tstamp timestamp without time zone NOT NULL
);

CREATE TABLE plugin_wiki_session (
    sess_id character(32) NOT NULL,
    sess_data text NOT NULL,
    sess_date integer,
    sess_ip character(40) NOT NULL
);

CREATE TABLE plugin_wiki_pref (
    userid character(48) NOT NULL,
    prefs text DEFAULT ''::text,
    passwd character(48) DEFAULT ''::bpchar,
    groupname character(48) DEFAULT 'users'::bpchar
);

CREATE TABLE plugin_wiki_member (
    userid character(48) NOT NULL,
    groupname character(48) DEFAULT 'users'::bpchar NOT NULL
);

CREATE TABLE plugin_wiki_accesslog (
    time_stamp integer,
    remote_host character varying(50),
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

CREATE UNIQUE INDEX plugin_wiki_page_id ON plugin_wiki_page USING btree (id);

CREATE UNIQUE INDEX plugin_wiki_page_nm ON plugin_wiki_page USING btree (pagename);

CREATE UNIQUE INDEX plugin_wiki_vers_id ON plugin_wiki_version USING btree (id, "version");

CREATE INDEX plugin_wiki_vers_mtime ON plugin_wiki_version USING btree (mtime);

CREATE UNIQUE INDEX plugin_wiki_recent_id ON plugin_wiki_recent USING btree (id);

CREATE UNIQUE INDEX plugin_wiki_nonmt_id ON plugin_wiki_nonempty USING btree (id);

CREATE INDEX plugin_wiki_link_from ON plugin_wiki_link USING btree (linkfrom);

CREATE INDEX plugin_wiki_link_to ON plugin_wiki_link USING btree (linkto);

CREATE UNIQUE INDEX plugin_wiki_rating_id ON plugin_wiki_rating USING btree (dimension, raterpage, rateepage);

CREATE INDEX plugin_wiki_sess_date ON plugin_wiki_session USING btree (sess_date);

CREATE INDEX plugin_wiki_sess_ip ON plugin_wiki_session USING btree (sess_ip);

CREATE INDEX plugin_wiki_member_id_idx ON plugin_wiki_member USING btree (userid);

CREATE INDEX plugin_wiki_member_group_idx ON plugin_wiki_member USING btree (groupname);

CREATE INDEX plugin_wiki_log_time ON plugin_wiki_accesslog USING btree (time_stamp);

CREATE INDEX plugin_wiki_log_host ON plugin_wiki_accesslog USING btree (remote_host);

ALTER TABLE ONLY plugin_wiki_session
    ADD CONSTRAINT plugin_wiki_session_pkey PRIMARY KEY (sess_id);

ALTER TABLE ONLY plugin_wiki_pref
    ADD CONSTRAINT pref_pkey PRIMARY KEY (userid);

ALTER TABLE ONLY plugin_wiki_member
    ADD CONSTRAINT "$1" FOREIGN KEY (userid) REFERENCES plugin_wiki_pref(userid) ON DELETE CASCADE;
