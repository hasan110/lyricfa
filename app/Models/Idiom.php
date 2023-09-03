<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\IdiomDefinition;

class Idiom extends Model
{
    use HasFactory;
    protected $table = 'idioms';

    public function idiom_definitions()
    {
        return $this->hasMany(IdiomDefinition::class);
    }
}
