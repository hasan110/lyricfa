<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    use HasFactory;
    protected $table = 'maps';
    protected $guarded = [];

    public function map_reasons()
    {
        return $this->belongsToMany(MapReason::class, 'word_map_reason', 'map_id', 'map_reason_id');
    }
}
