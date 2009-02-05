CREATE SEQUENCE plugin_report_maven_info_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_report_maven_info
(
    maven_info_id integer DEFAULT nextval('plugin_report_maven_info_pk_seq') PRIMARY KEY,
    insertion_date timestamp DEFAULT now() NOT NULL,
    maven_group_id varchar(512) NOT NULL,
    maven_artefact_id varchar(512) NOT NULL,
    maven_version varchar(512) NOT NULL,
    group_id integer REFERENCES groups(group_id) ON DELETE CASCADE NOT NULL
);

CREATE SEQUENCE plugin_report_javancss_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_report_javancss
(
    javancss_id integer DEFAULT nextval('plugin_report_javancss_pk_seq') PRIMARY KEY,
    report_date varchar(128) NOT NULL,
    report_time varchar(128) NOT NULL,
    maven_info_id integer REFERENCES plugin_report_maven_info(maven_info_id) ON DELETE CASCADE NOT NULL
);

CREATE SEQUENCE plugin_report_javancss_package_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_report_javancss_package
(
    javancss_package_id integer DEFAULT nextval('plugin_report_javancss_package_pk_seq') PRIMARY KEY,
    name varchar(512) NOT NULL,
    classes integer NOT NULL,
    functions integer NOT NULL,
    ncss integer NOT NULL,
    javadocs integer NOT NULL,
    javadoc_lines integer NOT NULL,
    single_comment_lines integer NOT NULL,
    multi_comment_lines integer NOT NULL,
    javancss_id integer REFERENCES plugin_report_javancss(javancss_id) ON DELETE CASCADE NOT NULL
);

CREATE SEQUENCE plugin_report_javancss_object_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_report_javancss_object
(
    javancss_object_id integer DEFAULT nextval('plugin_report_javancss_object_pk_seq') PRIMARY KEY,
    name varchar(512) NOT NULL,
    ncss integer NOT NULL,
    functions integer NOT NULL,
    classes integer NOT NULL,   
    javadocs integer NOT NULL,
    javancss_id integer REFERENCES plugin_report_javancss(javancss_id) ON DELETE CASCADE NOT NULL
);

CREATE SEQUENCE plugin_report_javancss_function_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_report_javancss_function
(
    javancss_function_id integer DEFAULT nextval('plugin_report_javancss_function_pk_seq') PRIMARY KEY,
    name varchar(512) NOT NULL,   
    ncss integer NOT NULL,
    ccn integer NOT NULL,
    javadocs integer NOT NULL,
    javancss_id integer REFERENCES plugin_report_javancss(javancss_id) ON DELETE CASCADE NOT NULL
);

CREATE SEQUENCE plugin_report_checkstyle_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_report_checkstyle
(
    checkstyle_id integer DEFAULT nextval('plugin_report_checkstyle_pk_seq') PRIMARY KEY,
    insertion_date timestamp DEFAULT now() NOT NULL,
    file_name varchar(512) NOT NULL,
    nb_line integer NOT NULL,
    nb_column integer NOT NULL,
    severity varchar(128) NOT NULL,
    message varchar(1024) NOT NULL,
    module_id varchar(128) NOT NULL,
    source varchar(512) NOT NULL,
    maven_info_id integer REFERENCES plugin_report_maven_info(maven_info_id) ON DELETE CASCADE NOT NULL
);
    
CREATE TABLE plugin_report_checker_checkstyle
(
    insertion_date timestamp DEFAULT now() NOT NULL,
    objective varchar(512) NOT NULL,
    criteria_name varchar(512) NOT NULL,
    criteria_coef varchar(512) NOT NULL,
    criteria_context varchar(512) NOT NULL,
    criteria_method varchar(512) NOT NULL,
    rule_id varchar(512) NOT NULL,
    maven_info_id integer REFERENCES plugin_report_maven_info(maven_info_id) ON DELETE CASCADE NOT NULL,
    PRIMARY KEY (objective,criteria_name, rule_id,maven_info_id)
);
