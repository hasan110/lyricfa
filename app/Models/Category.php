<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class Category extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = [self::POSTER_FILE_TYPE,'permission_label','persian_created_at'];

    public const POSTER_FILE_TYPE = 'category_poster';

    protected $casts = [
        'is_parent' => 'integer',
        'status' => 'integer',
        'is_public' => 'integer',
        'need_level' => 'integer',
    ];

    public function getCategoryPosterAttribute()
    {
        $path = File::getFileUploadPath($this->files, self::POSTER_FILE_TYPE);
        if ($path) {
            return config('app.files_base_path') . $path;
        }
        return null;
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function grammers()
    {
        return $this->morphedByMany(Grammer::class, 'categorizeable');
    }

    public function musics()
    {
        return $this->morphedByMany(Music::class, 'categorizeable');
    }

    public function films()
    {
        return $this->morphedByMany(Film::class, 'categorizeable');
    }

    public function word_definitions()
    {
        return $this->morphedByMany(WordDefinition::class, 'categorizeable');
    }

    public function idiom_definitions()
    {
        return $this->morphedByMany(IdiomDefinition::class, 'categorizeable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable', 'commentable_type', 'commentable_id');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable', 'likeable_type', 'likeable_id');
    }

    public function views()
    {
        return $this->morphMany(View::class, 'viewable', 'viewable_type', 'viewable_id');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable', 'fileable_type', 'fileable_id');
    }

    public function getPermissionLabelAttribute()
    {
        if ($this->permission_type === 'paid') {
            return 'اشتراکی';
        }
        return null;
    }

    public function getPersianCreatedAtAttribute(): ?string
    {
        if ($this->created_at) {
            return Jalalian::forge($this->created_at)->addMinutes(3.5*60)->format('%Y-%m-%d H:i') . ' - ' . Jalalian::forge($this->created_at)->ago();
        }
        return null;
    }
}
