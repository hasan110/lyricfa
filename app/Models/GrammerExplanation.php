<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrammerExplanation extends Model
{
    use HasFactory;
    protected $table = 'grammer_explanations';
    protected $guarded = [];
    protected $with = ['grammer_examples'];

    public function grammer()
    {
        return $this->belongsTo(Grammer::class, 'grammer_id');
    }

    public function grammer_section()
    {
        return $this->belongsTo(GrammerSection::class, 'grammer_section_id');
    }

    public function grammer_examples()
    {
        return $this->hasMany(GrammerExample::class, 'grammer_explanation_id');
    }
}
