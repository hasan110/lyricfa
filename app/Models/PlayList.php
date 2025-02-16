<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class PlayList extends Model
{
    use HasFactory;
    protected $table = 'playlists';
    protected $appends = ['persian_created_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function musics()
    {
        return $this->belongsToMany(Music::class, 'playlist_music', 'playlist_id', 'music_id');
    }

    public function getPersianCreatedAtAttribute(): ?string
    {
        if ($this->created_at) {
            return Jalalian::forge($this->created_at)->addMinutes(3.5*60)->format('%Y-%m-%d H:i') . ' - ' . Jalalian::forge($this->created_at)->ago();
        }
        return null;
    }
}
