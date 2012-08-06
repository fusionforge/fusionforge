--
-- Enforce unique user names
--

create unique index users_namename_uniq on users(user_name);
DROP INDEX user_user;
DROP INDEX idx_users_username;

--
--	INSTALL PL/pgSQL
--
CREATE FUNCTION plpgsql_call_handler() RETURNS language_handler
    AS '$libdir/plpgsql', 'plpgsql_call_handler'
    LANGUAGE c;

CREATE TRUSTED PROCEDURAL LANGUAGE 'plpgsql'
		 HANDLER plpgsql_call_handler
		 LANCOMPILER 'PL/pgSQL';

--
--      Define a trigger so when you create a new ArtifactType
--      You automatically create a related row over in the counters table
--
CREATE FUNCTION forumgrouplist_insert_agg () RETURNS OPAQUE AS '
BEGIN
        INSERT INTO forum_agg_msg_count (group_forum_id,count) \
                VALUES (NEW.group_forum_id,0);
        RETURN NEW;
END;
' LANGUAGE 'plpgsql';

CREATE TRIGGER forumgrouplist_insert_trig AFTER INSERT ON forum_group_list
        FOR EACH ROW EXECUTE PROCEDURE forumgrouplist_insert_agg();

--
--  Define a rule so that when new forum messages are submitted,
--  the counters increment
--
CREATE RULE forum_insert_agg AS
    ON INSERT TO forum
    DO UPDATE forum_agg_msg_count SET count=count+1
        WHERE group_forum_id=new.group_forum_id;

CREATE RULE forum_delete_agg AS
    ON DELETE TO forum
    DO UPDATE forum_agg_msg_count SET count=count-1
        WHERE group_forum_id=old.group_forum_id;


--
--	People want the open counts added to the artifact counts
--
ALTER TABLE artifact_counts_agg ADD COLUMN open_count int;

--
--	Define a trigger so when you create a new ArtifactType
--	You automatically create a related row over in the counters table
--
CREATE FUNCTION artifactgrouplist_insert_agg () RETURNS OPAQUE AS '
BEGIN
	INSERT INTO artifact_counts_agg (group_artifact_id,count,open_count) \
		VALUES (NEW.group_artifact_id,0,0);
        RETURN NEW;
END;
' LANGUAGE 'plpgsql';

CREATE TRIGGER artifactgrouplist_insert_trig AFTER INSERT ON artifact_group_list
        FOR EACH ROW EXECUTE PROCEDURE artifactgrouplist_insert_agg();

--
--	Define a rule so that when new artifacts are submitted,
--	the counters increment
--
CREATE RULE artifact_insert_agg AS
	ON INSERT TO artifact
	DO UPDATE artifact_counts_agg SET count=count+1,open_count=open_count+1
		WHERE group_artifact_id=new.group_artifact_id;

--
--
--
drop TRIGGER artifactgroup_update_trig ON artifact;

CREATE FUNCTION artifactgroup_update_agg () RETURNS OPAQUE AS '
BEGIN
	--
	-- see if they are moving to a new artifacttype
	-- if so, its a more complex operation
	--
	IF NEW.group_artifact_id <> OLD.group_artifact_id THEN
		--
		-- transferred artifacts always have a status of 1
		-- so we will increment the new artifacttypes sums
		--
		UPDATE artifact_counts_agg SET count=count+1, open_count=open_count+1 \
			WHERE group_artifact_id=NEW.group_artifact_id;

		--
		--	now see how to increment/decrement the old types sums
		--
		IF NEW.status_id <> OLD.status_id THEN
			IF OLD.status_id = 2 THEN
				UPDATE artifact_counts_agg SET count=count-1 \
					WHERE group_artifact_id=OLD.group_artifact_id;
			--
			--	no need to do anything if it was in deleted status
			--
			END IF;
		ELSE
			--
			--	Was already in open status before
			--
			UPDATE artifact_counts_agg SET count=count-1, open_count=open_count-1 \
				WHERE group_artifact_id=OLD.group_artifact_id;
		END IF;
	ELSE
		--
		-- just need to evaluate the status flag and
		-- increment/decrement the counter as necessary
		--
		IF NEW.status_id <> OLD.status_id THEN
			IF new.status_id = 1 THEN
				UPDATE artifact_counts_agg SET open_count=open_count+1 \
					WHERE group_artifact_id=new.group_artifact_id;
			ELSE
				IF new.status_id = 2 THEN
					UPDATE artifact_counts_agg SET open_count=open_count-1 \
						WHERE group_artifact_id=new.group_artifact_id;
				ELSE
					IF new.status_id = 3 THEN
						UPDATE artifact_counts_agg SET open_count=open_count-1,count=count-1 \
							WHERE group_artifact_id=new.group_artifact_id;
					END IF;
				END IF;
			END IF;
		END IF;
	END IF;
	RETURN NEW;
END;
' LANGUAGE 'plpgsql';

CREATE TRIGGER artifactgroup_update_trig AFTER UPDATE ON artifact
	FOR EACH ROW EXECUTE PROCEDURE artifactgroup_update_agg();
