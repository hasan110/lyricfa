<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Music;

class Text extends Model
{
    use HasFactory;
    protected $table = 'texts';

    public function music(){
        return $this->belongsTo(Music::class, 'id_Music');
    }

}
