
CREATE OR REPLACE TRIGGER A_bug_pk_seq
        BEFORE INSERT OR UPDATE of bug_id
        ON bug FOR EACH ROW
BEGIN
        IF (:new.bug_id is null) then
          IF INSERTING THEN
            SELECT bug_pk_seq.nextval INTO :new.bug_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.bug_id := :old.bug_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_bug_bug_dependencies_pk_seq
        BEFORE INSERT OR UPDATE of bug_depend_id
        ON bug_bug_dependencies FOR EACH ROW
BEGIN
        IF (:new.bug_depend_id is null) then
          IF INSERTING THEN
            SELECT bug_bug_dependencies_pk_seq.nextval INTO :new.bug_depend_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.bug_depend_id := :old.bug_depend_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_bug_canned_responses_pk_seq
        BEFORE INSERT OR UPDATE of bug_canned_id
        ON bug_canned_responses FOR EACH ROW
BEGIN
        IF (:new.bug_canned_id is null) then
          IF INSERTING THEN
            SELECT bug_canned_responses_pk_seq.nextval INTO :new.bug_canned_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.bug_canned_id := :old.bug_canned_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_bug_category_pk_seq
        BEFORE INSERT OR UPDATE of bug_category_id
        ON bug_category FOR EACH ROW
BEGIN
        IF (:new.bug_category_id is null) then
          IF INSERTING THEN
            SELECT bug_category_pk_seq.nextval INTO :new.bug_category_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.bug_category_id := :old.bug_category_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_bug_filter_pk_seq
        BEFORE INSERT OR UPDATE of filter_id
        ON bug_filter FOR EACH ROW
BEGIN
        IF (:new.filter_id is null) then
          IF INSERTING THEN
            SELECT bug_filter_pk_seq.nextval INTO :new.filter_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.filter_id := :old.filter_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_bug_group_pk_seq
        BEFORE INSERT OR UPDATE of bug_group_id
        ON bug_group FOR EACH ROW
BEGIN
        IF (:new.bug_group_id is null) then
          IF INSERTING THEN
            SELECT bug_group_pk_seq.nextval INTO :new.bug_group_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.bug_group_id := :old.bug_group_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_bug_history_pk_seq
        BEFORE INSERT OR UPDATE of bug_history_id
        ON bug_history FOR EACH ROW
BEGIN
        IF (:new.bug_history_id is null) then
          IF INSERTING THEN
            SELECT bug_history_pk_seq.nextval INTO :new.bug_history_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.bug_history_id := :old.bug_history_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_bug_resolution_pk_seq
        BEFORE INSERT OR UPDATE of resolution_id
        ON bug_resolution FOR EACH ROW
BEGIN
        IF (:new.resolution_id is null) then
          IF INSERTING THEN
            SELECT bug_resolution_pk_seq.nextval INTO :new.resolution_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.resolution_id := :old.resolution_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_bug_status_pk_seq
        BEFORE INSERT OR UPDATE of status_id
        ON bug_status FOR EACH ROW
BEGIN
        IF (:new.status_id is null) then
          IF INSERTING THEN
            SELECT bug_status_pk_seq.nextval INTO :new.status_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.status_id := :old.status_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_bug_task_dependencies_pk_seq
        BEFORE INSERT OR UPDATE of bug_depend_id
        ON bug_task_dependencies FOR EACH ROW
BEGIN
        IF (:new.bug_depend_id is null) then
          IF INSERTING THEN
            SELECT bug_task_dependencies_pk_seq.nextval INTO :new.bug_depend_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.bug_depend_id := :old.bug_depend_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_canned_responses_pk_seq
        BEFORE INSERT OR UPDATE of response_id
        ON canned_responses FOR EACH ROW
BEGIN
        IF (:new.response_id is null) then
          IF INSERTING THEN
            SELECT canned_responses_pk_seq.nextval INTO :new.response_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.response_id := :old.response_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_db_images_pk_seq
        BEFORE INSERT OR UPDATE of id
        ON db_images FOR EACH ROW
BEGIN
        IF (:new.id is null) then
          IF INSERTING THEN
            SELECT db_images_pk_seq.nextval INTO :new.id FROM DUAL;
          ELSIF UPDATING THEN
            :new.id := :old.id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_doc_data_pk_seq
        BEFORE INSERT OR UPDATE of docid
        ON doc_data FOR EACH ROW
