<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Jobs\SyncKinerja;

class KinerjaSyncController extends Controller
{
    public static function main()
    {
        // return DB::connection('pgsql')
        //     ->table('sync_history')
        //     ->delete();
        // $dbApp = DB::connection('pgsql');

        ini_set('max_execution_time', 1200);

        $date = date('Y-m-d', strtotime("+1 days"));
        $prev_date = date('Y-m-d', strtotime("-1 days"));

        $dbApp = DB::connection('pgsql');
        $dbZkbio = DB::connection('zkbio');

        // Ambil ID transaksi yang sudah pernah disinkronkan
        $dataFetched = $dbApp
            ->table('sync_history')
            ->pluck('transaction_id')
            ->toArray();

        // Ambil transaksi dari mesin fingerprint
        $finger_data = $dbZkbio
            ->table('acc_transaction as trx')
            ->select(
                'trx.id',
                'trx.pin',
                'trx.event_time',
                'trx.name'
            )
            ->where('trx.id', '402880d09f85019c019f8c93c12c2064') // untuk test
            // ->where('trx.dev_id', '402880d09ea66e6d019ea6a4002109d7')
            // ->where('trx.pin', '>', 0)
            ->whereNotIn('trx.id', $dataFetched)
            // ->whereBetween(
            //     DB::raw('DATE(trx.event_time)'),
            //     [$prev_date, $date]
            // )
            ->orderBy('trx.id', 'asc')
            ->get();

        try {

            $dbApp->beginTransaction();

            foreach ($finger_data as $item) {

                $dbApp->table('sync_history')->insert([
                    'transaction_id' => $item->id,
                    'emp_id'         => $item->pin,
                    'emp_name'       => $item->name,
                    'event_time'     => $item->event_time,
                    'created_at'     => Carbon::now(),
                ]);

                try {
                    dispatch(new SyncKinerja($item->id, $item->pin, $item->event_time, 'finger', 'TDBD251400279'))->delay(now()->addSeconds(20));
                } catch (\Throwable $e) {
                    echo $e;
                }
            }

            $dbApp->commit();

            return response()->json([
                'success' => true,
                'total_sync' => count($finger_data),
            ]);
        } catch (\Throwable $e) {

            if ($dbApp->transactionLevel() > 0) {
                $dbApp->rollBack();
            }

            \Log::error('SYNC ERROR', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public static function FetchAttendance()
    {
        ini_set('max_execution_time', 1200);

        $date = date('Y-m-d', strtotime("+1 days"));
        $prev_date = date('Y-m-d', strtotime("-1 days"));

        $dbApp = DB::connection('pgsql');
        $dbZkbio = DB::connection('zkbio');

        // Ambil ID transaksi yang sudah pernah fetch
        $dataFetched = $dbApp
            ->table('sync_history')
            ->pluck('transaction_id')
            ->toArray();

        // Ambil transaksi dari mesin fingerprint
        $finger_data = $dbZkbio
            ->table('acc_transaction as trx')
            ->select(
                'trx.id',
                'trx.pin',
                'trx.event_time',
                'trx.name'
            )
            // ->where('trx.id', '402880d09f85019c019f8c93c12c2064') // untuk test
            ->where('trx.dev_id', '402880d09f422d61019f845c775d5ea1')
            ->where('trx.pin', '>', 10000000)
            ->whereNotIn('trx.id', $dataFetched)
            ->whereBetween(
                DB::raw('DATE(trx.event_time)'),
                [$prev_date, $date]
            )
            ->orderBy('trx.id', 'asc')
            ->get();

        try {

            $dbApp->beginTransaction();

            foreach ($finger_data as $item) {

                $dbApp->table('sync_history')->insert([
                    'transaction_id' => $item->id,
                    'emp_id'         => $item->pin,
                    'emp_name'       => $item->name,
                    'event_time'     => $item->event_time,
                    'created_at'     => Carbon::now(),
                ]);

                try {
                    dispatch(new SyncKinerja($item->id, $item->pin, $item->event_time, 'finger', 'TDBD251400279'))->delay(now()->addSeconds(20));
                } catch (\Throwable $e) {
                    echo $e;
                }
            }

            $dbApp->commit();

            return response()->json([
                'success' => true,
                'total_sync' => count($finger_data),
            ]);
        } catch (\Throwable $e) {

            if ($dbApp->transactionLevel() > 0) {
                $dbApp->rollBack();
            }

            \Log::error('SYNC ERROR', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
