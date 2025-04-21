<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    use HasFactory;
    protected $table = 'films';
    protected $appends = [self::POSTER_FILE_TYPE,self::SOURCE_FILE_TYPE,'permission_label'];

    public const POSTER_FILE_TYPE = 'film_poster';
    public const SOURCE_FILE_TYPE = 'film_source';

    protected $casts = [
        'type'=>'integer',
        'persian_subtitle'=>'integer',
        'status'=>'integer',
    ];

    public function texts()
    {
        return $this->morphMany(Text::class, 'textable', 'textable_type', 'textable_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable', 'commentable_type', 'commentable_id');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable', 'likeable_type', 'likeable_id');
    }

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

    public function getPermissionLabelAttribute()
    {
        if ($this->permission_type === 'free') {
            return 'رایگان';
        } else if ($this->permission_type === 'first_episode_free') {
            return 'قسمت اول رایگان';
        } else if ($this->permission_type === 'first_season_free') {
            return 'فصل اول رایگان';
        }
        return null;
    }

    public function categories()
    {
        return $this->morphToMany(Category::class, 'categorizeable');
    }
}
