<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Idiom extends Model
{
    use HasFactory;
    protected $table = 'idioms';
    protected $guarded = [];

    protected $casts = [
        'type' => 'integer',
    ];

    public function idiom_definitions()
    {
        return $this->hasMany(IdiomDefinition::class)->orderBy('priority');
    }
}
