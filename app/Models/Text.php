<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
    use HasFactory;
    protected $table = 'texts';
    protected $guarded = [];
    protected $appends = ['id_Music' , 'film_id'];

    public function textable()
    {
        return $this->morphTo();
    }

    public function getIdMusicAttribute()
    {
        return $this->textable_id;
    }

    public function getFilmIdAttribute()
    {
        return $this->textable_id;
    }
}
