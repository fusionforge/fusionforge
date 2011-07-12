CREATE SEQUENCE layouts_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;
CREATE TABLE layouts (
  id INTEGER NOT NULL DEFAULT nextval('layouts_pk_seq'::text),
  name character varying(255) NOT NULL default '',
  description text NOT NULL,
  scope char(1) NOT NULL default 'S',
  PRIMARY KEY(id)
);

--
-- Contenu de la table 'layouts'
--

INSERT INTO layouts (id, name, description, scope) VALUES
(1, '2 columns', 'Simple layout made of 2 columns', 'S');
INSERT INTO layouts (id, name, description, scope) VALUES
(2, '3 columns', 'Simple layout made of 3 columns', 'S');
INSERT INTO layouts (id, name, description, scope) VALUES
(3, 'Left', 'Simple layout made of a main column and a small, left sided, column', 'S');
INSERT INTO layouts (id, name, description, scope) VALUES
(4, 'Right', 'Simple layout made of a main column and a small, right sided, column', 'S');

--
-- Structure de la table 'layouts_contents'
--

CREATE TABLE layouts_contents (
  owner_id INTEGER  NOT NULL default '0',
  owner_type character varying(1) NOT NULL default 'u',
  layout_id INTEGER  NOT NULL default '0',
  column_id INTEGER  NOT NULL default '0',
  name character varying(255) NOT NULL default '',
  rank INTEGER NOT NULL default '0',
  is_minimized INTEGER NOT NULL default '0',
  is_removed INTEGER NOT NULL default '0',
  display_preferences INTEGER NOT NULL default '0',
  content_id INTEGER  NOT NULL default '0'
);


CREATE SEQUENCE layouts_rows_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;
--
-- Structure de la table 'layouts_rows'
--

CREATE TABLE layouts_rows (
  id INTEGER  NOT NULL DEFAULT nextval('layouts_rows_pk_seq'::text),
  layout_id INTEGER  NOT NULL default '0',
  rank INTEGER NOT NULL default '0',
  PRIMARY KEY  (id)
);

--
-- Contenu de la table 'layouts_rows'
--

INSERT INTO layouts_rows (id, layout_id, rank) VALUES
(1, 1, 0);
INSERT INTO layouts_rows (id, layout_id, rank) VALUES
(2, 2, 0);
INSERT INTO layouts_rows (id, layout_id, rank) VALUES
(3, 3, 0);
INSERT INTO layouts_rows (id, layout_id, rank) VALUES
(4, 4, 0);

CREATE SEQUENCE layouts_rows_columns_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;

--
-- Structure de la table 'layouts_rows_columns'
--

CREATE TABLE layouts_rows_columns (
  id INTEGER  NOT NULL DEFAULT nextval('layouts_rows_columns_pk_seq'::text),
  layout_row_id INTEGER  NOT NULL default '0',
  width INTEGER  NOT NULL default '0',
  PRIMARY KEY  (id)
);

--
-- Contenu de la table 'layouts_rows_columns'
--

INSERT INTO layouts_rows_columns (id, layout_row_id, width) VALUES
(1, 1, 50);
INSERT INTO layouts_rows_columns (id, layout_row_id, width) VALUES
(2, 1, 50);
INSERT INTO layouts_rows_columns (id, layout_row_id, width) VALUES
(3, 2, 33);
INSERT INTO layouts_rows_columns (id, layout_row_id, width) VALUES
(4, 2, 33);
INSERT INTO layouts_rows_columns (id, layout_row_id, width) VALUES
(5, 2, 33);
INSERT INTO layouts_rows_columns (id, layout_row_id, width) VALUES
(6, 3, 33);
INSERT INTO layouts_rows_columns (id, layout_row_id, width) VALUES
(7, 3, 66);
INSERT INTO layouts_rows_columns (id, layout_row_id, width) VALUES
(8, 4, 66);
INSERT INTO layouts_rows_columns (id, layout_row_id, width) VALUES
(9, 4, 33);


--
-- Structure de la table 'owner_layouts'
--

CREATE TABLE owner_layouts (
  owner_id INTEGER  NOT NULL default '0',
  owner_type character varying(1) NOT NULL default 'u',
  layout_id INTEGER  NOT NULL default '0',
  is_default INTEGER NOT NULL default '0',
  PRIMARY KEY  (owner_id,owner_type,layout_id)
);

CREATE SEQUENCE widget_rss_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;

--- Widget Rss table ---
CREATE TABLE widget_rss (
  id INTEGER  NOT NULL DEFAULT nextval('widget_rss_pk_seq'::text),
  owner_id INTEGER  NOT NULL default '0',
  owner_type character varying(1) NOT NULL default 'u',
  title character varying(255) NOT NULL,
  url TEXT NOT NULL,
  PRIMARY KEY(id)
);

