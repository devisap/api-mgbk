<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian MGBK SMA Kota Malang</title>
    <style>
        
        
    </style> 
</head>

<body>

    <main>
        <div class="main-content remove-last-child">

            <table class="border w-100 border-collapse">
                <tr>
                    <th style="width: 10px;" class="text-align-left border-right border-bottom p-min">No</th>
                    <th style="width: 30%;" class="border-right border-bottom p-min">Jenis Kegiatan</th>
                    <th style="width: 40%;" class="border-right border-bottom p-min">Detail</th>
                    <th class="border-right border-bottom p-min">Jumlah Kegiatan</th>
                    <th class="border-bottom p-min">Ekuivalensi Jam</th>
                </tr>

                @if (count($reports) == 0)
                    <tr>
                        <td>No data found</td>
                    </tr>
                @else
                    @php
                        $totalJam = 0;
                    @endphp
                    @foreach($reports as $report)
                    @php
                        // $totalJam += $report->kegiatan()->sum('ekuivalen');
                        $totalJam += $report->jumlah_ekuivalen;
                    @endphp
                    <tr>
                        {{-- <td class="text-align-center border-right border-bottom p-min">{{ $loop->iteration }}</td>
                        <td class="text-align-left border-right border-bottom p-min">{{ $report->kegiatan->kegiatan }}</td>
                        <td class="text-align-justify border-right border-bottom p-min">{{ $report->detail }}</td>
                        <td class="text-align-center border-right border-bottom p-min">{{ $report->kegiatan()->count() }}</td>
                        <td class="text-align-center border-right border-bottom p-min">{{ $report->kegiatan()->sum('ekuivalen') }}</td> --}}

                        <td class="text-align-center box-decoration-break border-right border-top border-bottom p-min">{{ $loop->iteration }}</td>
                        <td class="text-align-left box-decoration-break border-right border-top border-bottom p-min">{{ $report->kegiatan }}</td>
                        <td class="text-align-justify box-decoration-break border-right border-top border-bottom p-min">{{ $report->detail }}</td>
                        <td class="text-align-center box-decoration-break border-right border-top border-bottom p-min">{{ $report->jumlah_kegiatan }}</td>
                        <td class="text-align-center box-decoration-break border-right border-top border-bottom p-min">{{ $report->jumlah_ekuivalen }}</td>
                    </tr>
                    @endforeach

                    <tr>
                        <th colspan="4" class="border-right border-bottom p-min">
                            Total Jam
                        </th>
                        <td class="text-align-center border-right border-bottom p-min">
                            {{ $totalJam }}
                        </td>
                    </tr>
                @endif
            </table>

        </div>
    </main>

</body>

</html>