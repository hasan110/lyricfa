<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Morilog\Jalali\Jalalian;

class OrderMusic extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'music_orders';
    protected $appends = ['persian_created_at'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getPersianCreatedAtAttribute(): ?string
    {
        if ($this->created_at) {
            return Jalalian::forge($this->created_at)->addMinutes(3.5*60)->format('%Y-%m-%d H:i') . ' - ' . Jalalian::forge($this->created_at)->ago();
        }
        return null;
    }
}
