<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $appends = [self::IMAGE_FILE_TYPE];

    public const IMAGE_FILE_TYPE = 'notification_image';

    protected $casts = [
        'status_send' => 'integer',
    ];

    public function files()
    {
        return $this->morphMany(File::class, 'fileable', 'fileable_type', 'fileable_id');
    }

    public function getNotificationImageAttribute()
    {
        $path = File::getFileUploadPath($this->files, self::IMAGE_FILE_TYPE);
        if ($path) {
            return config('app.files_base_path') . $path;
        }
        return null;
    }
}
