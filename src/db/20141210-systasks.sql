ALTER TABLE nss_usergroups ADD last_modified_date integer;
CREATE TRIGGER nss_usergroups_update_last_modified_date
    BEFORE INSERT OR UPDATE ON nss_usergroups
    FOR EACH ROW
    EXECUTE PROCEDURE update_last_modified_date();

-- system queue
CREATE TYPE systask_status AS ENUM ('TODO', 'WIP', 'DONE', 'ERROR');
CREATE TABLE systasks (
    systask_id       SERIAL PRIMARY KEY,
    plugin_id        integer REFERENCES plugins ON DELETE CASCADE,
    systask_type     text NOT NULL,
    group_id         integer REFERENCES groups ON DELETE CASCADE,
    user_id          integer REFERENCES users ON DELETE CASCADE,
    status           systask_status DEFAULT 'TODO' NOT NULL,
    error_message    text,
    requested        timestamp,
    started          timestamp,
    stopped          timestamp
);
CREATE INDEX systasks_status ON systasks(status);
