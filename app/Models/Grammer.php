<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grammer extends Model
{
    use HasFactory;
    protected $table = 'grammers';
    protected $guarded = [];

    public function grammer_rules()
    {
        return $this->belongsToMany(GrammerRule::class, 'grammer_rule', 'grammer_id', 'grammer_rule_id')->withPivot('level')->orderBy('grammer_rule.level');
    }

    public function grammer_prerequisites()
    {
        return $this->belongsToMany(Grammer::class, 'grammer_prerequisite', 'grammer_id', 'grammer_prerequisite_id');
    }

    public function grammer_explanations()
    {
        return $this->hasMany(GrammerExplanation::class, 'grammer_id');
    }
}
