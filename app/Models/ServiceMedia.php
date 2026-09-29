<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ServiceMedia extends Model
{
    protected $fillable = ['service_id', 'path', 'type', 'is_thumbnail', 'sort_order'];

    protected function casts(): array
    {
        return ['is_thumbnail' => 'boolean'];
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