BEGIN
        IF (:new.docid is null) then
          IF INSERTING THEN
            SELECT doc_data_pk_seq.nextval INTO :new.docid FROM DUAL;
          ELSIF UPDATING THEN
            :new.docid := :old.docid;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_doc_groups_pk_seq
        BEFORE INSERT OR UPDATE of doc_group
        ON doc_groups FOR EACH ROW
BEGIN
        IF (:new.doc_group is null) then
          IF INSERTING THEN
            SELECT doc_groups_pk_seq.nextval INTO :new.doc_group FROM DUAL;
          ELSIF UPDATING THEN
            :new.doc_group := :old.doc_group;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_doc_states_pk_seq
        BEFORE INSERT OR UPDATE of stateid
        ON doc_states FOR EACH ROW
BEGIN
        IF (:new.stateid is null) then
          IF INSERTING THEN
            SELECT doc_states_pk_seq.nextval INTO :new.stateid FROM DUAL;
          ELSIF UPDATING THEN
            :new.stateid := :old.stateid;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_filemodule_monitor_pk_seq
        BEFORE INSERT OR UPDATE of id
        ON filemodule_monitor FOR EACH ROW
BEGIN
        IF (:new.id is null) then
          IF INSERTING THEN
            SELECT filemodule_monitor_pk_seq.nextval INTO :new.id FROM DUAL;
          ELSIF UPDATING THEN
            :new.id := :old.id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_forum_pk_seq
        BEFORE INSERT OR UPDATE of msg_id
        ON forum FOR EACH ROW
BEGIN
        IF (:new.msg_id is null) then
          IF INSERTING THEN
            SELECT forum_pk_seq.nextval INTO :new.msg_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.msg_id := :old.msg_id;
          END IF;
        END IF;
END;


/

/

CREATE OR REPLACE TRIGGER A_forum_group_list_pk_seq
        BEFORE INSERT OR UPDATE of group_forum_id
        ON forum_group_list FOR EACH ROW
BEGIN
        IF (:new.group_forum_id is null) then
          IF INSERTING THEN
            SELECT forum_group_list_pk_seq.nextval INTO :new.group_forum_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.group_forum_id := :old.group_forum_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_forum_monitor_forums_pk_seq
        BEFORE INSERT OR UPDATE of monitor_id
        ON forum_monitored_forums FOR EACH ROW
BEGIN
        IF (:new.monitor_id is null) then
          IF INSERTING THEN
            SELECT forum_monitor_forums_pk_seq.nextval INTO :new.monitor_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.monitor_id := :old.monitor_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_forum_saved_place_pk_seq
        BEFORE INSERT OR UPDATE of saved_place_id
        ON forum_saved_place FOR EACH ROW
BEGIN
        IF (:new.saved_place_id is null) then
          IF INSERTING THEN
            SELECT forum_saved_place_pk_seq.nextval INTO :new.saved_place_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.saved_place_id := :old.saved_place_id;
          END IF;
        END IF;
END;


/

/

CREATE OR REPLACE TRIGGER A_foundry_news_pk_seq
        BEFORE INSERT OR UPDATE of foundry_news_id
        ON foundry_news FOR EACH ROW
BEGIN
        IF (:new.foundry_news_id is null) then
          IF INSERTING THEN
            SELECT foundry_news_pk_seq.nextval INTO :new.foundry_news_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.foundry_news_id := :old.foundry_news_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_foundry_prefer_proj_pk_seq
        BEFORE INSERT OR UPDATE of foundry_project_id
        ON foundry_preferred_projects FOR EACH ROW
BEGIN
        IF (:new.foundry_project_id is null) then
          IF INSERTING THEN
            SELECT foundry_prefer_proj_pk_seq.nextval INTO :new.foundry_project_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.foundry_project_id := :old.foundry_project_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_foundry_projects_pk_seq
        BEFORE INSERT OR UPDATE of id
        ON foundry_projects FOR EACH ROW
BEGIN
        IF (:new.id is null) then
          IF INSERTING THEN
            SELECT foundry_projects_pk_seq.nextval INTO :new.id FROM DUAL;
          ELSIF UPDATING THEN
            :new.id := :old.id;
          END IF;
        END IF;
END;


/

/

/

/

/

/

/

CREATE OR REPLACE TRIGGER A_frs_file_pk_seq
        BEFORE INSERT OR UPDATE of file_id
        ON frs_file FOR EACH ROW
