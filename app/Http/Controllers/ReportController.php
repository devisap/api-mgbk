<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use PDF;

class ReportController extends Controller
{
    /**
     * Store a new report.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $req = $request->all();

        $validator = Validator::make($req, [
            'id_user'       => 'required|int|exists:users',
            'id_sekolah'    => 'required|int|exists:sekolah',
            'id_kegiatan'   => 'required|int|exists:kegiatan',
            'tgl_transaksi' => 'required|date',
            'detail'        => 'required',
            'upload_doc_1'  => 'required|file|mimes:pdf,png,jpg,bmp|max:2048',
            'upload_doc_2'  => 'nullable|file|mimes:pdf,png,jpg,bmp|max:2048',
            'upload_doc_3'  => 'nullable|file|mimes:pdf,png,jpg,bmp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        if ($request->hasFile('upload_doc_1')) {
            $nama_doc_1 = time() . '_' . $request->file('upload_doc_1')->getClientOriginalName();

            $request->file('upload_doc_1')->move('upload/doc_1/' . $request->id_user, $nama_doc_1);
            $req['upload_doc_1'] = $nama_doc_1;
        }

        if ($request->hasFile('upload_doc_2')) {
            $nama_doc_2 = time() . '_' . $request->file('upload_doc_2')->getClientOriginalName();
            $request->file('upload_doc_2')->move('upload/doc_2/' . $request->id_user, $nama_doc_2);
            $req['upload_doc_2'] = $nama_doc_2;
        }

        if ($request->hasFile('upload_doc_3')) {
            $nama_doc_3 = time() . '_' . $request->file('upload_doc_3')->getClientOriginalName();
            $request->file('upload_doc_3')->move('upload/doc_3/' . $request->id_user, $nama_doc_3);
            $req['upload_doc_3'] = $nama_doc_3;
        }

        // setUp Data
        $req['created_at'] = date('Y-m-d H:i:s');
        $req['updated_at'] = date('Y-m-d H:i:s');
        DB::table('laporan')->insert($req);

        return response()->json(['status' => true, 'message' => 'Data berhasil ditambahkan', 'data' => null]);
    }

    public function loadWeeks(Request $request)
    {
        $weeks = DB::table('weeks')->where('year', $request->get('year'))
            ->pluck('week', 'id_week');

        return response()->json($weeks);
    }

    public function getReportByDate(Request $request, $tanggal)
    {
        $req['tgl_transaksi']   = $tanggal;
        $req['id_sekolah']      = $request->id_sekolah;
        $req['id_user']         = $request->id_user;

        $validator = Validator::make($req, [
            'tgl_transaksi' => 'required|date',
            'id_sekolah'    => 'required|int|exists:sekolah',
            'id_user'       => 'required|int|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        $reports = DB::table('v_laporan')->where('id_user', $req['id_user'])->where('id_sekolah', $req['id_sekolah'])->where('tgl_transaksi', $req['tgl_transaksi'])->get();
        if ($reports->count() > 0) {
            return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $reports]);
        } else {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan', 'data' => []]);
        }
    }

    public function printReportByDate(Request $request, $tanggal)
    {
        $req['tgl_transaksi']   = $tanggal;
        $req['id_sekolah']      = $request->id_sekolah;
        $req['id_user']         = $request->id_user;

        $validator = Validator::make($req, [
            'tgl_transaksi' => 'required|date',
            'id_sekolah'    => 'required|int|exists:sekolah',
            'id_user'       => 'required|int|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        $user           = DB::table('v_profiles')->where('id_user', $req['id_user'])->first();
        $filePath       = 'laporan/' . $user->name . '/harian';
        $fullFilePath   = 'laporan/' . $user->name . '/harian/LaporanHarian_' . $user->nama_lengkap . '_' . $req['tgl_transaksi'] . '.pdf';
        $isExist        = File::exists($filePath);
        if ($isExist == false) {
            File::makeDirectory($filePath, 0777, true, true);
        }

        // $reports = DB::table('laporan')
        //     ->leftJoin('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
        //     ->leftJoin('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
        //     ->leftJoin('profiles', 'laporan.id_user', '=', 'profiles.id_user')
        //     ->select('laporan.*', 'kegiatan.*', 'sekolah.nama_sekolah', 'profiles.nama_lengkap')
        //     ->where('laporan.id_user', $req['id_user'])
        //     ->where('laporan.id_sekolah', $req['id_sekolah'])
        //     ->where('laporan.tgl_transaksi', $req['tgl_transaksi'])
        //     ->get();

        $reports = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select('laporan.*', 'kegiatan.*', 'sekolah.nama_sekolah', 'profiles.*')
            ->where('profiles.id_user', $req['id_user'])
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah']);
        $reports->where('laporan.tgl_transaksi', $req['tgl_transaksi']);

        // return view('print.laporan.harian', compact('reports'));
        // $pdf = app()->make('dompdf.wrapper');
        $pdf = PDF::loadView('print.laporan.harian', compact('reports', 'user'));
        $pdf->setPaper('legal', 'potrait');
        $pdf->save($fullFilePath);
        return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $fullFilePath]);
        // return $pdf->stream();
        //return $pdf->download('laporan-harian.pdf');

    }

    public function printReportByWeek(Request $request)
    {
        $req['id_sekolah']      = $request->id_sekolah;
        $req['id_user']         = $request->id_user;

        $validator = Validator::make($req, [
            'id_sekolah'    => 'required|int|exists:sekolah',
            'id_user'       => 'required|int|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        $user           = DB::table('v_profiles')->where('id_user', $req['id_user'])->first();
        $filePath       = 'laporan/' . $user->name . '/mingguan';
        $fullFilePath   = 'laporan/' . $user->name . '/mingguan/LaporanMingguan_' . $user->nama_lengkap .  '.pdf';
        $isExist        = File::exists($filePath);
        if ($isExist == false) {
            File::makeDirectory($filePath, 0777, true, true);
        }

        $week = DB::table('weeks')
            ->where('id_week', $request->id_week)
            ->first();
        $tgl_awal  = date('Y-m-d', strtotime($week->start_date));
        $tgl_akhir = date('Y-m-d', strtotime($week->end_date));

        $reports = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select('laporan.*', 'kegiatan.*', 'sekolah.nama_sekolah', 'profiles.*')
            ->where('profiles.id_user', $req['id_user'])
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah']);
        $reports->whereBetween('tgl_transaksi', [$tgl_awal, $tgl_akhir]);

        $pdf = PDF::loadView('print.laporan.mingguan', compact('reports', 'user'));
        $pdf->setPaper('legal', 'potrait');
        $pdf->save($fullFilePath);
        return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $fullFilePath]);
    }

    public function printReportByMonth(Request $request)
    {
        $req['id_sekolah']      = $request->id_sekolah;
        $req['id_user']         = $request->id_user;

        $validator = Validator::make($req, [
            'id_sekolah'    => 'required|int|exists:sekolah',
            'id_user'       => 'required|int|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        $user           = DB::table('v_profiles')->where('id_user', $req['id_user'])->first();
        $filePath       = 'laporan/' . $user->name . '/bulanan';
        $fullFilePath   = 'laporan/' . $user->name . '/bulanan/LaporanBulanan_' . $user->nama_lengkap .  '.pdf';
        $isExist        = File::exists($filePath);
        if ($isExist == false) {
            File::makeDirectory($filePath, 0777, true, true);
        }

        $reports = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select('laporan.*', 'kegiatan.*', 'sekolah.nama_sekolah', 'profiles.*')
            ->where('profiles.id_user', $req['id_user'])
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah']);
        $reports->whereYear('tgl_transaksi', $request->year);
        $reports->whereMonth('tgl_transaksi', $request->month);

        $pdf = PDF::loadView('print.laporan.bulanan', compact('reports', 'user'));
        $pdf->setPaper('legal', 'potrait');
        $pdf->save($fullFilePath);
        return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $fullFilePath]);
    }

    public function printReportBySemester(Request $request)
    {
        $req['id_sekolah']      = $request->id_sekolah;
        $req['id_user']         = $request->id_user;

        $validator = Validator::make($req, [
            'id_sekolah'    => 'required|int|exists:sekolah',
            'id_user'       => 'required|int|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        $user           = DB::table('v_profiles')->where('id_user', $req['id_user'])->first();
        $filePath       = 'laporan/' . $user->name . '/semester';
        $fullFilePath   = 'laporan/' . $user->name . '/semester/LaporanSemester_' . $user->nama_lengkap .  '.pdf';
        $isExist        = File::exists($filePath);
        if ($isExist == false) {
            File::makeDirectory($filePath, 0777, true, true);
        }

        if ($request->semester == "1") {
            $start_date  =  date('Y-m-d', strtotime($request->year . "-01-01"));
            $end_date    =  date('Y-m-d', strtotime($request->year . "-06-30"));
        } else {
            $start_date  =  date('Y-m-d', strtotime($request->year . "-07-01"));
            $end_date    =  date('Y-m-d', strtotime($request->year . "-12-31"));
        }

        $reports = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select('laporan.*', 'kegiatan.*', 'sekolah.nama_sekolah', 'profiles.*')
            ->where('profiles.id_user', $req['id_user'])
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah']);
        $reports->whereYear('tgl_transaksi', $request->year);
        $reports->whereBetween('tgl_transaksi', [$start_date, $end_date]);

        $pdf = PDF::loadView('print.laporan.semesteran', compact('reports', 'user'));
        $pdf->setPaper('legal', 'potrait');
        $pdf->save($fullFilePath);
        return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $fullFilePath]);
    }

    public function printReportByYear(Request $request)
    {
        $req['id_sekolah']      = $request->id_sekolah;
        $req['id_user']         = $request->id_user;

        $validator = Validator::make($req, [
            'id_sekolah'    => 'required|int|exists:sekolah',
            'id_user'       => 'required|int|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        $user           = DB::table('v_profiles')->where('id_user', $req['id_user'])->first();
        $filePath       = 'laporan/' . $user->name . '/tahunan';
        $fullFilePath   = 'laporan/' . $user->name . '/tahunan/LaporanTahunan_' . $user->nama_lengkap .  '.pdf';
        $isExist        = File::exists($filePath);
        if ($isExist == false) {
            File::makeDirectory($filePath, 0777, true, true);
        }

        $reports = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select('laporan.*', 'kegiatan.*', 'sekolah.nama_sekolah', 'profiles.*')
            ->where('profiles.id_user', $req['id_user'])
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah']);
        $reports->whereYear('tgl_transaksi', $request->year);

        $pdf = PDF::loadView('print.laporan.tahunan', compact('reports', 'user'));
        $pdf->setPaper('legal', 'potrait');
        $pdf->save($fullFilePath);
        return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $fullFilePath]);
    }
}
