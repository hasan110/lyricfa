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
    protected $appends = ['joins_count'];

    public function grammer()
    {
        return $this->belongsTo(Grammer::class, 'grammer_id');
    }

    public function grammer_explanations()
    {
        return $this->hasMany(GrammerExplanation::class, 'grammer_section_id')->orderBy('priority');
    }

    public function text_joins()
    {
        return $this->morphMany(TextJoin::class, 'joinable', 'joinable_type', 'joinable_id');
    }

    public function getJoinsCountAttribute()
    {
        return $this->text_joins()->count();
    }
}
