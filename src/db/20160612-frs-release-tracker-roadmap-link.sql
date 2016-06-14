CREATE TABLE frs_release_tracker_roadmap_link (
    release_id      integer REFERENCES frs_release ON DELETE CASCADE,
    roadmap_id      integer REFERENCES roadmap ON DELETE CASCADE,
    roadmap_release text NOT NULL
);
