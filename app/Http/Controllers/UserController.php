<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {
        $req = $request->all();

        $validator = Validator::make($req, [
            'email'     => 'required|email|exists:users',
            'password'  => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        $userPass            = DB::table('users')->select('password')->where('email', $req['email'])->first();
        $isPasswordVerified  = Hash::check($req['password'], $userPass->password);
        if ($isPasswordVerified) {
            $user = DB::table('users')->select('id_user', 'name', 'email')->where('email', $req['email'])->first();
            return response()->json(['status' => true, 'message' => 'Data user berhasil ditemukan', 'data' => $user]);
        } else {
            return response()->json(['status' => false, 'message' => "Email atau password anda salah", 'data' => null]);
        }
    }

    public function register(Request $request)
    {
        $req = $request->all();

        $validator = Validator::make($req, [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        // setUp Data
        $req['password']    = Hash::make($req['password']);
        $req['user_level']  = '2';
        $req['is_active']   = '0';
        $req['created_at']  = date("Y-m-d H:i:s");
        $req['updated_at']  = date("Y-m-d H:i:s");

        DB::table('users')->insert($req);
        return response()->json(['status' => true, 'message' => "Data berhasil ditambahkan", 'data' => null]);
    }

    public function setProfile(Request $request)
    {
        $req = $request->all();

        $validator = Validator::make($req, [
            'id_user'               => 'required|int|exists:users',
            'id_sekolah'            => 'required|int|exists:sekolah',
            'nama_lengkap'          => 'required',
            'foto_profil'           => 'nullable|file|mimes:jgp,jpeg,bmp,png|dimensions:max_width=512,max_height=512|max:1024',
            'alamat_sekolah'        => 'required',
            'nama_kepala_sekolah'   => 'required',
            'tambahan_informasi'    => 'nullable',
            'logo_sekolah'          => 'nullable|file|mimes:jgp,jpeg,bmp,png|dimensions:max_width=512,max_height=512|max:1024'
        ]);


        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        $profile = DB::table('profiles')->where('id_user', $req['id_user'])->first();

        if ($profile == null) { // profile isNotFound then insert

            $imageName = time() . '_' . $request->file('foto_profil')->getClientOriginalName();
            $request->file('foto_profil')->move('upload/fotoProfil', $imageName);
            $req['foto_profil'] = $imageName;

            $imageName = time() . '_' . $request->file('logo_sekolah')->getClientOriginalName();
            $request->file('logo_sekolah')->move('upload/logoSekolah', $imageName);
            $req['logo_sekolah'] = $imageName;

            $req['created_at'] = date('Y-m-d H:i:s');
            $req['updated_at'] = date('Y-m-d H:i:s');

            DB::table('profiles')->insert($req);
            return response()->json(['status' => true, 'message' => 'Data berhasil ditambah', 'data' => null]);
        } else { // profile isFound then update
            if ($request->has('foto_profil')) {
                File::delete(public_path('upload/fotoProfil/' . $profile->foto_profil));
                $imageName = time() . '_' . $request->file('foto_profil')->getClientOriginalName();
                $request->file('foto_profil')->move('upload/fotoProfil', $imageName);
                $req['foto_profil'] = $imageName;
            }

            if ($request->has('logo_sekolah')) {
                File::delete(public_path('upload/logoSekolah/' . $profile->logo_sekolah));
                $imageName = time() . '_' . $request->file('logo_sekolah')->getClientOriginalName();
                $request->file('logo_sekolah')->move('upload/logoSekolah', $imageName);
                $req['logo_sekolah'] = $imageName;
            }

            $req['updated_at'] = date('Y-m-d H:i:s');

            DB::table('profiles')->where('id_profile', $profile->id_profile)->update($req);

            return response()->json(['status' => true, 'message' => 'Data berhasil diubah', 'data' => null]);
        }
    }

    public function getProfile($id_user)
    {
        $req['id_user'] = $id_user;

        $validator = Validator::make($req, [
            'id_user' => 'required|int|exists:profiles'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        $profile = DB::table('v_profiles')->where($req)->first();
        return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $profile]);
    }
}
