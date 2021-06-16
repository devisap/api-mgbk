<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
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
            'upload_doc_1'  => 'required',
            'upload_doc_2'  => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => null]);
        }

        // setUp Data
        $req['created_at'] = date('Y-m-d H:i:s');
        $req['updated_at'] = date('Y-m-d H:i:s');
        DB::table('laporan')->insert($req);

        return response()->json(['status' => true, 'message' => 'Data berhasil ditambahkan', 'data' => null]);
    }

    public function destroyReport(Request $request)
    {
        $report = DB::table('laporan')->where('id_laporan', $request->id_laporan)->get();
        $delete = DB::table('laporan')->where('id_laporan', $request->id_laporan)->delete();

        if ($report->count() > 0) {
            return response()->json(['status' => true, 'message' => 'Data berhasil dihapus', 'data' => $report]);
        } else {
            return response()->json(['status' => false, 'message' => 'Data gagal dihapus', 'data' => []]);
        }
    }

    public function loadWeeks(Request $request)
    {
        $weeks = DB::table('weeks')->where('year', $request->get('year'))->get();

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

    public function getReportByWeek(Request $request)
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

        $week = DB::table('weeks')
            ->where('id_week', $request->id_week)
            ->first();
        $tgl_awal  = date('Y-m-d', strtotime($week->start_date));
        $tgl_akhir = date('Y-m-d', strtotime($week->end_date));

        $laporan = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select('laporan.*', 'kegiatan.*', 'sekolah.nama_sekolah', 'profiles.*')
            ->where('profiles.id_user', $req['id_user'])
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah']);
        $laporan->whereBetween('tgl_transaksi', [$tgl_awal, $tgl_akhir]);
        $reports = $laporan->get();

        if ($reports->count() > 0) {
            return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $reports]);
        } else {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan', 'data' => []]);
        }
    }

    public function getReportByMonth(Request $request)
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

        $laporan = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
                DB::raw('COUNT(laporan.id_laporan) as jumlah_kegiatan'),
                DB::raw('SUM(ekuivalen) as jumlah_ekuivalen')
            )
            ->where('profiles.id_user', $req['id_user'])
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah'])
            ->groupBy(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
            )
            ->orderBy('laporan.id_laporan', 'desc');
        $laporan->whereYear('tgl_transaksi', $request->year);
        $laporan->whereMonth('tgl_transaksi', $request->month);
        $reports = $laporan->get();

        if ($reports->count() > 0) {
            return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $reports]);
        } else {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan', 'data' => []]);
        }
    }

    public function getReportBySemester(Request $request)
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

        if ($request->semester == "1") {
            $start_date  =  date('Y-m-d', strtotime($request->year . "-01-01"));
            $end_date    =  date('Y-m-d', strtotime($request->year . "-06-30"));
        } else {
            $start_date  =  date('Y-m-d', strtotime($request->year . "-07-01"));
            $end_date    =  date('Y-m-d', strtotime($request->year . "-12-31"));
        }

        $laporan = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
                DB::raw('COUNT(laporan.id_laporan) as jumlah_kegiatan'),
                DB::raw('SUM(ekuivalen) as jumlah_ekuivalen')
            )
            ->where('profiles.id_user', $req['id_user'])
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah'])
            ->groupBy(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
            )
            ->orderBy('laporan.id_laporan', 'desc');
        $laporan->whereYear('tgl_transaksi', $request->year);
        $laporan->whereBetween('tgl_transaksi', [$start_date, $end_date]);
        $reports = $laporan->get();

        if ($reports->count() > 0) {
            return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $reports]);
        } else {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan', 'data' => []]);
        }
    }

    public function getReportByYear(Request $request)
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

        $laporan = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
                DB::raw('COUNT(laporan.id_laporan) as jumlah_kegiatan'),
                DB::raw('SUM(ekuivalen) as jumlah_ekuivalen')
            )
            ->where('profiles.id_user', $req['id_user'])
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah'])
            ->groupBy(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
            )
            ->orderBy('laporan.id_laporan', 'desc');
        $laporan->whereYear('tgl_transaksi', $request->year);
        $reports = $laporan->get();

        if ($reports->count() > 0) {
            return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $reports]);
        } else {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan', 'data' => []]);
        }
    }

    private function tgl_indo($tanggal)
    {
        $bulan = array(
            1 =>   'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $pecahkan = explode('-', $tanggal);

        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }

    private function bln_indo($tanggal)
    {
        $bulan = array(
            1 =>   'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $pecahkan = explode('-', $tanggal);

        return $bulan[(int)$pecahkan[1]];
    }

    private function setHeaderFooter($mpdf, $guru, $table, $printBy, $reportTime = null, $withHeader)
    {
        $printTime  = $this->tgl_indo(date("Y-m-d"));

        if ($printBy == 'date') {
            $reportFor  = 'Tanggal laporan';
            $reportTime = $this->tgl_indo($guru->tgl_transaksi);
        } elseif ($printBy == 'week') {
            $reportFor  = 'Minggu ke';
            $reportTime = $reportTime->week;
        } elseif ($printBy == 'month') {
            $reportFor  = 'Bulan ke';
            $reportTime = $this->bln_indo($guru->tgl_transaksi);
        } elseif ($printBy == 'semester') {
            $reportFor  = 'Semester ke';
            $reportTime = $reportTime;
        } elseif ($printBy == 'year') {
            $reportFor  = 'Tahun ke';
            $reportTime = $reportTime;
        }

        $stylesheet = file_get_contents(public_path('css/mpdf.css'));
        $mpdf->WriteHTML($stylesheet, 1);

        if ($withHeader == 1) {
            // dengan header
            $mpdf->SetHTMLHeader('
            <table class="border w-100 p-max mb-max valign-middle">
                <tr>
                    <th>
                        <img src="https://api.mgbkkotamalang.my.id/upload/logoSekolah/' . $guru->logo_sekolah . '" width="80" height="80">
                    </th>
                    <th>
                        <span class="text-title">' . $guru->nama_sekolah . ' </span><br>
                        <span class="text-regular">' . $guru->alamat_sekolah . '</span><br>
                        <span class="text-regular">' . $guru->tambahan_informasi . '</span><br>
                    </th>
                </tr>
            </table>

            <table class="mb-max">
                <tr>
                    <th class="text-align-left" style="width:30%;">
                        Nama Guru
                    </th>
                    <td>
                        :  ' . $guru->nama_lengkap . '
                    </td>
                </tr>
                <tr>
                    <th class="text-align-left" style="width:30%;">
                        Kelas diampu
                    </th>
                    <td>
                        : 
                    </td>
                </tr>
                <tr>
                    <td colspan="2"> 
                        ' . $table . '
                    </td>
                </tr>
                <tr>
                    <th class="text-align-left" style="width:30%;">
                        ' . $reportFor . '
                    </th>
                    <td>
                        : ' . $reportTime . '
                    </td>
                </tr>
                <tr>
                    <th class="text-align-left" style="width:30%;">
                        Tanggal cetak laporan
                    </th>
                    <td>
                        : ' . $printTime . '
                    </td>
                </tr>
            </table>

            <p>
                Berikut detail laporan dari Guru BK yang bersangkutan :
            </p>');
        } else {
            // tanpa header
            $mpdf->SetHTMLHeader('
            <table class="mb-max">
                <tr>
                    <th class="text-align-left" style="width:30%;">
                        Nama Guru
                    </th>
                    <td>
                        :  ' . $guru->nama_lengkap . '
                    </td>
                </tr>
                <tr>
                    <th class="text-align-left" style="width:30%;">
                        Kelas diampu
                    </th>
                    <td>
                        : 
                    </td>
                </tr>
                <tr>
                    <td colspan="2"> 
                        ' . $table . '
                    </td>
                </tr>
                <tr>
                    <th class="text-align-left" style="width:30%;">
                        ' . $reportFor . '
                    </th>
                    <td>
                        : ' . $reportTime . '
                    </td>
                </tr>
                <tr>
                    <th class="text-align-left" style="width:30%;">
                        Tanggal cetak laporan
                    </th>
                    <td>
                        : ' . $printTime . '
                    </td>
                </tr>
            </table>

            <p>
                Berikut detail laporan dari Guru BK yang bersangkutan :
            </p>');
        }

        $mpdf->SetHTMLFooter('
        <table class="table-layout-fixed w-100">
            <tr>
                <td style="width:50%;">
                <table class="border-1 border-collapse table-layout-fixed" style="width: 400px;">
                    <tr>
                        <th class="border-1 p-min w-50">Mengetahui</th>
                    </tr>
                    <tr class="text-align-center">
                        <td class="border-1 p-min"><img style="visibility: hidden;" src="https://via.placeholder.com/100" width="100px" height="100px" /></td>
                    </tr>
                    <tr>
                        <td class="border-1 p-min text-align-center">' . $guru->nama_kepala_sekolah . '</td>
                    </tr>
                    <tr>
                        <td class="border-1 p-min text-align-center">Kepala Sekolah</td>
                    </tr>
                </table>
                </td>
                <td style="width:50%">
                <table class="border-1 border-collapse table-layout-fixed" style="width: 400px;">
                    <tr>
                        <th class="border-1 p-min w-50">Dibuat</th>
                    </tr>
                    <tr class="text-align-center">
                        <td class="border-1 p-min"><img style="visibility: hidden;" src="https://via.placeholder.com/100" width="100px" height="100px" /></td>
                    </tr>
                    <tr>
                        <td class="border-1 p-min text-align-center">' . $guru->nama_lengkap . '</td>
                    </tr>
                    <tr>
                        <td class="border-1 p-min text-align-center">Guru BK</td>
                    </tr>
                </table>
                </td>
            </tr>
        </table>
        ');
    }

    public function printReportByDate(Request $request, $tanggal)
    {
        $req['tgl_transaksi']   = $tanggal;
        $req['id_sekolah']      = $request->id_sekolah;
        $req['id_user']         = $request->id_user;
        $withHeader             = $request->withHeader;

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

        $laporan = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'laporan.tgl_transaksi',
                'laporan.detail',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.nama_kepala_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
                DB::raw('COUNT(laporan.id_laporan) as jumlah_kegiatan'),
                DB::raw('SUM(ekuivalen) as jumlah_ekuivalen')
            )
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah'])
            ->groupBy(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'laporan.tgl_transaksi',
                'laporan.detail',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.nama_kepala_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
            )
            ->orderBy('laporan.id_laporan', 'ASC');
        $laporan->where('laporan.tgl_transaksi', $req['tgl_transaksi']);
        $reports    = $laporan->get();
        $guru       = $laporan->first();
        // dd($reports);

        // return view('print.laporan.harian', compact('reports', 'user'));
        // $pdf = app()->make('dompdf.wrapper');
        // return $pdf->stream();
        // return $pdf->download('laporan-harian.pdf');

        if ($reports->count() < 1) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan', 'data' => []]);
        } else {
            // return view('print.laporan.harian', compact('reports', 'user'));
            // $pdf = PDF::loadView('print.laporan.harian', compact('reports', 'user'));
            // $pdf->setPaper('legal', 'potrait');
            // $pdf->save($fullFilePath);

            $namaLengkap = $guru->nama_lengkap;

            $kelas      = $guru->kelas_pengampu;
            $eachkelas  = explode(";", $kelas);

            $table = '<table style="width: 50%;"><tr>';
            $td = '<td style="width:50%;">';
            $ul = '<ul>';
            $no = 0;
            $marginTop = 95;
            $marginBot = 45;

            foreach ($eachkelas as $item) :
                $marginTop = $marginTop + 5;
                $marginBot = $marginBot + 5;

                if ($no == ($no % 4 == 0)) {
                    $ul .= '</ul></td>';

                    $ul .= $td . '<ul>';
                    $marginTop = 90;
                    $marginBot = 50;
                }
                $ul .= '<li>' . $item . '</li>';

                $no++;
                if ($no > 4) {
                    $marginTop = 105;
                    $marginBot = 65;
                }
            endforeach;

            $ul .= '</ul>';
            $td .= $ul . '</td>';
            $table .= $td . '</tr></table>';

            $mpdf = new \Mpdf\Mpdf();

            $filename   = 'laporan-harian' . $namaLengkap . '.pdf';
            $mpdf       = new \Mpdf\Mpdf([
                'margin_left'   => 10,
                'margin_right'  => 10,
                'margin_top'    => $marginTop,
                'margin_bottom' => $marginBot,
                'margin_header' => 10,
                'margin_footer' => 10,
            ]);

            $html   = View::make('print.laporan.harian')->with('reports', $reports);
            $html   = $html->render();

            $this->setHeaderFooter($mpdf, $guru, $table, 'date', null, $withHeader);

            $mpdf->autoPageBreak = true;
            $mpdf->WriteHTML($html);
            $mpdf->Output($fullFilePath, 'F');

            return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $fullFilePath]);
        }
    }

    public function printReportByWeek(Request $request)
    {
        $req['id_sekolah']      = $request->id_sekolah;
        $req['id_user']         = $request->id_user;
        $withHeader             = $request->withHeader;

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

        $laporan = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'laporan.tgl_transaksi',
                'laporan.detail',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.nama_kepala_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
                DB::raw('COUNT(laporan.id_laporan) as jumlah_kegiatan'),
                DB::raw('SUM(ekuivalen) as jumlah_ekuivalen')
            )
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah'])
            ->groupBy(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'laporan.tgl_transaksi',
                'laporan.detail',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.nama_kepala_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
            )
            ->orderBy('laporan.id_laporan', 'ASC');
        $laporan->whereBetween('tgl_transaksi', [$tgl_awal, $tgl_akhir]);
        $reports    = $laporan->get();
        $guru       = $laporan->first();

        if ($reports->count() < 1) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan', 'data' => []]);
        } else {
            // return view('print.laporan.mingguan', compact('reports', 'user', 'week'));
            // $pdf = PDF::loadView('print.laporan.mingguan', compact('reports', 'user', 'week'));
            // $pdf->setPaper('legal', 'potrait');
            // $pdf->save($fullFilePath);

            $namaLengkap = $guru->nama_lengkap;

            $kelas      = $guru->kelas_pengampu;
            $eachkelas  = explode(";", $kelas);

            $table = '<table style="width: 50%;"><tr>';
            $td = '<td style="width:50%;">';
            $ul = '<ul>';
            $no = 0;
            $marginTop = 95;
            $marginBot = 45;

            foreach ($eachkelas as $item) :
                $marginTop = $marginTop + 5;
                $marginBot = $marginBot + 5;

                if ($no == ($no % 4 == 0)) {
                    $ul .= '</ul></td>';

                    $ul .= $td . '<ul>';
                    $marginTop = 90;
                    $marginBot = 50;
                }
                $ul .= '<li>' . $item . '</li>';

                $no++;
                if ($no > 4) {
                    $marginTop = 105;
                    $marginBot = 65;
                }
            endforeach;

            $ul .= '</ul>';
            $td .= $ul . '</td>';
            $table .= $td . '</tr></table>';

            $mpdf = new \Mpdf\Mpdf();

            $filename   = 'laporan-mingguan' . $namaLengkap . '.pdf';
            $mpdf       = new \Mpdf\Mpdf([
                'margin_left'   => 10,
                'margin_right'  => 10,
                'margin_top'    => $marginTop,
                'margin_bottom' => $marginBot,
                'margin_header' => 10,
                'margin_footer' => 10,
            ]);

            $html   = View::make('print.laporan.mingguan')->with('reports', $reports);
            $html   = $html->render();

            $this->setHeaderFooter($mpdf, $guru, $table, 'week', $week, $withHeader);

            $mpdf->autoPageBreak = true;
            $mpdf->WriteHTML($html);
            $mpdf->Output($fullFilePath, 'F');

            return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $fullFilePath]);
        }
    }

    public function printReportByMonth(Request $request)
    {
        $req['id_sekolah']      = $request->id_sekolah;
        $req['id_user']         = $request->id_user;
        $withHeader             = $request->withHeader;

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

        $laporan = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'laporan.tgl_transaksi',
                'laporan.detail',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.nama_kepala_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
                DB::raw('COUNT(laporan.id_laporan) as jumlah_kegiatan'),
                DB::raw('SUM(ekuivalen) as jumlah_ekuivalen')
            )
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah'])
            ->groupBy(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'laporan.tgl_transaksi',
                'laporan.detail',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.nama_kepala_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
            )
            ->orderBy('laporan.id_laporan', 'ASC');
        $laporan->whereYear('tgl_transaksi', $request->year);
        $laporan->whereMonth('tgl_transaksi', $request->month);
        $reports    = $laporan->get();
        $guru       = $laporan->first();
        // dd($guru);

        if ($reports->count() < 1) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan', 'data' => []]);
        } else {
            // return view('print.laporan.bulanan', compact('reports', 'user'));
            // $pdf = PDF::loadView('print.laporan.bulanan', compact('reports', 'user'));
            // $pdf->setPaper('legal', 'potrait');
            // $pdf->save($fullFilePath);

            $namaLengkap = $guru->nama_lengkap;

            $kelas      = $guru->kelas_pengampu;
            $eachkelas  = explode(";", $kelas);

            $table = '<table style="width: 50%;"><tr>';
            $td = '<td style="width:50%;">';
            $ul = '<ul>';
            $no = 0;
            $marginTop = 95;
            $marginBot = 45;

            foreach ($eachkelas as $item) :
                $marginTop = $marginTop + 5;
                $marginBot = $marginBot + 5;

                if ($no == ($no % 4 == 0)) {
                    $ul .= '</ul></td>';

                    $ul .= $td . '<ul>';
                    $marginTop = 90;
                    $marginBot = 50;
                }
                $ul .= '<li>' . $item . '</li>';

                $no++;
                if ($no > 4) {
                    $marginTop = 105;
                    $marginBot = 65;
                }
            endforeach;

            $ul .= '</ul>';
            $td .= $ul . '</td>';
            $table .= $td . '</tr></table>';

            $mpdf = new \Mpdf\Mpdf();

            $filename   = 'laporan-bulanan' . $namaLengkap . '.pdf';
            $mpdf       = new \Mpdf\Mpdf([
                'margin_left'   => 10,
                'margin_right'  => 10,
                'margin_top'    => $marginTop,
                'margin_bottom' => $marginBot,
                'margin_header' => 10,
                'margin_footer' => 10,
            ]);

            $html   = View::make('print.laporan.bulanan')->with('reports', $reports);
            $html   = $html->render();

            $this->setHeaderFooter($mpdf, $guru, $table, 'month', null, $withHeader);

            $mpdf->autoPageBreak = true;
            $mpdf->WriteHTML($html);
            $mpdf->Output($fullFilePath, 'F');

            return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $fullFilePath]);
        }
    }

    public function printReportBySemester(Request $request)
    {
        $req['id_sekolah']      = $request->id_sekolah;
        $req['id_user']         = $request->id_user;
        $withHeader             = $request->withHeader;

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

        $laporan = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'laporan.tgl_transaksi',
                'laporan.detail',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.nama_kepala_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
                DB::raw('COUNT(laporan.id_laporan) as jumlah_kegiatan'),
                DB::raw('SUM(ekuivalen) as jumlah_ekuivalen')
            )
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah'])
            ->groupBy(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'laporan.tgl_transaksi',
                'laporan.detail',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.nama_kepala_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
            )
            ->orderBy('laporan.id_laporan', 'ASC');
        $laporan->whereYear('tgl_transaksi', $request->year);
        $laporan->whereBetween('tgl_transaksi', [$start_date, $end_date]);
        $reports    = $laporan->get();
        $guru       = $laporan->first();

        $semester = $request->semester;

        if ($reports->count() < 1) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan', 'data' => []]);
        } else {
            // return view('print.laporan.semesteran', compact('reports', 'user', 'semester'));
            // $pdf = PDF::loadView('print.laporan.semesteran', compact('reports', 'user', 'semester'));
            // $pdf->setPaper('legal', 'potrait');
            // $pdf->save($fullFilePath);

            $namaLengkap = $guru->nama_lengkap;

            $kelas      = $guru->kelas_pengampu;
            $eachkelas  = explode(";", $kelas);

            $table = '<table style="width: 50%;"><tr>';
            $td = '<td style="width:50%;">';
            $ul = '<ul>';
            $no = 0;
            $marginTop = 95;
            $marginBot = 45;

            foreach ($eachkelas as $item) :
                $marginTop = $marginTop + 5;
                $marginBot = $marginBot + 5;

                if ($no == ($no % 4 == 0)) {
                    $ul .= '</ul></td>';

                    $ul .= $td . '<ul>';
                    $marginTop = 90;
                    $marginBot = 50;
                }
                $ul .= '<li>' . $item . '</li>';

                $no++;
                if ($no > 4) {
                    $marginTop = 105;
                    $marginBot = 65;
                }
            endforeach;

            $ul .= '</ul>';
            $td .= $ul . '</td>';
            $table .= $td . '</tr></table>';

            $mpdf = new \Mpdf\Mpdf();

            $filename   = 'laporan-semesteran' . $namaLengkap . '.pdf';
            $mpdf       = new \Mpdf\Mpdf([
                'margin_left'   => 10,
                'margin_right'  => 10,
                'margin_top'    => $marginTop,
                'margin_bottom' => $marginBot,
                'margin_header' => 10,
                'margin_footer' => 10,
            ]);

            $html   = View::make('print.laporan.semesteran')->with('reports', $reports);
            $html   = $html->render();

            $this->setHeaderFooter($mpdf, $guru, $table, 'semester', $semester, $withHeader);

            $mpdf->autoPageBreak = true;
            $mpdf->WriteHTML($html);
            $mpdf->Output($fullFilePath, 'F');

            return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $fullFilePath]);
        }
    }

    public function printReportByYear(Request $request)
    {
        $req['id_sekolah']      = $request->id_sekolah;
        $req['id_user']         = $request->id_user;
        $withHeader             = $request->withHeader;

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

        $laporan = DB::table('laporan')
            ->Join('kegiatan', 'laporan.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->Join('sekolah', 'laporan.id_sekolah', '=', 'sekolah.id_sekolah')
            ->Join('users', 'laporan.id_user', '=', 'users.id_user')
            ->Join('profiles', 'users.id_user', '=', 'profiles.id_user')
            ->select(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'laporan.tgl_transaksi',
                'laporan.detail',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.nama_kepala_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
                DB::raw('COUNT(laporan.id_laporan) as jumlah_kegiatan'),
                DB::raw('SUM(ekuivalen) as jumlah_ekuivalen')
            )
            ->where('laporan.id_user', $req['id_user'])
            ->where('laporan.id_sekolah', $req['id_sekolah'])
            ->groupBy(
                'laporan.id_user',
                'laporan.id_kegiatan',
                'laporan.tgl_transaksi',
                'laporan.detail',
                'kegiatan.id_kegiatan',
                'kegiatan.kegiatan',
                'sekolah.nama_sekolah',
                'profiles.nama_lengkap',
                'profiles.logo_sekolah',
                'profiles.alamat_sekolah',
                'profiles.nama_kepala_sekolah',
                'profiles.tambahan_informasi',
                'profiles.kelas_pengampu',
            )
            ->orderBy('laporan.id_laporan', 'ASC');
        $laporan->whereYear('tgl_transaksi', $request->year);
        $reports    = $laporan->get();
        $guru       = $laporan->first();

        $year = $request->year;

        if ($reports->count() < 1) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan', 'data' => []]);
        } else {
            // return view('print.laporan.tahunan', compact('reports', 'user', 'year'));
            // $pdf = PDF::loadView('print.laporan.tahunan', compact('reports', 'user', 'year'));
            // $pdf->setPaper('legal', 'potrait');
            // $pdf->save($fullFilePath);

            $namaLengkap = $guru->nama_lengkap;

            // $kelas       = $guru->user->profile->kelas_pengampu;
            $kelas      = $guru->kelas_pengampu;
            $eachkelas  = explode(";", $kelas);

            $table = '<table style="width: 50%;"><tr>';
            $td = '<td style="width:50%;">';
            $ul = '<ul>';
            $no = 0;
            $marginTop = 95;
            $marginBot = 45;

            foreach ($eachkelas as $item) :
                $marginTop = $marginTop + 5;
                $marginBot = $marginBot + 5;

                if ($no == ($no % 4 == 0)) {
                    $ul .= '</ul></td>';

                    $ul .= $td . '<ul>';
                    $marginTop = 90;
                    $marginBot = 50;
                }
                $ul .= '<li>' . $item . '</li>';

                $no++;
                if ($no > 4) {
                    $marginTop = 105;
                    $marginBot = 65;
                }
            endforeach;

            $ul .= '</ul>';
            $td .= $ul . '</td>';
            $table .= $td . '</tr></table>';

            $mpdf = new \Mpdf\Mpdf();

            $filename   = 'laporan-tahunan' . $namaLengkap . '.pdf';
            $mpdf       = new \Mpdf\Mpdf([
                'margin_left'   => 10,
                'margin_right'  => 10,
                'margin_top'    => $marginTop,
                'margin_bottom' => $marginBot,
                'margin_header' => 10,
                'margin_footer' => 10,
            ]);

            $html   = View::make('print.laporan.tahunan')->with('reports', $reports);
            $html   = $html->render();

            $this->setHeaderFooter($mpdf, $guru, $table, 'year', $year, $withHeader);

            $mpdf->autoPageBreak = true;
            $mpdf->WriteHTML($html);
            $mpdf->Output($fullFilePath, 'F');

            return response()->json(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $fullFilePath]);
        }
    }
}