BEGIN
        IF (:new.file_id is null) then
          IF INSERTING THEN
            SELECT frs_file_pk_seq.nextval INTO :new.file_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.file_id := :old.file_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_frs_filetype_pk_seq
        BEFORE INSERT OR UPDATE of type_id
        ON frs_filetype FOR EACH ROW
BEGIN
        IF (:new.type_id is null) then
          IF INSERTING THEN
            SELECT frs_filetype_pk_seq.nextval INTO :new.type_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.type_id := :old.type_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_frs_package_pk_seq
        BEFORE INSERT OR UPDATE of package_id
        ON frs_package FOR EACH ROW
BEGIN
        IF (:new.package_id is null) then
          IF INSERTING THEN
            SELECT frs_package_pk_seq.nextval INTO :new.package_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.package_id := :old.package_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_frs_processor_pk_seq
        BEFORE INSERT OR UPDATE of processor_id
        ON frs_processor FOR EACH ROW
BEGIN
        IF (:new.processor_id is null) then
          IF INSERTING THEN
            SELECT frs_processor_pk_seq.nextval INTO :new.processor_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.processor_id := :old.processor_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_frs_release_pk_seq
        BEFORE INSERT OR UPDATE of release_id
        ON frs_release FOR EACH ROW
BEGIN
        IF (:new.release_id is null) then
          IF INSERTING THEN
            SELECT frs_release_pk_seq.nextval INTO :new.release_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.release_id := :old.release_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_frs_status_pk_seq
        BEFORE INSERT OR UPDATE of status_id
        ON frs_status FOR EACH ROW
BEGIN
        IF (:new.status_id is null) then
          IF INSERTING THEN
            SELECT frs_status_pk_seq.nextval INTO :new.status_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.status_id := :old.status_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_group_cvs_history_pk_seq
        BEFORE INSERT OR UPDATE of id
        ON group_cvs_history FOR EACH ROW
BEGIN
        IF (:new.id is null) then
          IF INSERTING THEN
            SELECT group_cvs_history_pk_seq.nextval INTO :new.id FROM DUAL;
          ELSIF UPDATING THEN
            :new.id := :old.id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_group_history_pk_seq
        BEFORE INSERT OR UPDATE of group_history_id
        ON group_history FOR EACH ROW
BEGIN
        IF (:new.group_history_id is null) then
          IF INSERTING THEN
            SELECT group_history_pk_seq.nextval INTO :new.group_history_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.group_history_id := :old.group_history_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_group_type_pk_seq
        BEFORE INSERT OR UPDATE of type_id
        ON group_type FOR EACH ROW
BEGIN
        IF (:new.type_id is null) then
          IF INSERTING THEN
            SELECT group_type_pk_seq.nextval INTO :new.type_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.type_id := :old.type_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_groups_pk_seq
        BEFORE INSERT OR UPDATE of group_id
        ON groups FOR EACH ROW
BEGIN
        IF (:new.group_id is null) then
          IF INSERTING THEN
            SELECT groups_pk_seq.nextval INTO :new.group_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.group_id := :old.group_id;
          END IF;
        END IF;
END;


/

/

CREATE OR REPLACE TRIGGER A_mail_group_list_pk_seq
        BEFORE INSERT OR UPDATE of group_list_id
        ON mail_group_list FOR EACH ROW
BEGIN
        IF (:new.group_list_id is null) then
          IF INSERTING THEN
            SELECT mail_group_list_pk_seq.nextval INTO :new.group_list_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.group_list_id := :old.group_list_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_news_bytes_pk_seq
        BEFORE INSERT OR UPDATE of id
        ON news_bytes FOR EACH ROW
BEGIN
        IF (:new.id is null) then
          IF INSERTING THEN
            SELECT news_bytes_pk_seq.nextval INTO :new.id FROM DUAL;
          ELSIF UPDATING THEN
            :new.id := :old.id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_patch_pk_seq
        BEFORE INSERT OR UPDATE of patch_id
        ON patch FOR EACH ROW
BEGIN
        IF (:new.patch_id is null) then
          IF INSERTING THEN
            SELECT patch_pk_seq.nextval INTO :new.patch_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.patch_id := :old.patch_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_patch_category_pk_seq
        BEFORE INSERT OR UPDATE of patch_category_id
        ON patch_category FOR EACH ROW
