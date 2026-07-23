<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;


class SyncKinerja implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $trx_id;
    public $emp_id;
    public $event_time;
    public $scan_type;
    public $serial;

    /**
     * Create a new job instance.
     */
    public function __construct($trx_id, $emp_id, $event_time, $scan_type, $serial)
    {
        $this->trx_id = $trx_id;
        $this->emp_id = $emp_id;
        $this->event_time = $event_time;
        $this->scan_type = $scan_type;
        $this->serial = $serial;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $dbApp = DB::connection('pgsql');

        try {

            $response = Http::withBasicAuth(
                config('services.kinerja.username'),
                config('services.kinerja.password')
            )->post(
                config('services.kinerja.url'),
                [
                    'employee_id'   => $this->emp_id,
                    'timestamp'     => $this->event_time,
                    'scan_type'     => $this->scan_type,
                    'serial_number' => $this->serial,
                ]
            );

            if ($response->successful()) {

                $dbApp->table('sync_history')
                    ->where('transaction_id', $this->trx_id)
                    ->update([
                        'status'  => true,
                        'err_msg' => null,
                    ]);

                return [
                    'success' => true,
                    'status'  => $response->status(),
                    'data'    => $response->json(),
                ];
            }

            $responseData = $response->json();

            $dbApp->table('sync_history')
                ->where('transaction_id', $this->trx_id)
                ->update([
                    'status'  => false,
                    'err_msg' => $responseData['message'] ?? $response->body(),
                ]);

            return [
                'success' => false,
                'status'  => $response->status(),
                'data'    => $responseData,
                'body'    => $response->body(),
            ];
        } catch (\Throwable $e) {

            \Log::error('SyncKinerja Job Error', [
                'transaction_id' => $this->trx_id,
                'employee_id'    => $this->emp_id,
                'message'        => $e->getMessage(),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
            ]);

            // Update status gagal
            $dbApp->table('sync_history')
                ->where('transaction_id', $this->trx_id)
                ->update([
                    'status'  => false,
                    'err_msg' => $e->getMessage(),
                ]);

            throw $e;
        }
    }
}
