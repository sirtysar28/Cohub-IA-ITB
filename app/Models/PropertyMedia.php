<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PropertyMedia extends Model
{
    protected $fillable = ['property_id', 'path', 'type', 'is_thumbnail', 'sort_order'];

    protected function casts(): array
    {
        return ['is_thumbnail' => 'boolean'];
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
