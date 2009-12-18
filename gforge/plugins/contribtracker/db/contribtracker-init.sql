CREATE TABLE plugin_contribtracker_entity form (
       form_id int,
       form_name text
) ;

CREATE TABLE plugin_contribtracker_role (
       role_id int,
       role_name text,
       role_description text
) ;

CREATE TABLE plugin_contribtracker_actor (
       actor_id int,
       actor_name text,
       actor_address text,
       actor_email text,
       actor_description text,
       form_id int,
       group_id int
) ;

CREATE TABLE plugin_contribtracker_contribution (
       contrib_id int,
       contrib_name text,
       contrib_date int,
       contrib_description text,
       group_id int
) ;

CREATE TABLE plugin_contribtracker_participation (
       participation_id int,
       contribution_id int,
       actor_id int,
       role_id int
) ;
