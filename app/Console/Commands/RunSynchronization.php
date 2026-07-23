<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Http\Controllers\KinerjaSyncController;

#[Signature('app:run-synchronization')]
#[Description('Command description')]
class RunSynchronization extends Command
{
    protected $signature = 'synchronization:run';

    protected $description = 'Run attendance synchronization to Kinerja';

    public function handle()
    {
        $this->info('Starting attendance synchronization...');

        try {

            $controller = app(KinerjaSyncController::class);

            $controller->FetchAttendance();

            $this->info('Attendance synchronization completed successfully.');

            return Command::SUCCESS;
        } catch (\Throwable $e) {

            $this->error('Synchronization failed.');
            $this->error($e->getMessage());

            \Log::error('RunSynchronization Command Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return Command::FAILURE;
        }
    }
}
