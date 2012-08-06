CREATE TABLE plugin_mailman (
	  listname character varying(100) NOT NULL,
	  address character varying(255) NOT NULL,
	  hide character varying(255) NOT NULL default 'N',
	  nomail character varying(255) NOT NULL default 'N',
	  ack character varying(255) NOT NULL default 'Y',
	  not_metoo character varying(255) NOT NULL default 'Y',
	  digest character varying(255) NOT NULL default 'N',
	  plain character varying(255) NOT NULL default 'N',
	  password character varying(255) NOT NULL default '!',
	  lang character varying(255) NOT NULL default 'en',
	  name character varying(255) default NULL,
	  one_last_digest character varying(255) NOT NULL default 'N',
	  user_options bigint NOT NULL default 0,
	  delivery_status integer NOT NULL default 0,
	  topics_userinterest character varying(255) default NULL,
	  delivery_status_timestamp timestamp without time zone default '1901-01-01 01:01:01',
	  bi_cookie character varying(255) default NULL,
	  bi_score double precision NOT NULL default '0',
	  bi_noticesleft double precision NOT NULL default '0',
	  bi_lastnotice date NOT NULL default '1901-01-01',
	  bi_date date NOT NULL default '1901-01-01',
	  PRIMARY KEY  (listname, address)
);
