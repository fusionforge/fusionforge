CREATE TABLE licenses (
license_id serial unique,
license_name text
);

SELECT setval('licenses_license_id_seq',100);
INSERT INTO licenses (license_id,license_name) VALUES (100,'None');

-- 101 gpl
INSERT INTO licenses (license_name) VALUES ('GNU General Public License (GPL)');
-- 102 lgpl
INSERT INTO licenses (license_name) VALUES ('GNU Library Public License (LGPL)');
-- 103 bsd
INSERT INTO licenses (license_name) VALUES ('BSD License');
-- 104 mit
INSERT INTO licenses (license_name) VALUES ('MIT License');
-- 105 artistic
INSERT INTO licenses (license_name) VALUES ('Artistic License');
-- 106 mpl
INSERT INTO licenses (license_name) VALUES ('Mozilla Public License 1.0 (MPL)');
-- 107 qpl
INSERT INTO licenses (license_name) VALUES ('Qt Public License (QPL)');
-- 108 ibm
INSERT INTO licenses (license_name) VALUES ('IBM Public License');
-- 109 cvw
INSERT INTO licenses (license_name) VALUES ('MITRE Collaborative Virtual Workspace License (CVW License)');
-- 110 rscpl
INSERT INTO licenses (license_name) VALUES ('Ricoh Source Code Public License');
-- 111 python
INSERT INTO licenses (license_name) VALUES ('Python License');
-- 112 zlib
INSERT INTO licenses (license_name) VALUES ('zlib/libpng License');
-- 113 apache
INSERT INTO licenses (license_name) VALUES ('Apache Software License');
-- 114 vovida
INSERT INTO licenses (license_name) VALUES ('Vovida Software License 1.0');
-- 115 sissl
INSERT INTO licenses (license_name) VALUES ('Sun Internet Standards Source License (SISSL)');
-- 116 iosl
INSERT INTO licenses (license_name) VALUES ('Intel Open Source License');
-- 117 mpl11
INSERT INTO licenses (license_name) VALUES ('Mozilla Public License 1.1 (MPL 1.1)');
-- 118 jabber
INSERT INTO licenses (license_name) VALUES ('Jabber Open Source License');
-- 119 nokia
INSERT INTO licenses (license_name) VALUES ('Nokia Open Source License');
-- 120 sleepycat
INSERT INTO licenses (license_name) VALUES ('Sleepycat License');
-- 121 nethack
INSERT INTO licenses (license_name) VALUES ('Nethack General Public License');
-- 122 ibmcpl
INSERT INTO licenses (license_name) VALUES ('IBM Common Public License');
-- 123 apsl
INSERT INTO licenses (license_name) VALUES ('Apple Public Source License');
-- 124 public
INSERT INTO licenses (license_name) VALUES ('Public Domain');
-- 125 website
INSERT INTO licenses (license_name) VALUES ('Website Only');
-- 126 other
INSERT INTO licenses (license_name) VALUES ('Other/Proprietary License');

ALTER TABLE groups RENAME COLUMN license TO license_dead;
ALTER TABLE groups ADD COLUMN license INT;
ALTER TABLE groups ALTER COLUMN license SET DEFAULT 100;

UPDATE groups SET license=100;
UPDATE groups SET license=101 where license_dead='gpl';
UPDATE groups SET license=102 where license_dead='lgpl';
UPDATE groups SET license=103 where license_dead='bsd';
UPDATE groups SET license=104 where license_dead='mit';
UPDATE groups SET license=105 where license_dead='artistic';
UPDATE groups SET license=106 where license_dead='mpl';
UPDATE groups SET license=107 where license_dead='qpl';
UPDATE groups SET license=108 where license_dead='ibm';
UPDATE groups SET license=109 where license_dead='cvw';
UPDATE groups SET license=110 where license_dead='rscpl';
UPDATE groups SET license=111 where license_dead='python';
UPDATE groups SET license=112 where license_dead='zlib';
UPDATE groups SET license=113 where license_dead='apache';
UPDATE groups SET license=114 where license_dead='vovida';
UPDATE groups SET license=115 where license_dead='sissl';
UPDATE groups SET license=116 where license_dead='iosl';
UPDATE groups SET license=117 where license_dead='mpl11';
UPDATE groups SET license=118 where license_dead='jabber';
UPDATE groups SET license=119 where license_dead='nokia';
UPDATE groups SET license=120 where license_dead='sleepycat';
UPDATE groups SET license=121 where license_dead='nethack';
UPDATE groups SET license=122 where license_dead='ibmcpl';
UPDATE groups SET license=123 where license_dead='apsl';
UPDATE groups SET license=124 where license_dead='public';
UPDATE groups SET license=125 where license_dead='website';
UPDATE groups SET license=126 where license_dead='other';


ALTER TABLE groups ADD CONSTRAINT groups_license
        FOREIGN KEY (license) REFERENCES licenses(license_id) MATCH FULL;
        ALTER TABLE groups DROP COLUMN license_dead;


ALTER TABLE groups RENAME COLUMN cvs_box TO scm_box;
ALTER TABLE groups RENAME COLUMN use_cvs TO use_scm;
ALTER TABLE groups RENAME COLUMN enable_anoncvs TO enable_anonscm;
