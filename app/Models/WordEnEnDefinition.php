<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordEnEnDefinition extends Model
{
    use HasFactory;
    protected $table = "english_word_definitions";
    protected $guarded = [];
}
