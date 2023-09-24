<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WordEnEnDefinition;

class WordEnEn extends Model
{
    use HasFactory;
    protected $table = "english_words";
    protected $guarded = [];
    public $timestamps = false;

    public function english_word_definitions()
    {
        return $this->hasMany(WordEnEnDefinition::class , 'english_word_id');
    }
}