BEGIN
        IF (:new.patch_category_id is null) then
          IF INSERTING THEN
            SELECT patch_category_pk_seq.nextval INTO :new.patch_category_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.patch_category_id := :old.patch_category_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_patch_history_pk_seq
        BEFORE INSERT OR UPDATE of patch_history_id
        ON patch_history FOR EACH ROW
BEGIN
        IF (:new.patch_history_id is null) then
          IF INSERTING THEN
            SELECT patch_history_pk_seq.nextval INTO :new.patch_history_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.patch_history_id := :old.patch_history_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_patch_status_pk_seq
        BEFORE INSERT OR UPDATE of patch_status_id
        ON patch_status FOR EACH ROW
BEGIN
        IF (:new.patch_status_id is null) then
          IF INSERTING THEN
            SELECT patch_status_pk_seq.nextval INTO :new.patch_status_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.patch_status_id := :old.patch_status_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_people_job_pk_seq
        BEFORE INSERT OR UPDATE of job_id
        ON people_job FOR EACH ROW
BEGIN
        IF (:new.job_id is null) then
          IF INSERTING THEN
            SELECT people_job_pk_seq.nextval INTO :new.job_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.job_id := :old.job_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_people_job_category_pk_seq
        BEFORE INSERT OR UPDATE of category_id
        ON people_job_category FOR EACH ROW
BEGIN
        IF (:new.category_id is null) then
          IF INSERTING THEN
            SELECT people_job_category_pk_seq.nextval INTO :new.category_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.category_id := :old.category_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_people_job_inventory_pk_seq
        BEFORE INSERT OR UPDATE of job_inventory_id
        ON people_job_inventory FOR EACH ROW
BEGIN
        IF (:new.job_inventory_id is null) then
          IF INSERTING THEN
            SELECT people_job_inventory_pk_seq.nextval INTO :new.job_inventory_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.job_inventory_id := :old.job_inventory_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_people_job_status_pk_seq
        BEFORE INSERT OR UPDATE of status_id
        ON people_job_status FOR EACH ROW
BEGIN
        IF (:new.status_id is null) then
          IF INSERTING THEN
            SELECT people_job_status_pk_seq.nextval INTO :new.status_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.status_id := :old.status_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_people_skill_pk_seq
        BEFORE INSERT OR UPDATE of skill_id
        ON people_skill FOR EACH ROW
BEGIN
        IF (:new.skill_id is null) then
          IF INSERTING THEN
            SELECT people_skill_pk_seq.nextval INTO :new.skill_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.skill_id := :old.skill_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_people_skill_inv_pk_seq
        BEFORE INSERT OR UPDATE of skill_inventory_id
        ON people_skill_inventory FOR EACH ROW
BEGIN
        IF (:new.skill_inventory_id is null) then
          IF INSERTING THEN
            SELECT people_skill_inv_pk_seq.nextval INTO :new.skill_inventory_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.skill_inventory_id := :old.skill_inventory_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_people_skill_level_pk_seq
        BEFORE INSERT OR UPDATE of skill_level_id
        ON people_skill_level FOR EACH ROW
BEGIN
        IF (:new.skill_level_id is null) then
          IF INSERTING THEN
            SELECT people_skill_level_pk_seq.nextval INTO :new.skill_level_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.skill_level_id := :old.skill_level_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_people_skill_year_pk_seq
        BEFORE INSERT OR UPDATE of skill_year_id
        ON people_skill_year FOR EACH ROW
BEGIN
        IF (:new.skill_year_id is null) then
          IF INSERTING THEN
            SELECT people_skill_year_pk_seq.nextval INTO :new.skill_year_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.skill_year_id := :old.skill_year_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_project_assigned_to_pk_seq
        BEFORE INSERT OR UPDATE of project_assigned_id
        ON project_assigned_to FOR EACH ROW
BEGIN
        IF (:new.project_assigned_id is null) then
          IF INSERTING THEN
            SELECT project_assigned_to_pk_seq.nextval INTO :new.project_assigned_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.project_assigned_id := :old.project_assigned_id;
          END IF;
        END IF;
END;


/

/

/

CREATE OR REPLACE TRIGGER A_project_dependencies_pk_seq
        BEFORE INSERT OR UPDATE of project_depend_id
        ON project_dependencies FOR EACH ROW
