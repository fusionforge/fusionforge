#
#
#  10200 is the group_id for the sfpeerratings project
#	REPLACE 10200 with your peerratings group_id
#
DROP TABLE user_metric0;
DROP SEQUENCE user_metric0_pk_seq;
CREATE SEQUENCE "user_metric0_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

CREATE TABLE "user_metric0" (
    "ranking" integer DEFAULT nextval('user_metric0_pk_seq'::text) NOT NULL,
    "user_id" integer DEFAULT '0' NOT NULL,
    "times_ranked" integer DEFAULT '0' NOT NULL,
    "avg_raters_importance" double precision DEFAULT '0.00000000' NOT NULL,
    "avg_rating" double precision DEFAULT '0.00000000' NOT NULL,
    "metric" double precision DEFAULT '0.00000000' NOT NULL,
    "percentile" double precision DEFAULT '0.00000000' NOT NULL,
    "importance_factor" double precision DEFAULT '0.00000000' NOT NULL,
    Constraint "user_metric0_pkey" Primary Key ("ranking")
);
CREATE  INDEX "user_metric0_user_id" on "user_metric0" using btree ( "user_id" "int4_ops" );

INSERT INTO user_metric0 (user_id,times_ranked,avg_raters_importance,avg_rating,metric,percentile,importance_factor)
SELECT user_id,5,1.25,1,0,0,1.25
FROM user_group
WHERE
user_group.group_id=4
AND user_group.admin_flags='A';

UPDATE user_metric0 SET
metric=(log(times_ranked::float)*avg_rating::float)::float,
percentile=(100-(100*((ranking::float-1)/(select count(*) from user_metric0))))::float;

UPDATE user_metric0 SET
importance_factor=(1+((percentile::float/100)*.5))::float;
