<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextJoin extends Model
{
    use HasFactory;
    protected $table = 'text_joins';
    protected $guarded = [];
    protected $with = ['texts'];

    public function text()
    {
        return $this->belongsTo(Text::class, 'text_id');
    }

    public function joinable()
    {
        return $this->morphTo();
    }

    public function texts()
    {
        return $this->belongsToMany(Text::class, 'text_join_text', 'text_join_id', 'text_id');
    }
}
