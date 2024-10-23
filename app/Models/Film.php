<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    use HasFactory;
    protected $table = 'films';
    protected $appends = [self::POSTER_FILE_TYPE,self::SOURCE_FILE_TYPE];

    public const POSTER_FILE_TYPE = 'film_poster';
    public const SOURCE_FILE_TYPE = 'film_source';

    protected $casts = [
        'type'=>'integer',
        'persian_subtitle'=>'integer',
        'status'=>'integer',
    ];

    public function files()
    {
        return $this->morphMany(File::class, 'fileable', 'fileable_type', 'fileable_id');
    }

    public function getFilmPosterAttribute()
    {
        $path = File::getFileUploadPath($this->files, self::POSTER_FILE_TYPE);
        if ($path) {
            return config('app.files_base_path') . $path;
        }
        return null;
    }

    public function getFilmSourceAttribute()
    {
        $path = File::getFileUploadPath($this->files, self::SOURCE_FILE_TYPE);
        if ($path) {
            return config('app.files_base_path') . $path;
        }
        return null;
    }
}
