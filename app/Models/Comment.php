<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class Comment extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $with = ['user','commentable'];
    protected $appends = ['persian_created_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function getPersianCreatedAtAttribute(): ?string
    {
        if ($this->created_at) {
            return Jalalian::forge($this->created_at)->addMinutes(3.5*60)->format('%Y-%m-%d H:i') . ' - ' . Jalalian::forge($this->created_at)->ago();
        }
        return null;
    }
}
