<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends Model
{
    use HasFactory;
    protected $guarded = [];

    public const TYPES = [
        Album::POSTER_FILE_TYPE,
        Film::POSTER_FILE_TYPE,
        Film::SOURCE_FILE_TYPE,
        Slider::BANNER_FILE_TYPE,
        Music::POSTER_FILE_TYPE,
        Music::SOURCE_FILE_TYPE,
        Singer::POSTER_FILE_TYPE,
        Notification::IMAGE_FILE_TYPE,
        WordDefinition::IMAGE_FILE_TYPE,
        IdiomDefinition::IMAGE_FILE_TYPE,
        Category::POSTER_FILE_TYPE,
    ];

    public const TYPES_PATH = [
        Album::POSTER_FILE_TYPE => 'albums',
        Film::POSTER_FILE_TYPE => 'films_banner',
        Film::SOURCE_FILE_TYPE => 'films',
        Slider::BANNER_FILE_TYPE => 'sliders',
        Music::POSTER_FILE_TYPE => 'musics_banner',
        Music::SOURCE_FILE_TYPE => 'musics/128',
        Singer::POSTER_FILE_TYPE => 'singers',
        Notification::IMAGE_FILE_TYPE => 'notifications',
        WordDefinition::IMAGE_FILE_TYPE => 'words',
        IdiomDefinition::IMAGE_FILE_TYPE => 'idioms',
        Category::POSTER_FILE_TYPE => 'categories',
    ];

    public function fileable()
    {
        return $this->morphTo();
    }

    public static function checkType(string $type): bool
    {
        return in_array($type, self::TYPES);
    }

    public static function getFileUploadPath($files , $type)
    {
        if (!self::checkType($type)) {
            return null;
        }

        foreach ($files as $file) {
            if ($file->type === $type) {
                return $file->upload_path;
            }
        }

        return null;
    }

    public static function createFile($file, $model, $type, $custom_name = false): ?string
    {
        if (!$file || !$model) return null;

        if (!self::checkType($type)) return null;

        $path = self::TYPES_PATH[$type];
        if ($custom_name) {
            $file_name = $model->id . '-' . date('Ymd') . '-' . Str::random(8) . '.' . $file->extension();
        } else {
            $file_name = $model->id . '.' .$file->getClientOriginalExtension();
        }
        if(config('app.deployed')){
            Storage::disk('ftp')->put('uploads/'. $path .'/'. $file_name, fopen($file, 'r+'));
        }else{
            $file_path = public_path().'/uploads/'.$path;
            \Illuminate\Support\Facades\File::ensureDirectoryExists($file_path);
            $file->move($file_path , $file_name);
        }

        $upload_path = 'uploads/'.$path .'/'. $file_name;
        $model->files()->create([
            'type'=>$type,
            'upload_path'=>$upload_path,
            'name'=>$file_name
        ]);

        return $upload_path;
    }

    public static function deleteFile($files , $type)
    {
        foreach ($files as $file) {
            if ($file->type === $type) {
                if(config('app.deployed')){
                    Storage::disk('ftp')->delete($file->upload_path);
                }else{
                    \Illuminate\Support\Facades\File::delete(public_path().'/'.$file->upload_path);
                }
                $file->delete();
            }
        }
        return true;
    }
}
