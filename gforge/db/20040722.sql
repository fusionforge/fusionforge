CREATE TABLE licenses (
license_id serial unique,
license_name text
);

SELECT setval('licenses_license_id_seq',100);
INSERT INTO licenses (license_id,license_name) VALUES (100,'None');

INSERT INTO licenses (license_name) VALUES ('GNU General Public License (GPL)');
INSERT INTO licenses (license_name) VALUES ('GNU Library Public License (LGPL)');
INSERT INTO licenses (license_name) VALUES ('BSD License');
INSERT INTO licenses (license_name) VALUES ('MIT License');
INSERT INTO licenses (license_name) VALUES ('Artistic License');
INSERT INTO licenses (license_name) VALUES ('Mozilla Public License 1.0 (MPL)');
INSERT INTO licenses (license_name) VALUES ('Qt Public License (QPL)');
INSERT INTO licenses (license_name) VALUES ('IBM Public License');
INSERT INTO licenses (license_name) VALUES ('MITRE Collaborative Virtual Workspace License (CVW License)');
INSERT INTO licenses (license_name) VALUES ('Ricoh Source Code Public License');
INSERT INTO licenses (license_name) VALUES ('Python License');
INSERT INTO licenses (license_name) VALUES ('zlib/libpng License');
INSERT INTO licenses (license_name) VALUES ('Apache Software License');
INSERT INTO licenses (license_name) VALUES ('Vovida Software License 1.0');
INSERT INTO licenses (license_name) VALUES ('Sun Internet Standards Source License (SISSL)');
INSERT INTO licenses (license_name) VALUES ('Intel Open Source License');
INSERT INTO licenses (license_name) VALUES ('Mozilla Public License 1.1 (MPL 1.1)');
INSERT INTO licenses (license_name) VALUES ('Jabber Open Source License');
INSERT INTO licenses (license_name) VALUES ('Nokia Open Source License');
INSERT INTO licenses (license_name) VALUES ('Sleepycat License');
INSERT INTO licenses (license_name) VALUES ('Nethack General Public License');
INSERT INTO licenses (license_name) VALUES ('IBM Common Public License');
INSERT INTO licenses (license_name) VALUES ('Apple Public Source License');
INSERT INTO licenses (license_name) VALUES ('Public Domain');
INSERT INTO licenses (license_name) VALUES ('Website Only');
INSERT INTO licenses (license_name) VALUES ('Other/Proprietary License');

ALTER TABLE groups RENAME COLUMN license TO license_dead;
ALTER TABLE groups ADD COLUMN license INT;
ALTER TABLE groups ALTER COLUMN license SET DEFAULT 100;
UPDATE groups SET license=100;
ALTER TABLE groups ADD CONSTRAINT groups_license
        FOREIGN KEY (license) REFERENCES licenses(license_id) MATCH FULL;
        ALTER TABLE groups DROP COLUMN license_dead;


ALTER TABLE groups RENAME COLUMN cvs_box TO scm_box;
ALTER TABLE groups RENAME COLUMN use_cvs TO use_scm;
ALTER TABLE groups RENAME COLUMN allow_anoncvs TO allow_anonscm;
