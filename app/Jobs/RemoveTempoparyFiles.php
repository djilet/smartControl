<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class RemoveTempoparyFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $expired = 3600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $storage = Storage::disk('local');
        $files = $storage->files('tmp');

        foreach ($files as $filePath) {
            if ($storage->exists($filePath) == false) {
                continue;
            }

            $diff = time() - $storage->lastModified($filePath);
            if ($diff >= $this->expired) {
                $storage->delete($filePath);
            }
        }
    }
}
