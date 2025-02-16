<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Morilog\Jalali\Jalalian;

class User extends Model
{
    use HasFactory, HasApiTokens;

    protected $guarded = [];
    protected $table = 'users';
    protected $appends = ['persian_created_at'];

    public function getPersianCreatedAtAttribute(): ?string
    {
        if ($this->created_at) {
            return Jalalian::forge($this->created_at)->addMinutes(3.5*60)->format('%Y-%m-%d H:i') . ' - ' . Jalalian::forge($this->created_at)->ago();
        }
        return null;
    }

    public function learned_grammers()
    {
        return $this->belongsToMany(Grammer::class, 'grammer_learned', 'user_id', 'grammer_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id')->latest();
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'user_id')->latest();
    }

    public function music_orders()
    {
        return $this->hasMany(OrderMusic::class, 'user_id')->latest();
    }

    public function playlists()
    {
        return $this->hasMany(PlayList::class, 'user_id')->latest();
    }
}
