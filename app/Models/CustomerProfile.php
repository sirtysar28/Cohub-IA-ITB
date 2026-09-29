<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    protected $fillable = [
        'user_id', 'provinsi', 'kota_kabupaten', 'kecamatan',
        'kelurahan', 'kode_pos', 'alamat',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
