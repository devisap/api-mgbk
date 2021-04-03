<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

        <title>Laporan Harian MGBK</title>
        <style type="text/css">
            /* * {
                font-family: Verdana, Arial, sans-serif;
            } */
            
            table{
                width:100%;
                font-size: x-small;
            }
            tfoot tr td{
                font-weight: bold;
                font-size: x-small;
            }
            table th,
            table td {
                vertical-align: top;
                padding: 5px;
            /* Apply cell padding */
            }
            .gray {
                background-color: lightgray
            }
            
        </style>
    </head>
    <body>
        <h3 class="card-title">list laporan harian</h3>
    
        <table class="table table-striped">
            <thead>
                <tr>
                <th scope="col">No</th>
                <th scope="col">Nama lengkap</th>
                <th scope="col">Nama sekolah</th>
                <th scope="col">Tanggal kegiatan</th>
                <th scope="col">Kegiatan</th>
                <th scope="col">Sasaran kegiatan</th>
                <th scope="col">Satuan kegiatan</th>
                <th scope="col">Uraian</th>
                <th scope="col">Pelaporan</th>
                <th scope="col">Durasi</th>
                <th scope="col">Satuan waktu</th>
                </tr>
            </thead>
            <tbody>

                @if (count($reports) == 0)
                    <tr>
                        <td>No data found</td>
                    </tr>
                @else

                    @foreach($reports as $report)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $report->nama_lengkap }}</td>
                        <td>{{ $report->nama_sekolah }}</td>
                        <td>{{ $report->tgl_transaksi }}</td>
                        <td>{{ $report->kegiatan }}</td>
                        <td>{{ $report->sasaran_kegiatan }}</td>
                        <td>{{ $report->satuan_kegiatan }}</td>
                        <td>{{ $report->uraian }}</td>
                        <td>{{ $report->pelaporan }}</td>
                        <td>{{ $report->durasi }}</td>
                        <td>{{ $report->satuan_waktu }}</td>
                    </tr>
                    @endforeach
                    
                @endif

            </tbody>
        </table>

    </body>
</html>