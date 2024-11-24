<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrammerSection extends Model
{
    use HasFactory;
    protected $table = 'grammer_sections';
    protected $guarded = [];
    protected $with = ['grammer_explanations'];

    public function grammer()
    {
        return $this->belongsTo(Grammer::class, 'grammer_id');
    }

    public function grammer_explanations()
    {
        return $this->hasMany(GrammerExplanation::class, 'grammer_section_id')->orderBy('priority');
    }
}
