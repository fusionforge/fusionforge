CREATE SEQUENCE artifact_display_widget_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;

CREATE TABLE artifact_display_widget (
    id       integer DEFAULT nextval('artifact_display_widget_pk_seq'::text) NOT NULL,
    owner_id integer NOT NULL,
    title    text    NOT NULL,
    cols     integer NOT NULL default 1
);

CREATE TABLE artifact_display_widget_field (
    id        integer NOT NULL,
    field_id  integer NOT NULL,
    column_id integer NOT NULL default 1,
    row_id    integer NOT NULL default 1
);
