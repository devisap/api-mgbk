<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian</title>
    <style>
        .w-100 {
            min-width: 100vw;
        }

        .p-min {
            padding: .2em;
        }

        .valign-middle {
            vertical-align: middle;
        }

        .p-max {
            padding: 2em;
        }

        .mb-max {
            margin-bottom: 3em;
        }

        .border-collapse {
            border-collapse: collapse;
        }

        .border {
            border: 1px solid black;
        }

        .border-right {
            border-right: 1px solid black;
        }

        .border-bottom {
            border-bottom: 1px solid black;
        }

        .text-title {
            font-size: 24px;
        }

        .text-regular {
            font-weight: 400;
        }

        .text-align-left {
            text-align: left;
        }
    </style>
</head>

<body>
    <table class="border w-100 p-max mb-max valign-middle">
        <tr>
            <th>
                <img src="{{ public_path('upload/logoSekolah/'.$user->logo_sekolah) }}" width="80" height="80">
            </th>
            <th>
                <span class="text-title">{{ $user->nama_sekolah }}</span><br>
                <span class="text-regular">{{ $user->alamat_sekolah }}</span><br>
                <span class="text-regular">{{ $user->tambahan_informasi }}</span><br>
                {{-- <span class="text-regular">Website: https://sman6malang.sch.id; E-mail: kontak@sman6malang.sch.id</span><br> --}}
            </th>
        </tr>
    </table>
    @php
        
        function tgl_indo($tanggal){
            $bulan = array (
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
            
            return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
        }

    $tgl = tgl_indo($reports[0]->tgl_transaksi);

    @endphp
    <table class="mb-max">
        <tr>
            <th class="text-align-left">
                Nama Guru
            </th>
            <td>
                {{-- : {{ $guru->profile->nama_lengkap }} --}}
                : {{ $user->nama_lengkap }}
            </td>
        </tr>
        <tr>
            <th class="text-align-left">
                Kelas yang diampuh
            </th>
            <td>
                {{-- : {{ $guru->profile->kelas_pengampu }} --}}
                : {{ $user->kelas_pengampu }}
            </td>
        </tr>
        <tr>
            <th class="text-align-left">
                Tanggal laporan
            </th>
            <td>
                : {{ $tgl }}
            </td>
        </tr>
    </table>

    <p>
        Berikut detail laporan dari Guru BK yang bersangkutan :
    </p>
    <table class="border w-100 border-collapse">

        <tr>
            <th class="text-align-left border-right border-bottom p-min">No</th>
            <th class="border-right border-bottom p-min">Jenis Kegiatan</th>
            <th class="border-bottom p-min">Detail</th>
        </tr>

        @if (count($reports) == 0)
            <tr>
                <td>No data found</td>
            </tr>
        @else
            @foreach($reports as $report)
            <tr>
                <td class="text-align-left border-right border-bottom p-min">{{ $loop->iteration }}</td>
                {{-- <td class="text-align-left border-right border-bottom p-min">{{ $report->kegiatan->kegiatan }}</td> --}}
                <td class="text-align-left border-right border-bottom p-min">{{ $report->kegiatan }}</td>
                <td class="text-align-left border-right border-bottom p-min">{{ $report->detail }}</td>
            </tr>
            @endforeach
        @endif

    </table>
</body>

</html>