CREATE SEQUENCE doc_review_comments_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE doc_review_attachments_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;

ALTER TABLE doc_review_comments ALTER COLUMN commentid SET DEFAULT nextval('doc_review_comments_pk_seq'::text);
ALTER TABLE doc_review_attachments ALTER COLUMN attachid SET DEFAULT nextval('doc_review_attachments_pk_seq'::text);
