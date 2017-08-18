ALTER TABLE stats_cvs_user ADD COLUMN reponame text NOT NULL default 'default_project_repo';
ALTER TABLE stats_cvs_group ADD COLUMN reponame text NOT NULL default 'default_project_repo';

UPDATE stats_cvs_group SET reponame = subquery.unix_group_name
                       FROM (SELECT unix_group_name, groups.group_id FROM groups, stats_cvs_group
                                                                     WHERE groups.group_id = stats_cvs_group.group_id) AS subquery
                       WHERE stats_cvs_group.group_id = subquery.group_id;

UPDATE stats_cvs_user SET reponame = subquery.unix_group_name
                      FROM (SELECT unix_group_name, groups.group_id FROM groups, stats_cvs_user
                                                                    WHERE groups.group_id = stats_cvs_user.group_id) AS subquery
                      WHERE stats_cvs_user.group_id = subquery.group_id;

DROP INDEX statscvsgroup_month_day_group;
CREATE UNIQUE INDEX statscvsgroup_month_day_group ON stats_cvs_group USING btree ("month", "day", group_id, "reponame");
