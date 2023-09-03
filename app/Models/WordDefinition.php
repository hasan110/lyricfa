<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WordDefinitionExample;

class WordDefinition extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'word_definitions';

    protected $with = ['word_definition_examples'];

    public function word_definition_examples()
    {
        return $this->hasMany(WordDefinitionExample::class);
    }
}
