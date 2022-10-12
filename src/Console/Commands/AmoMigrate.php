<?php

namespace LebedevSoft\AmoSync\Console\Commands;

use LebedevSoft\AmoSync\Http\Controllers\AmoMigrateController;
use Illuminate\Console\Command;

class AmoMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amo:migrate
                                {--cfs : For sync custom fields}
                                {--table= : Table name for migration (contacts or leads or companies)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AmoCRM migration';

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
        $amo = new AmoMigrateController();
        if ($options["cfs"]) {
			$amo->loadCustomFields("companies");
            $amo->loadCustomFields("contacts");
            $amo->loadCustomFields("leads");
        } elseif ($options["table"]) {
            print_r("Start " . $options["table"] . " migration\n");
            $amo->cfsMigrations($options["table"]);
            print_r("End " . $options["table"] . " migration\n");
        }
        return 0;
    }
}
