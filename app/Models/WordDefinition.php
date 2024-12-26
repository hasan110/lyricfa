<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WordDefinitionExample;

class WordDefinition extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'word_definitions';
    protected $appends = [self::IMAGE_FILE_TYPE, 'joins_count'];

    public const IMAGE_FILE_TYPE = 'word_definition_image';

    protected $with = ['word_definition_examples'];

    public function word_definition_examples()
    {
        return $this->hasMany(WordDefinitionExample::class);
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable', 'fileable_type', 'fileable_id');
    }

    public function text_joins()
    {
        return $this->morphMany(TextJoin::class, 'joinable', 'joinable_type', 'joinable_id');
    }

    public function getWordDefinitionImageAttribute()
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
}
