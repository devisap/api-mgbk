<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class ActivityController extends Controller
{
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request){
        $req = $request->all();

        $validator = Validator::make($req, [
            'sasaran_kegiatan'  => 'required',
            'kegiatan'          => 'required',
            'satuan_kegiatan'   => 'required',
            'uraian'            => 'required',
            'pelaporan'         => 'required',
            'durasi'            => 'required|int',
            'satuan_waktu'      => 'required',
            'jumlah_pertemuan'  => 'required|int',
            'ekuivalen'         => 'required'
        ]);
        
        if($validator->fails()){
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        // setUp Data
        $req['created_at'] = date('Y-m-d H:i:s');
        $req['updated_at'] = date('Y-m-d H:i:s');
        DB::table('kegiatan')->insert($req);

        return response()->json(['status' => true, 'message' => 'Data berhasil ditambahkan', 'data' => null]);
    }

    public function getList(){
        $listData = DB::table('kegiatan')->get();
        return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $listData]);
    }

}