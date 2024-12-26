<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasFactory, HasApiTokens;

    protected $guarded = [];
    protected $table = 'users';

    public function learned_grammers()
    {
        return $this->belongsToMany(Grammer::class, 'grammer_learned', 'user_id', 'grammer_id');
    }
}
