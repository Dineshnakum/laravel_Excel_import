<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\UserController;

class Excelcron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'excel:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = new UserController();
        return  $user->import();
    }
}
