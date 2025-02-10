<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    public static function fetch(bool $all = true, bool $cast = false): array
    {
        $settings = Setting::query();
        if (!$all) {
            $settings = $settings->where('is_public' , '=' ,1);
        }
        $settings = $settings->get();
        $result = [];

        foreach ($settings as $setting) {
            if ($cast && ($setting->value == 1 || $setting->value == 0)) {
                $setting->value = intval($setting->value);
            }
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }

    public static function getItem(string $item)
    {
        $setting = Setting::where('key' , $item)->first();
        if (!$setting) {
            return null;
        }
        return $setting->value;
    }

    public static function setItem(string $item , $value)
    {
        $setting = self::getItem($item);
        if (!$setting) {
            $setting = new Setting();
            $setting->key = $item;
            $setting->value = $value;
            $setting->is_public = 0;
            $setting->save();
        } else {
            $setting = Setting::where('key' , $item)->first();
            $setting->value = $value;
            $setting->save();
        }
        return self::getItem($item);
    }
}
