create table sshkeys (
	id_sshkeys	serial PRIMARY KEY,
	userid		integer REFERENCES users(user_id),
	algorithm	text,
	name		text,
	fingerprint	text,
	upload		integer default 0,
	sshkey		text,
	deploy		integer default 0,
	deleted		integer default 0
);

