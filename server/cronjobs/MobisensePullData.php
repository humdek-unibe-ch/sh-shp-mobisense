<?php
ob_start();
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

require_once __DIR__ . "/../../../../service/Services.php";
require_once __DIR__ . "/../../../../service/PageDb.php";
require_once __DIR__ . "/../../../../service/Transaction.php";
// Only include Clockwork if it exists
$clockworkPath = __DIR__ . "/../../../../service/Clockwork.php";
if (file_exists($clockworkPath)) {
    require_once $clockworkPath;
}
require_once __DIR__ . "/../component/mobisense/MobisenseModel.php";
require_once __DIR__ . "/../service/globals.php";

/**
 * SETUP
 * Make the script executable:  chmod +x
 * Cronjob (Pull Mobisense user data every hour and execute them if there any) 0 * * * * php --define apc.enable_cli=1 /home/user/selfhelp/server/plugins/Mobisense/server/cronjobs/MobisensePullData.php 
 */

/**
 * ScheduledJobsQueue class. It is scheduled on a cronjob and it is executed on given time. It checks for mails
 * that should be send within the time and schedule events for them.
 * TEST:
 * php --define apc.enable_cli=1 MobisensePullData.php
 */
class MobisensePullData
{

    /**
     * The db instance which grants access to the DB.
     */
    private $db = null;

    /**
     * The transaction instance which logs to the DB.
     */
    private $transaction = null;

    /**
     * Mobisense Model
     */
    private $mobisenseModel;

    /**
     * Clockwork service
     */
    public $clockwork;

    /**
     * The constructor.
     */
    public function __construct()
    {
        // Initialize Clockwork only if the service exists
        $this->clockwork = class_exists('ClockworkService') ? new ClockworkService() : null;
        
        // If Clockwork exists, pass it to PageDb, otherwise use the alternative constructor
        if ($this->clockwork) {
            $this->db = new PageDb(DBSERVER, DBNAME, DBUSER, DBPW, $this->clockwork);
        } else {
            $this->db = new PageDb(DBSERVER, DBNAME, DBUSER, DBPW);
        }
        
        $this->transaction = new Transaction($this->db);
        $this->mobisenseModel = new MobisenseModel(new Services(false), array("uid" => null));
    }

    /**
     * Check the mailing queue and send the mails if there are mails in the queue which should be sent
     */
    public function pull_data()
    {
        $debug_start_time = microtime(true);
        $this->mobisenseModel->pull_data(transactionBy_by_cron_job);
        $this->transaction->add_transaction(
            transactionTypes_insert,
            transactionBy_by_cron_job,
            null,
            $this->transaction::TABLE_dataTables,
            null,
            "",
            'Mobisense cronjob executed for: ' . (microtime(true) - $debug_start_time)
        );
    }
}

// Execute the script with output buffering to prevent any output

$MobisensePullData = new MobisensePullData();
// Only use Clockwork if it's available
if ($MobisensePullData->clockwork) {
    $MobisensePullData->clockwork->startEvent("[MobisensePullData][pull_data]");
    $MobisensePullData->pull_data();
    $MobisensePullData->clockwork->endEvent("[MobisensePullData][pull_data]");
    $MobisensePullData->clockwork->requestProcessed();
} else {
    // Execute without Clockwork instrumentation
    $MobisensePullData->pull_data();
}
ob_end_clean();

// Ensure clean exit with no trailing newlines
exit(0);