BEGIN
        IF (:new.project_depend_id is null) then
          IF INSERTING THEN
            SELECT project_dependencies_pk_seq.nextval INTO :new.project_depend_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.project_depend_id := :old.project_depend_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_project_group_list_pk_seq
        BEFORE INSERT OR UPDATE of group_project_id
        ON project_group_list FOR EACH ROW
BEGIN
        IF (:new.group_project_id is null) then
          IF INSERTING THEN
            SELECT project_group_list_pk_seq.nextval INTO :new.group_project_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.group_project_id := :old.group_project_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_project_history_pk_seq
        BEFORE INSERT OR UPDATE of project_history_id
        ON project_history FOR EACH ROW
BEGIN
        IF (:new.project_history_id is null) then
          IF INSERTING THEN
            SELECT project_history_pk_seq.nextval INTO :new.project_history_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.project_history_id := :old.project_history_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_project_metric_pk_seq
        BEFORE INSERT OR UPDATE of ranking
        ON project_metric FOR EACH ROW
BEGIN
        IF (:new.ranking is null) then
          IF INSERTING THEN
            SELECT project_metric_pk_seq.nextval INTO :new.ranking FROM DUAL;
          ELSIF UPDATING THEN
            :new.ranking := :old.ranking;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_project_metric_tmp1_pk_seq
        BEFORE INSERT OR UPDATE of ranking
        ON project_metric_tmp1 FOR EACH ROW
BEGIN
        IF (:new.ranking is null) then
          IF INSERTING THEN
            SELECT project_metric_tmp1_pk_seq.nextval INTO :new.ranking FROM DUAL;
          ELSIF UPDATING THEN
            :new.ranking := :old.ranking;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_proj_metric_weekly_tm_pk_seq
        BEFORE INSERT OR UPDATE of ranking
        ON project_metric_weekly_tmp1 FOR EACH ROW
BEGIN
        IF (:new.ranking is null) then
          IF INSERTING THEN
            SELECT proj_metric_weekly_tm_pk_seq.nextval INTO :new.ranking FROM DUAL;
          ELSIF UPDATING THEN
            :new.ranking := :old.ranking;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_project_status_pk_seq
        BEFORE INSERT OR UPDATE of status_id
        ON project_status FOR EACH ROW
BEGIN
        IF (:new.status_id is null) then
          IF INSERTING THEN
            SELECT project_status_pk_seq.nextval INTO :new.status_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.status_id := :old.status_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_project_task_pk_seq
        BEFORE INSERT OR UPDATE of project_task_id
        ON project_task FOR EACH ROW
BEGIN
        IF (:new.project_task_id is null) then
          IF INSERTING THEN
            SELECT project_task_pk_seq.nextval INTO :new.project_task_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.project_task_id := :old.project_task_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_project_weekly_metric_pk_seq
        BEFORE INSERT OR UPDATE of ranking
        ON project_weekly_metric FOR EACH ROW
BEGIN
        IF (:new.ranking is null) then
          IF INSERTING THEN
            SELECT project_weekly_metric_pk_seq.nextval INTO :new.ranking FROM DUAL;
          ELSIF UPDATING THEN
            :new.ranking := :old.ranking;
          END IF;
        END IF;
END;


/

/

CREATE OR REPLACE TRIGGER A_snippet_pk_seq
        BEFORE INSERT OR UPDATE of snippet_id
        ON snippet FOR EACH ROW
BEGIN
        IF (:new.snippet_id is null) then
          IF INSERTING THEN
            SELECT snippet_pk_seq.nextval INTO :new.snippet_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.snippet_id := :old.snippet_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_snippet_package_pk_seq
        BEFORE INSERT OR UPDATE of snippet_package_id
        ON snippet_package FOR EACH ROW
BEGIN
        IF (:new.snippet_package_id is null) then
          IF INSERTING THEN
            SELECT snippet_package_pk_seq.nextval INTO :new.snippet_package_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.snippet_package_id := :old.snippet_package_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_snippet_package_item_pk_seq
        BEFORE INSERT OR UPDATE of snippet_package_item_id
        ON snippet_package_item FOR EACH ROW
BEGIN
        IF (:new.snippet_package_item_id is null) then
          IF INSERTING THEN
            SELECT snippet_package_item_pk_seq.nextval INTO :new.snippet_package_item_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.snippet_package_item_id := :old.snippet_package_item_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_snippet_package_ver_pk_seq
        BEFORE INSERT OR UPDATE of snippet_package_version_id
        ON snippet_package_version FOR EACH ROW
