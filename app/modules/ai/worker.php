<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require dirname(__DIR__,2).'/config/config.php';
require APP_ROOT.'/app/core/database.php';
require APP_ROOT.'/app/modules/ai/evidence.php';
require APP_ROOT.'/app/modules/ai/offer_analysis.php';
require APP_ROOT.'/app/modules/ai/jobs.php';
$id=filter_var($argv[1]??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if(!$id){fwrite(STDERR,"Usage: php worker.php JOB_ID\n");exit(2);}
@set_time_limit(390);
ai_job_run((int)$id);
