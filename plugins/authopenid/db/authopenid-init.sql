CREATE TABLE plugin_authopenid_user_identities (user_id INTEGER NOT NULL,
	openid_identity text);
CREATE UNIQUE INDEX idx_authopenid_user_identities_openid_identity on plugin_authopenid_user_identities(openid_identity);

