<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrammerRule extends Model
{
    use HasFactory;
    protected $table = 'grammer_rules';
    protected $guarded = [];
    protected $casts = [
        'proccess_method' => 'integer',
    ];

    public function map_reason(){
        return $this->belongsTo(MapReason::class);
    }

    public function rule_group()
    {
        return $this->belongsToMany(GrammerRule::class, 'grammer_rule_group', 'grammer_rule_parent_id', 'grammer_rule_id');
    }
}
