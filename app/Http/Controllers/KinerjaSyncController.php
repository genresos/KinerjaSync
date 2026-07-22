<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class KinerjaSyncController extends Controller
{
    public function main()
    {
        // return now()->format('H:i:s.u');
        //402880d09f422d61019f845c775d5ea1 mesin absen
        $exe = DB::connection('zkbio')
            ->table('acc_transaction')
            ->select('id', 'pin', 'event_time', 'name')
            ->where('dev_id', '402880d09ea66e6d019ea6a4002109d7')
            ->where('pin', '262010015')
            ->whereDate('event_time', today())
            ->where('verify_mode_no', 1)
            ->orderByDesc('event_time')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $exe
        ]);
    }
}
