<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapReason extends Model
{
    use HasFactory;
    protected $table = 'map_reasons';
    protected $guarded = [];

    public function maps()
    {
        return $this->belongsToMany(Map::class, 'word_map_reason', 'map_reason_id', 'map_id');
    }
}
