<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\KinerjaSyncController;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('app:run-synchronization')]
#[Description('Run attendance synchronization to Kinerja')]
class RunSynchronization extends Command
{
    public function handle()
    {
        $this->info('Attendance synchronization service started.');

        while (true) {

            try {

                $this->info('Starting attendance synchronization...');

                $controller = app(KinerjaSyncController::class);

                $controller->FetchAttendance();

                $this->info(
                    'Attendance synchronization completed successfully.'
                );

            } catch (\Throwable $e) {

                $this->error('Synchronization failed.');
                $this->error($e->getMessage());

                \Log::error('RunSynchronization Command Error', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]);
            }

            sleep(10);
        }

        return Command::SUCCESS;
    }
}