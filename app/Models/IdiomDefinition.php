<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\IdiomDefinitionExample;

class IdiomDefinition extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'idiom_definitions';
    protected $with = ['idiom_definition_examples'];
    protected $appends = [self::IMAGE_FILE_TYPE, 'joins_count', 'links'];

    public const IMAGE_FILE_TYPE = 'idiom_definition_image';

    public function idiom()
    {
        return $this->belongsTo(Idiom::class, 'idiom_id');
    }

    public function idiom_definition_examples()
    {
        return $this->hasMany(IdiomDefinitionExample::class);
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable', 'fileable_type', 'fileable_id');
    }

    public function text_joins()
    {
        return $this->morphMany(TextJoin::class, 'joinable', 'joinable_type', 'joinable_id');
    }

    public function categories()
    {
        return $this->morphToMany(Category::class, 'categorizeable');
    }

    public function getIdiomDefinitionImageAttribute()
    {
        $path = File::getFileUploadPath($this->files, self::IMAGE_FILE_TYPE);
        if ($path) {
            return config('app.files_base_path') . $path;
        }
        return null;
    }

    public function getJoinsCountAttribute()
    {
        return $this->text_joins()->count();
    }

    public function getLinksAttribute()
    {
        return Link::get_links($this->id , 'idiom_definition');
    }
}
