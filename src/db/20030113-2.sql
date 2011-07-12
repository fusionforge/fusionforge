DROP TABLE stats_site_all;
DROP TABLE stats_site_last_30;
DROP TABLE stats_project_all;
DROP TABLE stats_project_developers_last30;
DROP TABLE stats_project_last_30;

CREATE VIEW stats_project_vw AS
SELECT spd.group_id,
    spd.month,
    spd.day,
    spd.developers,
    spm.ranking AS group_ranking,
    spm.percentile AS group_metric,
    salbg.count AS logo_showings,
    fdga.downloads,
    sasbg.count AS site_views,
    ssp.pages AS subdomain_views,
    (coalesce(sasbg.count,0) + coalesce(ssp.pages,0))::int AS page_views,
    sp.file_releases,
    sp.msg_posted,
    sp.msg_uniq_auth,
    sp.bugs_opened,
    sp.bugs_closed,
    sp.support_opened,
    sp.support_closed,
    sp.patches_opened,
    sp.patches_closed,
    sp.artifacts_opened,
    sp.artifacts_closed,
    sp.tasks_opened,
    sp.tasks_closed,
    sp.help_requests,
    scg.checkouts AS cvs_checkouts,
    scg.commits AS cvs_commits,
    scg.adds AS cvs_adds
FROM stats_project_developers spd
    LEFT JOIN stats_project sp USING (month,day,group_id)
    LEFT JOIN stats_project_metric spm USING (month,day,group_id)
    LEFT JOIN stats_cvs_group scg USING (month,day,group_id)
    LEFT JOIN stats_agg_site_by_group sasbg USING (month,day,group_id)
    LEFT JOIN stats_agg_logo_by_group salbg USING (month,day,group_id)
    LEFT JOIN stats_subd_pages ssp USING (month,day,group_id)
    LEFT JOIN frs_dlstats_group_vw fdga USING (month,day,group_id)
;

CREATE VIEW stats_project_all_vw AS
SELECT group_id,
    AVG(developers)::int AS developers,
    AVG(group_ranking)::int AS group_ranking,
    AVG(group_metric)::float AS group_metric,
    SUM(logo_showings) AS logo_showings,
    SUM(downloads) AS downloads,
    SUM(site_views) AS site_views,
    SUM(subdomain_views) AS subdomain_views,
    SUM(page_views) AS page_views,
    SUM(file_releases) AS file_releases,
    SUM(msg_posted) AS msg_posted,
    AVG(msg_uniq_auth)::int AS msg_uniq_auth,
    SUM(bugs_opened) AS bugs_opened,
    SUM(bugs_closed) AS bugs_closed,
    SUM(support_opened) AS support_opened,
    SUM(support_closed) AS support_closed,
    SUM(patches_opened) AS patches_opened,
    SUM(patches_closed) AS patches_closed,
    SUM(artifacts_opened) AS artifacts_opened,
    SUM(artifacts_closed) AS artifacts_closed,
    SUM(tasks_opened) AS tasks_opened,
    SUM(tasks_closed) AS tasks_closed,
    SUM(help_requests) AS help_requests,
    SUM(cvs_checkouts) AS cvs_checkouts,
    SUM(cvs_commits) AS cvs_commits,
    SUM(cvs_adds) AS cvs_adds
    FROM stats_project_months
    GROUP BY group_id;

CREATE VIEW stats_site_vw AS
SELECT p.month,
    p.day,
    sspbd.site_page_views,
    SUM(p.downloads) AS downloads,
    SUM(p.subdomain_views) AS subdomain_views,
    SUM(p.msg_posted) AS msg_posted,
    SUM(p.bugs_opened) AS bugs_opened,
    SUM(p.bugs_closed) AS bugs_closed,
    SUM(p.support_opened) AS support_opened,
    SUM(p.support_closed) AS support_closed,
    SUM(p.patches_opened) AS patches_opened,
    SUM(p.patches_closed) AS patches_closed,
    SUM(artifacts_opened) AS artifacts_opened,
    SUM(artifacts_closed) AS artifacts_closed,
    SUM(p.tasks_opened) AS tasks_opened,
    SUM(p.tasks_closed) AS tasks_closed,
    SUM(p.help_requests) AS help_requests,
    SUM(p.cvs_checkouts) AS cvs_checkouts,
    SUM(p.cvs_commits) AS cvs_commits,
    SUM(p.cvs_adds) AS cvs_adds
    FROM stats_project_vw p, stats_site_pages_by_day sspbd
        WHERE p.month=sspbd.month AND p.day=sspbd.day
    GROUP BY p.month, p.day, sspbd.site_page_views;


CREATE VIEW stats_site_all_vw AS
SELECT
    SUM(site_page_views) AS site_page_views,
    SUM(downloads) AS downloads,
    SUM(subdomain_views) AS subdomain_views,
    SUM(msg_posted) AS msg_posted,
    SUM(bugs_opened) AS bugs_opened,
    SUM(bugs_closed) AS bugs_closed,
    SUM(support_opened) AS support_opened,
    SUM(support_closed) AS support_closed,
    SUM(patches_opened) AS patches_opened,
    SUM(patches_closed) AS patches_closed,
    SUM(artifacts_opened) AS artifacts_opened,
    SUM(artifacts_closed) AS artifacts_closed,
    SUM(tasks_opened) AS tasks_opened,
    SUM(tasks_closed) AS tasks_closed,
    SUM(help_requests) AS help_requests,
    SUM(cvs_checkouts) AS cvs_checkouts,
    SUM(cvs_commits) AS cvs_commits,
    SUM(cvs_adds) AS cvs_adds
    FROM stats_site_months;
