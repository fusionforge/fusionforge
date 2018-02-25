CREATE SEQUENCE doc_review_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;

CREATE TABLE doc_review_status (
    statusid    integer NOT NULL UNIQUE,
    name        text,
    description text
);

INSERT INTO doc_review_status (statusid, name) VALUES (1, 'open');
INSERT INTO doc_review_status (statusid, name) VALUES (2, 'close');
INSERT INTO doc_review_status (statusid, name) VALUES (3, 'error');

CREATE TABLE doc_review (
    revid       integer DEFAULT nextval('doc_review_pk_seq'::text) NOT NULL UNIQUE,
    created_by  integer REFERENCES users (user_id) ON DELETE CASCADE,
    statusid    integer REFERENCES doc_review_status (statusid),
    docid       integer REFERENCES doc_data (docid) ON DELETE CASCADE,
    startdate   integer,
    enddate     integer,
    title       text,
    description text
);

CREATE TABLE doc_review_version (
    revid       integer REFERENCES doc_review (revid) ON DELETE CASCADE,
    serialid    integer REFERENCES doc_data_version (serial_id) ON DELETE CASCADE
);

CREATE TABLE doc_review_users_type (
    typeid      integer NOT NULL UNIQUE,
    name        text
);

INSERT INTO doc_review_users_type (typeid, name) VALUES (1, 'mandatory');
INSERT INTO doc_review_users_type (typeid, name) VALUES (2, 'optional');

CREATE TABLE doc_review_users_status (
    statusid    integer NOT NULL UNIQUE,
    name        text
);

INSERT INTO doc_review_users_status (statusid, name) VALUES (1, 'pending');
INSERT INTO doc_review_users_status (statusid, name) VALUES (2, 'done');

CREATE TABLE doc_review_users (
    revid       integer REFERENCES doc_review (revid) ON DELETE CASCADE,
    userid      integer REFERENCES users (user_id) ON DELETE CASCADE,
    typeid      integer REFERENCES doc_review_users_type (typeid),
    statusid    integer REFERENCES doc_review_users_status (statusid),
    updatedate  integer
);

CREATE TABLE doc_review_comments (
    commentid   integer NOT NULL UNIQUE,
    revid       integer REFERENCES doc_review (revid) ON DELETE CASCADE,
    userid      integer REFERENCES users (user_id) ON DELETE CASCADE,
    rcomment    text,
    createdate  integer
);

CREATE TABLE doc_review_attachments (
    attachid    integer,
    createdate  integer,
    commentid   integer REFERENCES doc_review_comments (commentid) ON DELETE CASCADE,
    filename    text,
    filetype    text,
    filesize    text
);
