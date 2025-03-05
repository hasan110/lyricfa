<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class View extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $with = ['user'];
    protected $appends = ['persian_created_at','persian_updated_at'];

    protected $casts = [
        'total' => 'integer',
        'progress' => 'integer',
        'percentage' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function viewable()
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

    public function getPersianUpdatedAtAttribute(): ?string
    {
        if ($this->updated_at) {
            return Jalalian::forge($this->updated_at)->addMinutes(3.5*60)->format('%Y-%m-%d H:i') . ' - ' . Jalalian::forge($this->updated_at)->ago();
        }
        return null;
    }
}
