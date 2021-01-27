<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class ReportController extends Controller
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
            'id_user'       => 'required|int|exists:users',
            'id_sekolah'    => 'required|int|exists:sekolah',
            'id_kegiatan'   => 'required|int|exists:kegiatan',
            'tgl_transaksi' => 'required|date',
            'detail'        => 'required',
            'upload_doc'    => 'required|file|mimes:pdf,png,jpg,bmp|max:2048',
            'upload_doc'    => 'nullable|file|mimes:pdf,png,jpg,bmp|max:2048'
        ]);
        
        if($validator->fails()){
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        // setUp Data
        $req['created_at'] = date('Y-m-d H:i:s');
        $req['updated_at'] = date('Y-m-d H:i:s');
        // DB::table('sekolah')->insert($req);

        return response()->json(['status' => true, 'message' => 'Data berhasil ditambahkan', 'data' => null]);
    }

}