BEGIN
        IF (:new.snippet_package_version_id is null) then
          IF INSERTING THEN
            SELECT snippet_package_ver_pk_seq.nextval INTO :new.snippet_package_version_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.snippet_package_version_id := :old.snippet_package_version_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_snippet_version_pk_seq
        BEFORE INSERT OR UPDATE of snippet_version_id
        ON snippet_version FOR EACH ROW
BEGIN
        IF (:new.snippet_version_id is null) then
          IF INSERTING THEN
            SELECT snippet_version_pk_seq.nextval INTO :new.snippet_version_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.snippet_version_id := :old.snippet_version_id;
          END IF;
        END IF;
END;


/

/

/

/

/

/

/

/

/

/

/

/

/

/

/

CREATE OR REPLACE TRIGGER A_support_pk_seq
        BEFORE INSERT OR UPDATE of support_id
        ON support FOR EACH ROW
BEGIN
        IF (:new.support_id is null) then
          IF INSERTING THEN
            SELECT support_pk_seq.nextval INTO :new.support_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.support_id := :old.support_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_support_canned_res_pk_seq
        BEFORE INSERT OR UPDATE of support_canned_id
        ON support_canned_responses FOR EACH ROW
BEGIN
        IF (:new.support_canned_id is null) then
          IF INSERTING THEN
            SELECT support_canned_res_pk_seq.nextval INTO :new.support_canned_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.support_canned_id := :old.support_canned_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_support_category_pk_seq
        BEFORE INSERT OR UPDATE of support_category_id
        ON support_category FOR EACH ROW
BEGIN
        IF (:new.support_category_id is null) then
          IF INSERTING THEN
            SELECT support_category_pk_seq.nextval INTO :new.support_category_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.support_category_id := :old.support_category_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_support_history_pk_seq
        BEFORE INSERT OR UPDATE of support_history_id
        ON support_history FOR EACH ROW
BEGIN
        IF (:new.support_history_id is null) then
          IF INSERTING THEN
            SELECT support_history_pk_seq.nextval INTO :new.support_history_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.support_history_id := :old.support_history_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_support_messages_pk_seq
        BEFORE INSERT OR UPDATE of support_message_id
        ON support_messages FOR EACH ROW
BEGIN
        IF (:new.support_message_id is null) then
          IF INSERTING THEN
            SELECT support_messages_pk_seq.nextval INTO :new.support_message_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.support_message_id := :old.support_message_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_support_status_pk_seq
        BEFORE INSERT OR UPDATE of support_status_id
        ON support_status FOR EACH ROW
BEGIN
        IF (:new.support_status_id is null) then
          IF INSERTING THEN
            SELECT support_status_pk_seq.nextval INTO :new.support_status_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.support_status_id := :old.support_status_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_supported_languages_pk_seq
        BEFORE INSERT OR UPDATE of language_id
        ON supported_languages FOR EACH ROW
BEGIN
        IF (:new.language_id is null) then
          IF INSERTING THEN
            SELECT supported_languages_pk_seq.nextval INTO :new.language_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.language_id := :old.language_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_survey_question_types_pk_seq
        BEFORE INSERT OR UPDATE of id
        ON survey_question_types FOR EACH ROW
BEGIN
        IF (:new.id is null) then
          IF INSERTING THEN
            SELECT survey_question_types_pk_seq.nextval INTO :new.id FROM DUAL;
          ELSIF UPDATING THEN
            :new.id := :old.id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_survey_questions_pk_seq
        BEFORE INSERT OR UPDATE of question_id
        ON survey_questions FOR EACH ROW
BEGIN
        IF (:new.question_id is null) then
          IF INSERTING THEN
            SELECT survey_questions_pk_seq.nextval INTO :new.question_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.question_id := :old.question_id;
          END IF;
        END IF;
END;


/

/

/

/

CREATE OR REPLACE TRIGGER A_surveys_pk_seq
        BEFORE INSERT OR UPDATE of survey_id
        ON surveys FOR EACH ROW
BEGIN
        IF (:new.survey_id is null) then
          IF INSERTING THEN
            SELECT surveys_pk_seq.nextval INTO :new.survey_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.survey_id := :old.survey_id;
          END IF;
        END IF;
END;


/

/

