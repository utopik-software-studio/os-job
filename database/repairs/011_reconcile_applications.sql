-- OS::JOB v0.9.0: repair missing application records, non-destructive.
-- BACK UP YOUR LIVE DATABASE BEFORE RUNNING. Run once, on os_job only.
USE os_job;
START TRANSACTION;
INSERT INTO applications (user_id,job_offer_id,status,applied_at)
SELECT o.user_id,o.id,o.status,
 CASE WHEN o.status IN ('applied','interview','rejected','accepted') THEN o.updated_at ELSE NULL END
FROM job_offers o
LEFT JOIN applications a ON a.user_id=o.user_id AND a.job_offer_id=o.id
WHERE a.id IS NULL AND o.status IN ('applied','interview','rejected','accepted');
COMMIT;
-- Verify results:
SELECT o.id AS offer_id,o.title,o.status AS offer_status,a.id AS application_id,a.status AS application_status,a.applied_at
FROM job_offers o LEFT JOIN applications a ON a.job_offer_id=o.id AND a.user_id=o.user_id
ORDER BY o.id DESC;
