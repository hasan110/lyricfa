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

    public static function getWordTypeDetails($type)
    {
        if (!$type) return null;

        $list = [
            'verb' => [
                'color' => '#1abc9c',
                'title' => 'فعل'
            ],
            'adverb' => [
                'color' => '#8e2eff',
                'title' => 'قید'
            ],
            'transitive-verb' => [
                'color' => '#1abc9c',
                'title' => 'فعل'
            ],
            'intransitive-verb' => [
                'color' => '#1abc9c',
                'title' => 'فعل'
            ],
            'adjective' => [
                'color' => '#2ee6ff',
                'title' => 'صفت'
            ],
            'noun' => [
                'color' => '#2980b9',
                'title' => 'اسم'
            ],
            'pronoun' => [
                'color' => '#ff802e',
                'title' => 'ضمیر'
            ],
            'plural' => [
                'color' => '#ff2e2e',
                'title' => 'جمع'
            ],
            'conjunction' => [
                'color' => '#7a3000',
                'title' => 'حرف ربط'
            ],
            'interjection' => [
                'color' => '#7a7200',
                'title' => 'حرف ندا'
            ],
            'determiner' => [
                'color' => '#333',
                'title' => 'تخصیص گر'
            ],
            'superlative' => [
                'color' => '#3f7a00',
                'title' => 'صفت عالی'
            ],
            'auxiliary' => [
                'color' => '#007a22',
                'title' => 'فعل کمکی'
            ],
            'singular' => [
                'color' => '#00767a',
                'title' => 'مفرد'
            ],
            'abbreviation' => [
                'color' => '#00437a',
                'title' => 'مخفف'
            ],
            'prefix' => [
                'color' => '#00057a',
                'title' => 'پیشوند'
            ],
            'suffix' => [
                'color' => '#2e007a',
                'title' => 'پسوند'
            ],
            'article' => [
                'color' => '#7a0041',
                'title' => 'حرف تعریف'
            ],
            'preposition' => [
                'color' => '#3498db',
                'title' => 'حرف اضافه'
            ],
        ];

        if (array_key_exists($type, $list)) {
            return $list[$type];
        }
        return null;
    }
}
