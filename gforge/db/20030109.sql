
---
--- This function updates the project_sums_agg count for a given type.
---
CREATE FUNCTION "project_sums" () RETURNS OPAQUE AS '
	DECLARE
		num integer;
		curr_group integer;
	BEGIN
		---
		--- Get number of things this group has now
		---
		IF TG_ARGV[0]=\'surv\' THEN
			IF TG_OP=\'DELETE\' THEN
				SELECT INTO num count(*) FROM surveys WHERE OLD.group_id=group_id AND is_active=1;
				curr_group := OLD.group_id;
			ELSE
				SELECT INTO num count(*) FROM surveys WHERE NEW.group_id=group_id AND is_active=1;
				curr_group := NEW.group_id;
			END IF;
		END IF;
		IF TG_ARGV[0]=\'mail\' THEN
			IF TG_OP=\'DELETE\' THEN
				SELECT INTO num count(*) FROM mail_group_list WHERE OLD.group_id=group_id AND is_public=1;
				curr_group := OLD.group_id;
			ELSE
				SELECT INTO num count(*) FROM mail_group_list WHERE NEW.group_id=group_id AND is_public=1;
				curr_group := NEW.group_id;
			END IF;
		END IF;
		IF TG_ARGV[0]=\'fmsg\' THEN
			IF TG_OP=\'DELETE\' THEN
				SELECT INTO curr_group group_id FROM forum_group_list WHERE OLD.group_forum_id=group_forum_id;
				SELECT INTO num count(*) FROM forum, forum_group_list WHERE forum.group_forum_id=forum_group_list.group_forum_id AND forum_group_list.is_public=1 AND forum_group_list.group_id=curr_group;
			ELSE
				SELECT INTO curr_group group_id FROM forum_group_list WHERE NEW.group_forum_id=group_forum_id;
				SELECT INTO num count(*) FROM forum, forum_group_list WHERE forum.group_forum_id=forum_group_list.group_forum_id AND forum_group_list.is_public=1 AND forum_group_list.group_id=curr_group;
			END IF;
		END IF;
		IF TG_ARGV[0]=\'fora\' THEN
			IF TG_OP=\'DELETE\' THEN
				SELECT INTO num count(*) FROM forum_group_list WHERE OLD.group_id=group_id AND is_public=1;
				curr_group = OLD.group_id;
				--- also need to update message count
				DELETE FROM project_sums_agg WHERE group_id=OLD.group_id AND type=\'fmsg\';
				INSERT INTO project_sums_agg
					SELECT forum_group_list.group_id,\'fmsg\'::text AS type, count(forum.msg_id) AS count
					FROM forum, forum_group_list
					WHERE forum.group_forum_id=forum_group_list.group_forum_id AND forum_group_list.is_public=1 GROUP BY group_id,type;
			ELSE
				SELECT INTO num count(*) FROM forum_group_list WHERE NEW.group_id=group_id AND is_public=1;
				curr_group = NEW.group_id;
				--- fora do not get deleted... they get their status set to 9
				IF NEW.is_public=9 THEN
					--- also need to update message count
					DELETE FROM project_sums_agg WHERE group_id=NEW.group_id AND type=\'fmsg\';
					INSERT INTO project_sums_agg
						SELECT forum_group_list.group_id,\'fmsg\'::text AS type, count(forum.msg_id) AS count
						FROM forum, forum_group_list
						WHERE forum.group_forum_id=forum_group_list.group_forum_id AND forum_group_list.is_public=1 GROUP BY group_id,type;
				END IF;
			END IF;
		END IF;
		---
		--- See if this group already has a row in project_sums_agg for these things
		---
		PERFORM * FROM project_sums_agg WHERE curr_group=group_id AND type=TG_ARGV[0];

		IF NOT FOUND THEN
			---
			--- Create row for this group
			---
			INSERT INTO project_sums_agg
				VALUES (curr_group, TG_ARGV[0], num);
		ELSE
			---
			--- Update count
			---
			UPDATE project_sums_agg SET count=num
			WHERE curr_group=group_id AND type=TG_ARGV[0];
		END IF;

		IF TG_OP=\'DELETE\' THEN
			RETURN OLD;
		ELSE
			RETURN NEW;
		END IF;
	END;
' LANGUAGE 'plpgsql';

CREATE TRIGGER "surveys_agg_trig" AFTER INSERT OR DELETE OR UPDATE ON "surveys" FOR EACH ROW EXECUTE PROCEDURE project_sums('surv');

CREATE TRIGGER "mail_agg_trig" AFTER INSERT OR DELETE OR UPDATE ON "mail_group_list" FOR EACH ROW EXECUTE PROCEDURE project_sums('mail');

CREATE TRIGGER "fmsg_agg_trig" AFTER INSERT OR DELETE OR UPDATE ON "forum" FOR EACH ROW EXECUTE PROCEDURE project_sums('fmsg');

CREATE TRIGGER "fora_agg_trig" AFTER INSERT OR DELETE OR UPDATE ON "forum_group_list" FOR EACH ROW EXECUTE PROCEDURE project_sums('fora');
