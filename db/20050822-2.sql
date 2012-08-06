--
-- Modification to forum table for bbcode support
--

alter table forum add bbcode_uid character varying(15);

--
-- forum_attachment
--


--
-- Name: forum_attachment; Type: TABLE; Schema: public; Owner: gforge; Tablespace:
--


CREATE TABLE forum_attachment (
    attachmentid serial NOT NULL,
    userid integer DEFAULT 0 NOT NULL,
    dateline integer DEFAULT 0 NOT NULL,
    filename character varying(100) DEFAULT ''::character varying NOT NULL,
    filedata text NOT NULL,
    visible smallint DEFAULT (0)::smallint NOT NULL,
    counter smallint DEFAULT (0)::smallint NOT NULL,
    filesize integer DEFAULT 0 NOT NULL,
    msg_id integer DEFAULT 0 NOT NULL,
    filehash character varying(32) DEFAULT ''::character varying NOT NULL
);



--
-- Name: filehash; Type: CONSTRAINT; Schema: public; Owner: gforge; Tablespace:
--

ALTER TABLE ONLY forum_attachment
    ADD CONSTRAINT filehash UNIQUE (filehash);



--
-- Name: forum_attachment_key; Type: CONSTRAINT; Schema: public; Owner: gforge; Tablespace:
--

ALTER TABLE ONLY forum_attachment
    ADD CONSTRAINT forum_attachment_key PRIMARY KEY (attachmentid);



--
-- Name: msg_id; Type: CONSTRAINT; Schema: public; Owner: gforge; Tablespace:
--

ALTER TABLE ONLY forum_attachment
    ADD CONSTRAINT msg_id UNIQUE (msg_id);



--
-- forum_attachmenttype
--


--
-- Name: forum_attachmenttype; Type: TABLE; Schema: public; Owner: gforge; Tablespace:
--


CREATE TABLE forum_attachmenttype (
    extension character varying(20) DEFAULT ''::character varying NOT NULL,
    mimetype character varying(255) DEFAULT ''::character varying NOT NULL,
    size integer DEFAULT 0 NOT NULL,
    width smallint DEFAULT 0::smallint NOT NULL,
    height smallint DEFAULT 0::smallint NOT NULL,
    enabled smallint DEFAULT 1::smallint NOT NULL,
    display smallint DEFAULT 0::smallint NOT NULL
);



--
-- Data for Name: forum_attachmenttype; Type: TABLE DATA; Schema: public; Owner: gforge
--

INSERT INTO forum_attachmenttype VALUES ('gif', 'a:1:{i:0;s:23:"Content-type: image/gif";}', 20000, 620, 280, 1, 0);
INSERT INTO forum_attachmenttype VALUES ('jpeg', 'a:1:{i:0;s:24:"Content-type: image/jpeg";}', 20000, 620, 280, 1, 0);
INSERT INTO forum_attachmenttype VALUES ('jpg', 'a:1:{i:0;s:24:"Content-type: image/jpeg";}', 100000, 0, 0, 1, 0);
INSERT INTO forum_attachmenttype VALUES ('jpe', 'a:1:{i:0;s:24:"Content-type: image/jpeg";}', 20000, 620, 280, 1, 0);
INSERT INTO forum_attachmenttype VALUES ('png', 'a:1:{i:0;s:23:"Content-type: image/png";}', 20000, 620, 280, 1, 0);
INSERT INTO forum_attachmenttype VALUES ('txt', 'a:1:{i:0;s:24:"Content-type: plain/text";}', 20000, 0, 0, 1, 2);
INSERT INTO forum_attachmenttype VALUES ('doc', 'a:2:{i:0;s:20:"Accept-ranges: bytes";i:1;s:32:"Content-type: application/msword";}', 20000, 0, 0, 1, 0);
INSERT INTO forum_attachmenttype VALUES ('pdf', 'a:1:{i:0;s:29:"Content-type: application/pdf";}', 20000, 0, 0, 1, 0);
INSERT INTO forum_attachmenttype VALUES ('bmp', 'a:1:{i:0;s:26:"Content-type: image/bitmap";}', 20000, 620, 280, 1, 0);
INSERT INTO forum_attachmenttype VALUES ('psd', 'a:1:{i:0;s:29:"Content-type: unknown/unknown";}', 20000, 0, 0, 1, 0);
INSERT INTO forum_attachmenttype VALUES ('zip', 'a:1:{i:0;s:29:"Content-type: application/zip";}', 100000, 0, 0, 1, 0);


--
-- Name: forum_attachmenttype_key; Type: CONSTRAINT; Schema: public; Owner: gforge; Tablespace:
--

ALTER TABLE ONLY forum_attachmenttype
    ADD CONSTRAINT forum_attachmenttype_key PRIMARY KEY (extension);


--
-- change the views
--

DROP VIEW forum_user_vw;

CREATE VIEW forum_user_vw AS
    SELECT forum.msg_id, forum.group_forum_id, forum.posted_by, forum.subject, forum.body, forum.post_date, forum.is_followup_to, forum.thread_id, forum.has_followups, forum.most_recent_date,
forum.bbcode_uid, users.user_name, users.realname FROM forum, users WHERE (forum.posted_by = users.user_id);


