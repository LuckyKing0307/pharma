<?php

namespace App\Jobs;

use App\Exports\TabletsExport;
use App\Models\User;
use App\Notifications\TaskCompleted;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPodcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $file;
    public $filter;
    /**
     * Create a new job instance.
     */
    public function __construct($file_id,$data)
    {
       $this->file = $file_id;
       $this->filter = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $startTime = microtime(true);
            (new TabletsExport($this->filter))->store('public/'.Carbon::now()->format('Y-m-d').'.xlsx');
            $endTime = microtime(true);
            $timeTaken = $endTime - $startTime;
            $users = User::all();
            foreach ($users as $user) {
                $user->notify(new TaskCompleted($this->file, $timeTaken));
            }
        } catch (\Exception $e){
            info($e);
            $users = User::all();
            foreach ($users as $user) {
                $user->notify(new TaskCompleted($this->file, 0));
            }
        }


    }
}
