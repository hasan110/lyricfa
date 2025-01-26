<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WordDefinition;

class Word extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = "words";
    public $timestamps = false;

    public function word_definitions()
    {
        return $this->hasMany(WordDefinition::class)->orderBy("priority");
    }

    public static function getWordTypes()
    {
        return [
            'verb',
            'adverb',
            'transitive-verb',
            'intransitive-verb',
            'adjective',
            'noun',
            'pronoun',
            'plural',
            'conjunction',
            'interjection',
            'determiner',
            'superlative',
            'auxiliary',
            'singular',
            'abbreviation',
            'prefix',
            'suffix',
            'article',
            'preposition',
        ];
    }
}
