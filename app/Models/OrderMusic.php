<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class OrderMusic extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'music_orders';

    public function user(){
        return $this->belongsTo(User::class);
    }
}
