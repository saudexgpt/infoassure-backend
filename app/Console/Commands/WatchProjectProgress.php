<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectCertificate;
use Illuminate\Console\Command;

class WatchProjectProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:watch-project-progress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command watch the progress of client progress and updates it accordingly';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function watchProgress()
    {
        $projects = Project::where(['year' => date('Y', strtotime('now'))])->get();

        foreach ($projects as $project) {
            $project->watchProjectProgress($project);

            //create certificate
            $certificate_object = new ProjectCertificate();
            $certificate_object->create($project);
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->watchProgress();
    }
}
