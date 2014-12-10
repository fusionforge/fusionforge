ALTER TABLE nss_usergroups ADD last_modified_date integer;
CREATE TRIGGER nss_usergroups_update_last_modified_date
    BEFORE INSERT OR UPDATE ON artifact
    FOR EACH ROW
    EXECUTE PROCEDURE update_last_modified_date();

-- system queue
CREATE TYPE sysactionsq_status AS ENUM ('TODO', 'WIP', 'DONE', 'ERROR');
CREATE TABLE sysactionsq (
    sysactionsq_id  SERIAL PRIMARY KEY,
    plugin_id       integer REFERENCES plugins ON DELETE CASCADE,
    sysaction_id    integer NOT NULL,
    user_id         integer REFERENCES users ON DELETE CASCADE,
    group_id        integer REFERENCES groups ON DELETE CASCADE,
    status          sysactionsq_status DEFAULT 'TODO' NOT NULL,
    error_message   text,
    requested       timestamp,
    started         timestamp,
    stopped         timestamp
);
CREATE INDEX sysactionsq_status ON sysactionsq(status);
