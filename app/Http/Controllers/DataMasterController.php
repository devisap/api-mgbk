<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use PDF;

class DataMasterController extends Controller
{
    /**
     * Store a new report.
     *
     * @param  Request  $request
     * @return Response
     */
    public function getYears(Request $request)
    {
        $years = DB::table('years')->get();
        return response()->json(['status' => true, 'message' => 'Data berhasil ditambahkan', 'data' => $years]);
    }
}
