<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    use HasFactory;
    protected $table = 'musics';

    protected $casts = [
        'is_user_request' => 'integer',
        'status' => 'integer',
        'degree' => 'integer',
    ];

    public function text(){
        return $this->hasMany(Text::class, 'id_Music');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable', 'commentable_type', 'commentable_id');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable', 'likeable_type', 'likeable_id');
    }

    public function singers()
    {
        return $this->belongsToMany(Singer::class, 'music_singer', 'music_id', 'singer_id');
    }

    public function views()
    {
        return $this->morphMany(View::class, 'viewable', 'viewable_type', 'viewable_id');
    }
}
