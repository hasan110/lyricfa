<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    use HasFactory;
    protected $table = 'musics';
    protected $appends = [self::POSTER_FILE_TYPE,self::SOURCE_FILE_TYPE,'permission_label'];

    public const POSTER_FILE_TYPE = 'music_poster';
    public const SOURCE_FILE_TYPE = 'music_source';

    protected $casts = [
        'is_user_request' => 'integer',
        'status' => 'integer',
        'degree' => 'integer',
        'views' => 'integer',
        'start_demo' => 'integer',
        'end_demo' => 'integer',
    ];

    public function getMusicPosterAttribute()
    {
        $path = File::getFileUploadPath($this->files, self::POSTER_FILE_TYPE);
        if ($path) {
            return config('app.files_base_path') . $path;
        }
        return null;
    }

    public function getMusicSourceAttribute()
    {
        $path = File::getFileUploadPath($this->files, self::SOURCE_FILE_TYPE);
        if ($path) {
            return config('app.files_base_path') . $path;
        }
        return null;
    }

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

    public function singers()
    {
        return $this->belongsToMany(Singer::class, 'music_singer', 'music_id', 'singer_id');
    }

    public function views()
    {
        return $this->morphMany(View::class, 'viewable', 'viewable_type', 'viewable_id');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable', 'fileable_type', 'fileable_id');
    }

    public function categories()
    {
        return $this->morphToMany(Category::class, 'categorizeable');
    }

    public function getPermissionLabelAttribute()
    {
        if ($this->permission_type === 'free') {
            return 'رایگان';
        }
        return null;
    }
}
