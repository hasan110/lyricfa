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
    protected $appends = [self::IMAGE_FILE_TYPE];

    public const IMAGE_FILE_TYPE = 'idiom_definition_image';

    public function idiom_definition_examples()
    {
        return $this->hasMany(IdiomDefinitionExample::class);
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable', 'fileable_type', 'fileable_id');
    }

    public function getIdiomDefinitionImageAttribute()
    {
        $path = File::getFileUploadPath($this->files, self::IMAGE_FILE_TYPE);
        if ($path) {
            return config('app.files_base_path') . $path;
        }
        return null;
    }
}
