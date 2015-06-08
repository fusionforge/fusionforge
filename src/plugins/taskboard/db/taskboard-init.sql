
CREATE TABLE plugin_taskboard (
    taskboard_id SERIAL primary key,
    group_id integer  REFERENCES groups(group_id) ON DELETE CASCADE,
    release_field_alias text,
    release_field_tracker integer NOT NULL default 1,
    estimated_cost_field_alias text,
    remaining_cost_field_alias text,
    user_stories_reference_field_alias text,
    user_stories_sort_field_alias text,
    user_stories_group_artifact_id integer,
    first_column_by_default integer NOT NULL default 1
);
-- ALTER TABLE public.plugin_taskboard OWNER TO gforge;

CREATE TABLE plugin_taskboard_trackers (
    taskboard_id integer REFERENCES plugin_taskboard(taskboard_id) ON DELETE CASCADE,
    group_artifact_id integer,
    card_background_color text,
    PRIMARY KEY (taskboard_id, group_artifact_id)
);
-- ALTER TABLE public.plugin_taskboard_trackers OWNER TO gforge;

CREATE TABLE plugin_taskboard_columns (
    taskboard_column_id SERIAL primary key,
    taskboard_id integer REFERENCES plugin_taskboard(taskboard_id) ON DELETE CASCADE,
    title text NOT NULL,
    title_background_color text,
    column_background_color text,
    order_num integer NOT NULL,
    max_tasks integer
);
-- ALTER TABLE plugin_taskboard_columns OWNER TO gforge;

CREATE TABLE plugin_taskboard_columns_resolutions (
    taskboard_column_value_id SERIAL primary key,
    taskboard_column_id integer REFERENCES plugin_taskboard_columns( taskboard_column_id ) ON DELETE CASCADE,
    taskboard_column_resolution text NOT NULL
);
-- ALTER TABLE plugin_taskboard_columns_resolutions OWNER TO gforge;

-- Task, having any element, can be accepted, if there no sources specified
-- records with empty source is a records by default
-- possible set rules:
-- resolution = 'Fixed' (alias = element_name, for extra fields, having elements)
-- remaining_estimated_cost = 0 (alias = value, for extra fields, using values )
--
CREATE TABLE plugin_taskboard_columns_sources (
    taskboard_column_source_id SERIAL primary key,
    target_taskboard_column_id integer NOT NULL REFERENCES plugin_taskboard_columns(taskboard_column_id) ON DELETE CASCADE,
    source_taskboard_column_id integer REFERENCES plugin_taskboard_columns(taskboard_column_id) ON DELETE CASCADE,
    target_resolution TEXT NOT NULL,
    alert text,
    autoassign integer NOT NULL DEFAULT 0,
    autoref integer NULL
);
-- ALTER TABLE plugin_taskboard_columns_sources OWNER TO gforge;

CREATE TABLE plugin_taskboard_releases (
    taskboard_release_id SERIAL primary key,
    taskboard_id integer REFERENCES plugin_taskboard(taskboard_id) ON DELETE CASCADE,
    element_id integer NOT NULL,
    start_date integer NOT NULL,
    end_date integer NOT NULL,
    goals TEXT,
    page_url TEXT
);

CREATE TABLE plugin_taskboard_releases_snapshots (
    taskboard_release_snapshot_id SERIAL primary key,
    taskboard_release_id integer REFERENCES plugin_taskboard_releases(taskboard_release_id) ON DELETE CASCADE,
    snapshot_date integer NOT NULL,
    completed_user_stories integer NOT NULL DEFAULT 0,
    completed_tasks integer NOT NULL DEFAULT 0,
    completed_story_points integer NOT NULL DEFAULT 0,
    completed_man_days integer NOT NULL DEFAULT 0
);
ALTER TABLE plugin_taskboard_releases_snapshots
    ADD CONSTRAINT plugin_taskboard_releases_snapshots_date
        UNIQUE (taskboard_release_id, snapshot_date);
