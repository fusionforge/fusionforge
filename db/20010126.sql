-- by: apzen
-- purpose: stuff for project database and vhost maintanance

     CREATE TABLE prdb_dbs (
        dbid SERIAL PRIMARY KEY,
        group_id INT NOT NULL,
        dbname TEXT NOT NULL,
        dbusername TEXT NOT NULL,
        dbuserpass TEXT NOT NULL,
        requestdate INT NOT NULL,
        dbtype INT NOT NULL,
        created_by INT NOT NULL,
        state INT NOT NULL
    );

    CREATE TABLE prdb_states (

        stateid INT NOT NULL,
        statename TEXT
    );

	CREATE UNIQUE INDEX idx_prdb_dbname ON prdb_dbs (dbname);

    INSERT INTO prdb_states VALUES ('1', 'Active');
    INSERT INTO prdb_states VALUES ('2', 'Pending Create');
    INSERT INTO prdb_states VALUES ('3', 'Pending Delete');
    INSERT INTO prdb_states VALUES ('4', 'Pending Update');
    INSERT INTO prdb_states VALUES ('5', 'Failed Create');
    INSERT INTO prdb_states VALUES ('6', 'Failed Delete');
	INSERT INTO prdb_states VALUES ('7', 'Failed Update');

    CREATE TABLE prdb_types (
        dbtypeid INT PRIMARY KEY,
        dbservername TEXT NOT NULL,
        dbsoftware TEXT NOT NULL
    );

   INSERT INTO prdb_types VALUES ('1','pr-db1','mysql');

    CREATE TABLE prweb_vhost (
        vhostid SERIAL PRIMARY KEY,
        vhost_name TEXT,
        docdir TEXT,
		cgidir TEXT,
        group_id INT NOT NULL
    );

   CREATE INDEX idx_vhost_groups ON prweb_vhost (group_id);

   CREATE UNIQUE INDEX idx_vhost_hostnames ON prweb_vhost(vhost_name);

