<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;
    protected $appends = [self::BANNER_FILE_TYPE];

    public const BANNER_FILE_TYPE = 'slider_banner';

    protected $casts = [
        'id_singer_music_album' => 'integer',
        'type' => 'integer',
        'show_it' => 'integer',
    ];

    public function getSliderBannerAttribute()
    {
        $path = File::getFileUploadPath($this->files, self::BANNER_FILE_TYPE);
        if ($path) {
            return config('app.files_base_path') . $path;
        }
        return null;
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable', 'fileable_type', 'fileable_id');
    }
}
