-- Projects

INSERT INTO owner_layouts (owner_id, owner_type, layout_id, is_default)
VALUES (1, 'g', 4, 1);

-- First column
INSERT INTO layouts_contents (owner_id, owner_type, layout_id, column_id, name, rank)
VALUES ('1','g', 4, 8, 'projectdescription', 0);

INSERT INTO layouts_contents (owner_id, owner_type, layout_id, column_id, name, rank)
VALUES ('1','g', 4, 8, 'projectinfo', 1);

INSERT INTO layouts_contents (owner_id, owner_type, layout_id, column_id, name, rank)
VALUES ('1','g', 4, 8, 'projectlatestfilereleases', 2);

INSERT INTO layouts_contents (owner_id, owner_type, layout_id, column_id, name, rank)
VALUES ('1','g', 4, 8, 'projectpublicareas', 3);

-- Second column
INSERT INTO layouts_contents (owner_id, owner_type, layout_id, column_id, name, rank)
VALUES ('1','g', 4, 9, 'projectmembers', 0);

INSERT INTO layouts_contents (owner_id, owner_type, layout_id, column_id, name, rank)
VALUES ('1','g', 4, 9, 'projectlatestnews', 1);

