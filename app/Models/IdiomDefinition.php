<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\IdiomDefinitionExample;

class IdiomDefinition extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'idiom_definitions';

    protected $with = ['idiom_definition_examples'];

    public function idiom_definition_examples()
    {
        return $this->hasMany(IdiomDefinitionExample::class);
    }
}
