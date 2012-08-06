CREATE SEQUENCE plugin_oauthconsumer_provider_id_seq start 1 increment 1 minvalue 1 cache 1;
CREATE TABLE plugin_oauthconsumer_provider (id	INTEGER PRIMARY KEY DEFAULT NEXTVAL('plugin_oauthconsumer_provider_id_seq'),
				name VARCHAR(128) NOT NULL,
				description VARCHAR(500) NOT NULL,
                                consumer_key VARCHAR(250) NOT NULL,
                                consumer_secret VARCHAR(250) NOT NULL,
				request_token_url VARCHAR(250),
				authorize_url VARCHAR(250),
				access_token_url VARCHAR(250)
);
CREATE UNIQUE INDEX idx_oauthconsumer_provider_name on plugin_oauthconsumer_provider(name);
CREATE UNIQUE INDEX idx_oauthconsumer_provider_consumer_key on plugin_oauthconsumer_provider(consumer_key);

CREATE SEQUENCE plugin_oauthconsumer_access_token_id_seq start 1 increment 1 minvalue 1 cache 1;
CREATE TABLE plugin_oauthconsumer_access_token (id INTEGER PRIMARY KEY DEFAULT NEXTVAL('plugin_oauthconsumer_access_token_id_seq'),
                                provider_id INTEGER REFERENCES plugin_oauthconsumer_provider(id),
                                token_key VARCHAR(250) NOT NULL,
                                token_secret VARCHAR(250) NOT NULL,
				user_id	INTEGER	NOT NULL,
				time_stamp INTEGER NOT NULL,
				CHECK (user_id>=0),
				CHECK (provider_id>=0),
				CHECK (time_stamp>=0)
);
CREATE UNIQUE INDEX idx_oauthconsumer_access_token_key on plugin_oauthconsumer_access_token(token_key);

CREATE SEQUENCE plugin_oauthconsumer_resource_id_seq start 1 increment 1 minvalue 1 cache 1;
CREATE TABLE plugin_oauthconsumer_resource (id INTEGER PRIMARY KEY DEFAULT NEXTVAL('plugin_oauthconsumer_resource_id_seq'),
                                provider_id INTEGER REFERENCES plugin_oauthconsumer_provider(id),
                                url VARCHAR(250) NOT NULL,
                                http_method VARCHAR(10) NOT NULL
);
