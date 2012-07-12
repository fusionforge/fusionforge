CREATE TABLE plugin_authwebid_user_identities (user_id INTEGER NOT NULL,
	webid_identity text);
CREATE UNIQUE INDEX idx_authwebid_user_identities_webid_identity on plugin_authwebid_user_identities(webid_identity);

