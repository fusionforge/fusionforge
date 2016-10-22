CREATE TABLE fusionforge_object_assiociation (
    from_id          integer NOT NULL,
    from_object_type varchar(50) NOT NULL,
    from_ref_id      integer NOT NULL,
    to_id            integer NOT NULL,
    to_object_type   varchar(50) NOT NULL,
    to_ref_id        integer NOT NULL
);
