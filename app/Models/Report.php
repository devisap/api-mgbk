<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $table = "laporan";
    protected $primaryKey = "id_laporan";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable =
    [
        'id_user', 'id_sekolah', 'id_kegiatan', 'tgl_transaksi',
        'detail', 'upload_doc_1', 'upload_doc_2', 'upload_doc_3',
    ];
}
