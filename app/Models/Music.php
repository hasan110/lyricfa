<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    use HasFactory;
    protected $table = 'musics';

    protected $casts = [
        'degree' => 'integer',
        'is_user_request' => 'integer',
    ];

    public function text(){
        return $this->hasMany(Text::class, 'id_Music');
    }
}
