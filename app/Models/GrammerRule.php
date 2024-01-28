<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrammerRule extends Model
{
    use HasFactory;
    protected $table = 'grammer_rules';
    protected $guarded = [];
}
