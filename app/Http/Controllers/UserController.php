<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $data = DB::table('users')->get();
        return response()->json(['data', $data]);
    }

    public function login(Request $request){
        $req = $request->all();

        $validator = Validator::make($req, [
            'email'     => 'required|email|exists:users',
            'password'  => 'required|min:8'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message'=> $validator->errors()->first(), 'data' => null ]);
        }

        $userPass            = DB::table('users')->select('password')->where('email', $req['email'])->first();
        $isPasswordVerified  = Hash::check($req['password'], $userPass->password);
        if($isPasswordVerified){
            $user = DB::table('users')->select('id', 'name', 'email')->first();
            return response()->json(['status' => true, 'message' => 'Data user berhasil ditemukan', 'data' => $user]);
        }else{
            return response()->json(['status' => false, 'message'=> "Email atau password anda salah", 'data' => null ]);
        }
    }

    public function register(Request $request){
        $req = $request->all();

        $validator = Validator::make($req, [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message'=> $validator->errors()->first(), 'data' => null ]);
        }

        $req['password']    = Hash::make($req['password']);
        $req['user_level']  = '2';
        $req['is_active']   = '0';

        DB::table('users')->insert($req);
        return response()->json(['status' => true, 'message'=> "Data berhasil ditambahkan", 'data' => null ]);
    }

    public function setProfile(Request $request){
        $req = $request->all();

        $validator = Validator::make($req, [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message'=> $validator->errors()->first(), 'data' => null ]);
        }
    }
}