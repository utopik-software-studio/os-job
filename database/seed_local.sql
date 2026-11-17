-- Development-only bootstrap for a trusted local installation.
-- This is NOT an authenticated account. Never expose OS::JOB publicly.
INSERT INTO users (id,email,password_hash) VALUES
(1,'local@os-job.invalid','LOCAL_ONLY_NOT_AN_AUTHENTICATION_PASSWORD');
INSERT INTO profiles (user_id,first_name,last_name,headline,email,salary_min)
VALUES (1,'','','','','45000');
