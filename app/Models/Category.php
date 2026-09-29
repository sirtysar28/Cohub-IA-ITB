<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['type', 'name', 'slug', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function scopeProperty($query)
    {
        return $query->where('type', 'property')->where('is_active', true);
    }

    public function scopeService($query)
    {
        return $query->where('type', 'service')->where('is_active', true);
    }
}
