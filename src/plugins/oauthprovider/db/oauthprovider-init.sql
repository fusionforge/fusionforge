CREATE SEQUENCE plugin_oauthprovider_consumer_id_seq start 1 increment 1 minvalue 1 cache 1;
CREATE TABLE plugin_oauthprovider_consumer (id	INTEGER PRIMARY KEY DEFAULT NEXTVAL('plugin_oauthprovider_consumer_id_seq'),
				name VARCHAR(128) NOT NULL,
                                consumer_key VARCHAR(250) NOT NULL,
                                consumer_secret VARCHAR(250) NOT NULL,
				consumer_url VARCHAR(250) NOT NULL,
				consumer_desc VARCHAR(500) NOT NULL,
				consumer_email VARCHAR(250) NOT NULL
);
CREATE UNIQUE INDEX idx_oauthprovider_consumer_name on plugin_oauthprovider_consumer(name);
CREATE UNIQUE INDEX idx_oauthprovider_consumer_consumer_key on plugin_oauthprovider_consumer(consumer_key);

CREATE SEQUENCE plugin_oauthprovider_request_token_id_seq start 1 increment 1 minvalue 1 cache 1;
CREATE TABLE plugin_oauthprovider_request_token (id INTEGER PRIMARY KEY DEFAULT NEXTVAL('plugin_oauthprovider_request_token_id_seq'),
                                consumer_id INTEGER REFERENCES plugin_oauthprovider_consumer(id),
                                token_key VARCHAR(250) NOT NULL,
                                token_secret VARCHAR(250) NOT NULL,
				authorized INTEGER NOT NULL DEFAULT 0,
				user_id	INTEGER NULL,
				role_id INTEGER NOT NULL DEFAULT 0,
				time_stamp INTEGER NOT NULL,
				CHECK (user_id IS NULL OR user_id>=0),
				CHECK (consumer_id>=0),
				CHECK (time_stamp>=0)
);
CREATE UNIQUE INDEX idx_oauthprovider_request_token_key on plugin_oauthprovider_request_token(token_key);

CREATE SEQUENCE plugin_oauthprovider_access_token_id_seq start 1 increment 1 minvalue 1 cache 1;
CREATE TABLE plugin_oauthprovider_access_token (id INTEGER PRIMARY KEY DEFAULT NEXTVAL('plugin_oauthprovider_access_token_id_seq'),
                                consumer_id INTEGER REFERENCES plugin_oauthprovider_consumer(id),
                                token_key VARCHAR(250) NOT NULL,
                                token_secret VARCHAR(250) NOT NULL,
				user_id	INTEGER	NULL,
				role_id INTEGER NOT NULL REFERENCES pfo_role(role_id),
				time_stamp INTEGER NOT NULL,
				CHECK (user_id IS NULL OR user_id>=0),
				CHECK (consumer_id>=0),
				CHECK (time_stamp>=0)
);
CREATE UNIQUE INDEX idx_oauthprovider_access_token_key on plugin_oauthprovider_access_token(token_key);

CREATE SEQUENCE plugin_oauthprovider_consumer_nonce_id_seq start 1 increment 1 minvalue 1 cache 1;
CREATE TABLE plugin_oauthprovider_consumer_nonce (id INTEGER PRIMARY KEY DEFAULT NEXTVAL('plugin_oauthprovider_consumer_nonce_id_seq'),
                                consumer_id INTEGER NOT NULL REFERENCES plugin_oauthprovider_consumer(id),
                                token_key VARCHAR(250) NOT NULL,
                                nonce VARCHAR(250) NOT NULL,
				time_stamp INTEGER NOT NULL,
				CHECK (consumer_id>=0),
				CHECK (time_stamp>=0)
);
