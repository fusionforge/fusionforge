CREATE OR REPLACE TRIGGER user_group_user_id_fk 
        AFTER INSERT OR UPDATE 
        ON user_group FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from users
        where :new.user_id = users.user_id;
        if (:new.user_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE user_group using non-existing user_id (user_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER user_group_group_id_fk 
        AFTER INSERT OR UPDATE 
        ON user_group FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from groups
        where :new.group_id = groups.group_id;
        if (:new.group_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE user_group using non-existing group_id (group_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER forum_posted_by_fk 
        AFTER INSERT OR UPDATE 
        ON forum FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from users
        where :new.posted_by = users.user_id;
        if (:new.posted_by is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE forum using non-existing user_id (posted_by).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER forum_group_forum_id_fk 
        AFTER INSERT OR UPDATE 
        ON forum FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from forum_group_list
        where :new.group_forum_id = forum_group_list.group_forum_id;
        if (:new.group_forum_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE forum using non-existing group_forum_id (group_forum_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER forum_group_list_group_id_fk 
        AFTER INSERT OR UPDATE 
        ON forum_group_list FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from groups
        where :new.group_id = groups.group_id;
        if (:new.group_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE forum_group_list using non-existing group_id (group_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER bug_group_group_fk 
        AFTER INSERT OR UPDATE 
        ON bug_group FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from groups
        where :new.group_id = groups.group_id;
        if (:new.group_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE bug_group using non-existing group_id (group_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER bug_category_group_fk 
        AFTER INSERT OR UPDATE 
        ON bug_category FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from groups
        where :new.group_id = groups.group_id;
        if (:new.group_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE bug_category using non-existing group_id (group_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER bug_submitted_by_fk 
        AFTER INSERT OR UPDATE 
        ON bug FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from users
        where :new.submitted_by = users.user_id;
        if (:new.submitted_by is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE bug using non-existing user_id (submitted_by).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER bug_assigned_to_fk 
        AFTER INSERT OR UPDATE 
        ON bug FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from users
        where :new.assigned_to = users.user_id;
        if (:new.assigned_to is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE bug using non-existing user_id (assigned_to).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER bug_status_fk 
        AFTER INSERT OR UPDATE 
        ON bug FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from bug_status
        where :new.status_id = bug_status.status_id;
        if (:new.status_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE bug using non-existing status_id (status_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER bug_category_fk 
        AFTER INSERT OR UPDATE 
        ON bug FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from bug_category
        where :new.category_id = bug_category.bug_category_id;
        if (:new.category_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE bug using non-existing bug_category_id (category_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER bug_resolution_fk 
        AFTER INSERT OR UPDATE 
        ON bug FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from bug_resolution
        where :new.resolution_id = bug_resolution.resolution_id;
        if (:new.resolution_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE bug using non-existing resolution_id (resolution_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER bug_group_fk 
        AFTER INSERT OR UPDATE 
        ON bug FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from bug_group
        where :new.bug_group_id = bug_group.bug_group_id;
        if (:new.bug_group_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE bug using non-existing bug_group_id (bug_group_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER forum_posted_by_fk 
        AFTER INSERT OR UPDATE 
        ON forum FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from users
        where :new.posted_by = users.user_id;
        if (:new.posted_by is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE forum using non-existing user_id (posted_by).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER forum_group_forum_id_fk 
        AFTER INSERT OR UPDATE 
        ON forum FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from forum_group_list
        where :new.group_forum_id = forum_group_list.group_forum_id;
        if (:new.group_forum_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE forum using non-existing group_forum_id (group_forum_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER project_group_list_group_id_fk 
        AFTER INSERT OR UPDATE 
        ON project_group_list FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from groups
        where :new.group_id = groups.group_id;
        if (:new.group_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE project_group_list using non-existing group_id (group_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER project_task_group_proj_id_f 
        AFTER INSERT OR UPDATE 
        ON project_task FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from project_group_list
        where :new.group_project_id = project_group_list.group_project_id;
        if (:new.group_project_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE project_task using non-existing group_project_id (group_project_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER project_task_created_by_fk 
        AFTER INSERT OR UPDATE 
        ON project_task FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from users
        where :new.created_by = users.user_id;
        if (:new.created_by is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE project_task using non-existing user_id (created_by).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER project_task_status_id_fk 
        AFTER INSERT OR UPDATE 
        ON project_task FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from project_status
        where :new.status_id = project_status.status_id;
        if (:new.status_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE project_task using non-existing status_id (status_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER patch_status_id_fk 
        AFTER INSERT OR UPDATE 
        ON patch FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from patch_status
        where :new.patch_status_id = patch_status.patch_status_id;
        if (:new.patch_status_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE patch using non-existing patch_status_id (patch_status_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER patch_category_id_fk 
        AFTER INSERT OR UPDATE 
        ON patch FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from patch_category
        where :new.patch_category_id = patch_category.patch_category_id;
        if (:new.patch_category_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE patch using non-existing patch_category_id (patch_category_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER patch_submitted_by_fk 
        AFTER INSERT OR UPDATE 
        ON patch FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from users
        where :new.submitted_by = users.user_id;
        if (:new.submitted_by is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE patch using non-existing user_id (submitted_by).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER patch_assigned_to_fk 
        AFTER INSERT OR UPDATE 
        ON patch FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from users
        where :new.assigned_to = users.user_id;
        if (:new.assigned_to is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE patch using non-existing user_id (assigned_to).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER patch_category_group_id_fk 
        AFTER INSERT OR UPDATE 
        ON patch_category FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from groups
        where :new.group_id = groups.group_id;
        if (:new.group_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE patch_category using non-existing group_id (group_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER support_status_id_fk 
        AFTER INSERT OR UPDATE 
        ON support FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from support_status
        where :new.support_status_id = support_status.support_status_id;
        if (:new.support_status_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE support using non-existing support_status_id (support_status_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER support_category_id_fk 
        AFTER INSERT OR UPDATE 
        ON support FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from support_category
        where :new.support_category_id = support_category.support_category_id;
        if (:new.support_category_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE support using non-existing support_category_id (support_category_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER support_submitted_by_fk 
        AFTER INSERT OR UPDATE 
        ON support FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from users
        where :new.submitted_by = users.user_id;
        if (:new.submitted_by is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE support using non-existing user_id (submitted_by).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER support_assigned_to_fk 
        AFTER INSERT OR UPDATE 
        ON support FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from users
        where :new.assigned_to = users.user_id;
        if (:new.assigned_to is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE support using non-existing user_id (assigned_to).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER support_category_group_id_fk 
        AFTER INSERT OR UPDATE 
        ON support_category FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from groups
        where :new.group_id = groups.group_id;
        if (:new.group_id is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE support_category using non-existing group_id (group_id).');
        end if;
end;

/
CREATE OR REPLACE TRIGGER users_languageid_fk 
        AFTER INSERT OR UPDATE 
        ON users FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from supported_languages
        where :new.language = supported_languages.language_id;
        if (:new.language is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE users using non-existing language_id (language).');
        end if;
end;

/
