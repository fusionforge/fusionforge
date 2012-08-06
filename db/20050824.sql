alter table forum_attachment DROP CONSTRAINT forum_attachment_key;
alter table forum_attachment DROP CONSTRAINT msg_id;

alter table forum_attachment add PRIMARY KEY (attachmentid);
alter table forum_attachment alter userid set DEFAULT 100;

ALTER TABLE forum_attachment ADD FOREIGN KEY (msg_id) REFERENCES forum(msg_id) ON DELETE CASCADE;
ALTER TABLE forum_attachment ADD FOREIGN KEY (userid) REFERENCES users(user_id) ON DELETE SET DEFAULT;

DROP TABLE forum_attachmenttype;

CREATE TABLE forum_attachment_type (
    extension character varying(20) DEFAULT ''::character varying NOT NULL,
    mimetype character varying(255) DEFAULT ''::character varying NOT NULL,
    size integer DEFAULT 0 NOT NULL,
    width smallint DEFAULT 0::smallint NOT NULL,
    height smallint DEFAULT 0::smallint NOT NULL,
    enabled smallint DEFAULT 1::smallint NOT NULL
);

ALTER TABLE ONLY forum_attachment_type ADD PRIMARY KEY (extension);

INSERT INTO forum_attachment_type VALUES ('gif', 'a:1:{i:0;s:23:"Content-type: image/gif";}', 20000, 620, 280, 1);
INSERT INTO forum_attachment_type VALUES ('jpeg', 'a:1:{i:0;s:24:"Content-type: image/jpeg";}', 20000, 620, 280, 1);
INSERT INTO forum_attachment_type VALUES ('jpg', 'a:1:{i:0;s:24:"Content-type: image/jpeg";}', 100000, 0, 0, 1);
INSERT INTO forum_attachment_type VALUES ('jpe', 'a:1:{i:0;s:24:"Content-type: image/jpeg";}', 20000, 620, 280, 1);
INSERT INTO forum_attachment_type VALUES ('png', 'a:1:{i:0;s:23:"Content-type: image/png";}', 20000, 620, 280, 1);
INSERT INTO forum_attachment_type VALUES ('doc', 'a:2:{i:0;s:20:"Accept-ranges: bytes";i:1;s:32:"Content-type: application/msword";}', 20000, 0, 0, 1);
INSERT INTO forum_attachment_type VALUES ('pdf', 'a:1:{i:0;s:29:"Content-type: application/pdf";}', 20000, 0, 0, 1);
INSERT INTO forum_attachment_type VALUES ('bmp', 'a:1:{i:0;s:26:"Content-type: image/bitmap";}', 20000, 620, 280, 1);
INSERT INTO forum_attachment_type VALUES ('psd', 'a:1:{i:0;s:29:"Content-type: unknown/unknown";}', 20000, 0, 0, 1);
INSERT INTO forum_attachment_type VALUES ('zip', 'a:1:{i:0;s:29:"Content-type: application/zip";}', 100000, 0, 0, 1);
INSERT INTO forum_attachment_type VALUES ('txt', 'a:1:{i:0;s:24:"Content-type: plain/text";}', 20000, 0, 0, 1);
