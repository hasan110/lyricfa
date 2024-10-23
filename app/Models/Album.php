<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;
    protected $appends = [self::POSTER_FILE_TYPE];

    public const POSTER_FILE_TYPE = 'album_poster';

    public function singers()
    {
        return $this->belongsToMany(Singer::class, 'album_singer', 'album_id', 'singer_id');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable', 'fileable_type', 'fileable_id');
    }

    public function getAlbumPosterAttribute()
    {
        $path = File::getFileUploadPath($this->files, self::POSTER_FILE_TYPE);
        if ($path) {
            return config('app.files_base_path') . $path;
        }
        return null;
    }
}
