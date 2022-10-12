<?php

namespace LebedevSoft\AmoSync\Console\Commands;

use LebedevSoft\AmoSync\Http\Controllers\AmoSyncController;
use Illuminate\Console\Command;

class AmoSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amo:sync
                                {--all : For sync all data}
                                {--calls : For sync calls}
								{--notes : For sync notes}
								{--emails : For sync emails}
								{--chats : For sync chats}
                                {--contacts : For sync contacts}
								{--companies : For sync companies}								
                                {--history : For sync statuses history}
                                {--full : For sync data from all time}
                                {--leads : or sync leads}
                                {--pipelines : For sync pipelines and statuses}
                                {--tags : For sync tags}
                                {--tasks : For sync tasks}
								{--task_deadlines : For sync tasks deadline}
                                {--time_range=2 : Set day number for data sync (integer)}								
                                {--users : For sync users}
								{--del_logs : For delete logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AmoCRM sync';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $options = $this->options();
        $amo = new AmoSyncController();
        if ($options["full"]) {
            $date_range = null;
        } else {
            $date_range = ["from" => strtotime("-" . $options["time_range"] . "day")];
			$date_range_emails = strtotime("-" . $options["time_range"] . "day");
        }
        if ($options["calls"]) {
            $amo->loadAmoCalls($date_range);
		} elseif ($options["companies"]) {
            $amo->loadAmoCompanies($date_range);	
        } elseif ($options["contacts"]) {
            $amo->loadAmoContacts($date_range);
		}elseif ($options["notes"]) {
            $amo->loadAmoNotesLeads($date_range);
			$amo->loadAmoNotesContacts($date_range);
			$amo->loadAmoNotesCompanies($date_range);
		}elseif ($options["emails"]) {
            $amo->loadAmoEmailsLeads($date_range_emails);
			$amo->loadAmoEmailsContacts($date_range_emails);
			$amo->loadAmoEmailsCompanies($date_range_emails);
		}elseif ($options["chats"]) {
            $amo->loadAmoChats($date_range);
        } elseif ($options["history"]) {
            $amo->loadAmoStatusHistory($date_range);
        } elseif ($options["leads"]) {
            $amo->loadAmoLeads($date_range);
            $amo->loadDeletedLeads($date_range);
        } elseif ($options["pipelines"]) {
            $amo->loadAmoPipelines();
        } elseif ($options["tags"]) {
            $amo->loadAmoTags();
        } elseif ($options["tasks"]) {
            $amo->loadTaskTypes();
            $amo->loadAmoTasks($date_range);
		} elseif ($options["task_deadlines"]) {
            $amo->loadAmoTaskDeadlines($date_range);	
        } elseif ($options["users"]) {
            $amo->loadUserGroups();
            $amo->loadAmoUsers();
		} elseif ($options["del_logs"]) {
            $amo->deleteAmoLogs();
        } elseif ($options["all"]) {
            $amo->loadUserGroups();
            $amo->loadAmoUsers();
            $amo->loadAmoPipelines();
            $amo->loadAmoTags();
			$amo->loadAmoCompanies($date_range);
            $amo->loadAmoContacts($date_range);
            $amo->loadAmoLeads($date_range);
            $amo->loadDeletedLeads($date_range);
            $amo->loadTaskTypes();
            $amo->loadAmoTasks($date_range);
			$amo->loadAmoTaskDeadlines($date_range);
            $amo->loadAmoStatusHistory($date_range);
            $amo->loadAmoCalls($date_range);
			$amo->loadAmoNotesLeads($date_range);
			$amo->loadAmoNotesContacts($date_range);
			$amo->loadAmoNotesCompanies($date_range);
			$amo->loadAmoEmailsLeads($date_range_emails);
			$amo->loadAmoEmailsContacts($date_range_emails);
			$amo->loadAmoEmailsCompanies($date_range_emails);
			$amo->loadAmoChats($date_range);
        } else {
            print_r("Not supported command\n");
        }
        return 0;
    }
}
