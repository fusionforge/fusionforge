CREATE SEQUENCE "artifact_group_selection_box_list_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE TABLE "artifact_group_selection_box_list" (
	"id" integer DEFAULT nextval('"artifact_group_selection_box_list_id_seq"'::text)NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"selection_box_name" text NOT NULL,
	Constraint "artifact_group_selection_box_list_pkey" Primary Key ("id")
);
SELECT setval ('"artifact_group_selection_box_list_id_seq"',100,true);

CREATE SEQUENCE "artifact_group_selection_box_options_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE TABLE "artifact_group_selection_box_options" (
	"id" integer DEFAULT nextval('"artifact_group_selection_box_options_id_seq"'::text)NOT NULL,
	"artifact_box_id" integer NOT NULL,
	"box_options_name" text NOT NULL,
	Constraint "artifact_group_selection_box_options_pkey" Primary Key ("id")
);
SELECT setval ('"artifact_group_selection_box_options_id_seq"',100,true);


CREATE SEQUENCE "artifact_extra_field_data_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE TABLE "artifact_extra_field_data" (
	"id" integer DEFAULT nextval('"artifact_extra_field_data_id_seq"'::text)NOT NULL,
	"artifact_id" integer NOT NULL,
	"choice_id" integer NOT NULL,
	Constraint "artifact_extra_field_data_pkey" Primary Key ("id")
);
SELECT setval ('"artifact_extra_field_data_id_seq"',100,true);

