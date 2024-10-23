<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Singer extends Model
{
    use HasFactory;
    protected $table = 'singers';
    protected $guarded = [];
    protected $appends = [self::POSTER_FILE_TYPE];

    public const POSTER_FILE_TYPE = 'singer_poster';

    public function getSingerPosterAttribute()
    {
        $path = File::getFileUploadPath($this->files, self::POSTER_FILE_TYPE);
        if ($path) {
            return config('app.files_base_path') . $path;
        }
        return null;
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable', 'commentable_type', 'commentable_id');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable', 'likeable_type', 'likeable_id');
    }

    public function musics()
    {
        return $this->belongsToMany(Music::class, 'music_singer', 'singer_id', 'music_id');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable', 'fileable_type', 'fileable_id');
    }
}