CREATE OR REPLACE TRIGGER A_themes_pk_seq
        BEFORE INSERT OR UPDATE of theme_id
        ON themes FOR EACH ROW
BEGIN
        IF (:new.theme_id is null) then
          IF INSERTING THEN
            SELECT themes_pk_seq.nextval INTO :new.theme_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.theme_id := :old.theme_id;
          END IF;
        END IF;
END;


/

/

/

CREATE OR REPLACE TRIGGER A_trove_cat_pk_seq
        BEFORE INSERT OR UPDATE of trove_cat_id
        ON trove_cat FOR EACH ROW
BEGIN
        IF (:new.trove_cat_id is null) then
          IF INSERTING THEN
            SELECT trove_cat_pk_seq.nextval INTO :new.trove_cat_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.trove_cat_id := :old.trove_cat_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_trove_group_link_pk_seq
        BEFORE INSERT OR UPDATE of trove_group_id
        ON trove_group_link FOR EACH ROW
BEGIN
        IF (:new.trove_group_id is null) then
          IF INSERTING THEN
            SELECT trove_group_link_pk_seq.nextval INTO :new.trove_group_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.trove_group_id := :old.trove_group_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_trove_treesums_pk_seq
        BEFORE INSERT OR UPDATE of trove_treesums_id
        ON trove_treesums FOR EACH ROW
BEGIN
        IF (:new.trove_treesums_id is null) then
          IF INSERTING THEN
            SELECT trove_treesums_pk_seq.nextval INTO :new.trove_treesums_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.trove_treesums_id := :old.trove_treesums_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_user_bookmarks_pk_seq
        BEFORE INSERT OR UPDATE of bookmark_id
        ON user_bookmarks FOR EACH ROW
BEGIN
        IF (:new.bookmark_id is null) then
          IF INSERTING THEN
            SELECT user_bookmarks_pk_seq.nextval INTO :new.bookmark_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.bookmark_id := :old.bookmark_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_user_diary_pk_seq
        BEFORE INSERT OR UPDATE of id
        ON user_diary FOR EACH ROW
BEGIN
        IF (:new.id is null) then
          IF INSERTING THEN
            SELECT user_diary_pk_seq.nextval INTO :new.id FROM DUAL;
          ELSIF UPDATING THEN
            :new.id := :old.id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_user_diary_monitor_pk_seq
        BEFORE INSERT OR UPDATE of monitor_id
        ON user_diary_monitor FOR EACH ROW
BEGIN
        IF (:new.monitor_id is null) then
          IF INSERTING THEN
            SELECT user_diary_monitor_pk_seq.nextval INTO :new.monitor_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.monitor_id := :old.monitor_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_user_group_pk_seq
        BEFORE INSERT OR UPDATE of user_group_id
        ON user_group FOR EACH ROW
BEGIN
        IF (:new.user_group_id is null) then
          IF INSERTING THEN
            SELECT user_group_pk_seq.nextval INTO :new.user_group_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.user_group_id := :old.user_group_id;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_user_metric_pk_seq
        BEFORE INSERT OR UPDATE of ranking
        ON user_metric FOR EACH ROW
BEGIN
        IF (:new.ranking is null) then
          IF INSERTING THEN
            SELECT user_metric_pk_seq.nextval INTO :new.ranking FROM DUAL;
          ELSIF UPDATING THEN
            :new.ranking := :old.ranking;
          END IF;
        END IF;
END;


/

CREATE OR REPLACE TRIGGER A_user_metric0_pk_seq
        BEFORE INSERT OR UPDATE of ranking
        ON user_metric0 FOR EACH ROW
BEGIN
        IF (:new.ranking is null) then
          IF INSERTING THEN
            SELECT user_metric0_pk_seq.nextval INTO :new.ranking FROM DUAL;
          ELSIF UPDATING THEN
            :new.ranking := :old.ranking;
          END IF;
        END IF;
END;


/

/

/

CREATE OR REPLACE TRIGGER A_users_pk_seq
        BEFORE INSERT OR UPDATE of user_id
        ON users FOR EACH ROW
BEGIN
        IF (:new.user_id is null) then
          IF INSERTING THEN
            SELECT users_pk_seq.nextval INTO :new.user_id FROM DUAL;
          ELSIF UPDATING THEN
            :new.user_id := :old.user_id;
          END IF;
        END IF;
END;


/

/

/
